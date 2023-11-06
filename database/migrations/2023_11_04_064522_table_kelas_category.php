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
        Schema::create('kelas_category', function (Blueprint $table) {
            // Foreign Key Constraints
            $table->foreignUuid('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreignUuid('category_kelas_id')->references('id')->on('category_kelas')->onDelete('cascade');

            // Setting The Primary Keys
            $table->primary(['kelas_id', 'category_kelas_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_category');

    }
};
