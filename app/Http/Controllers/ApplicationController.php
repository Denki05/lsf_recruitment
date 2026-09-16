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
        $positions = Position::active()->orderBy('title')->get();
        $selectedPosition = null;
        if ($position) {
            $selectedPosition = Position::active()->find($position);
            if (!$selectedPosition) {
                return redirect()->route('jobs.index');
            }
        }
        return view('public.form', compact('positions', 'selectedPosition'));
    }

    public function store(Request $request)
    {
        // Tahap 1: form cepat (identitas minimal + SIM + CV)
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'no_hp' => 'required|regex:/^[0-9+\-\s]{10,20}$/',
            'email' => 'nullable|email|max:150',
            'domisili' => 'required|string|max:100',
            'sim' => 'required|in:A,B,C,A dan C,Tidak Punya',
            'position_id' => 'required|exists:positions,id',
            'berkas' => 'required|file|mimes:pdf,doc,docx,zip|max:5120',
            'ai_consent' => 'required|accepted',
        ], [
            'berkas.max' => 'Ukuran file maksimal 5 MB.',
            'berkas.mimes' => 'File harus PDF / DOC / DOCX / ZIP.',
            'ai_consent.accepted' => 'Anda harus menyetujui pemrosesan data untuk melamar.',
        ]);

        $position = Position::findOrFail($validated['position_id']);
        if (!$position->is_active) {
            return back()->withErrors(['position_id' => 'Lowongan ini sudah ditutup.'])->withInput();
        }

        $file = $request->file('berkas');
        // Simpan dengan nama acak di disk (aman), nama cantik dipakai saat download
        $storedPath = $file->store('lamaran', 'public');

        $applicant = Applicant::create([
            'position_id' => $position->id,
            'nama_lengkap' => $validated['nama_lengkap'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'no_hp' => $validated['no_hp'],
            'email' => $validated['email'] ?? null,
            'domisili' => $validated['domisili'],
            'sim' => $validated['sim'],
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

        return redirect()->route('lamaran.sukses', $applicant->id);
    }

    public function success($id)
    {
        $applicant = Applicant::with('position')->findOrFail($id);
        return view('public.success', compact('applicant'));
    }
}
