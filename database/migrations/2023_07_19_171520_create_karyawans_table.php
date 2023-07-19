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
        Schema::create('karyawans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('sex', ['Laki-Laki', 'Perempuan']);
            $table->date('birth_date');
            $table->string('phone');
            $table->string('slug');
            $table->foreignUuid('user_id')->nullable()->references('id')->on('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
