<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API endpoint untuk Log Aktivitas (audit trail).
 * Superadmin only.
 *
 * Endpoint:
 *   GET /api/superadmin/activity-logs                        → list paginated
 *      Query params:
 *        q         : cari no_registrasi / no_transaksi arsip
 *        user_id   : filter editor
 *        per_page  : 10..1000 atau 'all' (cap 99999)
 *        page      : nomor halaman
 *   GET /api/superadmin/activity-logs/users                  → list user yg pernah edit
 *                                                             (untuk populate filter dropdown)
 */
class ActivityLogApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (!$this->isSuperadmin()) {
            return response()->json(['success' => false, 'message' => 'Superadmin only'], 403);
        }

        $query = AuditLog::with([
            'user:id,name,role,employee_id',
            'arsip:id,no_registrasi,no_transaksi,no_doc,jenis_pengajuan,department_id,unit_id',
            'arsip.department:id,name',
            'arsip.unit:id,name',
        ]);

        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->whereHas('arsip', function ($w) use ($q) {
                $w->where('no_registrasi', 'like', "%{$q}%")
                  ->orWhere('no_transaksi', 'like', "%{$q}%")
                  ->orWhere('no_doc', 'like', "%{$q}%");
            });
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $perPageRaw = $request->input('per_page', 20);
        $perPage = ($perPageRaw === 'all') ? 99999 : max(1, min(1000, (int) $perPageRaw));

        $paginator = $query->latest()->paginate($perPage);

        $items = $paginator->getCollection()->map(fn($log) => $this->transformLog($log))->values();

        return response()->json([
            'success' => true,
            'message' => 'Activity logs OK',
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ]);
    }

    /**
     * List user yg pernah melakukan perubahan — untuk populate dropdown filter di Android.
     */
    public function users(Request $request): JsonResponse
    {
        if (!$this->isSuperadmin()) {
            return response()->json(['success' => false, 'message' => 'Superadmin only'], 403);
        }

        $userIds = AuditLog::distinct()->whereNotNull('user_id')->pluck('user_id');
        $users = User::whereIn('id', $userIds)
            ->select('id', 'name', 'role', 'employee_id')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }

    /**
     * Transform 1 log record ke shape yg friendly untuk mobile display.
     */
    private function transformLog(AuditLog $log): array
    {
        // Bikin field `changes_summary` — human-readable list "field: OLD → NEW"
        $changes = [];
        $old = $log->old_values ?? [];
        $new = $log->new_values ?? [];
        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        foreach ($keys as $k) {
            $oldVal = $this->stringify($old[$k] ?? null);
            $newVal = $this->stringify($new[$k] ?? null);
            if ($oldVal === $newVal) continue;
            $changes[] = [
                'field' => $k,
                'old'   => $oldVal,
                'new'   => $newVal,
            ];
        }

        return [
            'id'         => $log->id,
            'action'     => $log->action,
            'created_at' => $log->created_at?->toIso8601String(),
            'created_at_display' => $log->created_at?->format('d M Y, H:i'),
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'user'       => $log->user ? [
                'id'          => $log->user->id,
                'name'        => $log->user->name,
                'role'        => $log->user->role,
                'employee_id' => $log->user->employee_id,
            ] : null,
            'arsip'      => $log->arsip ? [
                'id'              => $log->arsip->id,
                'no_registrasi'   => $log->arsip->no_registrasi,
                'no_transaksi'    => $log->arsip->no_transaksi,
                'no_doc'          => $log->arsip->no_doc,
                'jenis_pengajuan' => $log->arsip->jenis_pengajuan,
                'department'      => optional($log->arsip->department)->name,
                'unit'            => optional($log->arsip->unit)->name,
            ] : null,
            'changes'         => $changes,
            'changes_count'   => count($changes),
        ];
    }

    private function stringify($val): ?string
    {
        if ($val === null) return null;
        if (is_scalar($val)) return (string) $val;
        return json_encode($val, JSON_UNESCAPED_UNICODE);
    }

    private function isSuperadmin(): bool
    {
        return auth()->check() && auth()->user()->role === 'superadmin';
    }
}
