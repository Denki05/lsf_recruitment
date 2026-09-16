<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddAiScreeningToApplicants extends Migration
{
    public function up()
    {
        Schema::table('applicants', function ($table) {
            $table->unsignedTinyInteger('ai_score')->nullable()->after('catatan_admin');
            $table->text('ai_summary')->nullable()->after('ai_score');
            $table->text('ai_strengths')->nullable()->after('ai_summary');
            $table->text('ai_gaps')->nullable()->after('ai_strengths');
            $table->timestamp('ai_evaluated_at')->nullable()->after('ai_gaps');
        });
    }

    public function down()
    {
        Schema::table('applicants', function ($table) {
            $table->dropColumn(['ai_score', 'ai_summary', 'ai_strengths', 'ai_gaps', 'ai_evaluated_at']);
        });
    }
}
