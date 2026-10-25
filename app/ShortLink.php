<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShortLink extends Model
{
    /** Segmen root yang dipakai halaman sistem, tidak boleh jadi kode. */
    const RESERVED_CODES = ['admin', 'lamaran', 'loker', 's', 'c', 'api', 'login', 'logout'];

    const CODE_CHARS = 'abcdefghijkmnpqrstuvwxyz23456789';

    protected $fillable = ['position_id', 'code', 'is_active', 'expires_at', 'is_default'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'expires_at' => 'datetime',
        'last_clicked_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (ShortLink $link) {
            $link->code = strtolower(trim((string) $link->code));
        });
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /** Input datetime-local memakai format ISO "Y-m-d\TH:i", bukan format bawaan cast. */
    public function setExpiresAtAttribute($value)
    {
        $this->attributes['expires_at'] = $value ? Carbon::parse($value) : null;
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isAvailable()
    {
        return $this->is_active && !$this->isExpired();
    }

    public function getUrlAttribute()
    {
        return route('jobs.short', $this->code);
    }

    public function getTargetUrlAttribute()
    {
        return $this->position ? route('jobs.show', $this->position->id) : '#';
    }

    /** Hitung 1 klik tanpa menimpa atribut lain (aman untuk request bersamaan). */
    public function recordClick()
    {
        static::whereKey($this->getKey())->update([
            'clicks' => DB::raw('clicks + 1'),
            'last_clicked_at' => now(),
        ]);
        $this->clicks = (int) $this->clicks + 1;
        $this->last_clicked_at = now();
    }

    public static function isReservedCode($code)
    {
        return in_array(strtolower(trim((string) $code)), static::RESERVED_CODES, true);
    }

    public static function codeExists($code)
    {
        return static::where('code', strtolower(trim((string) $code)))->exists();
    }

    public static function generateCode($length = 5)
    {
        $max = strlen(static::CODE_CHARS) - 1;
        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= static::CODE_CHARS[random_int(0, $max)];
            }
        } while (static::isReservedCode($code) || static::codeExists($code));

        return $code;
    }
}
