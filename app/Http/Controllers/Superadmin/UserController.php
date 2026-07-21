<?php

namespace App\Http\Controllers\Superadmin;


use App\Http\Controllers\Controller; // ✅ WAJIB
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // ✅ INI WAJIB

class UserController extends Controller
{
    public function index(Request $request)
    {
        // submission count per admin_id (FULL data, bukan filtered)
        $submissionCounts = \DB::table('arsips')
            ->select('admin_id', \DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('admin_id')
            ->groupBy('admin_id')
            ->pluck('cnt', 'admin_id');

        $q = trim((string) $request->get('q', ''));
        $roleFilter = $request->get('role');

        $users = User::with('department')
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('name', 'like', "%{$q}%")
                      ->orWhere('username', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('employee_id', 'like', "%{$q}%");
                });
            })
            ->when($roleFilter, fn ($w) => $w->where('role', $roleFilter))
            ->orderBy('name');

        $perPageRaw = $request->input('per_page', 15);
        $perPage = ($perPageRaw === 'all') ? 99999 : max(1, (int) $perPageRaw);
        $users = $users->paginate($perPage)->withQueryString();

        $users->getCollection()->each(function ($u) use ($submissionCounts) {
            $u->submissions_count = (int) ($submissionCounts[$u->id] ?? 0);
        });

        $totalAdmin = User::where('role', 'admin')->count();
        $totalSuper = User::where('role', 'superadmin')->count();
        $totalAccounting = User::where('role', 'accounting')->count();
        $totalActive = User::where('is_active', true)->count();
        $totalInactive = User::where('is_active', false)->count();
        $activeNow = User::where('last_login_at', '>=', now()->subMinutes(30))->count();
        $newThisMonth = User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $loggedInToday = User::whereDate('last_login_at', now()->toDateString())->count();
        $neverLoggedIn = User::whereNull('last_login_at')->count();
        $latestUser = User::latest()->first()->name ?? '-';
        $departments = Department::orderBy('name')->get();

        return view('users.index', compact(
            'users', 'totalAdmin', 'totalSuper', 'totalAccounting',
            'totalActive', 'totalInactive', 'activeNow', 'newThisMonth',
            'loggedInToday', 'neverLoggedIn', 'latestUser', 'departments'
        ));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('users.create', compact('departments'));
    }

    /**
     * Default password per-role (dipakai saat field password dikosongkan).
     */
    public static function defaultPasswordForRole(string $role): string
    {
        return match ($role) {
            'admin'      => 'admin123',
            'spv'        => 'spv123',
            'kabag'      => 'kab123',
            'manager'    => 'man123',
            'accounting' => 'acc123',
            'superadmin' => 'super123',
            default      => 'user123',
        };
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users,username',
            'password' => 'nullable|min:6',
            'role' => 'required|in:admin,superadmin,accounting,spv,kabag,manager',
            'department_id' => 'required',
        ]);

        $plain = $request->filled('password')
            ? $request->password
            : self::defaultPasswordForRole($request->role);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($plain),
            'role' => $request->role,
            'department_id' => $request->department_id,
            'is_active' => true,
            'must_change_password' => !$request->filled('password'),
        ]);

        $msg = $request->filled('password')
            ? 'User berhasil ditambahkan.'
            : "User berhasil ditambahkan. Password default: \"{$plain}\" (wajib diganti saat login pertama).";

        return redirect()
            ->route('superadmin.users.index')
            ->with('success', $msg);
    }

    public function edit(User $user)
    {
        $departments = Department::orderBy('name')->get();
        return view('users.edit', compact('user', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users,username,' . $user->id,
            'role' => 'required|in:admin,superadmin,accounting,spv,kabag,manager',
            'department_id' => 'required',
        ]);

        $data = $request->only([
            'name',
            'username',
            'role',
            'department_id',
        ]);

        // 🔐 password opsional
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'min:6'
            ]);

            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()
            ->route('superadmin.users.index')
            ->with('success', 'User berhasil diperbarui');
    }


    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('superadmin.users.index')
            ->with('success', 'User berhasil dihapus');
    }

    /**
     * Set delegasi TTD user (SuperAdmin mengelola atas nama user).
     * Delegasi aktif dalam window [from, until]; TTD approval yg ditugaskan
     * ke user akan auto-forward ke delegate.
     */
    public function setDelegate(Request $request, User $user)
    {
        $data = $request->validate([
            'delegate_to_id' => 'required|exists:users,id|different:id',
            'delegate_active_from' => 'nullable|date',
            'delegate_active_until' => 'nullable|date|after_or_equal:delegate_active_from',
            'delegate_reason' => 'nullable|string|max:200',
        ]);

        if ((int) $data['delegate_to_id'] === (int) $user->id) {
            return back()->with('error', 'User tidak boleh mendelegasikan ke dirinya sendiri.');
        }

        // Cegah delegation loop (A→B, B→A)
        $target = User::find($data['delegate_to_id']);
        if ($target && (int) $target->delegate_to_id === (int) $user->id) {
            return back()->with('error', 'Terdeteksi loop delegasi (user target juga sedang mendelegasikan ke user ini). Batalkan salah satu terlebih dahulu.');
        }

        $user->update($data);
        return back()->with('success', "Delegasi TTD {$user->name} → {$target->name} berhasil di-set.");
    }

    /**
     * Hapus delegasi TTD user.
     */
    public function clearDelegate(User $user)
    {
        $user->update([
            'delegate_to_id' => null,
            'delegate_active_from' => null,
            'delegate_active_until' => null,
            'delegate_reason' => null,
        ]);
        return back()->with('success', "Delegasi TTD {$user->name} dicabut.");
    }

    public function toggleIsActive(User $user)
    {
        // Jangan biarkan menonaktifkan diri sendiri
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "User \"{$user->name}\" berhasil {$status}.");
    }

    // ================= PROFILE =================
    public function profile()
    {
        return view('superadmin.profile.index', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Profile berhasil diperbarui');
    }
}
