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
}
