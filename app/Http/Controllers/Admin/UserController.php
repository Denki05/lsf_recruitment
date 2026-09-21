<?php

namespace App\Http\Controllers\Admin;

use App\Branch;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->isSuperadmin()) {
                abort(403, 'Hanya superadmin/developer.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $users = User::with('branches')->orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        return view('admin.users.form', ['user' => new User(), 'branches' => $branches, 'selected' => []]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:6|max:100',
            'is_superadmin' => 'nullable|boolean',
            'branch_ids' => 'nullable|array',
            'branch_ids.*' => 'exists:branches,id',
        ]);
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->is_superadmin = $request->boolean('is_superadmin');
        $user->save();
        if (!$user->is_superadmin) {
            $user->branches()->sync($data['branch_ids'] ?? []);
        } else {
            $user->branches()->detach();
        }
        return redirect()->route('admin.users.index')->with('success', 'Login baru dibuat.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $branches = Branch::orderBy('name')->get();
        $selected = $user->branches()->pluck('branches.id')->all();
        return view('admin.users.form', compact('user', 'branches', 'selected'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|max:100',
            'is_superadmin' => 'nullable|boolean',
            'branch_ids' => 'nullable|array',
            'branch_ids.*' => 'exists:branches,id',
        ]);
        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        // Jangan biarkan superadmin terakhir dicabut / akun sendiri dicabut
        $willBeSuper = $request->boolean('is_superadmin');
        if ($user->is_superadmin && !$willBeSuper) {
            $count = User::where('is_superadmin', true)->where('id', '<>', $user->id)->count();
            if ($count < 1) {
                return back()->withErrors(['is_superadmin' => 'Minimal harus ada 1 superadmin.'])->withInput();
            }
            if (auth()->id() === $user->id) {
                return back()->withErrors(['is_superadmin' => 'Tidak bisa mencabut superadmin akun sendiri.'])->withInput();
            }
        }
        $user->is_superadmin = $willBeSuper;
        $user->save();
        if (!$user->is_superadmin) {
            $user->branches()->sync($data['branch_ids'] ?? []);
        } else {
            $user->branches()->detach();
        }
        return redirect()->route('admin.users.index')->with('success', 'Login diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if (auth()->id() === $user->id) {
            return back()->withErrors(['msg' => 'Tidak bisa menghapus akun sendiri.']);
        }
        if ($user->is_superadmin && User::where('is_superadmin', true)->count() <= 1) {
            return back()->withErrors(['msg' => 'Tidak bisa hapus satu-satunya superadmin.']);
        }
        $user->branches()->detach();
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Login dihapus.');
    }
}
