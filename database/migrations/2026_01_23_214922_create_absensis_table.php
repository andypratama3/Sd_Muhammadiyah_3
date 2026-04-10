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
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('karyawan_id')->nullable()->references('id')->on('karyawans')->onDelete('cascade');
            $table->foreignId('lokasi_absensi_id')->nullable();
            $table->foreignId('jam_kerja_id')->nullable();
            $table->date('tanggal');
            $table->string('status_kehadiran')->nullable();
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->string('status_masuk')->nullable();
            $table->string('status_pulang')->nullable();
            $table->integer('rp_masuk')->default(0);
            $table->integer('rp_pulang')->default(0);
            $table->decimal('latitude_masuk', 10, 8)->nullable();
            $table->decimal('longitude_masuk', 11, 8)->nullable();
            $table->integer('jarak_masuk')->nullable();
            $table->decimal('latitude_pulang', 10, 8)->nullable();
            $table->decimal('longitude_pulang', 11, 8)->nullable();
            $table->integer('jarak_pulang')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_id')->nullable();
            $table->string('ip_address_pulang')->nullable();
            $table->string('user_agent_pulang')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
