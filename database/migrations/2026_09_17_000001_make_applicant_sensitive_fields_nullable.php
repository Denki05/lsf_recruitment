<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeApplicantSensitiveFieldsNullable extends Migration
{
    public function up()
    {
        // Tanpa doctrine/dbal: pakai MODIFY langsung (MySQL/MariaDB)
        DB::statement("ALTER TABLE `applicants` MODIFY `nama_panggilan` VARCHAR(50) NULL");
        DB::statement("ALTER TABLE `applicants` MODIFY `tempat_lahir` VARCHAR(60) NULL");
        DB::statement("ALTER TABLE `applicants` MODIFY `tanggal_lahir` DATE NULL");
        DB::statement("ALTER TABLE `applicants` MODIFY `no_ktp` VARCHAR(20) NULL");
        DB::statement("ALTER TABLE `applicants` MODIFY `status_pernikahan` VARCHAR(20) NULL");
        DB::statement("ALTER TABLE `applicants` MODIFY `agama` VARCHAR(30) NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE `applicants` MODIFY `nama_panggilan` VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE `applicants` MODIFY `tempat_lahir` VARCHAR(60) NOT NULL");
        DB::statement("ALTER TABLE `applicants` MODIFY `tanggal_lahir` DATE NOT NULL");
        DB::statement("ALTER TABLE `applicants` MODIFY `no_ktp` VARCHAR(20) NOT NULL");
        DB::statement("ALTER TABLE `applicants` MODIFY `status_pernikahan` VARCHAR(20) NOT NULL");
        DB::statement("ALTER TABLE `applicants` MODIFY `agama` VARCHAR(30) NOT NULL");
    }
}
