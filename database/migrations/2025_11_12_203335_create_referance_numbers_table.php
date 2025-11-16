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
        Schema::create('referance_numbers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('year');
            $table->string('gov_code');
            $table->bigInteger('counter')->default(0);
            $table->unique(['year', 'gov_code']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referance_numbers');
    }
};
