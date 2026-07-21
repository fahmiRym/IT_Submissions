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
     * Query data pengajuan yang menunggu user (helper — dipakai web view + API).
     */
    protected function myApprovalsData($user)
    {
        return Arsip::with([
                'approvals.approver',
                'approvals.delegatedFrom',
                'signatures.delegatedFrom',
                'admin', 'department', 'unit',
            ])
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
    }

    /**
     * Halaman "Persetujuan Saya": pengajuan yang tahap aktifnya menunggu user ini.
     * Web → return view. API (wantsJson) → return JSON list.
     */
    public function myApprovals(Request $request)
    {
        $user = auth()->user();
        $arsips = $this->myApprovalsData($user);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status'  => 'success',
                'count'   => $arsips->count(),
                'data'    => $arsips->map(fn($a) => $this->transformArsipForApi($a, $user))->values(),
            ]);
        }

        return view('approvals.index', ['arsips' => $arsips]);
    }

    /**
     * Compact transform 1 arsip untuk API inbox (info penting saja, hindari payload berat).
     * Dipakai di /api/approvals (inbox list).
     *
     * Field shape ALIGNED dgn Android DTO ApprovalInboxItem + ApprovalStep:
     *   - department, unit: object {id, name} (Android expect DepartmentInfo/UnitInfo)
     *   - current_step.step: int alias step_order (Android expect field 'step')
     *   - current_step.delegated_from: string nama (Android expect String?, bukan object)
     */
    protected function transformArsipForApi(Arsip $arsip, $user): array
    {
        $cur = $arsip->currentApproval();
        return [
            'id'              => $arsip->id,
            'no_registrasi'   => $arsip->no_registrasi,
            'no_doc'          => $arsip->no_doc,
            'jenis'           => $arsip->jenis_pengajuan, // alias utk Android fallback
            'jenis_pengajuan' => $arsip->jenis_pengajuan,
            'jenis_label'     => str_replace('_', ' ', (string) $arsip->jenis_pengajuan),
            'pemohon'         => $arsip->pemohon ?: optional($arsip->admin)->name,
            'department'      => $arsip->department ? [
                'id'   => $arsip->department->id,
                'name' => $arsip->department->name,
                'code' => $arsip->department->code ?? null,
            ] : null,
            'unit'            => $arsip->unit ? [
                'id'   => $arsip->unit->id,
                'name' => $arsip->unit->name,
                'code' => $arsip->unit->code ?? null,
            ] : null,
            'tgl_pengajuan'   => optional($arsip->tgl_pengajuan)->toDateString(),
            'status'          => $arsip->status,
            'ket_process'     => $arsip->ket_process,
            'current_step'    => $cur ? [
                'step'       => $cur->step_order,  // Android field name
                'step_order' => $cur->step_order,  // backward-compat web
                'role_label' => $cur->role_label,
                'role'       => $cur->role_label,  // Android fallback field
                'is_mine'    => (int) $cur->approver_id === (int) $user->id
                                 || ($user->role === 'superadmin' && $cur->role_label === ArsipApproval::FINAL_ROLE),
                'delegated_from' => optional($cur->delegatedFrom)->name, // string nama (Android expect String?)
            ] : null,
            'approvals_count' => $arsip->approvals->count(),
            'signatures_count' => $arsip->signatures?->count() ?? 0,
        ];
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
            $msg = 'Tidak ada tahap approval yang menunggu.';
            if ($request->wantsJson()) return response()->json(['status' => 'error', 'message' => $msg], 422);
            return back()->with('error', $msg);
        }

        if (!$this->canActOnStep($step, $user)) {
            $msg = 'Tahap ini bukan giliran Anda.';
            if ($request->wantsJson()) return response()->json(['status' => 'error', 'message' => $msg], 403);
            return back()->with('error', $msg);
        }

        // hasSignature() tidak lagi wajib — TTD digital = QR auto-generated.

        DB::transaction(function () use ($arsip, $step, $user, $request) {
            // Stempel TTD sesuai peran tahap (Pemohon/SPV/Kabag/Manager/Accounting/Departemen IT).
            // Bila step ini merupakan hasil delegasi (delegated_from_id sudah di-set saat generate),
            // ATAU kalau superadmin override tahap orang lain, propagate info delegasi ke signature.
            $delegatedFromId = $step->delegated_from_id;
            if (!$delegatedFromId
                && $step->approver_id
                && (int) $step->approver_id !== (int) $user->id
                && $user->role === 'superadmin') {
                // Superadmin override — anggap sebagai delegasi dari approver original.
                $delegatedFromId = $step->approver_id;
            }
            $this->applySignature($arsip, $user, $step->role_label, $request->input('note'), $request->ip(), $delegatedFromId);

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

        $msg = "Tahap {$step->role_label} berhasil disetujui & ditandatangani.";
        if ($request->wantsJson()) {
            $arsip->refresh();
            return response()->json([
                'success' => true,
                'status'  => 'success',
                'message' => $msg,
                'data'    => $arsip->toApiDetailArray($user),
            ]);
        }
        return back()->with('success', $msg);
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
            $msg = 'Tidak ada tahap approval yang menunggu.';
            if ($request->wantsJson()) return response()->json(['status' => 'error', 'message' => $msg], 422);
            return back()->with('error', $msg);
        }
        if (!$this->canActOnStep($step, $user)) {
            $msg = 'Tahap ini bukan giliran Anda.';
            if ($request->wantsJson()) return response()->json(['status' => 'error', 'message' => $msg], 403);
            return back()->with('error', $msg);
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

        $msg = "Pengajuan ditolak pada tahap {$step->role_label}.";
        if ($request->wantsJson()) {
            $arsip->refresh();
            return response()->json([
                'success' => true,
                'status'  => 'success',
                'message' => $msg,
                'data'    => $arsip->toApiDetailArray($user),
            ]);
        }
        return back()->with('success', $msg);
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

        // TTD Pemohon auto saat submit — sekarang tidak perlu specimen image.
        $this->applySignature($arsip, $pengaju, 'Pemohon', null, request()->ip());

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
