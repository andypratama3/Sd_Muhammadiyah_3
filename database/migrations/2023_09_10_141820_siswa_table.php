<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('jk', ['laki-laki', 'perempuan']);
            $table->string('tmpt_lahir');
            $table->date('tgl_lahir');
            $table->string('nik', 16);
            $table->string('nisn', 16);
            $table->string('agama');
            $table->string('rt');
            $table->string('rw');
            $table->string('provinsi_id');
            $table->string('kabupaten_id');
            $table->string('kecamatan_id');
            $table->string('kelurahan_id');
            $table->longText('nama_jalan');
            $table->string('jenis_tinggal');
            $table->string('no_hp');
            $table->string('beasiswa');
            $table->string('foto', 100)->nullable()->default('text');
            $table->string('slug');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
