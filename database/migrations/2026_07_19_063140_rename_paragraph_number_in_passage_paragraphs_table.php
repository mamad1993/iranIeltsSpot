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
        Schema::table('passage_paragraphs', function (Blueprint $table) {
            $table->renameColumn('paragraph_number', 'number');
            $table->renameColumn('text', 'english');
            $table->renameColumn('persian_translation', 'persian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passage_paragraphs', function (Blueprint $table) {
            $table->renameColumn('number', 'paragraph_number');
            $table->renameColumn('english', 'text');
            $table->renameColumn('persian', 'persian_translation');
        });
    }
};
