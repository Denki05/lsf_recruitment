<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddSkillTagsToPositions extends Migration
{
    public function up()
    {
        Schema::table('positions', function ($table) {
            $table->string('skill_tags', 500)->nullable()->after('requirements');
        });
    }

    public function down()
    {
        Schema::table('positions', function ($table) {
            $table->dropColumn('skill_tags');
        });
    }
}
