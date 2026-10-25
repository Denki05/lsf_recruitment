<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ShortenDefaultShortLinkCodes extends Migration
{
    public function up()
    {
        $alphabet = 'abcdefghijkmnpqrstuvwxyz23456789';
        $reserved = ['admin', 'lamaran', 'loker', 's', 'c', 'api', 'login', 'logout'];
        $max = strlen($alphabet) - 1;

        $links = DB::table('short_links')->where('is_default', true)->get(['id', 'code']);

        foreach ($links as $link) {
            if (strlen((string) $link->code) <= 5) {
                continue;
            }

            do {
                $code = '';
                for ($i = 0; $i < 5; $i++) {
                    $code .= $alphabet[random_int(0, $max)];
                }
            } while (in_array($code, $reserved, true)
                || DB::table('short_links')->where('code', $code)->exists());

            DB::table('short_links')->where('id', $link->id)->update(['code' => $code]);
        }
    }

    public function down()
    {
        // Kode lama yang panjang tidak dapat dipulihkan.
    }
}
