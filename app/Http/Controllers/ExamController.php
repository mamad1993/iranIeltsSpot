<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Passage;
use App\Models\PassageParagraph;
use http\Env\Response;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mockery\Generator\StringManipulation\Pass\Pass;

class ExamController extends Controller
{
    public function singleExam($examId)
    {

        $exam = Exam::with([
            'passages.paragraphs',
            'passages.groups.options',
            'passages.groups.questions.options'
        ])->findOrFail($examId);


        return view('main.exam', compact('exam'));




    }

    public function index($examId)
    {
        $exam = Exam::with([
            'passages.paragraphs',
            'passages.groups.options',
            'passages.groups.questions.options'
        ])->findOrFail($examId);



        return view('exam.index', compact('exam'));
    }


    public function exam()
    {
        $exams = Exam::all();



        return view('admin.exam.index', compact('exams'));
    }

    public function addExam(Request $request)
    {

        $validator = $request->validate([
            'exam_level' => 'required|string|max:255',
        ]);



        $lastExam = Exam::query()->orderBy('number', 'asc')->get()->last();

        $newExam = new Exam();

        if($lastExam === null){
            $newExam->number = 1;


        }else{
            $lastExam->number++;
            $newExam->number = $lastExam->number;

        }

        $examLevel = $validator['exam_level'];

        $newExam->level = $validator['exam_level'];
        if($examLevel === 'B1'){
            $newExam->min_mark = 5;
            $newExam->max_mark = 5.5;
        }else if($examLevel === 'B2'){
            $newExam->min_mark = 6;
            $newExam->max_mark = 6.5;
        }else{
            $newExam->min_mark = 7;
            $newExam->max_mark = 8;
        }

        $newExam->slug = "ielts-reading-level-" . $newExam->min_mark . "-" . $newExam->max_mark;


        $newExam->save();

        return back()->with('toast_success', 'آزمون با موفقیت اضافه شد!');

    }

    public function updateModal(Request $request)
    {
       $validator = Validator::make($request->all(), [
           'examNumber' => ['required', 'integer'],
       ], [
           'examNumber.required' => 'شماره آزمون الزامیه.',
           'examNumber.integer' => 'شماره آزمون باید عدد صحیح باشه.',
       ]);

       if ($validator->fails()){
           return response()->json([
               'status' => 'error',
               'messages' => $validator->errors()->first(),
               'errors' => $validator->errors(),
           ], 422);
       }

       $exam = Exam::query()->where('number', $request->examNumber)->first();


       if(!$exam){
           return response()->json([
               'status' => 'error',
               'message' => 'آزمونی با این شماره پیدا نشد.',

           ], 404);
       }

       return response()->json([
           'exam' => $exam,
       ]);
    }


    public function updateExam(Request $request)
    {
        $validator = $request->validate([
            'exam_id' => 'required|integer|exists:exams,id',
            'exam_level' => 'required|string|max:255'
        ]);

        $exam = Exam::query()->find($validator['exam_id']);
        $examLevel = $validator['exam_level'];
        $exam->level = $examLevel;
        if($examLevel === 'B1'){
            $exam->min_mark = 5;
            $exam->max_mark = 5.5;
        }else if($examLevel === 'B2'){
            $exam->min_mark = 6;
            $exam->max_mark = 6.5;
        }else{
            $exam->min_mark = 7;
            $exam->max_mark = 8;
        }
        $exam->slug = "ielts-reading-level-" . $exam->min_mark . "-" . $exam->max_mark;

        $exam->save();

        return redirect()->back()->with('toast_success', 'آزمون با موفقیت بروزرسانی شد.');


    }
}
