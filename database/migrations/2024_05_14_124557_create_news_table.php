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
        Schema::create('news', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('document_number', 15)->collation('utf8_general_ci');
            $table->string('name', 50)->collation('utf8_general_ci')->nullable();
            $table->string('phone', 15)->collation('utf8_general_ci')->nullable();
            $table->string('address', 100)->collation('utf8_general_ci')->nullable();
            $table->unsignedBigInteger('sector')->nullable();
            $table->string('district', 50)->collation('utf8_general_ci')->nullable();
            $table->string('occupation', 50)->collation('utf8_general_ci')->nullable();
            $table->string('observation', 100)->collation('utf8_general_ci')->nullable();
            $table->string('status', 25)->collation('utf8_general_ci')->default('borrador');
            $table->unsignedBigInteger('user_send')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->integer('attempts')->default(0);
            $table->foreign('sector')->references('id')->on('yards');
            $table->foreign('user_send')->references('id')->on('users');
            $table->string('family_reference_document_number', 50)->collation('utf8_general_ci')->nullable();
            $table->string('family_reference_name', 50)->collation('utf8_general_ci')->nullable();
            $table->string('family_reference_address', 50)->collation('utf8_general_ci')->nullable();
            $table->string('family_reference_phone', 50)->collation('utf8_general_ci')->nullable();
            $table->string('personal_reference_name', 50)->collation('utf8_general_ci')->nullable();
            $table->string('personal_reference_document_number', 50)->collation('utf8_general_ci')->nullable();
            $table->string('personal_reference_address', 50)->collation('utf8_general_ci')->nullable();
            $table->string('personal_reference_phone', 50)->collation('utf8_general_ci')->nullable();
            $table->string('guarantor_name', 50)->collation('utf8_general_ci')->nullable();
            $table->string('guarantor_document_number', 50)->collation('utf8_general_ci')->nullable();
            $table->string('guarantor_address', 50)->collation('utf8_general_ci')->nullable();
            $table->string('guarantor_phone', 50)->collation('utf8_general_ci')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
