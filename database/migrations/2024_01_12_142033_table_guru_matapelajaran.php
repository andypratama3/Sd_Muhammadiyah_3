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
        Schema::create('guru_matapelajaran', function (Blueprint $table) {
            // Foreign Key Constraints
            $table->foreignUuid('guru_id')->references('id')->on('gurus')->onDelete('cascade');
            $table->foreignUuid('pelajaran_id')->references('id')->on('pelajarans')->onDelete('cascade');

            // Setting The Primary Keys
            $table->primary(['guru_id', 'pelajaran_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guru_matapelajaran');
    }
};
