<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Passage;
use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use function Laravel\Prompts\number;

class PassageController extends Controller
{




    public function selectPassage(Request $request)
    {
        $validator = $request->validate([
            'passageId' => ['required', 'integer', 'exists:exams,id'],

        ]);

        $exam = Exam::with('passages')->findOrFail($validator['passageId']);

        return response()->json([
            'passages' => $exam->passages->map(function ($passage){
                return [
                    'id' => $passage->id,
                    'number' => $passage->number,
                    'title' => $passage->title,
                    'label' => $passage->label
                ];
            })->values(),
        ]);

    }


    public function upsert(Request $request)
    {
        $validator = $request->validate([
            'id' => ['required', 'string', 'regex:/^(\d+|new-\d+)$/'],
            'title' => ['required', 'string', 'max:255'],
            'exam_id' => 'required', 'integer', 'exists:exams,id',
            'label' => 'required|boolean'
        ]);

        if(is_numeric($validator['id'])){
            $passage = Passage::query()->find($validator['id']);
            if(!$passage){
                return response()->json([
                    'message' => 'passage does not exist.'
                ], 422);
            }

            $passage->title = $validator['title'];
            $passage->label = $validator['label'];

            $passage->save();

            return response()->json([
                'message' => 'passage updated successfully',
                'passage' => $passage,
            ]);
        }

        $number = (int) Str::after($validator['id'], 'new-');

        $passage = new Passage();
        $passage->exam_id = $validator['exam_id'];
        $passage->number = $number;
        $passage->title = $validator['title'];
        $passage->label = $validator['label'];
        $passage->save();
        $message = 'passage ' . $number . ' is added';

        return response()->json([
            'message' => $message,
            'passage' => $passage,

        ]);



    }
}
