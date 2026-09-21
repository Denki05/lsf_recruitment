<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScreeningFieldsToApplicants extends Migration
{
    public function up()
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->unsignedInteger('expected_salary')->nullable()->after('domisili');
            $table->boolean('willing_overtime')->default(false)->after('expected_salary');
            $table->string('education', 20)->nullable()->after('willing_overtime');
        });
    }

    public function down()
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn(['expected_salary', 'willing_overtime', 'education']);
        });
    }
}
