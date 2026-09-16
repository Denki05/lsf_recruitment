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
            'nama_panggilan' => 'required|string|max:50',
            'tempat_lahir' => 'required|string|max:60',
            'tanggal_lahir' => 'required|date|before:-17 years',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'no_ktp' => 'required|digits:16|unique:applicants,no_ktp',
            'alamat_ktp' => 'required|string|max:1000',
            'alamat_sekarang' => 'nullable|string|max:1000',
            'sama_dengan_ktp' => 'nullable|boolean',
            'no_hp' => 'required|regex:/^[0-9+\-\s]{10,20}$/',
            'sosmed' => 'nullable|string|max:255',
            'status_pernikahan' => 'required|in:Belum Menikah,Menikah,Cerai',
            'agama' => 'required|string|max:30',
            'kendaraan' => 'required|string|max:30',
            'sim' => 'required|string|max:30',
            'position_id' => 'required|exists:positions,id',
            'berkas' => 'required|file|mimes:pdf,doc,docx,zip|max:5120',
        ], [
            'tanggal_lahir.before' => 'Minimal usia 17 tahun.',
            'no_ktp.digits' => 'No KTP harus 16 digit angka.',
            'no_ktp.unique' => 'No KTP ini sudah pernah melamar.',
            'berkas.max' => 'Ukuran file maksimal 5 MB.',
            'berkas.mimes' => 'File harus PDF / DOC / DOCX / ZIP.',
        ]);

        $position = Position::findOrFail($validated['position_id']);
        if (!$position->is_active) {
            return back()->withErrors(['position_id' => 'Lowongan ini sudah ditutup.'])->withInput();
        }

        $alamatSekarang = $validated['alamat_sekarang'] ?? null;
        if ($request->boolean('sama_dengan_ktp')) {
            $alamatSekarang = $validated['alamat_ktp'];
        }

        $file = $request->file('berkas');
        // Simpan dengan nama acak di disk (aman), nama cantik dipakai saat download
        $storedPath = $file->store('lamaran', 'public');

        $applicant = Applicant::create([
            'position_id' => $position->id,
            'nama_lengkap' => $validated['nama_lengkap'],
            'nama_panggilan' => $validated['nama_panggilan'],
            'tempat_lahir' => $validated['tempat_lahir'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'no_ktp' => $validated['no_ktp'],
            'alamat_ktp' => $validated['alamat_ktp'],
            'alamat_sekarang' => $alamatSekarang,
            'no_hp' => $validated['no_hp'],
            'sosmed' => $validated['sosmed'] ?? null,
            'status_pernikahan' => $validated['status_pernikahan'],
            'agama' => $validated['agama'],
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
