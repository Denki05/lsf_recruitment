<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'location', 'address', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDisplayAttribute()
    {
        return $this->location ? $this->name . ' — ' . $this->location : $this->name;
    }
}
