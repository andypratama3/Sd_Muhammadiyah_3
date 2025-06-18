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
        Schema::create('charges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('order_id');
            $table->string('order_id_1')->nullable();
            $table->foreignUuid('siswa_id')->references('id')->on('siswas');
            $table->integer('gross_amount');
            $table->string('payment_type')->default('bank_transfer');
            $table->string('bank')->nullable();
            $table->string('va_number')->nullable();
            $table->string('transaction_id');
            $table->date('transaction_time')->nullable();
            $table->string('fraud_status')->default('accept');
            $table->foreignUuid('category_payment_id')->references('id')->on('judulpembayarans');
            $table->string('transaction_status');
            $table->longText('name_action')->nullable();
            $table->longText('method')->nullable();
            $table->longText('url_action')->nullable();
            $table->string('snap_token')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charges');
    }
};
