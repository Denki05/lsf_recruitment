<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['title', 'location', 'syarat_sim', 'description', 'requirements', 'skill_tags', 'is_active'];

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
