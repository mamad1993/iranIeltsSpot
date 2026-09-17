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
            $table->text('persian_translation')->after('text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passage_paragraphs', function (Blueprint $table) {
            $table->dropColumn('persian_translation');

        });
    }
};
