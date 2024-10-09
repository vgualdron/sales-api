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
        Schema::create('districts', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('name', 100)->collation('utf8_general_ci');
            $table->unsignedBigInteger('sector');
            $table->unsignedBigInteger('group');
            $table->string('order', 5)->collation('utf8_general_ci');
            $table->string('status', 100)->default('activo');
            $table->foreign('sector')->references('id')->on('yards');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
