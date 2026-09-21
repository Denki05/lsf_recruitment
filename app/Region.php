<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = ['nama', 'provinsi'];

    public function scopeOrdered($query)
    {
        return $query->orderBy('nama');
    }
}
