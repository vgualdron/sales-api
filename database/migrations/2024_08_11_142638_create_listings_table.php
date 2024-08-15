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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('user_id_collector');
            $table->unsignedBigInteger('user_id_leader');
            $table->unsignedBigInteger('user_id_authorized')->nullable();
            $table->string('status');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id_collector')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('user_id_leader')->references('id')->on('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
