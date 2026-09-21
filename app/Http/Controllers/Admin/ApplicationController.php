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
        $positions = \App\Services\BranchAccess::scopePositions(Position::with('branch')->orderBy('title'))->get();
        $query = $this->filteredQuery($request);
        $perPage = $this->perPage($request);

        // Urut skor AI (level DB, null di bawah) atau skor keyword (dihitung per baris)
        if ($request->input('sort') === 'ai') {
            $query->orderByRaw('ai_score IS NULL, ai_score DESC');
            $applicants = $query->paginate($perPage)->appends($request->query());
        } elseif ($request->input('sort') === 'skor') {
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
            $applicants = $this->paginateManual($all, $request, $perPage);
        } else {
            $applicants = $query->paginate($perPage)->appends($request->query());
        }

        // Hot reload: request AJAX hanya me-render ulang daftar (tanpa reload halaman)
        if ($request->ajax()) {
            return view('admin.applications.partials.list', compact('applicants', 'positions'))->render();
        }

        return view('admin.applications.index', compact('applicants', 'positions'));
    }

    /** 10 data per halaman agar pas 1 layar tanpa scroll panjang. */
    protected function perPage(Request $request)
    {
        return 10;
    }

    /** Query lamaran sesudah scope cabang + semua filter/screening (tanpa ordering). */
    protected function filteredQuery(Request $request)
    {
        $query = \App\Services\BranchAccess::scopeApplicants(Applicant::with(['position.branch'])->latest());

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
        // Screening: SIM, pendidikan, bersedia lembur
        if ($request->filled('sim')) {
            $query->where('sim', $request->sim);
        }
        if ($request->filled('education')) {
            $query->where('education', $request->education);
        }
        if ($request->input('lembur') === '1') {
            $query->where('willing_overtime', true);
        } elseif ($request->input('lembur') === '0') {
            $query->where('willing_overtime', false);
        }
        // Screening: range gaji (preset select2 "min-maks", fallback input manual)
        if ($request->filled('gaji_range') && strpos($request->gaji_range, '-') !== false) {
            [$gmin, $gmax] = array_map('intval', explode('-', $request->gaji_range, 2));
            $query->where('expected_salary', '>=', $gmin)->where('expected_salary', '<=', $gmax);
        } else {
            if ($request->filled('gaji_min')) {
                $query->where('expected_salary', '>=', (int) $request->gaji_min);
            }
            if ($request->filled('gaji_max')) {
                $query->where('expected_salary', '<=', (int) $request->gaji_max);
            }
        }
        // Screening: umur (preset select2 "min-maks", dihitung per hari ini dari tanggal_lahir)
        if ($request->filled('umur_range') && strpos($request->umur_range, '-') !== false) {
            [$umin, $umaks] = array_map('intval', explode('-', $request->umur_range, 2));
            $query->whereNotNull('tanggal_lahir')
                ->whereDate('tanggal_lahir', '<=', now()->subYears($umin)->toDateString())
                ->whereDate('tanggal_lahir', '>=', now()->subYears($umaks + 1)->addDay()->toDateString());
        } elseif ($request->filled('umur_min') || $request->filled('umur_maks')) {
            $query->whereNotNull('tanggal_lahir');
            if ($request->filled('umur_min')) {
                $query->whereDate('tanggal_lahir', '<=', now()->subYears((int) $request->umur_min)->toDateString());
            }
            if ($request->filled('umur_maks')) {
                $query->whereDate('tanggal_lahir', '>=', now()->subYears((int) $request->umur_maks + 1)->addDay()->toDateString());
            }
        }

        return $query;
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
        $allowed = \App\Services\BranchAccess::scopeApplicants(Applicant::whereIn('id', $request->ids))->pluck('id')->all();
        if (count($allowed) !== count($request->ids)) {
            abort(403, 'Ada lamaran di luar cabang Anda.');
        }
        $rows = Applicant::whereIn('id', $allowed)->get(['id', 'status']);
        Applicant::whereIn('id', $allowed)->update([
            'status' => $request->status,
            'updated_at' => now(),
        ]);
        \App\ApplicantStatusLog::recordMany($rows, $request->status);
        return back()->with('success', count($request->ids) . ' lamaran diubah ke ' . $request->status . '.');
    }

    /**
     * Ubah status SEMUA hasil filter+screening aktif (tanpa centang satu-satu).
     * Param "status" = filter (boleh kosong), "target_status" = status tujuan.
     * Dibedakan agar filter status tidak tertabrak status tujuan.
     * Scope cabang + filter sama persis dengan daftar. Selalu JSON (dipakai fetch).
     */
    public function bulkFiltered(Request $request)
    {
        $request->validate([
            'target_status' => 'required|in:Baru,Seleksi,Interview,Diterima,Ditolak',
        ]);
        $rows = $this->filteredQuery($request)->setEagerLoads([])->get(['id', 'status']);
        $count = $this->filteredQuery($request)->update([
            'status' => $request->target_status,
            'updated_at' => now(),
        ]);
        \App\ApplicantStatusLog::recordMany($rows, $request->target_status);
        return response()->json(['ok' => true, 'count' => $count, 'status' => $request->target_status]);
    }

    /** Bandingkan 2-4 kandidat berdampingan (skor keyword + AI + data). */
    public function compare(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:2|max:4',
            'ids.*' => 'exists:applicants,id',
        ], ['ids.required' => 'Centang 2-4 lamaran untuk dibandingkan.', 'ids.min' => 'Pilih minimal 2 lamaran.', 'ids.max' => 'Maksimal 4 lamaran.']);
        $svc = new \App\Services\CvScreening();
        $rows = \App\Services\BranchAccess::scopeApplicants(Applicant::with('position')->whereIn('id', $request->ids))->get();
        if ($rows->count() !== count($request->ids)) {
            abort(403, 'Ada lamaran di luar cabang Anda.');
        }
        $data = [];
        foreach ($rows as $a) {
            try {
                $kw = $svc->score($a);
            } catch (\Exception $e) {
                $kw = ['score' => null];
            }
            $data[] = ['applicant' => $a, 'skor' => isset($kw['score']) ? $kw['score'] : null];
        }
        usort($data, function ($x, $y) {
            return ($y['skor'] === null ? -1 : $y['skor']) - ($x['skor'] === null ? -1 : $x['skor']);
        });
        return view('admin.applications.compare', compact('data'));
    }

    /** Salin evaluasi AI terakhir pelamar yang sama (tanpa hit API). */
    public function reuseAi($id)
    {
        $applicant = Applicant::findOrFail($id);
        \App\Services\BranchAccess::ensureApplicantAccess($applicant->load('position'));
        $prev = Applicant::where('id', '<>', $id)
            ->where('no_hp', $applicant->no_hp)
            ->whereNotNull('ai_score')
            ->latest('ai_evaluated_at')
            ->first();
        if (!$prev) {
            return back()->withErrors(['ai' => 'Tidak ada riwayat evaluasi AI untuk pelamar ini.']);
        }
        $applicant->update([
            'ai_score' => $prev->ai_score,
            'ai_summary' => $prev->ai_summary . ' (disalin dari lamaran #' . $prev->id . ')',
            'ai_strengths' => $prev->ai_strengths,
            'ai_gaps' => $prev->ai_gaps,
            'ai_evaluated_at' => now(),
        ]);
        return back()->with('success', 'Evaluasi disalin dari riwayat (skor ' . $prev->ai_score . '%) tanpa hit AI.');
    }

    public function show($id)
    {
        $applicant = Applicant::with(['position', 'statusLogs.user'])->findOrFail($id);
        \App\Services\BranchAccess::ensureApplicantAccess($applicant);
        $zipList = [];
        if (strtolower(pathinfo($applicant->file_original, PATHINFO_EXTENSION)) === 'zip') {
            $full = Storage::disk('local')->path($applicant->file_path);
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
        // Riwayat lamaran pelamar yang sama (no HP sama)
        $history = Applicant::with('position')
            ->where('id', '<>', $applicant->id)
            ->where('no_hp', $applicant->no_hp)
            ->latest()
            ->take(5)
            ->get();
        return view('admin.applications.show', compact('applicant', 'zipList', 'screening', 'history'));
    }

    /** Jalankan evaluasi AI (on-demand, hasil di-cache di DB). */
    public function evaluateAi($id)
    {
        $applicant = Applicant::with('position')->findOrFail($id);
        \App\Services\BranchAccess::ensureApplicantAccess($applicant);
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
        \App\Services\BranchAccess::ensureApplicantAccess($applicant);
        $applicant->status = $request->status;
        if ($request->has('catatan_admin')) {
            $applicant->catatan_admin = $request->catatan_admin;
        }
        $applicant->save();

        \App\ApplicantStatusLog::record($applicant->id, $old, $applicant->status, $request->catatan_admin);

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

        // Aksi kilat dari kartu (AJAX): balas JSON agar daftar bisa refresh tanpa reload
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'status' => $applicant->status, 'nama' => $applicant->nama_lengkap]);
        }

        return back()->with('success', 'Status lamaran diperbarui.');
    }

    public function download($id)
    {
        $applicant = Applicant::with('position')->findOrFail($id);
        \App\Services\BranchAccess::ensureApplicantAccess($applicant);
        if (!Storage::disk('local')->exists($applicant->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }
        return Storage::disk('local')->download($applicant->file_path, $applicant->download_name);
    }

    /** Tampilkan PDF inline di browser (untuk preview di halaman detail). */
    public function preview($id)
    {
        $applicant = Applicant::findOrFail($id);
        \App\Services\BranchAccess::ensureApplicantAccess($applicant);

        $disk = Storage::disk('local');
        $isPdf = strtolower(pathinfo($applicant->file_original, PATHINFO_EXTENSION)) === 'pdf';
        if (!$isPdf || !$disk->exists($applicant->file_path)) {
            abort(404);
        }

        // Jangan percaya ekstensi: pastikan isinya benar-benar PDF
        $full = $disk->path($applicant->file_path);
        $fh = fopen($full, 'rb');
        $magic = $fh ? fread($fh, 5) : '';
        if ($fh) {
            fclose($fh);
        }
        if ($magic !== '%PDF-') {
            abort(404);
        }

        return response()->file($full, [
            'Content-Type'           => 'application/pdf',
            'Content-Disposition'    => 'inline; filename="cv-' . $applicant->id . '.pdf"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control'          => 'private, no-store',
        ]);
    }

    public function destroy($id)
    {
        $applicant = Applicant::findOrFail($id);
        \App\Services\BranchAccess::ensureApplicantAccess($applicant->load('position'));
        Storage::disk('local')->delete($applicant->file_path);
        $applicant->delete();
        return redirect()->route('admin.applications.index')->with('success', 'Data lamaran dihapus.');
    }

    public function export(Request $request)
    {
        // Export CSV (bisa dibuka di Excel) — tanpa dependency tambahan agar stabil di PHP 7.3.
        // Mengikuti filter+screening yang sedang aktif (sama dengan daftar).
        $query = $this->filteredQuery($request)->orderBy('created_at', 'desc');
        $rows = $query->get();

        $filename = 'lamaran_' . date('Ymd_His') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM agar Excel baca UTF-8 dengan benar
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Tanggal', 'Nama Lengkap', 'Tgl Lahir', 'Umur', 'JK', 'HP', 'Email', 'Domisili', 'Pendidikan', 'Gaji Diminta', 'Lembur', 'SIM', 'Pengalaman Kerja', 'Cabang', 'Posisi', 'Lokasi', 'Status Lamaran']);
            foreach ($rows as $r) {
                fputcsv($out, array_map([$this, 'csvSafe'], [
                    $r->created_at->format('Y-m-d H:i'),
                    $r->nama_lengkap,
                    $r->tanggal_lahir ? $r->tanggal_lahir->format('Y-m-d') : '-',
                    $r->umur !== null ? $r->umur : '-',
                    $r->jenis_kelamin, $r->no_hp,
                    $r->email ?: '-', $r->domisili ?: '-',
                    $r->education ?: '-', $r->expected_salary !== null ? $r->expected_salary : '-',
                    $r->willing_overtime ? 'Ya' : 'Tidak',
                    $r->sim ?: '-',
                    $r->pengalaman_kerja ?: '-',
                    $r->position && $r->position->branch ? $r->position->branch->name : '-',
                    $r->position->title ?? '-', $r->position->location ?? '-', $r->status,
                ]));
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Cegah formula injection di Excel: sel berawalan = + - @ diberi tanda petik. */
    protected function csvSafe($v)
    {
        if (is_string($v) && $v !== '' && $v !== '-' && preg_match('/^[=+\-@\t\r]/', $v)) {
            return "'" . $v;
        }
        return $v;
    }
}
