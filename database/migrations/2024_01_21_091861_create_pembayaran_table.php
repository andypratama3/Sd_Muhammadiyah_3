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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_id');
            $table->string('siswa_id');
            $table->string('kelas_id');
            $table->string('category_kelas');
            $table->string('email')->nullable();
            $table->float('gross_amount')->nullable();
            $table->string('startdate')->nullable();
            $table->string('enddate')->nullable();
            $table->string('type_payment')->nullable();
            $table->string('SessionID')->nullable();
            $table->longText('Url')->nullable();
            $table->string('trx_id')->nullable();
            $table->string('bulkId')->nullable();
            $table->string('account_id')->nullable();
            $table->foreignUuid('judul_id')->references('id')->on('judulpembayarans')->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
