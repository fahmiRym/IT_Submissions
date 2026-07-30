<?php

/**
 * ============================================================================
 * PROD-SAFE BACKPORT — ArsipNoteController (X3 M3 deploy 2026-07-30)
 * ----------------------------------------------------------------------------
 * Sumber: local Admin/ArsipNoteController.php + strip:
 * - ArsipLampiranService dependency (M4 territory, belum ada di prod)
 * - $arsip->canBeEditedBy() method call → replace dgn inline check simple
 * - bustCache logic (butuh service belum ada)
 *
 * Auth logic sederhana untuk prod (era pre-approval-chain):
 * - Superadmin: full access
 * - Owner (admin_id): full access
 * - Non-owner: deny
 * ============================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Arsip;
use App\Models\ArsipPersonalNote;
use Illuminate\Http\Request;

class ArsipNoteController extends Controller
{
    /** Simple access check — superadmin atau owner. */
    private function canAccess(Arsip $arsip): bool
    {
        $u = auth()->user();
        if (!$u) return false;
        if ($u->role === 'superadmin') return true;
        return (int) $arsip->admin_id === (int) $u->id;
    }

    /** Listing semua personal notes pada arsip (untuk modal AJAX). */
    public function index(Arsip $arsip)
    {
        if (!$this->canAccess($arsip)) {
            abort(403, 'Anda tidak punya akses ke arsip ini.');
        }

        $notes = $arsip->personalNotes()
            ->with('user:id,name,role,department_id')
            ->get();

        return response()->json([
            'notes' => $notes->map(fn ($n) => [
                'id' => $n->id,
                'user_id' => $n->user_id,
                'is_mine' => (int) $n->user_id === (int) auth()->id(),
                'author_name' => $n->user->name ?? '—',
                'author_role' => $n->user->role ?? '',
                'note' => $n->note,
                'created_at' => $n->created_at?->format('d/m/Y H:i'),
                'updated_at' => $n->updated_at?->format('d/m/Y H:i'),
            ]),
        ]);
    }

    public function store(Request $request, Arsip $arsip)
    {
        if (!$this->canAccess($arsip)) {
            abort(403, 'Anda tidak punya akses untuk menambahkan catatan di arsip ini.');
        }

        $data = $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        $note = ArsipPersonalNote::create([
            'arsip_id' => $arsip->id,
            'user_id' => auth()->id(),
            'note' => trim($data['note']),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'note_id' => $note->id]);
        }
        return back()->with('success', 'Catatan personal disimpan.');
    }

    public function update(Request $request, Arsip $arsip, ArsipPersonalNote $note)
    {
        if ((int) $note->arsip_id !== (int) $arsip->id) abort(404);
        if ((int) $note->user_id !== (int) auth()->id() && auth()->user()->role !== 'superadmin') {
            abort(403, 'Hanya pemilik catatan yang boleh mengubahnya.');
        }

        $data = $request->validate([
            'note' => 'required|string|max:2000',
        ]);
        $note->update(['note' => trim($data['note'])]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Catatan diperbarui.');
    }

    public function destroy(Request $request, Arsip $arsip, ArsipPersonalNote $note)
    {
        if ((int) $note->arsip_id !== (int) $arsip->id) abort(404);
        if ((int) $note->user_id !== (int) auth()->id() && auth()->user()->role !== 'superadmin') {
            abort(403, 'Hanya pemilik catatan yang boleh menghapusnya.');
        }
        $note->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Catatan dihapus.');
    }
}
