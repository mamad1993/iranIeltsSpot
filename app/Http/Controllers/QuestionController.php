<?php

namespace App\Http\Controllers;

use App\Models\Passage;
use App\Models\PassageParagraph;
use App\Models\Question;
use App\Models\QuestionGroup;
use App\Models\QuestionOption;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{



    public function getQuestions(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:question_groups,id',

        ]);

        $group = QuestionGroup::with('questions.options')
            ->find($validated['id']);



        return response()->json([
            'questions' => $group->questions,

        ]);
    }

    public function addQuestionGroup(Request $request)
    {
        $validator = $request->validate([
            'passage_id' => 'required|integer|exists:passages,id',
            'title' => 'required|string|max:255',
            'instruction' => 'required|string',
            'type' => 'required|string|max:50',

            'option_keys' => 'nullable|array',
            'option_keys.*' => 'required_with:option_texts.*|string|max:50',

            'option_texts' => 'nullable|array',
            'option_texts.*' => 'required_with:option_keys.*|string|max:1000',

        ]);

        $newGroup = new QuestionGroup();
        $newGroup->passage_id = $validator['passage_id'];
        $newGroup->title = $validator['title'];
        $newGroup->instructions = $validator['instruction'];
        $newGroup->type = $validator['type'];

        $newGroup->save();




        if(!empty($validator['option_keys']) && !empty($validator['option_texts'])){
            $keys = $validator['option_keys'];
            $texts = $validator['option_texts'];

            foreach ($keys as $index => $key){
                if (isset($texts[$index]) && !is_null($key) && !is_null($texts[$index])){
                    $option = new QuestionOption();
                    $option->question_group_id = $newGroup->id;
                    $option->key = $key;
                    $option->text = $texts[$index];
                    $option->save();

                }
            }
        }


        return response()->json([
            'status' => 'success',
            'message' => 'گروه سوال با موفقیت ایجاد شد.'
        ], 200);
    }

    public function passageQuestionGroups(Request $request)
    {
        $id = (string) $request->input('id');

        if (str_starts_with($id, 'new-')) {
            return response()->json([
                'groups' => [],
            ]);
        }

        $validated = $request->validate([
            'id' => 'required|integer',
        ]);

        $passage = Passage::with('groups.options')->find($validated['id']);

        if (!$passage) {
            return response()->json([
                'message' => 'پسیج مورد نظر پیدا نشد.',
            ], 422);
        }

        return response()->json([
            'groups' => $passage->groups,
        ]);


    }

    public function updateGroup(Request $request, QuestionGroup $group)
    {
        $validator = $request->validate([
            'title' => 'required|string|max:255',
            'instruction' => 'required|string',
            'type' => 'required|string|max:50',

            'option_keys' => 'nullable|array',
            'option_keys.*' => 'required_with:option_texts.*|string|max:50',

            'option_texts' => 'nullable|array',
            'option_texts.*' => 'required_with:option_keys.*|string|max:1000',

        ]);

        $group->title = $validator['title'];
        $group->instructions = $validator['instruction'];
        $group->type = $validator['type'];

        $group->save();

        $group->options()->delete();

        if ($request->has('option_keys')){
            foreach ($request->option_keys as $index => $key){
                $text = $request->option_texts[$index] ?? null;

                if(!empty($key) || !empty($text)){
                    $option = new QuestionOption();
                    $option->question_group_id = $group->id;
                    $option->key = $key;
                    $option->text = $text;
                    $option->save();

                }

            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'گروه سوال با موفقیت ویرایش شد.'
        ]);

    }

    public function addQuestion(Request $request)
    {
        $validated = $request->validate([
            'group_id' => 'required|integer|exists:question_groups,id',
            'number' => 'required|integer|min:1',
            'text' => 'required|string',
            'location' => 'nullable|string|max:255',
            'english' => 'required|string',
            'persian' => 'required|string',
            'answer' => 'required|string|max:255',

            'option_keys' => 'nullable|array',
            'option_keys.*' => 'required_with:option_texts.*|string|max:50',

            'option_texts' => 'nullable|array',
            'option_texts.*' => 'required_with:option_keys.*|string|max:1000',
        ]);

        $question = new Question();
        $question->question_group_id = $validated['group_id'];
        $question->number = $validated['number'];
        $question->text = $validated['text'];
        $question->paragraph_location = $validated['location'];
        $question->english_explanation = $validated['english'];
        $question->persian_explanation = $validated['persian'];
        $question->answer = $validated['answer'];

        $question->save();

        if(!empty($validated['option_keys']) && !empty($validated['option_texts'])){
            $keys = $validated['option_keys'];
            $texts = $validated['option_texts'];

            foreach ($keys as $index => $key){
                if(isset($texts[$index]) && !is_null($key) && !is_null($texts[$index])){
                    $option = new QuestionOption();
                    $option->question_id = $question->id;
                    $option->key = $key;
                    $option->text = $texts[$index];
                    $option->save();
                }
            }
        }

        return response()->json([
            'message' => 'Question created successfully.',
            'id' => $question->id,
        ]);



    }
    public function importQuestions(Request $request)
    {


        $request->validate([
            'group_id' => 'required|integer|exists:question_groups,id',
            'questions_json' => 'required|string',
            'type' => 'required|string'

        ]);

        $decode = json_decode($request->questions_json, true);

        if(json_last_error() !== JSON_ERROR_NONE){
            return response()->json([
                'errors' => [
                    'questions_json' => ['JSON format is invalid.']
                ]
            ], 422);
        }

        if(!is_array($decode) || empty($decode)){
            return response()->json([
                'errors' => [
                    'questions_json' => ['MCQ JSON must be a non-empty array.']
                ]
            ], 422);
        }

        $type = strtoupper($request->type);

        try {
            return DB::transaction(function () use ($decode, $request, $type) {
                return match ($type){
                    'MCQ', 'MC X FROM N' => $this->importMultipleChoice($decode, $request->group_id),
                    'TRUE FALSE NOT GIVEN',
                    'YES NO NOT GIVEN',
                    'COMPLETING SENTENCE' ,
                    'FILLING IN SUMMARIES',
                    'COMPLETING FLOW CHART',
                    'MATCHING HEADING',
                    'MATCHING FEATURE',
                    'MATCHING INFORMATION',
                    'MATCHING SENTENCE ENDING'=> $this->withoutOption($decode, $request->group_id, $type),

                    default => response()->json([
                        'errors' => [
                            'type' => ["Type '{$type}' is not supported yet for bulk import."]
                        ]
                    ], 422)
                };
            });
        }catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return $e->getResponse();
        }
    }

    private function importMultipleChoice(array $decode, $groupId){
        foreach ($decode as $index => $item){
            $validator = Validator::make($item, [
                'number' => 'required|string|max:255',
                'text' => 'required|string',
                'paragraph_location' => 'nullable|string|max:255',
                'english_explanation' => 'required|string',
                'persian_explanation' => 'required|string',
                'answers' => 'required|array|min:1',
                'answers.*' => 'required|string',
                'options' => 'required|array|min:2',
                'options.*.key' => 'required|string',
                'options.*.text' => 'required|string|max:1000',
            ]);

            if ($validator->fails()){
                $firstError = $validator->errors()->first();

                abort(response()->json([
                    'errors' => [
                        'questions_json' => ['Row ' . ($index + 1) . ': ' . $firstError]
                    ]
                ], 422));
            }

            $optionKeys = collect($item['options'])->pluck('key')->toArray();

            if(count($optionKeys) !== count(array_unique($optionKeys))){
                abort(response()->json([
                    'errors' => [
                        'questions_json' => ['Row ' . ($index + 1) . ': option keys must be unique.']
                    ]
                ], 422));
            }

            $invalidAnswers = array_diff($item['answers'], $optionKeys);
            if(!empty($invalidAnswers)){
                abort(response()->json([
                    'errors' => [
                        'questions_json' => ['Row ' . ($index + 1) . ': answer must match one of the option keys.']
                    ]
                ], 422));
            }

            $question = new Question();
            $question->question_group_id = $groupId;
            $question->number = $item['number'];
            $question->text = $item['text'];
            $question->paragraph_location = $item['paragraph_location'] ?? null;
            $question->english_explanation = $item['english_explanation'];
            $question->persian_explanation = $item['persian_explanation'];
            $question->answers = array_map('trim', $item['answers']);
            $question->save();


            $optionToInsert = [];

            foreach ($item['options'] as $option){
                $optionToInsert[] = [
                    'question_id' => $question->id,
                    'key' => $option['key'],
                    'text' => $option['text'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            QuestionOption::query()->insert($optionToInsert);
        }
        return response()->json([
            'message' => 'MCQ questions imported successfully.',
        ]);
    }

    private function withoutOption(array $decode, $groupId, string $type){


        foreach ($decode as $index => $item){
            $rules = [
                'number' => 'required|string|max:255',
                'text' => 'required|string',
                'paragraph_location' => 'nullable|string|max:255',
                'english_explanation' => 'required|string',
                'persian_explanation' => 'required|string',
                'answers.*' => 'required|string',
            ];

            if ($type === 'TRUE FALSE NOT GIVEN') {
                $rules['answers'] = 'required|array|size:1';
                $rules['answers.*'] = 'required|string|in:TRUE,FALSE,NOT GIVEN';
            }

            if ($type === 'YES NO NOT GIVEN') {
                $rules['answers'] = 'required|array|size:1';
                $rules['answers.*'] = 'required|string|in:YES,NO,NOT GIVEN';
            }

            if (in_array($type, ['COMPLETING SENTENCE',
                'FILLING IN SUMMARIES'
                , 'MATCHING HEADING', 'COMPLETING FLOW CHART', 'MATCHING FEATURE'
                ,'MATCHING INFORMATION', 'MATCHING SENTENCE ENDING'])) {
                $rules['answers'] = 'required|array|size:1';
                $rules['answers.*'] = 'required|string|max:255';
            }

            $validator = Validator::make($item, $rules);


            if ($validator->fails()) {
                $firstError = $validator->errors()->first();

                abort(response()->json([
                    'errors' => [
                        'questions_json' => ['Row ' . ($index + 1) . ': ' . $firstError]
                    ]
                ], 422));
            }

            $question = new Question();
            $question->question_group_id = $groupId;
            $question->number = $item['number'];
            $question->text = $item['text'];
            $question->paragraph_location = $item['paragraph_location'] ?? null;
            $question->english_explanation = $item['english_explanation'];
            $question->persian_explanation = $item['persian_explanation'];
            $question->answers = array_map('trim', $item['answers']);
            $question->save();


        }

        $message = match ($type){
            'TRUE FALSE NOT GIVEN' => 'True/False/Not Given questions imported successfully.',
            'YES NO NOT GIVEN' => 'Yes/No/Not Given questions imported successfully.',
            'COMPLETING SENTENCE' => 'Completing Sentence questions imported successfully.',
            'FILLING IN SUMMARIES' => 'Filling in Summaries questions imported successfully.',
            'MATCHING HEADING' => 'matching heading questions imported successfully.',
            'COMPLETING FLOW CHART' => 'flow chart questions imported successfully',
            'MATCHING FEATURE' => 'MATCHING FEATURE questions imported successfully',
            'MATCHING INFORMATION' => 'MATCHING INFORMATION questions imported successfully',
            'MATCHING SENTENCE ENDING' => 'MATCHING SENTENCE ENDING questions imported successfully',
            default => 'Questions imported successfully.',
        };

        return response()->json([
            'message' => $message,
        ]);

    }


    public function importAll(Request $request)
    {

        $request->validate([
            'passage_id' => 'required|exists:passages,id',
            'json' => 'required|string',
        ]);

        $data = json_decode($request->input('json'), true);

        if(json_last_error() !== JSON_ERROR_NONE || !is_array($data)){
            return response()->json([
                'status' => false,
                'message' => 'json invalid'
            ], 422);
        }

        $validator = Validator::make($data, [
            'paragraphs' => ['required', 'array', 'min:1'],
            'paragraphs.*.number' => ['required', 'string'],
            'paragraphs.*.english' => ['required', 'string'],
            'paragraphs.*.persian' => ['required', 'string'],


            'question_groups' => ['required', 'array', 'min:1'],
            'question_groups.*.title' => ['required', 'string'],
            'question_groups.*.instructions' => ['required', 'string'],
            'question_groups.*.type' => ['required', 'string'],
            'question_groups.*.options' => ['nullable', 'array'],
            'question_groups.*.options.*.key' => ['required', 'string'],
            'question_groups.*.options.*.text' => ['required', 'string'],

            'question_groups.*.questions' => ['nullable', 'array'],



        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try{
            DB::transaction(function () use($request, $data){
                $passage = Passage::query()->findOrFail($request->passage_id);

                foreach ($data['paragraphs'] as $p){
                    $paragraph = new PassageParagraph();

                    $paragraph->passage_id = $passage->id;
                    $paragraph->number = trim($p['number']);
                    $paragraph->english = $p['english'];
                    $paragraph->persian = $p['persian'];
                    $paragraph->save();
                }

                foreach ($data['question_groups'] as $groupData){
                    $questionGroup = new QuestionGroup();
                    $questionGroup->passage_id = $passage->id;
                    $questionGroup->title = trim($groupData['title']);
                    $questionGroup->instructions = trim($groupData['instructions']);
                    $type = strtoupper(trim($groupData['type']));

                    $questionGroup->type =  $type;
                    $questionGroup->save();

                    if(!empty($groupData['options']) && is_array($groupData['options'])){
                        foreach ($groupData['options'] as $optionData){
                            $groupOption = new QuestionOption();
                            $groupOption->question_group_id = $questionGroup->id;
                            $groupOption->key = trim($optionData['key']);
                            $groupOption->text = trim($optionData['text']);
                            $groupOption->save();

                        }
                    }

                    if(!empty($groupData['questions']) && is_array($groupData['questions'])){
                        $this->processQuestionImport(
                            $groupData['questions'],
                            $questionGroup->id,
                            strtoupper($groupData['type'])
                        );
                    }


                }



            });

            return response()->json(['status' => true, 'message' => 'پاراگراف‌ها، گروه‌های سوال و سوالات با موفقیت وارد شدند.'], 200);

        }catch (ValidationException $e){
            return response()->json([
                'status' => false,
                'errors' => $e->errors(),

            ], 422);
        }catch (\Throwable $e){
            return response()->json([
                'status' => false,
                'message' => 'خطای سرور: ' . $e->getMessage(),
            ], 500);
        }

    }

    private function processQuestionImport(array $questions, int $groupId, string $type): void
    {
        match ($type) {
            'MCQ', 'MC X FROM N' => $this->MultipleChoice($questions, $groupId),

            'TRUE FALSE NOT GIVEN',
            'YES NO NOT GIVEN',
            'COMPLETING SENTENCE',
            'FILLING IN SUMMARIES',
            'FILLING IN NOTES',
            'COMPLETING FLOW CHART',
            'MATCHING HEADING',
            'MATCHING FEATURE',
            'MATCHING INFORMATION',
            'MATCHING SENTENCE ENDING'=> $this->importSimpleQuestions($questions, $groupId, $type),

            default => throw ValidationException::withMessages([
                'json' => ["Type '{$type}' is not supported for questions import."],
            ]),
        };
    }
    /**
     * @throws ValidationException
     */
    private function importSimpleQuestions(array $decode, $groupId, string $type){


        foreach ($decode as $index => $item){
            $rules = [
                'number' => 'required|string|max:255',
                'text' => 'required|string',
                'paragraph_location' => 'nullable|string|max:255',
                'english_explanation' => 'required|string',
                'persian_explanation' => 'required|string',
            ];

            if ($type === 'TRUE FALSE NOT GIVEN') {
                $rules['answers'] = 'required|array|size:1';
                $rules['answers.*'] = 'required|string|in:TRUE,FALSE,NOT GIVEN';
            }

            else if ($type === 'YES NO NOT GIVEN') {
                $rules['answers'] = 'required|array|size:1';
                $rules['answers.*'] = 'required|string|in:YES,NO,NOT GIVEN';
            }

            else if (in_array($type, ['COMPLETING SENTENCE',
                'FILLING IN SUMMARIES', 'FILLING IN NOTES'
                , 'MATCHING HEADING', 'COMPLETING FLOW CHART', 'MATCHING FEATURE'
                ,'MATCHING INFORMATION', 'MATCHING SENTENCE ENDING'])) {
                $rules['answers'] = 'required|array|min:1';
                $rules['answers.*'] = 'required|string|max:255';
            }else{
                throw ValidationException::withMessages([
                    'json' => ["type '$type' is not supported in import questions"],
                ]);
            }

            $validator = Validator::make($item, $rules);

            if ($validator->fails()) {
                $firstError = $validator->errors()->first();

                throw ValidationException::withMessages([
                    'questions_json' => ['Row ' . ($index + 1) . ': ' . $firstError],
                ]);
            }

            $question = new Question();
            $question->question_group_id = $groupId;
            $question->number = trim($item['number']);
            $question->text = trim($item['text']);
            $question->paragraph_location = $item['paragraph_location'] ?? '';
            $question->english_explanation = trim($item['english_explanation']);
            $question->persian_explanation = trim($item['persian_explanation']);
            $question->answers = array_map('trim', $item['answers']);
            $question->save();


        }

    }

    /**
     * @throws ValidationException
     */
    private function MultipleChoice(array $decode, int $groupId) : void{
        foreach ($decode as $index => $item){
            $validator = Validator::make($item, [
                'number' => 'required|string|max:255',
                'text' => 'required|string',
                'paragraph_location' => 'nullable|string|max:255',
                'english_explanation' => 'required|string',
                'persian_explanation' => 'required|string',
                'answers' => 'required|array|min:1',
                'answers.*' => 'required|string',
                'options' => 'required|array|min:2',
                'options.*.key' => 'required|string',
                'options.*.text' => 'required|string|max:1000',
            ]);

            if ($validator->fails()){
                throw ValidationException::withMessages([
                    'questions_json' => [
                        'Row ' . ($index + 1) . ': ' . $validator->errors()->first(),
                    ],
                ]);
            }

            $answers = array_map('trim', $item['answers']);
            $optionKeys = collect($item['options'])
                ->pluck('key')->map(fn($key) => trim($key))->toArray();

            if(count($optionKeys) !== count(array_unique($optionKeys))){
                throw ValidationException::withMessages([
                    'questions_json' => [
                        'Row ' . ($index + 1) . ': option keys must be unique.',
                    ],
                ]);
            }

            $invalidAnswers = array_diff($answers, $optionKeys);
            if(!empty($invalidAnswers)){
                throw ValidationException::withMessages([
                    'questions_json' => [
                        'Row ' . ($index + 1) . ': answer must match one of the option keys.',
                    ],
                ]);
            }

            $paragraphLocation = trim($item['paragraph_location'] ?? '');
            $question = new Question();
            $question->question_group_id = $groupId;
            $question->number = trim($item['number']);
            $question->text = trim($item['text']);
            $question->paragraph_location = $paragraphLocation !== ''
                ? $paragraphLocation
                : null;
            $question->english_explanation = trim($item['english_explanation']);
            $question->persian_explanation = trim($item['persian_explanation']);
            $question->answers = $answers;
            $question->save();


            $optionToInsert = [];

            foreach ($item['options'] as $option){
                $optionToInsert[] = [
                    'question_id' => $question->id,
                    'key' => trim($option['key']),
                    'text' => trim($option['text']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            QuestionOption::query()->insert($optionToInsert);
        }
    }
}
