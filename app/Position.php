<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['branch_id', 'title', 'location', 'gaji', 'pendidikan_minimal', 'usia_min', 'usia_maks', 'butuh_lembur', 'syarat_sim', 'description', 'requirements', 'skill_tags', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'butuh_lembur' => 'boolean'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }

    /** Daftar SIM yang disyaratkan, dukung format tunggal "B" maupun multi "A,C". */
    public function getSimListAttribute()
    {
        if (empty($this->syarat_sim)) {
            return [];
        }
        $parts = preg_split('/[,;\/|]+/', (string) $this->syarat_sim);
        $out = [];
        foreach ($parts as $p) {
            $p = strtoupper(trim($p));
            if (in_array($p, ['A', 'B', 'C'], true) && !in_array($p, $out, true)) {
                $out[] = $p;
            }
        }
        return $out;
    }

    public static function normalizeSim($value)
    {
        if (empty($value)) {
            return null;
        }
        $list = is_array($value) ? $value : preg_split('/[,;\/|]+/', (string) $value);
        $out = [];
        foreach ((array) $list as $p) {
            $p = strtoupper(trim((string) $p));
            if (in_array($p, ['A', 'B', 'C'], true) && !in_array($p, $out, true)) {
                $out[] = $p;
            }
        }
        return $out ? implode(',', $out) : null;
    }

    public function getFullTitleAttribute()
    {
        $branch = $this->branch ? ' [' . $this->branch->name . ']' : '';
        return $this->title . ' (' . $this->location . ')' . $branch;
    }

    public function scopeActive($query)
    {
        // Loker tampil hanya jika loker aktif DAN cabangnya aktif
        return $query->where('is_active', true)
            ->whereHas('branch', function ($q) {
                $q->where('is_active', true);
            });
    }

    /**
     * Daftar tag skill: [['tag' => ..., 'weight' => 1-5], ...].
     * Format input: "gudang, forklift:3, stock opname:2" (bobot opsional).
     */
    public function getSkillListAttribute()
    {
        if (empty($this->skill_tags)) {
            return [];
        }
        $out = [];
        foreach (explode(',', $this->skill_tags) as $raw) {
            $raw = trim(mb_strtolower($raw));
            if ($raw === '') {
                continue;
            }
            $weight = 1;
            if (strpos($raw, ':') !== false) {
                [$tag, $w] = array_map('trim', explode(':', $raw, 2));
                $w = (int) $w;
                if ($tag !== '' && $w >= 1 && $w <= 5) {
                    $raw = $tag;
                    $weight = $w;
                }
            }
            if ($raw !== '' && !isset($out[$raw])) {
                $out[$raw] = $weight;
            }
        }
        $list = [];
        foreach ($out as $tag => $weight) {
            $list[] = ['tag' => $tag, 'weight' => $weight];
        }
        return $list;
    }

    /** Daftar baris requirement bersih. */
    public function getRequirementListAttribute()
    {
        if (empty($this->requirements)) {
            return [];
        }
        $lines = preg_split('/\r\n|\r|\n/', $this->requirements);
        $out = [];
        foreach ($lines as $l) {
            $l = trim(preg_replace('/^[-•*\d.)\s]+/u', '', $l));
            if (mb_strlen($l) >= 3) {
                $out[] = $l;
            }
        }
        return array_values(array_unique($out));
    }
}
