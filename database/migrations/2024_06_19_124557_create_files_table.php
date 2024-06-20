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
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('name', 100)->collation('utf8_general_ci');
            $table->string('url', 300)->collation('utf8_general_ci');
            $table->unsignedBigInteger('model_id');
            $table->string('model_name', 200)->collation('utf8_general_ci');
            $table->string('type', 20)->collation('utf8_general_ci');
            $table->string('extension', 10)->collation('utf8_general_ci');
            $table->string('status', 100)->collation('utf8_general_ci')->default('creado');
            $table->string('observation', 100)->collation('utf8_general_ci')->nullable();
            $table->unsignedBigInteger('registered_by');
            $table->timestamp('registered_date');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_date')->nullable();
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
