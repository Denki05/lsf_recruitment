<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddRequirementsToPositions extends Migration
{
    public function up()
    {
        Schema::table('positions', function ($table) {
            $table->text('requirements')->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('positions', function ($table) {
            $table->dropColumn('requirements');
        });
    }
}
