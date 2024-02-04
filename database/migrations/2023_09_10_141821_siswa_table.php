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
            // data siswa
            $table->string('name');
            $table->enum('jk', ['Laki-laki', 'Perempuan']);
            $table->string('tmpt_lahir');
            $table->date('tgl_lahir');
            $table->string('nisn', 20);
            $table->string('agama');
            // data sekolah
            $table->string('kelas_tahun');
            $table->date('tanggal_masuk');
            $table->string('beasiswa');
            $table->string('foto', 100)->nullable()->default('text');
            // data orang tua
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('pendidikan_ayah')->nullable();
            $table->string('pendidikan_ibu')->nullable();
            //wali
            $table->string('nama_wali')->nullable();
            $table->string('pekerjaan_wali')->nullable();
            $table->string('alamat_wali')->nullable();
            //pekerjaan
            $table->string('pekerjaan_ayah');
            $table->string('pekerjaan_ibu');
            //alamat
            $table->string('rt');
            $table->string('rw');
            $table->string('provinsi_id');
            $table->string('kabupaten_id');
            $table->string('kecamatan_id');
            $table->string('kelurahan_id');
            $table->longText('nama_jalan');
            $table->string('jenis_tinggal');
            $table->string('no_hp');

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
