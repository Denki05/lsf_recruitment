<?php

namespace App\Http\Controllers;

use App\ShortLink;

class ShortLinkController extends Controller
{
    /** URL pendek publik, cth. /s/mm → /loker/6 (klik dicatat). */
    public function redirect($code)
    {
        $link = ShortLink::with('position.branch')
            ->where('code', strtolower($code))
            ->first();

        if (!$link || !$link->isAvailable()) {
            abort(404);
        }

        $position = $link->position;
        if (!$position || !$position->is_active || !$position->branch || !$position->branch->is_active) {
            abort(404);
        }

        $link->recordClick();

        return redirect()->route('jobs.show', $position->id);
    }

    /** Link lama di root diarahkan permanen ke format /s/{code}. */
    public function legacyRedirect($code)
    {
        return redirect()->route('jobs.short', $code, 301);
    }
}
