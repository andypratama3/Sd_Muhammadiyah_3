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
        Schema::create('charge_not_found', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('transaction_type');
            $table->string('transaction_time');
            $table->string('transaction_status');
            $table->string('transaction_id');
            $table->string('status_message');
            $table->string('status_code');
            $table->string('signature_key');
            $table->string('settlement_time')->nullable();
            $table->string('payment_type');
            $table->string('order_id');
            $table->json('metadata')->nullable();
            $table->string('merchant_id');
            $table->string('issuer');
            $table->string('gross_amount');
            $table->string('fraud_status');
            $table->string('expiry_time');
            $table->string('currency');
            $table->string('acquirer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charge_not_found');
    }
};
