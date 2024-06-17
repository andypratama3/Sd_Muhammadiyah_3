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
        Schema::create('artikels', function (Blueprint $table){
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('image');
            $table->longText('artikel');
            $table->string('slug');
            $table->foreignUUid('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->unsignedInteger('like')->default(0);
            $table->unsignedInteger('jumlah_klik')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
