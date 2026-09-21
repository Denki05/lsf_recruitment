<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'is_superadmin',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_superadmin' => 'boolean',
    ];

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_user');
    }

    public function isSuperadmin()
    {
        return (bool) $this->is_superadmin;
    }

    /** Label peran: Manajer/Developer (semua cabang) vs Admin/HRD (cabang sendiri). */
    public function roleLabel()
    {
        return $this->isSuperadmin() ? 'Manajer/Developer' : 'Admin/HRD';
    }

    /** Daftar branch_id yang boleh diakses user ini. Superadmin = semua. */
    public function accessibleBranchIds()
    {
        if ($this->isSuperadmin()) {
            return Branch::pluck('id')->all();
        }
        return $this->branches()->pluck('branches.id')->all();
    }

    public function canAccessBranch($branchId)
    {
        if ($this->isSuperadmin()) {
            return true;
        }
        if (empty($branchId)) {
            return false;
        }
        return $this->branches()->where('branches.id', $branchId)->exists();
    }
}
