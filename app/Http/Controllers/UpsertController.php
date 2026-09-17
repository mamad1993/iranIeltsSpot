<?php

namespace App\Http\Controllers;

use App\Models\Passage;
use App\Models\PassageParagraph;
use Illuminate\Http\Request;

class UpsertController extends Controller
{
    public function index()
    {
        return view('passage.index');

    }

    public function createPassage(Request $request)
    {
        $validator = $request->validate([
            'passageTitle' => 'string|required|max:255',
        ]);

        $passage = new Passage();
        $passage->title = $validator['passageTitle'];
        $passage->save();
        return redirect()->to('/details/' . $passage->id);

    }

    public function details($passageId)
    {

        $passage = Passage::query()->with(['paragraphs' => function ($query) {
            $query->orderBy('paragraph_number', 'asc');
        }])->findOrFail($passageId);
        return view('passage.details', compact('passage'));
    }



    public function updateParagraph(Request $request, $paragraphId)
    {
        $validator = $request->validate([
            'updateEnglish' => 'required|string',
            'updatePersian' => 'required|string',
        ]);


        $paragraph = PassageParagraph::query()->findOrFail($paragraphId);
        $paragraph->text = $validator['updateEnglish'];
        $paragraph->persian_translation = $validator['updatePersian'];
        $paragraph->save();

        return redirect()->back();


    }


    public function deleteParagraph($paragraphId)
    {
        $paragraph = PassageParagraph::query()->findOrFail($paragraphId);
        $paragraph->delete();

        return redirect()->back();
    }
}
