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
        Schema::create('zips', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('name', 100)->collation('utf8_general_ci');
            $table->unsignedBigInteger('registered_by');
            $table->timestamp('registered_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zips');
    }
};
