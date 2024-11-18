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
        Schema::create('questions', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->unsignedBigInteger('model_id');
            $table->string('model_name', 200)->collation('utf8_general_ci');
            $table->string('type', 20)->collation('utf8_general_ci');
            $table->string('status', 50)->collation('utf8_general_ci')->default('creado');
            $table->string('observation', 300)->collation('utf8_general_ci')->nullable();
            $table->string('value', 300)->collation('utf8_general_ci')->nullable();
            $table->unsignedBigInteger('area_id');
            $table->unsignedBigInteger('registered_by');
            $table->unsignedBigInteger('answered_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
