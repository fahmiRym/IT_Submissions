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
            ArsipShare::firstOrCreate(
                ['arsip_id' => $arsip->id, 'target_type' => 'user', 'user_id' => $data['user_id']],
                ['shared_by' => auth()->id(), 'note' => $data['note'] ?? null, 'role' => null]
            );
            $target = User::find($data['user_id']);
            $label = "user {$target->name}";
        } else {
            ArsipShare::firstOrCreate(
                ['arsip_id' => $arsip->id, 'target_type' => 'role', 'role' => $data['role']],
                ['shared_by' => auth()->id(), 'note' => $data['note'] ?? null, 'user_id' => null]
            );
            $roleLabel = RolePengajuanAccess::ROLE_LIST[$data['role']]['label'] ?? $data['role'];
            $label = "role {$roleLabel}";
        }

        return back()->with('success', "Pengajuan {$arsip->no_registrasi} dibagikan ke {$label}.");
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
