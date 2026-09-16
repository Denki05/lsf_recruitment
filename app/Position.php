<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['title', 'location', 'syarat_sim', 'description', 'requirements', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }

    public function getFullTitleAttribute()
    {
        return $this->title . ' (' . $this->location . ')';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Cek SIM pelamar terhadap syarat loker.
     * $sim: A / B / C / A dan C / Tidak Punya (atau null)
     * Hasil: Cocok | Ditinjau | Kurang
     */
    public function flagSim($sim)
    {
        if (empty($this->syarat_sim)) {
            return 'Ditinjau'; // loker tanpa syarat SIM
        }
        if (empty($sim) || stripos($sim, 'Tidak Punya') !== false) {
            return 'Kurang';
        }
        // Syarat 'A' terpenuhi oleh 'A' / 'A dan C'; dst.
        return stripos($sim, $this->syarat_sim) !== false ? 'Cocok' : 'Kurang';
    }
}
