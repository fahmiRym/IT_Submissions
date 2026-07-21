<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * API endpoint untuk alur approval (mobile Android).
 *
 * Semua method di-delegasikan ke trait `HandlesApproval` + `SignsArsip` yang sama
 * dipakai web (single source of truth). Trait method sudah support `wantsJson()` →
 * saat request datang dgn `Accept: application/json` (default utk Sanctum API),
 * response otomatis JSON.
 *
 * Endpoint:
 *   GET  /api/approvals              → inbox: pengajuan yg tahap aktifnya menunggu user login
 *   POST /api/arsip/{id}/approve     → setujui + auto-TTD digital sesuai role step
 *   POST /api/arsip/{id}/reject      → tolak (body: note wajib)
 *   POST /api/arsip/{id}/sign        → TTD self (Pemohon / Accounting Adjust)
 */
class ApprovalApiController extends Controller
{
    use \App\Traits\HandlesApproval;   // provides: myApprovals, approveArsip, rejectArsip
    use \App\Traits\SignsArsip;        // provides: signArsip, applySignature (dipakai internal HandlesApproval)
}
