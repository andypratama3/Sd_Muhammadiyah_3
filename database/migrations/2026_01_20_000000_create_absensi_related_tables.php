<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jam_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('nama_shift')->nullable();
            $table->string('jenis_pegawai')->nullable();
            $table->time('jam_masuk')->nullable();
            $table->time('batas_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->time('batas_pulang')->nullable();
            $table->string('hari')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('lokasi_absensi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lokasi')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('radius')->default(50);
            $table->text('alamat')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('pengajuan_cuti', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('karyawan_id')->nullable()->references('id')->on('karyawans')->onDelete('cascade');
            $table->string('jenis')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->integer('jumlah_hari')->nullable();
            $table->text('alasan')->nullable();
            $table->string('file_pendukung')->nullable();
            $table->string('status')->default('menunggu');
            $table->foreignUuid('disetujui_oleh')->nullable()->references('id')->on('users');
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_cuti');
        Schema::dropIfExists('lokasi_absensi');
        Schema::dropIfExists('jam_kerja');
    }
};
