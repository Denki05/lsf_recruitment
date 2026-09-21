<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicantStatusLogsTable extends Migration
{
    public function up()
    {
        Schema::create('applicant_status_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('applicant_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('applicant_id')->references('id')->on('applicants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('applicant_status_logs');
    }
}