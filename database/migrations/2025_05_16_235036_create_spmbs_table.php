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
        Schema::create('spmbs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('agama')->default('islam');
            $table->string('suku');
            $table->text('alamat');
            $table->string('nama_asal_sekolah')->nullable();
            $table->string('sttb')->nullable();
            $table->text('alamat_sekolah')->nullable();
            $table->enum('select_data', ['orang_tua', 'wali'])->default('orang_tua');
            // data orang tua
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('pendidikan_ayah')->nullable();
            $table->string('pendidikan_ibu')->nullable();
            $table->text('alamat_ayah')->nullable();
            $table->text('alamat_ibu')->nullable();
            //pekerjaan
            $table->string('pekerjaan_ayah')->nullable();
            $table->string('pekerjaan_ibu')->nullable();
            //wali
            $table->string('nama_wali')->nullable();
            $table->string('pekerjaan_wali')->nullable();
            $table->string('alamat_wali')->nullable();

            $table->string('file_sttb')->nullable();
            $table->string('akta_kelahiran');
            $table->string('kk');
            $table->string('pas_foto');
            $table->string('phone');
            $table->string('nomor_urut');
            $table->string('status_pembayaran');
            $table->string('order_id');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmbs');
    }
};
