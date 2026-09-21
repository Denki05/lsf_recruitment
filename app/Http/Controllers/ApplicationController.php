<?php

namespace App\Http\Controllers;

use App\Applicant;
use App\Position;
use App\Mail\ApplicationReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function create($position = null)
    {
        $positions = Position::with('branch')->active()->orderBy('title')->get();
        $selectedPosition = null;
        if ($position) {
            $selectedPosition = Position::with('branch')->active()->find($position);
            if (!$selectedPosition) {
                return redirect()->route('jobs.index');
            }
        }
        $educations = \App\Applicant::EDUCATION_LEVELS;
        session(['form_loaded_at' => time()]);
        return view('public.form', compact('positions', 'selectedPosition', 'educations'));
    }

    public function store(Request $request)
    {
        // Anti-bot: honeypot harus kosong + form tidak boleh terkirim < 3 detik
        if ($request->filled('website')) {
            return redirect()->route('jobs.index');
        }
        $loadedAt = session('form_loaded_at');
        if ($loadedAt && (time() - $loadedAt) < 3) {
            return back()->withErrors(['form' => 'Form terkirim terlalu cepat. Silakan coba lagi.'])->withInput();
        }

        // Rapikan no HP: buang spasi/strip, awalan +62/62 menjadi 0 (agar deteksi duplikat konsisten)
        if ($request->filled('no_hp')) {
            $hp = preg_replace('/[^0-9+]/', '', $request->input('no_hp'));
            $hp = preg_replace('/^(\+62|62)/', '0', $hp);
            $request->merge(['no_hp' => $hp]);
        }

        // Tahap 1: form cepat (identitas minimal + SIM + CV)
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tanggal_lahir' => 'required|date|before:today|after:1950-01-01',
            'no_hp' => 'required|regex:/^[0-9+\-\s]{10,20}$/',
            'email' => 'nullable|email|max:150',
            'domisili' => 'required|string|max:100',
            'expected_salary' => 'required|integer|min:0|max:1000000000',
            'education' => 'required|in:SD,SMP,SMA/SMK,D3,D4/S1,S2,S3',
            'willing_overtime' => 'nullable|boolean',
            'sim' => 'required|in:A,B,C,A dan C,Tidak Punya',
            'belum_berpengalaman' => 'nullable|boolean',
            'pengalaman_kerja' => 'required_without:belum_berpengalaman|nullable|string|max:3500',
            'position_id' => 'required|exists:positions,id',
            'berkas' => 'required|file|mimes:pdf,doc,docx,zip|max:5120',
            'ai_consent' => 'required|accepted',
        ], [
            'berkas.max' => 'Ukuran file maksimal 5 MB.',
            'berkas.mimes' => 'File harus PDF / DOC / DOCX / ZIP.',
            'ai_consent.accepted' => 'Anda harus menyetujui pemrosesan data untuk melamar.',
            'pengalaman_kerja.required_without' => 'Pengalaman kerja wajib diisi, atau centang "Saya belum punya pengalaman kerja".',
            'pengalaman_kerja.max' => 'Pengalaman kerja terlalu panjang (maks. sekitar 3000 karakter).',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'expected_salary.required' => 'Permintaan gaji wajib diisi.',
            'education.required' => 'Jenjang pendidikan wajib dipilih.',
        ]);

        $position = Position::with('branch')->findOrFail($validated['position_id']);
        if (!$position->is_active || !optional($position->branch)->is_active) {
            return back()->withErrors(['position_id' => 'Lowongan ini sudah ditutup.'])->withInput();
        }

        // Cegah lamaran ganda: no HP + posisi yang sama dalam 30 hari terakhir
        $sudahMelamar = Applicant::where('position_id', $position->id)
            ->where('no_hp', $validated['no_hp'])
            ->where('created_at', '>=', now()->subDays(30))
            ->exists();
        if ($sudahMelamar) {
            return back()->withErrors(['no_hp' => 'Nomor HP ini sudah melamar posisi tersebut dalam 30 hari terakhir. Mohon tunggu kabar dari tim HRD.'])->withInput();
        }

        $file = $request->file('berkas');
        // Simpan dengan nama acak di disk (aman), nama cantik dipakai saat download
        $storedPath = $file->store('lamaran', 'local');

        $pengalaman = $request->boolean('belum_berpengalaman')
            ? 'Belum memiliki pengalaman kerja.'
            : trim($validated['pengalaman_kerja'] ?? '');

        $applicant = Applicant::create([
            'position_id' => $position->id,
            'nama_lengkap' => $validated['nama_lengkap'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'no_hp' => $validated['no_hp'],
            'email' => $validated['email'] ?? null,
            'domisili' => $validated['domisili'],
            'expected_salary' => $validated['expected_salary'],
            'education' => $validated['education'],
            'willing_overtime' => $request->boolean('willing_overtime'),
            'sim' => $validated['sim'],
            'pengalaman_kerja' => $pengalaman,
            'ai_consent' => true,
            'file_path' => $storedPath,
            'file_original' => $file->getClientOriginalName(),
            'file_mime' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'Baru',
        ]);

        // Notifikasi email ke HRD (jangan gagalkan submit jika SMTP belum disetting)
        try {
            $to = env('HRD_MAIL_TO', env('MAIL_FROM_ADDRESS'));
            if ($to) {
                Mail::to($to)->send(new ApplicationReceived($applicant));
            }
        } catch (\Exception $e) {
            \Log::warning('Gagal kirim email lamaran: ' . $e->getMessage());
        }

        $request->session()->put('last_applicant_id', $applicant->id);
        return redirect()->route('lamaran.sukses');
    }

    public function success()
    {
        $id = session('last_applicant_id');
        if (!$id) {
            return redirect()->route('jobs.index');
        }
        $applicant = Applicant::with('position.branch')->findOrFail($id);
        return view('public.success', compact('applicant'));
    }
}
