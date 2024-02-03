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
            $table->string('kelas');
            $table->string('category_kelas');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->flpoat('gross_amount',8, 2);
            $table->string('startdate')->nullable();
            $table->string('enddate')->nullable();
            $table->string('type_payment')->nullable();
            $table->string('bulkId')->nullable();
            $table->string('account_id')->nullable();
            $table->string('status')->nullable();
            $table->string('slug');
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
