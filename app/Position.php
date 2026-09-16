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

    /** Daftar tag skill bersih (lowercase, unik). */
    public function getSkillListAttribute()
    {
        if (empty($this->skill_tags)) {
            return [];
        }
        $tags = array_map('trim', explode(',', mb_strtolower($this->skill_tags)));
        return array_values(array_unique(array_filter($tags)));
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
