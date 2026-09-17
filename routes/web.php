<?php

use App\Http\Controllers\ExamController;
use App\Http\Controllers\ParagraphController;
use App\Http\Controllers\PassageController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\UpsertController;
use Illuminate\Support\Facades\Route;


Route::get('/', [UpsertController::class, 'index']);
Route::post('CreatePassage', [UpsertController::class, 'createPassage'])->name('create.passage');
Route::get('/details/{id}', [UpsertController::class, 'details']);


Route::DELETE('/details/delete/paragraph/{paragraphId}', [UpsertController::class, 'deleteParagraph']);


Route::get('admin/exams', [ExamController::class, 'exam']);
Route::POST('admin/addExam', [ExamController::class, 'addExam'])->name('add.exam');
Route::GET('admin/exam/updateModal', [ExamController::class, 'updateModal'])->name('updateModal');
Route::PUT('admin/exam/updateExam', [ExamController::class, 'updateExam'])->name('update.exam');
Route::GET('admin/passage/select', [PassageController::class, 'selectPassage'])->name('select.passage');
Route::POST('admin/passage/upsert', [PassageController::class, 'upsert'])->name('upsert.passage');
Route::get('admin/paragraphs/view', [ParagraphController::class, 'viewParagraphs'])->name('passage.paragraphs');
Route::POST('/admin/addParagraph', [ParagraphController::class, 'addParagraph'])->name('add.paragraph');
Route::POST('/admin/updateParagraph', [ParagraphController::class, 'updateParagraph'])->name('update.paragraph');
Route::POST('/admin/addQuestionGroup', [QuestionController::class, 'addQuestionGroup'])->name('add.question-group');
Route::GET('/admin/passageQuestionGroups', [QuestionController::class, 'passageQuestionGroups'])->name('passage.question-groups');
Route::PUT('/admin/updateGroup/{group}', [QuestionController::class, 'updateGroup']);
Route::POST('/admin/addQuestion', [QuestionController::class, 'addQuestion'])->name('add.question');
Route::GET('/admin/getQuestions', [QuestionController::class, 'getQuestions'])->name('get-questions');


Route::POST('/admin/importAll', [QuestionController::class, 'importAll'])->name('passage.import-all');
Route::post('/admin/import-questions', [QuestionController::class, 'importQuestions'])->name('questions.import-questions');

Route::GET('/single_exam/{id}', [ExamController::class, 'singleExam']);
Route::GET('/exam/{id}', [ExamController::class, 'index']);
