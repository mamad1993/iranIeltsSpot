<div class="questions-container d-none">
    <button id="openQuestionsModal" class="btn btn-primary">
        <i class="fas fa-plus"> add question</i>
    </button>
    <button id="openImportQuestionsModal" class="btn btn-success">
        <i class="fas fa-file-import"></i> import JSON
    </button>
    <table class="table table-bordered mt-3">
        <thead>
        <tr>
            <th>number</th>
            <th>Question</th>
            <th>Answer</th>
            <th>Location</th>
        </tr>
        </thead>
        <tbody id="questionTbody"></tbody>
    </table>
    <div class="modal fade" id="questionModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="questionModalTitle">اضافه کردن سوال</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="questionForm d-flex flex-column" id="questionForm">
                        <input type="hidden" name="group_id" id="group_id">
                        <input type="text" name="number" id="question_number" placeholder="enter question number">
                        <input type="text" name="text" id="question_text" placeholder="enter text">
                        <input type="text" name="location" id="question_location" placeholder="enter question location">
                        <textarea name="english" id="english_explanation" cols="30" rows="10" placeholder="enter english explanation"></textarea>
                        <textarea dir="rtl" name="persian" id="persian_explanation" cols="30" rows="10" placeholder="enter persian explanation"></textarea>
                        <input type="text" name="answer" id="question_answer" placeholder="enter answer">
                        <button class="btn btn-primary" type="button" id="addQuestionOptionBtn">
                            <i class="fas fa-plus"></i> add option
                        </button>
                        <div id="QuestionOptionsWrapper" class="my-3">

                        </div>
                        <button type="button" class="btn btn-primary" id="submitQuestion"></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ImportQuestionsModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import JSON</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="ImportQuestionsForm">
                        <input type="hidden" name="group_id" id="questions_group_id">
                        <label for="questions_json" class="form-label">MCQ JSON</label>
                        <textarea name="questions_json" id="questions_json" class="form-control" cols="30" rows="18"></textarea>
                        <button type="button" class="btn btn-success mt-3" id="submitQuestionsImport">
                            Import Questions
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>

@push('script')
    <script>
        $(document).ready(function (){
            let formMode = 'createQuestion';

            let selectedGroup = null;


            /*temp mcq starts*/
            function resetQuestionsImportForm(){
                $('#ImportQuestionsForm')[0].reset();
                $('#questions_group_id').val('');
                $('#questions_json').val('');
            }

            $('#openImportQuestionsModal').on('click', function (){
                if(!selectedGroup || !selectedGroup.id){
                    showToast('error', 'لطفاً ابتدا یک گروه سوالی انتخاب کنید.');
                    return;
                }

                resetQuestionsImportForm();
                $('#questions_group_id').val(selectedGroup.id);
                $('#ImportQuestionsModal').modal('show');

            });


            $('#submitQuestionsImport').on('click', function (){
                let groupId = selectedGroup.id;
                let questionType = selectedGroup.type;

                if (!groupId) {
                    showToast('error', 'لطفاً ابتدا یک گروه سوالی انتخاب کنید.');
                    return;
                }
                let formElement = document.querySelector('#ImportQuestionsForm');
                let formData = new FormData(formElement);
                formData.append('group_id', groupId);
                formData.append('type', questionType);


                $.ajax({
                    url: "{{ route('questions.import-questions') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response){
                        console.log('typeof response:', typeof response);
                        console.log('response:', response);
                        console.log('response.message:', response?.message);
                        showToast('success', response.message);
                    },

                    error: function (xhr){
                        if(xhr.status === 422){
                            let errors = xhr.responseJSON.errors;
                            let firstError = Object.values(errors)[0][0];
                            showToast('error', firstError);
                            return;
                        }
                        showToast('error', 'Import failed.');
                    }
                });
            });
            /*temp mcq ends*/

            function resetQuestionForm(){
                $('#questionForm')[0].reset();
                /*for today if the enteries did not submitted coming from here*/
                $('#group_id').val('');
                $('#option_keys').remove();


            }

            function setCreateMode(){
                formMode = 'createQuestion';
                resetQuestionForm();
                $('#submitQuestion').text('add question');
                $('#questionModal').modal('show');
            }

            window.questions = function (group){
               selectedGroup = group;

               $('.questions-container').removeClass('d-none');
               renderQuestion(selectedGroup);

            }

            function renderQuestion(group){
                const tbody = $('#questionTbody');
                tbody.empty();

                $.ajax({
                    url: "{{ route('get-questions') }}",
                    type: 'GET',
                    data: {id: group.id},
                    success: function (response){
                        let questions = response.questions;

                        if(!questions.length){
                            tbody.append(`
                                <tr>
                                    <td colspan="4" class="text-center">No questions found</td>
                                </tr>
                            `);
                            return;
                        }else{
                            questions.forEach(function (question){
                                tbody.append(`
                                    <tr>
                                    <td>${ question.number }</td>
                                    <td>${ question.text }</td>
                                    <td>${ question.answer }</td>
                                    <td>${ question.paragraph_location }</td>
                                    </tr>
                                `);
                            });
                        }
                    }
                });


            }

            $('#openQuestionsModal').on('click', function (){
                setCreateMode();

            });

            $('#submitQuestion').on('click', function (){
                let groupId = selectedGroup.id;

                let formElement = document.querySelector('#questionForm');
                let formData = new FormData(formElement);
                formData.append('group_id', groupId);

                if(!groupId) {
                    showToast('error', 'لطفاً ابتدا یک گروه سوالی انتخاب کنید.');
                    return;
                }

                let url = "{{ route('add.question') }}";

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function (response){
                        resetQuestionForm();
                        showToast('success', response.message);

                    },

                    error: function (xhr){
                        if(xhr.status === 422){
                            let errors = xhr.responseJSON.errors;
                            let firstError = Object.values(errors)[0][0];
                            showToast('error', firstError);
                        }
                    }
                });


            });


            $('#addQuestionOptionBtn').on('click', function (){
                $('#QuestionOptionsWrapper').append(getOptionRow());
            });


            function getOptionRow(key = '', text = ''){
                return `
                <div class="option-row d-flex gap-2 mb-1">
                    <input class="form-control form-control-sm w-25"
                           type="text" name="option_keys[]" placeholder="Key" value="${key}">

                    <input class="form-control form-control-sm w-75"
                           type="text" name="option_texts[]" placeholder="Text/Value" value="${text}">

                    <button type="button" class="btn btn-danger btn-sm remove-option">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                `;
            }
        });
    </script>
@endpush
