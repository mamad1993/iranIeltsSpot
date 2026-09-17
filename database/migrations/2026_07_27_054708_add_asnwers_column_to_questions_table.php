<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->json('answers')->nullable()->after('answer');

        });
        DB::table('questions')->select('id', 'answer')
            ->orderBy('id')->chunk(500, function ($questions) {
                foreach ($questions as $question) {
                    $value = trim((string)$question->answer);

                    DB::table('questions')->where('id', $question->id)
                        ->update([
                            'answers' => $value === '' ?
                                null : json_encode([$value]),
                        ]);
                }
            });

        Schema::table('questions', function (Blueprint $table){
            $table->dropColumn('answer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('answer')->nullable()->after('answers');
        });
        DB::table('questions')->select('id', 'answers')
            ->orderBy('id')->chunk(500, function ($questions){
                foreach ($questions as $question){
                    $answers = json_decode($question->answers, true);

                    DB::table('questions')->where('id', $question->id)
                        ->update([
                            'answer' => is_array($answers) && count($answers) > 0 ? $answers[0] : null,

                        ]);
                }
            });
        Schema::table('questions', function (Blueprint $table){
            $table->dropColumn('answers');

        });
    }
};
