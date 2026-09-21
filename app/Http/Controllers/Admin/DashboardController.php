<?php

namespace App\Http\Controllers\Admin;

use App\Applicant;
use App\Http\Controllers\Controller;
use App\Position;

class DashboardController extends Controller
{
    public function index()
    {
        $appQuery = \App\Services\BranchAccess::scopeApplicants(Applicant::query());
        $posQuery = \App\Services\BranchAccess::scopePositions(Position::query());

        $total = (clone $appQuery)->count();
        $baru = (clone $appQuery)->where('status', 'Baru')->count();
        $proses = (clone $appQuery)->whereIn('status', ['Seleksi', 'Interview'])->count();
        $diterima = (clone $appQuery)->where('status', 'Diterima')->count();
        $ditolak = (clone $appQuery)->where('status', 'Ditolak')->count();
        $perPosisi = (clone $posQuery)->withCount('applicants')->orderBy('title')->get();
        $terbaru = (clone $appQuery)->with('position')->latest()->take(5)->get();

        // Tren 14 hari terakhir (ikut scope cabang)
        $trendLabels = [];
        $trendData = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $trendLabels[] = $d->format('d/m');
            $trendData[] = \App\Services\BranchAccess::scopeApplicants(Applicant::whereDate('created_at', $d->toDateString()))->count();
        }

        return view('admin.dashboard', compact('total', 'baru', 'proses', 'diterima', 'ditolak', 'perPosisi', 'terbaru', 'trendLabels', 'trendData'));
    }
}
