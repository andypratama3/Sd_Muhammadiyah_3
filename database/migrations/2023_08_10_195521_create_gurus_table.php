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
        Schema::create('gurus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->longText('description');
            $table->string('lulusan');
            $table->foreignUuid('karyawan_id')->nullable()->references('id')->on('karyawans')->onDelete('cascade');
            $table->longText('foto');
            $table->string('slug');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gurus');
    }
};
