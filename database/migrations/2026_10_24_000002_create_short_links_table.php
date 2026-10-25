<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateShortLinksTable extends Migration
{
    public function up()
    {
        Schema::create('short_links', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('position_id');
            $table->string('code', 40)->unique();
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamp('last_clicked_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
        });

        // Setiap loker lama langsung punya satu link pendek default (kode = slug).
        $reserved = ['admin', 'lamaran', 'loker', 'c', 'api', 'login', 'logout'];
        $used = [];
        $now = now();
        $positions = DB::table('positions')->select('id', 'title', 'slug')->orderBy('id')->get();

        foreach ($positions as $position) {
            $base = $position->slug ?: (Str::slug($position->title) ?: 'loker');
            $base = trim(substr($base, 0, 40), '-') ?: 'loker';
            if (in_array($base, $reserved, true)) {
                $base = 'loker-' . $base;
            }

            $code = $base;
            $i = 2;
            while (in_array($code, $reserved, true) || in_array($code, $used, true)) {
                $code = substr($base, 0, 33) . '-' . $i++;
            }
            $used[] = $code;

            DB::table('short_links')->insert([
                'position_id' => $position->id,
                'code' => $code,
                'clicks' => 0,
                'is_active' => true,
                'is_default' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('short_links');
    }
}
