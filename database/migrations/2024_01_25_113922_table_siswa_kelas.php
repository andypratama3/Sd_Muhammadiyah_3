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
        Schema::create('siswa_kelas', function (Blueprint $table) {
            // Foreign Key Constraints
            $table->foreignUuid('siswa_id')->references('id')->on('siswas')->onDelete('cascade');
            $table->foreignUuid('kelas_id')->references('id')->on('kelas')->onDelete('cascade');

            // Setting The Primary Keys
            $table->primary(['siswa_id', 'kelas_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa_kelas');

    }
};
