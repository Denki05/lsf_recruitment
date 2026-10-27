<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisibilityFlagsToPositionsTable extends Migration
{
    public function up()
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->boolean('show_salary')->default(false)->after('gaji');
            $table->boolean('show_benefits')->default(false)->after('benefits');
        });
    }

    public function down()
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn(['show_salary', 'show_benefits']);
        });
    }
}
