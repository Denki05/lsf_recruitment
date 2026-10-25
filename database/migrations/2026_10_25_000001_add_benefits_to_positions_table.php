<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBenefitsToPositionsTable extends Migration
{
    public function up()
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->text('benefits')->nullable()->after('requirements');
        });
    }

    public function down()
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('benefits');
        });
    }
}
