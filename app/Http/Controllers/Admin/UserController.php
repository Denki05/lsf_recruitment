<?php

namespace App\Http\Controllers\Admin;

use App\Branch;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
            'password' => 'required|string|min:8|max:100',
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

    public function magicLink($id)
    {
        $user = User::findOrFail($id);

        $token = Str::random(64);
        $expires = now()->addDays(7);
        // Satu link aktif per akun; membuat link baru otomatis mematikan yang lama.
        Cache::put('magic_login_user:' . $user->id, hash('sha256', $token), $expires);
        $url = URL::temporarySignedRoute('admin.magic', $expires, [
            'user' => $user->id,
            'token' => $token,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Link login untuk ' . $user->name . ' dibuat. Bisa dipakai berulang.')
            ->with('magic_link', $url)
            ->with('magic_link_user', $user->name . ' (' . $user->email . ')')
            ->with('magic_link_expires', $expires->format('d/m/Y H:i'));
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
            'password' => 'nullable|string|min:8|max:100',
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
