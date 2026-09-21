<?php

namespace App\Http\Controllers\Admin;

use App\Branch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->isSuperadmin()) {
                abort(403, 'Hanya superadmin/developer.');
            }
            return $next($request);
        })->except(['switch']);
    }

    public function index()
    {
        $branches = Branch::withCount('positions')->orderBy('name')->get();
        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.form', ['branch' => new Branch()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:branches,name',
            'location' => 'nullable|string|max:150',
            'address' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        Branch::create($data);
        return redirect()->route('admin.branches.index')->with('success', 'Cabang ditambahkan.');
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        return view('admin.branches.form', compact('branch'));
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:branches,name,' . $branch->id,
            'location' => 'nullable|string|max:150',
            'address' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $branch->update($data);
        return redirect()->route('admin.branches.index')->with('success', 'Cabang diperbarui.');
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);
        if ($branch->positions()->count() > 0) {
            return back()->withErrors(['msg' => 'Tidak bisa dihapus: masih ada loker di cabang ini. Pindahkan/nonaktifkan dulu.']);
        }
        $branch->users()->detach();
        $branch->delete();
        if ((int) session('current_branch_id') === (int) $id) {
            session()->forget('current_branch_id');
        }
        return redirect()->route('admin.branches.index')->with('success', 'Cabang dihapus.');
    }

    /** Ganti cabang aktif (switcher di navbar). branch_id kosong = semua cabang saya. */
    public function switch(Request $request)
    {
        $request->validate(['branch_id' => 'nullable|exists:branches,id']);
        $bid = $request->input('branch_id');
        $user = auth()->user();
        if (empty($bid)) {
            session()->forget('current_branch_id');
            return back()->with('success', 'Cabang: semua yang Anda kelola.');
        }
        if (!$user->isSuperadmin() && !$user->canAccessBranch($bid)) {
            abort(403, 'Anda tidak punya akses ke cabang ini.');
        }
        session(['current_branch_id' => (int) $bid]);
        $b = Branch::find($bid);
        return back()->with('success', 'Cabang aktif: ' . ($b ? $b->name : ''));
    }
}
