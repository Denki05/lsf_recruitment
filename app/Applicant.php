<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $fillable = [
        'position_id', 'nama_lengkap', 'nama_panggilan', 'tempat_lahir', 'tanggal_lahir',
        'jenis_kelamin', 'no_ktp', 'alamat_ktp', 'alamat_sekarang', 'no_hp', 'email', 'domisili',
        'expected_salary', 'willing_overtime', 'education', 'sosmed',
        'status_pernikahan', 'agama', 'kendaraan', 'sim', 'pengalaman_kerja',
        'file_path', 'file_original', 'file_mime', 'file_size',
        'status', 'catatan_admin', 'ai_consent',
        'ai_score', 'ai_summary', 'ai_strengths', 'ai_gaps', 'ai_evaluated_at',
    ];

    const EDUCATION_LEVELS = ['SD', 'SMP', 'SMA/SMK', 'D3', 'D4/S1', 'S2', 'S3'];

    protected $casts = [
        'ai_consent' => 'boolean',
        'willing_overtime' => 'boolean',
        'ai_evaluated_at' => 'datetime',
    ];

    protected $dates = ['tanggal_lahir'];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /** Umur dalam tahun penuh per hari ini (tanggal-bulan-tahun berjalan). Null bila tgl lahir kosong. */
    public function getUmurAttribute()
    {
        if (empty($this->tanggal_lahir)) {
            return null;
        }
        try {
            $tgl = $this->tanggal_lahir instanceof \DateTimeInterface
                ? \Carbon\Carbon::instance($this->tanggal_lahir)
                : \Carbon\Carbon::parse($this->tanggal_lahir);
            return $tgl->age;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function educationRank($edu)
    {
        $i = array_search((string) $edu, self::EDUCATION_LEVELS);
        return $i === false ? -1 : $i;
    }

    public function getDownloadNameAttribute()
    {
        $ext = pathinfo($this->file_original, PATHINFO_EXTENSION);
        $nama = preg_replace('/[^A-Za-z0-9]+/', '_', $this->nama_lengkap);
        $posisi = $this->position ? preg_replace('/[^A-Za-z0-9]+/', '_', $this->position->title) : 'Posisi';
        return trim($nama, '_') . '-' . trim($posisi, '_') . '-' . $this->created_at->format('Ymd_His') . '.' . $ext;
    }

    public function statusLogs()
    {
        return $this->hasMany(ApplicantStatusLog::class)->orderBy('id', 'desc');
    }
}
