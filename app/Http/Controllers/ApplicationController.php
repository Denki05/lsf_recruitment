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
    public function create()
    {
        $positions = Position::active()->orderBy('title')->get();
        return view('public.form', compact('positions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'status_pernikahan' => 'nullable|in:Belum Menikah,Menikah,Cerai',
            'agama' => 'nullable|string|max:30',
            'alamat_ktp' => 'required|string|max:1000',
            'no_hp' => 'required|regex:/^[0-9+\-\s]{10,20}$/',
            'sosmed' => 'nullable|string|max:255',
            'kendaraan' => 'required|string|max:30',
            'sim' => 'required|string|max:30',
            'position_id' => 'required|exists:positions,id',
            'berkas' => 'required|file|mimes:pdf,doc,docx,zip|max:5120',
        ], [
            'berkas.max' => 'Ukuran file maksimal 5 MB.',
            'berkas.mimes' => 'File harus PDF / DOC / DOCX / ZIP.',
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
            'status_pernikahan' => $validated['status_pernikahan'] ?? null,
            'agama' => $validated['agama'] ?? null,
            'alamat_ktp' => $validated['alamat_ktp'],
            'no_hp' => $validated['no_hp'],
            'sosmed' => $validated['sosmed'] ?? null,
            'kendaraan' => $validated['kendaraan'],
            'sim' => $validated['sim'],
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
