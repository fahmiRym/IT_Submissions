<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Arsip;
use App\Models\ArsipShare;
use App\Models\RolePengajuanAccess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArsipShareController extends Controller
{
    /** Hanya SUPERADMIN yang boleh membuat/cabut share. */
    private function canManageShare(): bool
    {
        return auth()->user()->role === 'superadmin';
    }

    /** List share aktif untuk arsip (modal AJAX). */
    public function index(Arsip $arsip)
    {
        // Hanya superadmin yang melihat manage panel. Owner arsip melihat info read-only.
        $shares = $arsip->shares()
            ->with([
                'user:id,name,username,role,department_id',
                'user.department:id,name',
                'sharedBy:id,name',
            ])
            ->latest()
            ->get();

        return response()->json([
            'can_manage' => $this->canManageShare(),
            'shares' => $shares->map(function ($s) {
                if ($s->target_type === 'role') {
                    $roleInfo = RolePengajuanAccess::ROLE_LIST[$s->role] ?? ['label' => strtoupper($s->role)];
                    return [
                        'id' => $s->id,
                        'target_type' => 'role',
                        'display_name' => 'Role: ' . ($roleInfo['label'] ?? $s->role),
                        'sub_text' => 'Semua user role ' . strtoupper($s->role),
                        'role' => $s->role,
                        'note' => $s->note,
                        'shared_by_name' => optional($s->sharedBy)->name,
                        'created_at' => $s->created_at?->format('d/m/Y H:i'),
                    ];
                }
                return [
                    'id' => $s->id,
                    'target_type' => 'user',
                    'display_name' => $s->user->name ?? '—',
                    'sub_text' => ($s->user->username ?? '')
                        . (optional($s->user)->department ? ' · ' . $s->user->department->name : '')
                        . ' · ' . strtoupper($s->user->role ?? ''),
                    'user_id' => $s->user_id,
                    'note' => $s->note,
                    'shared_by_name' => optional($s->sharedBy)->name,
                    'created_at' => $s->created_at?->format('d/m/Y H:i'),
                ];
            }),
        ]);
    }

    public function store(Request $request, Arsip $arsip)
    {
        if (!$this->canManageShare()) abort(403, 'Hanya Superadmin yang boleh membagikan pengajuan.');

        $data = $request->validate([
            'target_type' => 'required|in:user,role',
            'user_id'     => 'required_if:target_type,user|nullable|exists:users,id',
            'role'        => 'required_if:target_type,role|nullable|in:admin,accounting,spv,kabag,manager',
            'note'        => 'nullable|string|max:255',
        ]);

        if ($data['target_type'] === 'user') {
            if ((int) $data['user_id'] === (int) $arsip->admin_id) {
                return back()->with('info', 'Pemohon arsip ini sendiri, tidak perlu di-share.');
            }
            $share = ArsipShare::firstOrCreate(
                ['arsip_id' => $arsip->id, 'target_type' => 'user', 'user_id' => $data['user_id']],
                ['shared_by' => auth()->id(), 'note' => $data['note'] ?? null, 'role' => null]
            );
            $target = User::find($data['user_id']);
            $label = "user {$target->name}";
            // Kirim FCM push (web + android) ke target user — muncul OS-level walau browser closed.
            $this->sendSharePush([$target->id], $arsip, $share, $data['note'] ?? null);
        } else {
            $share = ArsipShare::firstOrCreate(
                ['arsip_id' => $arsip->id, 'target_type' => 'role', 'role' => $data['role']],
                ['shared_by' => auth()->id(), 'note' => $data['note'] ?? null, 'user_id' => null]
            );
            $roleLabel = RolePengajuanAccess::ROLE_LIST[$data['role']]['label'] ?? $data['role'];
            $label = "role {$roleLabel}";
            // Kirim FCM push ke semua user dengan role tsb.
            $userIds = User::where('role', $data['role'])->where('is_active', true)->pluck('id')->all();
            $this->sendSharePush($userIds, $arsip, $share, $data['note'] ?? null);
        }

        return back()->with('success', "Pengajuan {$arsip->no_registrasi} dibagikan ke {$label}.");
    }

    /**
     * Kirim FCM push notif "BA Dibagikan" ke user penerima share.
     * Best-effort — kalau gagal, share tetap tersimpan.
     */
    private function sendSharePush(array $userIds, Arsip $arsip, ArsipShare $share, ?string $note = null): void
    {
        try {
            $fcm = app(\App\Services\FcmService::class);
            if (!$fcm->isConfigured()) return;
            $title = "BA Dibagikan · " . ($arsip->no_registrasi ?? '');
            $body  = "Dari " . (auth()->user()->name ?? 'Sistem')
                   . " · " . str_replace('_', ' ', (string) $arsip->jenis_pengajuan)
                   . ($note ? "\n\"{$note}\"" : '');
            $data = [
                'type'          => 'share',
                'share_id'      => (string) $share->id,
                'arsip_id'      => (string) $arsip->id,
                'no_registrasi' => (string) ($arsip->no_registrasi ?? ''),
                'click_url'     => url('/'),
                'tag'           => 'share-' . $share->id,
            ];
            foreach ($userIds as $uid) {
                $fcm->sendToUser((int) $uid, $title, $body, $data);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('FCM share push gagal: ' . $e->getMessage());
        }
    }

    public function destroy(Arsip $arsip, ArsipShare $share)
    {
        if (!$this->canManageShare()) abort(403, 'Hanya Superadmin yang boleh mencabut share.');
        if ((int) $share->arsip_id !== (int) $arsip->id) abort(404);

        $label = $share->target_type === 'role'
            ? ('role ' . strtoupper($share->role))
            : ('user ' . optional($share->user)->name);

        $share->delete();
        return back()->with('success', "Akses {$label} ke {$arsip->no_registrasi} dicabut.");
    }

    /**
     * Search target (user + role). Return JSON.
     * - q = keyword search (apply ke user only)
     * - mode = 'user' | 'role' | 'both' (default 'both')
     */
    public function searchUsers(Request $request)
    {
        $q = trim($request->get('q', ''));
        $mode = $request->get('mode', 'both');

        $users = [];
        if (in_array($mode, ['user', 'both'], true)) {
            $users = User::query()
                ->where('is_active', true)
                ->where('id', '!=', auth()->id())
                ->when($q !== '', function ($w) use ($q) {
                    $w->where(function ($x) use ($q) {
                        $x->where('name', 'like', "%{$q}%")
                          ->orWhere('username', 'like', "%{$q}%")
                          ->orWhere('employee_id', 'like', "%{$q}%");
                    });
                })
                ->with('department:id,name')
                ->orderBy('name')
                ->limit(12)
                ->get(['id', 'name', 'username', 'role', 'department_id'])
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'role' => $u->role,
                    'department' => optional($u->department)->name,
                ])
                ->all();
        }

        $roles = [];
        if (in_array($mode, ['role', 'both'], true)) {
            foreach (RolePengajuanAccess::ROLE_LIST as $key => $info) {
                if ($q === '' || stripos($info['label'], $q) !== false || stripos($key, $q) !== false) {
                    $count = User::where('role', $key)->where('is_active', true)->count();
                    $roles[] = [
                        'key' => $key,
                        'label' => $info['label'],
                        'icon' => $info['icon'],
                        'color' => $info['color'],
                        'user_count' => $count,
                    ];
                }
            }
        }

        return response()->json(compact('users', 'roles'));
    }

    /**
     * Polling endpoint: count share yang belum dibaca + latest N untuk toast popup.
     * Panggilan ringan (dipanggil tiap ~10s dari layout).
     */
    public function unread(Request $request)
    {
        $user = auth()->user();

        $baseQ = ArsipShare::query()
            ->where(function ($w) use ($user) {
                $w->where(function ($x) use ($user) {
                    $x->where('target_type', 'user')->where('user_id', $user->id);
                })->orWhere(function ($x) use ($user) {
                    $x->where('target_type', 'role')->where('role', $user->role);
                });
            })
            ->whereNull('read_at');

        $count = (clone $baseQ)->count();

        $latest = (clone $baseQ)
            ->with(['arsip:id,no_registrasi,jenis_pengajuan,pemohon,department_id', 'arsip.department:id,name', 'sharedBy:id,name'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($s) => [
                'share_id'       => $s->id,
                'arsip_id'       => $s->arsip_id,
                'no_registrasi'  => $s->arsip->no_registrasi ?? '—',
                'jenis'          => $s->arsip->jenis_pengajuan ?? '',
                'pemohon'        => $s->arsip->pemohon ?? '—',
                'department'     => optional(optional($s->arsip)->department)->name,
                'shared_by'      => optional($s->sharedBy)->name ?? 'Sistem',
                'note'           => $s->note,
                'shared_at'      => $s->created_at?->diffForHumans(),
            ]);

        return response()->json(['count' => $count, 'items' => $latest]);
    }

    /** Detail lengkap share untuk populate modal (no_transaksi list + approvals). */
    public function detail(Request $request, ArsipShare $share)
    {
        $this->assertOwnedByAuth($share);
        $share->load(['arsip:id,no_registrasi,jenis_pengajuan,pemohon,department_id,unit_id,no_transaksi,keterangan',
                      'arsip.department:id,name', 'arsip.unit:id,name', 'sharedBy:id,name']);

        // Parse no_transaksi ke array (support pipe | dan newline separator).
        $noTxRaw  = trim((string) ($share->arsip->no_transaksi ?? ''));
        $normalized = preg_replace('/\|+/', "\n", $noTxRaw);
        $normalized = str_replace("\r\n", "\n", $normalized);
        $noTx = array_values(array_filter(array_map('trim', explode("\n", $normalized))));

        // Approvals sudah tersimpan per no_transaksi (dari user ini saja).
        $existingApprovals = $share->approvals();

        return response()->json([
            'share' => [
                'id'           => $share->id,
                'note'         => $share->note,
                'note_content' => $share->note_content,
                'read_at'      => optional($share->read_at)->toIso8601String(),
                'noted_at'     => optional($share->noted_at)->toIso8601String(),
                'shared_by'    => optional($share->sharedBy)->name ?? 'Sistem',
                'shared_at'    => optional($share->created_at)->format('d M Y, H:i'),
            ],
            'arsip' => [
                'id'              => $share->arsip->id,
                'no_registrasi'   => $share->arsip->no_registrasi,
                'jenis_pengajuan' => str_replace('_', ' ', (string) $share->arsip->jenis_pengajuan),
                'pemohon'         => $share->arsip->pemohon,
                'department'      => optional($share->arsip->department)->name,
                'unit'            => optional($share->arsip->unit)->name,
                'keterangan'      => $share->arsip->keterangan,
            ],
            'no_transaksis' => $noTx,
            'approvals'     => $existingApprovals,
            'view_url'      => route('admin.arsip.show-document', $share->arsip->id),
        ]);
    }

    /** Mark 1 share sebagai dibaca (auto dipanggil saat modal terbuka). */
    public function markRead(Request $request, ArsipShare $share)
    {
        $this->assertOwnedByAuth($share);
        if (is_null($share->read_at)) {
            $share->update(['read_at' => now()]);
        }
        return response()->json(['ok' => true, 'read_at' => $share->read_at?->toIso8601String()]);
    }

    /** Simpan catatan text (opsional dari user penerima share). */
    public function note(Request $request, ArsipShare $share)
    {
        $this->assertOwnedByAuth($share);
        $data = $request->validate(['note_content' => 'nullable|string|max:1000']);
        $share->update([
            'note_content' => $data['note_content'],
            'noted_at'     => now(),
            'read_at'      => $share->read_at ?? now(),
        ]);
        return response()->json(['ok' => true, 'noted_at' => $share->noted_at->toIso8601String()]);
    }

    /**
     * Approve satu / beberapa no_transaksi. Payload: { no_transaksis: [str, ...] }.
     * Simpan snapshot user name + timestamp per no_transaksi ke approvals_json.
     */
    public function approve(Request $request, ArsipShare $share)
    {
        // TEMP DEBUG — hapus setelah fix confirmed
        \Illuminate\Support\Facades\Log::info('SHARE APPROVE payload', [
            'share_id' => $share->id,
            'user_id'  => auth()->id(),
            'all'      => $request->all(),
            'content_type' => $request->header('Content-Type'),
        ]);

        $this->assertOwnedByAuth($share);
        $data = $request->validate([
            'no_transaksis'   => 'required|array|min:1',
            'no_transaksis.*' => 'string|max:255',
        ]);

        $user = auth()->user();
        $existing = $share->approvals();
        $existingKeys = array_map(fn ($a) => mb_strtolower(trim($a['no_transaksi'] ?? '')), $existing);

        foreach ($data['no_transaksis'] as $tx) {
            $txTrim = trim($tx);
            if ($txTrim === '') continue;
            if (in_array(mb_strtolower($txTrim), $existingKeys, true)) continue;
            $existing[] = [
                'no_transaksi'  => $txTrim,
                'approved_name' => $user->name,
                'approved_at'   => now()->toIso8601String(),
            ];
        }

        $share->update([
            'approvals_json' => $existing,
            'read_at'        => $share->read_at ?? now(),
        ]);

        return response()->json(['ok' => true, 'approvals' => $existing]);
    }

    /** Guard: pastikan share memang milik auth user (target_type user atau role match). */
    private function assertOwnedByAuth(ArsipShare $share): void
    {
        $user = auth()->user();
        $isUserTarget = $share->target_type === 'user' && (int) $share->user_id === (int) $user->id;
        $isRoleTarget = $share->target_type === 'role' && $share->role === $user->role;
        if (!$isUserTarget && !$isRoleTarget) {
            abort(403, 'Share bukan untuk Anda.');
        }
    }

    /** Inbox: arsip yang di-share ke user current via user_id ATAU role. */
    public function inbox(Request $request)
    {
        $user = auth()->user();
        $q = $request->get('q');

        $arsips = Arsip::query()
            ->select('arsips.*',
                    'arsip_shares.note as share_note',
                    'arsip_shares.created_at as share_created_at',
                    'arsip_shares.target_type as share_target_type')
            ->join('arsip_shares', 'arsip_shares.arsip_id', '=', 'arsips.id')
            ->where(function ($w) use ($user) {
                $w->where(function ($x) use ($user) {
                    $x->where('arsip_shares.target_type', 'user')
                      ->where('arsip_shares.user_id', $user->id);
                })->orWhere(function ($x) use ($user) {
                    $x->where('arsip_shares.target_type', 'role')
                      ->where('arsip_shares.role', $user->role);
                });
            })
            ->when($q, fn ($w) => $w->where(function ($x) use ($q) {
                $x->where('arsips.no_registrasi', 'like', "%{$q}%")
                  ->orWhere('arsips.no_doc', 'like', "%{$q}%")
                  ->orWhere('arsips.keterangan', 'like', "%{$q}%");
            }))
            ->with(['department:id,name', 'unit:id,name', 'admin:id,name'])
            ->orderByDesc('arsip_shares.created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.arsip.shared_inbox', compact('arsips'));
    }
}
