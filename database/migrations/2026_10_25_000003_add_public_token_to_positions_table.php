<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPublicTokenToPositionsTable extends Migration
{
    public function up()
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->string('public_token', 16)->nullable()->unique()->after('slug');
        });

        $chars = 'abcdefghijkmnpqrstuvwxyz23456789';
        $max = strlen($chars) - 1;

        $positions = DB::table('positions')->select('id')->whereNull('public_token')->get();
        foreach ($positions as $position) {
            do {
                $token = '';
                for ($i = 0; $i < 10; $i++) {
                    $token .= $chars[random_int(0, $max)];
                }
            } while (!preg_match('/[a-z]/', $token) || DB::table('positions')->where('public_token', $token)->exists());

            DB::table('positions')->where('id', $position->id)->update(['public_token' => $token]);
        }
    }

    public function down()
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
            $table->dropColumn('public_token');
        });
    }
}
