<?php

namespace App\Traits;

use App\Models\Arsip;
use App\Models\ArsipApproval;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait HandlesApproval
{
    /**
     * Halaman "Persetujuan Saya": pengajuan yang tahap aktifnya menunggu user ini.
     */
    public function myApprovals(Request $request)
    {
        $user = auth()->user();

        $arsips = Arsip::with(['approvals.approver', 'admin', 'department', 'unit'])
            ->whereHas('approvals', function ($q) use ($user) {
                $q->where('status', 'pending');
                if ($user->role === 'superadmin') {
                    $q->where(function ($x) use ($user) {
                        $x->where('approver_id', $user->id)
                          ->orWhere('role_label', ArsipApproval::FINAL_ROLE);
                    });
                } else {
                    $q->where('approver_id', $user->id);
                }
            })
            ->latest('id')
            ->get()
            ->filter(function ($a) use ($user) {
                $cur = $a->currentApproval();
                if (!$cur) {
                    return false;
                }
                if ($user->role === 'superadmin') {
                    return (int) $cur->approver_id === (int) $user->id
                        || $cur->role_label === ArsipApproval::FINAL_ROLE;
                }
                return (int) $cur->approver_id === (int) $user->id;
            })
            ->values();

        return view('approvals.index', ['arsips' => $arsips]);
    }

    /**
     * Setujui tahap approval saat ini (sekaligus stempel TTD digital approver).
     * Membutuhkan trait SignsArsip (applySignature).
     */
    public function approveArsip(Request $request, $id)
    {
        $arsip = Arsip::with(['approvals'])->findOrFail($id);
        $user = auth()->user();

        $step = $arsip->currentApproval();
        if (!$step) {
            return back()->with('error', 'Tidak ada tahap approval yang menunggu.');
        }

        if (!$this->canActOnStep($step, $user)) {
            return back()->with('error', 'Tahap ini bukan giliran Anda.');
        }

        if (!$user->hasSignature()) {
            return back()->with('error', 'Anda belum mengatur specimen tanda tangan di Profil.');
        }

        DB::transaction(function () use ($arsip, $step, $user, $request) {
            // Stempel TTD sesuai peran tahap (Pemohon/SPV/Kabag/Manager/Accounting/Departemen IT)
            $this->applySignature($arsip, $user, $step->role_label, $request->input('note'), $request->ip());

            $step->update([
                'status'   => 'approved',
                'acted_by' => $user->id,
                'acted_at' => now(),
                'note'     => $request->input('note'),
            ]);

            $arsip->load('approvals');
            $next = $arsip->currentApproval();

            if ($next) {
                $this->notifyApprover($arsip, $next);
            } else {
                // Semua tahap selesai → tandai Done
                $arsip->update([
                    'status'      => 'Done',
                    'ket_process' => 'Done',
                    'ba'          => 'Done',
                    'arsip'       => $arsip->arsip === 'Done' ? 'Done' : 'Process',
                    'updated_by'  => $user->id,
                ]);
                Notification::create([
                    'user_id'     => $arsip->admin_id,
                    'role_target' => 'admin',
                    'arsip_id'    => $arsip->id,
                    'title'       => 'Pengajuan Disetujui Penuh',
                    'message'     => "Pengajuan {$arsip->no_registrasi} telah disetujui seluruh approver (final: Departemen IT).",
                    'is_read'     => false,
                ]);
            }
        });

        return back()->with('success', "Tahap {$step->role_label} berhasil disetujui & ditandatangani.");
    }

    /**
     * Tolak tahap approval saat ini → pengajuan dikembalikan ke pengaju.
     */
    public function rejectArsip(Request $request, $id)
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        $arsip = Arsip::with(['approvals'])->findOrFail($id);
        $user = auth()->user();

        $step = $arsip->currentApproval();
        if (!$step) {
            return back()->with('error', 'Tidak ada tahap approval yang menunggu.');
        }
        if (!$this->canActOnStep($step, $user)) {
            return back()->with('error', 'Tahap ini bukan giliran Anda.');
        }

        DB::transaction(function () use ($arsip, $step, $user, $request) {
            $step->update([
                'status'   => 'rejected',
                'acted_by' => $user->id,
                'acted_at' => now(),
                'note'     => $request->input('note'),
            ]);

            $arsip->update([
                'status'      => 'Reject',
                'ket_process' => 'Void',
                'updated_by'  => $user->id,
            ]);

            Notification::create([
                'user_id'     => $arsip->admin_id,
                'role_target' => 'admin',
                'arsip_id'    => $arsip->id,
                'title'       => 'Pengajuan Ditolak',
                'message'     => "Pengajuan {$arsip->no_registrasi} ditolak pada tahap {$step->role_label}" .
                    ($request->input('note') ? ": {$request->input('note')}" : '.'),
                'is_read'     => false,
            ]);
        });

        return back()->with('success', "Pengajuan ditolak pada tahap {$step->role_label}.");
    }

    /**
     * Boleh menindak tahap ini bila: approver yg ditugaskan, ATAU superadmin
     * (khusus tahap final Departemen IT atau sebagai override).
     */
    private function canActOnStep(ArsipApproval $step, User $user): bool
    {
        if ($step->approver_id && (int) $step->approver_id === (int) $user->id) {
            return true;
        }
        if ($user->role === 'superadmin') {
            // Superadmin menindak tahap final, dan boleh override tahap lain bila perlu.
            return true;
        }
        return false;
    }

    /**
     * Inisialisasi rantai approval saat pengajuan dibuat:
     * generate chain, stempel TTD Pemohon (bila punya specimen), notifikasi approver pertama.
     */
    protected function initApprovalChain(Arsip $arsip, array $approverMap, User $pengaju): void
    {
        // Bersihkan key kosong
        $approverMap = array_filter($approverMap, fn($v) => !empty($v));

        ArsipApproval::generateFor($arsip, $approverMap);

        if ($pengaju->hasSignature()) {
            $this->applySignature($arsip, $pengaju, 'Pemohon', null, request()->ip());
        }

        $arsip->load('approvals');
        if ($next = $arsip->currentApproval()) {
            $this->notifyApprover($arsip, $next);
        }
    }

    /**
     * Kirim notifikasi ke approver pada sebuah tahap.
     */
    private function notifyApprover(Arsip $arsip, ArsipApproval $step): void
    {
        $targetId = $step->approver_id;
        $roleTarget = 'admin';
        if ($step->role_label === ArsipApproval::FINAL_ROLE) {
            $sa = User::where('role', 'superadmin')->first();
            $targetId = $sa?->id ?? 1;
            $roleTarget = 'superadmin';
        }
        if (!$targetId) {
            return;
        }
        Notification::create([
            'user_id'     => $targetId,
            'role_target' => $roleTarget,
            'arsip_id'    => $arsip->id,
            'title'       => 'Menunggu Persetujuan Anda',
            'message'     => "Pengajuan {$arsip->no_registrasi} menunggu persetujuan ({$step->role_label}).",
            'is_read'     => false,
        ]);
    }
}
