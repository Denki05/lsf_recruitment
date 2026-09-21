<?php

namespace App\Services;

use App\Branch;
use Illuminate\Support\Facades\Auth;

class BranchAccess
{
    /** Cabang yang boleh diakses user login (superadmin = semua). */
    public static function accessibleBranches()
    {
        $user = Auth::user();
        if (!$user) {
            return Branch::whereRaw('1=0')->get();
        }
        if ($user->isSuperadmin()) {
            return Branch::orderBy('name')->get();
        }
        return $user->branches()->orderBy('name')->get();
    }

    public static function accessibleIds()
    {
        return self::accessibleBranches()->pluck('id')->all();
    }

    /** branch_id aktif di sesi (null = semua yang boleh diakses). */
    public static function currentBranchId()
    {
        $id = session('current_branch_id');
        if (empty($id)) {
            return null;
        }
        $user = Auth::user();
        if (!$user) {
            return null;
        }
        if ($user->isSuperadmin()) {
            return Branch::where('id', $id)->exists() ? (int) $id : null;
        }
        return $user->branches()->where('branches.id', $id)->exists() ? (int) $id : null;
    }

    public static function currentBranch()
    {
        $id = self::currentBranchId();
        return $id ? Branch::find($id) : null;
    }

    /** Filter query Position sesuai hak + cabang aktif. */
    public static function scopePositions($query)
    {
        $user = Auth::user();
        if ($user && $user->isSuperadmin()) {
            if ($bid = self::currentBranchId()) {
                $query->where('positions.branch_id', $bid);
            }
            return $query;
        }
        $ids = self::accessibleIds();
        $query->whereIn('positions.branch_id', $ids);
        if ($bid = self::currentBranchId()) {
            $query->where('positions.branch_id', $bid);
        }
        return $query;
    }

    /** Filter query Applicant (via position) sesuai hak + cabang aktif. */
    public static function scopeApplicants($query)
    {
        $user = Auth::user();
        $bid = self::currentBranchId();
        if ($user && $user->isSuperadmin()) {
            if ($bid) {
                $query->whereHas('position', function ($q) use ($bid) {
                    $q->where('branch_id', $bid);
                });
            }
            return $query;
        }
        $ids = self::accessibleIds();
        $query->whereHas('position', function ($q) use ($ids, $bid) {
            $q->whereIn('branch_id', $ids);
            if ($bid) {
                $q->where('branch_id', $bid);
            }
        });
        return $query;
    }

    public static function ensurePositionAccess($position)
    {
        $user = Auth::user();
        if ($user && $user->isSuperadmin()) {
            return;
        }
        if (!$position || !$user || !$user->canAccessBranch($position->branch_id)) {
            abort(403, 'Anda tidak punya akses ke cabang ini.');
        }
    }

    public static function ensureApplicantAccess($applicant)
    {
        $user = Auth::user();
        if ($user && $user->isSuperadmin()) {
            return;
        }
        $bid = $applicant && $applicant->position ? $applicant->position->branch_id : null;
        if (!$applicant || !$user || !$user->canAccessBranch($bid)) {
            abort(403, 'Anda tidak punya akses ke cabang ini.');
        }
    }
}
