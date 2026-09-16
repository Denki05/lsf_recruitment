<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicantsTable extends Migration
{
    public function up()
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('position_id');
            $table->string('nama_lengkap', 100);
            $table->string('nama_panggilan', 50);
            $table->string('tempat_lahir', 60);
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('no_ktp', 20)->unique();
            $table->text('alamat_ktp');
            $table->text('alamat_sekarang')->nullable();
            $table->string('no_hp', 20);
            $table->string('sosmed', 255)->nullable();
            $table->enum('status_pernikahan', ['Belum Menikah', 'Menikah', 'Cerai']);
            $table->string('agama', 30);
            $table->string('kendaraan', 30);
            $table->string('sim', 30);
            $table->string('file_path', 255);
            $table->string('file_original', 255);
            $table->string('file_mime', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->enum('status', ['Baru', 'Seleksi', 'Interview', 'Diterima', 'Ditolak'])->default('Baru');
            $table->text('catatan_admin')->nullable();
            $table->timestamps();

            $table->foreign('position_id')->references('id')->on('positions')->onDelete('restrict');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('applicants');
    }
}
