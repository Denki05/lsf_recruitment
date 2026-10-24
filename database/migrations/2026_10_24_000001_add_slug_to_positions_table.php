<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddSlugToPositionsTable extends Migration
{
    public function up()
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->string('slug', 60)->nullable()->after('title');
        });

        // Isi slug loker lama agar link share langsung tersedia tanpa menunggu edit.
        $used = [];
        foreach (DB::table('positions')->orderBy('id')->get() as $position) {
            $base = Str::slug($position->title) ?: 'loker';
            if (in_array($base, ['admin', 'lamaran', 'loker', 'c', 'api'], true)) {
                $base = 'loker-' . $base;
            }
            $slug = $base;
            $i = 2;
            while (isset($used[$slug])) {
                $slug = $base . '-' . $i++;
            }
            $used[$slug] = true;
            DB::table('positions')->where('id', $position->id)->update(['slug' => $slug]);
        }

        Schema::table('positions', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down()
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
}
