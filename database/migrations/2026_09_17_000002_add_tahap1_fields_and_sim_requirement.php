<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTahap1FieldsAndSimRequirement extends Migration
{
    public function up()
    {
        Schema::table('applicants', function ($table) {
            $table->string('email', 150)->nullable()->after('no_hp');
            $table->string('domisili', 100)->nullable()->after('email');
        });
        // Alamat lengkap & kendaraan pindah ke Tahap 2
        DB::statement("ALTER TABLE `applicants` MODIFY `alamat_ktp` TEXT NULL");
        DB::statement("ALTER TABLE `applicants` MODIFY `kendaraan` VARCHAR(30) NULL");

        Schema::table('positions', function ($table) {
            // Syarat SIM per loker, cth: A / B / C / null (tidak ada syarat)
            $table->string('syarat_sim', 20)->nullable()->after('location');
        });
    }

    public function down()
    {
        Schema::table('positions', function ($table) {
            $table->dropColumn('syarat_sim');
        });
        Schema::table('applicants', function ($table) {
            $table->dropColumn(['email', 'domisili']);
        });
    }
}
