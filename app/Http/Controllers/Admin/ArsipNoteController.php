<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Arsip;
use App\Models\ArsipPersonalNote;
use App\Services\ArsipLampiranService;
use Illuminate\Http\Request;

class ArsipNoteController extends Controller
{
    private function bustCache(int $arsipId): void
    {
        (new ArsipLampiranService())->invalidateCache($arsipId);
    }

    /** Listing semua personal notes pada arsip (untuk modal AJAX). */
    public function index(Arsip $arsip)
    {
        // User boleh lihat notes kalau punya akses ke arsip-nya
        if (!$arsip->canBeEditedBy(auth()->user())) {
            abort(403, 'Anda tidak punya akses ke arsip ini.');
        }

        $notes = $arsip->personalNotes()
            ->with('user:id,name,role,department_id')
            ->with('user.department:id,name')
            ->get();

        return response()->json([
            'notes' => $notes->map(fn ($n) => [
                'id' => $n->id,
                'user_id' => $n->user_id,
                'is_mine' => (int) $n->user_id === (int) auth()->id(),
                'author_name' => $n->user->name ?? '—',
                'author_role' => $n->user->role ?? '',
                'author_dept' => optional($n->user->department)->name,
                'note' => $n->note,
                'created_at' => $n->created_at?->format('d/m/Y H:i'),
                'updated_at' => $n->updated_at?->format('d/m/Y H:i'),
            ]),
        ]);
    }

    public function store(Request $request, Arsip $arsip)
    {
        if (!$arsip->canBeEditedBy(auth()->user())) {
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
        $this->bustCache($arsip->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'note_id' => $note->id]);
        }
        return back()->with('success', 'Catatan personal disimpan.');
    }

    public function update(Request $request, Arsip $arsip, ArsipPersonalNote $note)
    {
        if ((int) $note->arsip_id !== (int) $arsip->id) abort(404);
        // Hanya pemilik note yang boleh edit
        if ((int) $note->user_id !== (int) auth()->id() && auth()->user()->role !== 'superadmin') {
            abort(403, 'Hanya pemilik catatan yang boleh mengubahnya.');
        }

        $data = $request->validate([
            'note' => 'required|string|max:2000',
        ]);
        $note->update(['note' => trim($data['note'])]);
        $this->bustCache($arsip->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Catatan diperbarui.');
    }

    public function destroy(Request $request, Arsip $arsip, ArsipPersonalNote $note)
    {
        if ((int) $note->arsip_id !== (int) $arsip->id) abort(404);
        // Hanya pemilik note atau superadmin yang boleh hapus
        if ((int) $note->user_id !== (int) auth()->id() && auth()->user()->role !== 'superadmin') {
            abort(403, 'Hanya pemilik catatan yang boleh menghapusnya.');
        }
        $note->delete();
        $this->bustCache($arsip->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Catatan dihapus.');
    }
}
