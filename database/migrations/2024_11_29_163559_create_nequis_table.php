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
        Schema::create('nequis', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->collation('utf8_general_ci')->required();
            $table->string('number', 20)->collation('utf8_general_ci')->required();
            $table->unsignedBigInteger('order');
            $table->string('status', 20)->collation('utf8_general_ci')->required();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nequis');
    }
};
