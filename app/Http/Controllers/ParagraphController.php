<?php

namespace App\Http\Controllers;

use App\Models\Passage;
use App\Models\PassageParagraph;
use App\Models\QuestionGroup;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;


class ParagraphController extends Controller
{





    public function viewParagraphs(Request $request)
    {

        $validator = $request->validate([
            'id' => ['required', 'integer', 'exists:passages,id']
        ]);

        $passage = Passage::query()->with(['paragraphs' => function ($query) {
            $query->orderBy('number', 'asc');
        }])->findOrFail($validator['id']);



        return response()->json([
            'paragraphs' => $passage->paragraphs,

        ]);
    }

    public function addParagraph(Request $request)
    {
        $validator = $request->validate([
            'passageId' => 'required|integer|exists:passages,id',
            'english' => 'required|string',
            'persian' => 'required|string',
            'paragraphNumber' => 'required|integer',

        ]);

        $newParagraph = new PassageParagraph();
        $newParagraph->passage_id = $validator['passageId'];
        $newParagraph->number = $validator['paragraphNumber'];
        $newParagraph->english = $validator['english'];
        $newParagraph->persian = $validator['persian'];
        $newParagraph->save();




        return response()->json([

            'success' => 'paragraph added successfully',
            'paragraph' => [
                'id' => $newParagraph->id,
                'number' => $newParagraph->number,
                'english' => $newParagraph->english,
                'persian' => $newParagraph->persian
            ],

        ]);
    }

    public function updateParagraph(Request $request)
    {
        $validator = $request->validate([
            'id' => 'required|integer|exists:passage_paragraphs,id',
            'number' => 'required|integer',
            'english' => 'required|string',
            'persian' => 'required|string',
        ]);


        $paragraph = PassageParagraph::query()->findOrFail($validator['id']);
        $paragraph->number = $validator['number'];
        $paragraph->english = $validator['english'];
        $paragraph->persian = $validator['persian'];
        $paragraph->save();

        return response()->json([

            'success' => 'paragraph added successfully',
            'paragraph' => [
                'id' => $paragraph->id,
                'number' => $paragraph->number,
                'english' => $paragraph->english,
                'persian' => $paragraph->persian
            ],

        ]);


    }

}
