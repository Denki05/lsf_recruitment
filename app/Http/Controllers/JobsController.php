<?php

namespace App\Http\Controllers;

use App\Branch;
use App\Position;

class JobsController extends Controller
{
    public function index()
    {
        $branches = Branch::active()->orderBy('name')->get();
        $q = Position::with('branch')->active();
        $selectedBranch = null;
        if (request()->filled('branch_id')) {
            $selectedBranch = Branch::active()->find(request('branch_id'));
            if ($selectedBranch) {
                $q->where('branch_id', $selectedBranch->id);
            }
        }
        $positions = $q->orderBy('title')->get();
        return view('public.jobs', compact('positions', 'branches', 'selectedBranch'));
    }

    /** Daftar loker 1 cabang (link share per cabang). */
    public function branch($branch)
    {
        $b = Branch::active()->where(function ($q) use ($branch) {
            $q->where('name', $branch);
            if (ctype_digit((string) $branch)) {
                $q->orWhere('id', $branch);
            }
        })->first();
        if (!$b) {
            // coba cari case-insensitive by name
            $b = Branch::active()->get()->first(function ($x) use ($branch) {
                return strtolower($x->name) === strtolower($branch);
            });
        }
        if (!$b) {
            abort(404);
        }
        $branches = Branch::active()->orderBy('name')->get();
        $selectedBranch = $b;
        $positions = Position::with('branch')->active()->where('branch_id', $b->id)->orderBy('title')->get();
        return view('public.jobs', compact('positions', 'branches', 'selectedBranch'));
    }

    /** Detail 1 loker via token publik; ID numerik lama dialihkan permanen. */
    public function show($code)
    {
        if (ctype_digit((string) $code)) {
            $position = Position::with('branch')->active()->findOrFail($code);
            return redirect()->route('jobs.show', $position, 301);
        }

        $position = Position::with('branch')->active()->where('public_token', $code)->firstOrFail();
        return $this->detail($position);
    }

    protected function detail(Position $position)
    {
        return view('public.job-show', compact('position'));
    }
}
