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
        Schema::create('diaries', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('uuid', 100)->collation('utf8_general_ci');
            $table->timestamp('date');
            $table->string('status', 100)->collation('utf8_general_ci')->default('creado');
            $table->string('observation', 100)->collation('utf8_general_ci')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('new_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('new_id')->references('id')->on('news');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diaries');
    }
};
