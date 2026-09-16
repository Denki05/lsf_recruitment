<?php

namespace App\Http\Controllers\Admin;

use App\Applicant;
use App\Http\Controllers\Controller;
use App\Mail\ApplicationStatusChanged;
use App\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $positions = Position::orderBy('title')->get();
        $query = Applicant::with('position')->latest();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('nama_lengkap', 'like', "%{$q}%")
                  ->orWhere('no_hp', 'like', "%{$q}%");
            });
        }
        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        // Urut skor tertinggi (dihitung per baris — cocok untuk skala HRD)
        if ($request->input('sort') === 'skor') {
            $svc = new \App\Services\CvScreening();
            $all = $query->get()->map(function ($a) use ($svc) {
                try {
                    $a->skor = $svc->score($a)['score'];
                } catch (\Exception $e) {
                    $a->skor = null;
                }
                return $a;
            })->sortByDesc(function ($a) {
                return $a->skor === null ? -1 : $a->skor;
            })->values();
            $applicants = $this->paginateManual($all, $request);
        } else {
            $applicants = $query->paginate(15)->appends($request->query());
        }

        return view('admin.applications.index', compact('applicants', 'positions'));
    }

    protected function paginateManual($items, Request $request, $perPage = 15)
    {
        $page = max(1, (int) $request->input('page', 1));
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($page, $perPage), $items->count(), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /** Ubah status massal dari checkbox tabel. */
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:applicants,id',
            'status' => 'required|in:Baru,Seleksi,Interview,Diterima,Ditolak',
        ], ['ids.required' => 'Pilih minimal 1 lamaran dulu.']);
        Applicant::whereIn('id', $request->ids)->update([
            'status' => $request->status,
            'updated_at' => now(),
        ]);
        return back()->with('success', count($request->ids) . ' lamaran diubah ke ' . $request->status . '.');
    }

    public function show($id)
    {
        $applicant = Applicant::with('position')->findOrFail($id);
        $zipList = [];
        if (strtolower(pathinfo($applicant->file_original, PATHINFO_EXTENSION)) === 'zip') {
            $full = Storage::disk('public')->path($applicant->file_path);
            if (is_file($full)) {
                $zip = new \ZipArchive();
                if ($zip->open($full) === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $stat = $zip->statIndex($i);
                        $zipList[] = $stat['name'] . ' (' . number_format($stat['size'] / 1024, 1) . ' KB)';
                    }
                    $zip->close();
                }
            }
        }
        // Saran kecocokan (tidak pernah menggagalkan halaman)
        try {
            $screening = (new \App\Services\CvScreening())->score($applicant);
        } catch (\Exception $e) {
            $screening = ['scorable' => false, 'score' => null, 'matched' => [], 'missing' => [], 'note' => 'Gagal menghitung saran.'];
        }
        return view('admin.applications.show', compact('applicant', 'zipList', 'screening'));
    }

    /** Jalankan evaluasi AI (on-demand, hasil di-cache di DB). */
    public function evaluateAi($id)
    {
        $applicant = Applicant::with('position')->findOrFail($id);
        if (!$applicant->ai_consent) {
            return back()->withErrors(['ai' => 'Pelamar tidak menyetujui pemrosesan AI.']);
        }
        $cvText = (new \App\Services\CvScreening())->getCvRawText($applicant);
        if (trim($cvText) === '') {
            return back()->withErrors(['ai' => 'Teks CV tidak bisa dibaca AI (bukan PDF/DOCX berteks).']);
        }
        $result = (new \App\Services\AiScreening())->evaluate($applicant, $cvText);
        if (!$result['ok']) {
            return back()->withErrors(['ai' => $result['error']]);
        }
        $applicant->update([
            'ai_score' => $result['score'],
            'ai_summary' => $result['summary'],
            'ai_strengths' => json_encode($result['strengths']),
            'ai_gaps' => json_encode($result['gaps']),
            'ai_evaluated_at' => now(),
        ]);
        return back()->with('success', 'Evaluasi AI selesai: skor ' . $result['score'] . '%.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Baru,Seleksi,Interview,Diterima,Ditolak',
            'catatan_admin' => 'nullable|string|max:2000',
            'kirim_email' => 'nullable|boolean',
        ]);
        $applicant = Applicant::with('position')->findOrFail($id);
        $old = $applicant->status;
        $applicant->status = $request->status;
        $applicant->catatan_admin = $request->catatan_admin;
        $applicant->save();

        if ($request->boolean('kirim_email') && $old !== $request->status) {
            // Email pelamar: butuh kolom email? form tidak meminta email, jadi kirim hanya jika sosmed berisi email
            // Untuk aman: log saja + coba kirim ke HRD sebagai arsip. Email pelamar dibahas terpisah.
            try {
                // Placeholder: jika nanti ada kolom email pelamar, kirim ke sana.
                // Mail::to($pelamarEmail)->send(new ApplicationStatusChanged($applicant));
            } catch (\Exception $e) {
                \Log::warning('Gagal kirim email status: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Status lamaran diperbarui.');
    }

    public function download($id)
    {
        $applicant = Applicant::with('position')->findOrFail($id);
        if (!Storage::disk('public')->exists($applicant->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }
        return Storage::disk('public')->download($applicant->file_path, $applicant->download_name);
    }

    public function destroy($id)
    {
        $applicant = Applicant::findOrFail($id);
        Storage::disk('public')->delete($applicant->file_path);
        $applicant->delete();
        return redirect()->route('admin.applications.index')->with('success', 'Data lamaran dihapus.');
    }

    public function export(Request $request)
    {
        // Export CSV (bisa dibuka di Excel) — tanpa dependency tambahan agar stabil di PHP 7.3
        $query = Applicant::with('position')->orderBy('created_at', 'desc');
        if ($request->filled('position_id')) $query->where('position_id', $request->position_id);
        if ($request->filled('status')) $query->where('status', $request->status);
        $rows = $query->get();

        $filename = 'lamaran_' . date('Ymd_His') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM agar Excel baca UTF-8 dengan benar
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Tanggal', 'Nama Lengkap', 'JK', 'HP', 'Email', 'Domisili', 'SIM', 'Posisi', 'Lokasi', 'Status Lamaran']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->created_at->format('Y-m-d H:i'),
                    $r->nama_lengkap, $r->jenis_kelamin, $r->no_hp,
                    $r->email ?: '-', $r->domisili ?: '-', $r->sim ?: '-',
                    $r->position->title ?? '-', $r->position->location ?? '-', $r->status,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
