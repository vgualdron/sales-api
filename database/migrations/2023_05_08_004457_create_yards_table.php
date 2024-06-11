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
        Schema::create('yards', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('code', 10)->collation('utf8_general_ci')->unique();
            $table->string('name', 30)->collation('utf8_general_ci')->unique();
            $table->unsignedBigInteger('zone');
            $table->decimal('longitude', 9,5)->nullable();
            $table->decimal('latitude', 8,5)->nullable();
            $table->boolean('active')->default(1);
            $table->foreign('zone')->references('id')->on('zones');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yards');
    }
};
