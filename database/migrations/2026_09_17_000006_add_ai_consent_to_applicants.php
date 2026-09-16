<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddAiConsentToApplicants extends Migration
{
    public function up()
    {
        Schema::table('applicants', function ($table) {
            $table->boolean('ai_consent')->default(false)->after('sim');
        });
    }

    public function down()
    {
        Schema::table('applicants', function ($table) {
            $table->dropColumn('ai_consent');
        });
    }
}
