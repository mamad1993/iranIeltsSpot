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
        Schema::table('question_options', function (Blueprint $table) {

            $table->dropForeign(['question_group_id']);

            $table->unsignedBigInteger('question_group_id')
                ->after('id')->nullable()->change();


            $table->foreign('question_group_id')
                ->references('id')->on('question_groups')
            ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_options', function (Blueprint $table) {
            $table->dropForeign('question_group_id');
            $table->unsignedBigInteger('question_group_id')
                ->after('id')->nullable(false)->change();
            $table->foreign('question_group_id')
                ->references('id')->on('question_groups');
        });
    }
};
