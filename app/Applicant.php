<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $fillable = [
        'position_id', 'nama_lengkap', 'nama_panggilan', 'tempat_lahir', 'tanggal_lahir',
        'jenis_kelamin', 'no_ktp', 'alamat_ktp', 'alamat_sekarang', 'no_hp', 'email', 'domisili', 'sosmed',
        'status_pernikahan', 'agama', 'kendaraan', 'sim',
        'file_path', 'file_original', 'file_mime', 'file_size',
        'status', 'catatan_admin',
    ];

    protected $dates = ['tanggal_lahir'];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function getDownloadNameAttribute()
    {
        $ext = pathinfo($this->file_original, PATHINFO_EXTENSION);
        $nama = preg_replace('/[^A-Za-z0-9]+/', '_', $this->nama_lengkap);
        $posisi = $this->position ? preg_replace('/[^A-Za-z0-9]+/', '_', $this->position->title) : 'Posisi';
        return trim($nama, '_') . '-' . trim($posisi, '_') . '-' . $this->created_at->format('Ymd_His') . '.' . $ext;
    }

    /** Label auto-flag screening SIM dari syarat loker. */
    public function getSimFlagAttribute()
    {
        if (!$this->position) {
            return 'Ditinjau';
        }
        return $this->position->flagSim($this->sim);
    }
}
