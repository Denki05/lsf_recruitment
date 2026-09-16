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
        $perPosisi = Position::withCount('applicants')->orderBy('title')->get();
        $terbaru = Applicant::with('position')->latest()->take(8)->get();

        return view('admin.dashboard', compact('total', 'baru', 'proses', 'diterima', 'perPosisi', 'terbaru'));
    }
}
