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
        Schema::create('redcollectors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('collector_id');
            $table->unsignedBigInteger('registered_by');
            $table->timestamp('registered_date');
            $table->unsignedBigInteger('sector_id');
            $table->foreign('collector_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('registered_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('sector_id')->references('id')->on('yards')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redcollectors');
    }
};
