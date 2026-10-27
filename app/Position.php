<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Position extends Model
{
    protected $fillable = ['branch_id', 'title', 'slug', 'location', 'gaji', 'show_salary', 'pendidikan_minimal', 'usia_min', 'usia_maks', 'butuh_lembur', 'syarat_sim', 'description', 'requirements', 'benefits', 'show_benefits', 'skill_tags', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'butuh_lembur' => 'boolean', 'show_salary' => 'boolean', 'show_benefits' => 'boolean'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Position $position) {
            if (!$position->public_token) {
                $position->public_token = static::generatePublicToken();
            }
            $position->slug = static::uniqueSlug($position->slug ?: $position->title, $position->id);
        });

        static::saved(function (Position $position) {
            $position->ensureDefaultShortLink();
        });
    }

    /** Token acak untuk URL publik, hindari karakter mirip (0/1/l/o). */
    public static function generatePublicToken($length = 10)
    {
        $chars = 'abcdefghijkmnpqrstuvwxyz23456789';
        $max = strlen($chars) - 1;

        do {
            $token = '';
            for ($i = 0; $i < $length; $i++) {
                $token .= $chars[random_int(0, $max)];
            }
        } while (!preg_match('/[a-z]/', $token) || static::where('public_token', $token)->exists());

        return $token;
    }

    public function getRouteKeyName()
    {
        return 'public_token';
    }

    /** Slug unik untuk URL share pendek, mis. "mm" atau "manajer-marketing". */
    public static function uniqueSlug($value, $ignoreId = null)
    {
        $base = Str::slug((string) $value) ?: 'loker';
        if (ShortLink::isReservedCode($base)) {
            $base = 'loker-' . $base;
        }
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)
            ->when($ignoreId, function ($q) use ($ignoreId) {
                $q->where('id', '<>', $ignoreId);
            })
            ->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public function getShareUrlAttribute()
    {
        $link = $this->relationLoaded('defaultShortLink')
            ? $this->defaultShortLink
            : $this->defaultShortLink()->first();

        if ($link && $link->isAvailable()) {
            return $link->url;
        }
        return route('jobs.show', $this);
    }

    public function shortLinks()
    {
        return $this->hasMany(ShortLink::class);
    }

    public function defaultShortLink()
    {
        return $this->hasOne(ShortLink::class)->where('is_default', true);
    }

    /** Setiap loker otomatis punya satu link pendek default. */
    public function ensureDefaultShortLink()
    {
        $existing = $this->shortLinks()->where('is_default', true)->first();
        if ($existing) {
            return $existing;
        }
        return $this->shortLinks()->create([
            'code' => ShortLink::generateCode(),
            'is_active' => true,
            'is_default' => true,
        ]);
    }

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

    /** Daftar benefit/fasilitas, satu poin per baris. */
    public function getBenefitListAttribute()
    {
        if (empty($this->benefits)) {
            return [];
        }
        $lines = preg_split('/\r\n|\r|\n/', $this->benefits);
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
