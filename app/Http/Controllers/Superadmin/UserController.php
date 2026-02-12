<?php

namespace App\Http\Controllers\Superadmin;


use App\Http\Controllers\Controller; // ✅ WAJIB
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // ✅ INI WAJIB

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('department')->orderBy('name')->get();
        $totalAdmin = User::where('role', 'admin')->count();
        $totalSuper = User::where('role', 'superadmin')->count();
        $latestUser = User::latest()->first()->name ?? '-';
        $departments = Department::orderBy('name')->get();

        return view('users.index', compact('users', 'totalAdmin', 'totalSuper', 'latestUser', 'departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('users.create', compact('departments'));
    }

    public function store(Request $request)
{
    $request->validate([
        'name'          => 'required',
        'username'      => 'required|unique:users,username',
        'password'      => 'required|min:6',
        'role'          => 'required|in:admin,superadmin',
        'department_id' => 'required',
    ]);

    User::create([
        'name'          => $request->name,
        'username'      => $request->username,
        'password'      => Hash::make($request->password),
        'role'          => $request->role,
        'department_id' => $request->department_id,
    ]);

    return redirect()
        ->route('superadmin.users.index')
        ->with('success','User berhasil ditambahkan');
}

    public function edit(User $user)
    {
        $departments = Department::orderBy('name')->get();
        return view('users.edit', compact('user', 'departments'));
    }

    public function update(Request $request, User $user)
{
    $request->validate([
        'name'          => 'required',
        'username'      => 'required|unique:users,username,' . $user->id,
        'role'          => 'required|in:admin,superadmin',
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
            'name'     => 'required',
            'email'    => 'required|email',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success','Profile berhasil diperbarui');
    }
}
