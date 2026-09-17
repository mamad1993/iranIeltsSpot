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
        Schema::create('persian_passage_paragraphs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('passage_paragraph_id');
            $table->foreign('passage_paragraph_id')->references('id')->on('passage_paragraphs');
            $table->text('text');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persian_passage_paragraphs');
    }
};
