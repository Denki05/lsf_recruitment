<?php

namespace App\Http\Controllers\Admin;

use App\Applicant;
use App\Http\Controllers\Controller;
use App\Position;

class DashboardController extends Controller
{
    public function index()
    {
        $total = Applicant::count();
        $baru = Applicant::where('status', 'Baru')->count();
        $proses = Applicant::whereIn('status', ['Seleksi', 'Interview'])->count();
        $diterima = Applicant::where('status', 'Diterima')->count();
        $ditolak = Applicant::where('status', 'Ditolak')->count();
        $perPosisi = Position::withCount('applicants')->orderBy('title')->get();
        $terbaru = Applicant::with('position')->latest()->take(5)->get();

        // Tren 14 hari terakhir
        $trendLabels = [];
        $trendData = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $trendLabels[] = $d->format('d/m');
            $trendData[] = Applicant::whereDate('created_at', $d->toDateString())->count();
        }

        return view('admin.dashboard', compact('total', 'baru', 'proses', 'diterima', 'ditolak', 'perPosisi', 'terbaru', 'trendLabels', 'trendData'));
    }
}
