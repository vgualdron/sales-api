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
        Schema::create('reddirections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('collector_id')->required();
            $table->unsignedBigInteger('registered_by')->required();
            $table->timestamp('registered_date')->required();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_date')->nullable();
            $table->unsignedBigInteger('lending_id')->required();
            $table->timestamp('start_date')->required();
            $table->timestamp('end_date')->nullable();
            $table->string('address', 200)->collation('utf8_general_ci')->required();
            $table->unsignedBigInteger('district_id')->required();
            $table->string('type_ref', 300)->collation('utf8_general_ci')->required();
            $table->string('description_ref', 300)->collation('utf8_general_ci')->required();
            $table->integer('value')->required();
            $table->unsignedBigInteger('file_id')->nullable();
            $table->unsignedBigInteger('file2_id')->nullable();
            $table->string('status', 50)->collation('utf8_general_ci')->required();
            $table->string('attended', 300)->collation('utf8_general_ci')->nullable();
            $table->string('solution', 100)->collation('utf8_general_ci')->nullable();
            $table->string('observation', 300)->collation('utf8_general_ci')->nullable();
            $table->foreign('collector_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('registered_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('lending_id')->references('id')->on('lendings')->onDelete('restrict');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('restrict');
            $table->foreign('file_id')->references('id')->on('files')->onDelete('restrict');
            $table->foreign('file2_id')->references('id')->on('files')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reddirections');
    }
};
