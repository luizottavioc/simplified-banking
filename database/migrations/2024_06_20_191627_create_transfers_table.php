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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payer_id')->nullable(false);
            $table->foreign('payer_id')->references('id')->on('users');
            $table->unsignedBigInteger('payee_id')->nullable(false);
            $table->foreign('payee_id')->references('id')->on('users');
            $table->integer('value');
            $table->dateTime('datetime_init');
            $table->dateTime('datetime_finish')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
