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
        Schema::create('lendings', function (Blueprint $table) {
            $table->id();
            $table->string('nameDebtor');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->timestamp('firstDate')->nullable();
            $table->timestamp('endDate')->nullable();
            $table->integer('amount');
            $table->integer('amountFees');
            $table->integer('percentage');
            $table->string('period');
            $table->integer('order')->nullable();
            $table->string('status');
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('new_id')->nullable();
            $table->string('type')->default('normal');
            $table->boolean('has_double_interest')->default(false);
            $table->foreign('listing_id')->references('id')->on('listings')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lendings');
    }
};
