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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cpf', 14)->unique()->nullable();
            $table->string('cnpj', 18)->unique()->nullable();
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->unsignedBigInteger('user_type_id')->nullable(false);
            $table->foreign('user_type_id')->references('id')->on('user_types');
            $table->integer('wallet')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
