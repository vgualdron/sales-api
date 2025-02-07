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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('nit', 50)->collation('utf8_general_ci')->nullable();
            $table->string('name', 200)->collation('utf8_general_ci')->required();
            $table->string('agreement', 300)->collation('utf8_general_ci')->required();
            $table->string('address', 150)->collation('utf8_general_ci')->nullable();
            $table->string('phone', 25)->collation('utf8_general_ci')->nullable();
            $table->string('email', 100)->collation('utf8_general_ci')->nullable();
            $table->string('status', 100)->collation('utf8_general_ci')->default('activo');
            $table->string('observation', 100)->collation('utf8_general_ci')->nullable();
            $table->unsignedBigInteger('order');
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
