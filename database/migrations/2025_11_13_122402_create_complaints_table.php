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
        Schema::create('complaints', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('description');
            $table->enum('status', ['new', 'in_progress', 'completed','rejected'])->default('new');
            $table->string('reference_number')->unique();
            $table->string('location')->nullable();
            $table->string('type');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('government_entity_id');
            $table->timestamps();


            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('government_entity_id')->references('id')->on('government_entities')->onDelete('cascade');

            // $table->index('user_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
