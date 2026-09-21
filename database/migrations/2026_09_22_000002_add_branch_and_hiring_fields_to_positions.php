<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBranchAndHiringFieldsToPositions extends Migration
{
    public function up()
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            $table->unsignedInteger('gaji')->nullable()->after('location');
            $table->string('pendidikan_minimal', 20)->nullable()->after('gaji');
            $table->unsignedTinyInteger('usia_min')->nullable()->after('pendidikan_minimal');
            $table->unsignedTinyInteger('usia_maks')->nullable()->after('usia_min');
            $table->boolean('butuh_lembur')->default(false)->after('syarat_sim');
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn(['branch_id', 'gaji', 'pendidikan_minimal', 'usia_min', 'usia_maks', 'butuh_lembur']);
        });
    }
}
