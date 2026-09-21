<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPengalamanKerjaToApplicantsTable extends Migration
{
    public function up()
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->text('pengalaman_kerja')->nullable();
        });
    }

    public function down()
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('pengalaman_kerja');
        });
    }
}