<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_template_kelas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_template_id');
            $table->uuid('kelas_id');
            $table->timestamps();

            $table->foreign('document_template_id')
                  ->references('id')->on('document_templates')
                  ->onDelete('cascade');

            $table->foreign('kelas_id')
                  ->references('id')->on('kelas')
                  ->onDelete('cascade');

            $table->unique(['document_template_id', 'kelas_id'], 'dt_kelas_unique');
        });

        Schema::create('document_template_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_template_id');
            $table->uuid('pelajaran_id');
            $table->timestamps();

            $table->foreign('document_template_id')
                  ->references('id')->on('document_templates')
                  ->onDelete('cascade');

            $table->foreign('pelajaran_id')
                  ->references('id')->on('pelajarans')
                  ->onDelete('cascade');

            $table->unique(['document_template_id', 'pelajaran_id'], 'dt_pelajaran_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_template_pelajaran');
        Schema::dropIfExists('document_template_kelas');
    }
};
