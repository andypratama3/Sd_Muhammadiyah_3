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
        Schema::create('artikel_categorys', function (Blueprint $table) {
            // Foreign Key Constraints
            $table->foreignUuid('artikel_id')->references('id')->on('artikels')->onDelete('cascade');
            $table->foreignUuid('category_id')->references('id')->on('categorys')->onDelete('cascade');

            // Setting The Primary Keys
            $table->primary(['artikel_id', 'category_id']);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artikel_category');
    }
};
