<div class="question-group-container d-none">
    <h5>Question Groups</h5>
    <button type="button" class="btn btn-primary" id="openCreateGroupModal"
    ><i class="fas fa-plus"></i>question group</button>
    <div class="modal fade" id="questionGroupModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="groupModalTitle">اضافه کردن گروه سوال</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="QGForm d-flex flex-column" id="groupForm">
                        <input type="hidden" name="group_id" id="group_id">
                        <input class="form-control mb-1" type="text" aria-label="title" name="title" placeholder="enter title" id="group_title">
                        <textarea class="form-control mb-1" name="instruction" aria-label="instruction" id="group_instruction" cols="30" rows="10"></textarea>
                        <select class="form-control mb-1" name="type" id="group_type">
                            <option value="">select question type</option>
                            <option value="matching heading">matching heading</option>{{--yes--}}
                            <option value="matching information">matching information</option>
                            <option value="matching feature">matching feature</option>{{--yes--}}
                            <option value="matching sentence ending">matching sentence ending</option>{{--yes--}}
                            <option value="completing sentence">completing sentence</option>
                            <option value="filling in diagram">filling in diagram</option>
                            <option value="completing flow chart">completing flow chart</option>
                            <option value="filling in notes">filling in notes</option>
                            <option value="filling in summaries">filling in summaries</option>
                            <option value="MCQ">MCQ</option>
                            <option value="MC X FROM N">MC X FROM N</option>
                            <option value="true false not given">True false not given</option>
                            <option value="yes no not given">yes no not given</option>
                        </select>
                        <button class="btn btn-primary" type="button" id="addOptionBtn">
                            <i class="fas fa-plus"></i> add option
                        </button>
                        <div id="optionsWrapper" class="my-3">

                        </div>
                        <button type="button" class="btn btn-primary" id="submitQuestionGroup">add question</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
    <div class="table-wrapper">
        <table class="qg-table table table-bordered">
            <thead>
            <tr>
                <th>title</th>
                <th>instructions</th>
                <th>type</th>
                <th>actions</th>
            </tr>
            </thead>
            <tbody id="gqTableBody">
            </tbody>
        </table>
    </div>
</div>



@push('script')
    <script>
        $(document).ready(function (){




            let currentGroups = [];

            let formMode = 'create';

            function setCreateMode(){
                formMode = 'create';
                resetGroupForm();
                $('#groupModalTitle').text('اضافه کردن گروه سوال');
                $('#submitQuestionGroup').text('add question');

            }


            function setEditMode(group){
                formMode = 'edit';
                resetGroupForm();

                $('#groupModalTitle').text('ویرایش گروه سوال');
                $('#submitQuestionGroup').text('update question');

                $('#group_id').val(group.id);
                $('#group_title').val(group.title);
                $('#group_instruction').val(group.instructions || '');
                $('#group_type').val(group.type || '');

                if(group.options && group.options.length > 0){
                    group.options.forEach(function (option){
                        $('#optionsWrapper').append(
                            getOptionRow(option.key || '', option.text || '')
                        );
                    });
                }
            }


            function resetGroupForm(){
                $('#groupForm')[0].reset();
                $('#group_id').val('');
                $('#optionsWrapper').empty();

            }

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


            $(document).on('click', '.group-row', function (e){

                if($(e.target).closest('.edit-group-btn .delete-group-btn').length){
                    return;
                }

                const groupId = $(this).data('group-id');
                const selectedGroup = currentGroups.find(group => group.id === groupId);

                if(!selectedGroup){
                    showToast('error', 'گروه سوال پیدا نشد.');
                    return;
                }

                window.questions(selectedGroup);

            });



            $(document).on('click', '.edit-group-btn', function (e){
                e.stopPropagation();

                const groupId = $(this).data('id');
                const selectedGroup = currentGroups.find(group => group.id === groupId);

                if(!selectedGroup){
                    showToast('error', 'گروه سوال پیدا نشد.');
                    return;
                }

                setEditMode(selectedGroup);
                $('#questionGroupModal').modal('show');

            });

            $('#openCreateGroupModal').on('click', function (){
                setCreateMode();
                $('#questionGroupModal').modal('show');
            });


            function renderGroupTable(groups){
                const tbody = $('#gqTableBody');
                tbody.empty();

                if(!groups || groups.length === 0){
                    tbody.append('<tr><td colspan="4" class="text-center">هیچ گروه سوالی یافت نشد.</td></tr>');
                    return;
                }

                groups.forEach(function (group){
                    const shortInstruction = group.instructions &&
                        group.instructions.length > 50 ?
                        group.instructions.substring(0,50) + '...'
                        : (group.instructions || '');

                    const row = `
                    <tr class="group-row" data-group-id="${group.id}">
                        <td>${group.title}</td>
                        <td>${shortInstruction}</td>
                        <td><span class="badge bg-secondary">${group.type}</span></td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm edit-group-btn" data-id="${group.id}">
                                        <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm delete-group-btn" data-id="${group.id}">
                                 <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    `;

                    tbody.append(row);

                });
            }


            $('#addOptionBtn').on('click', function (){


                $('#optionsWrapper').append(getOptionRow());

            });

            $(document).on('click', '.remove-option', function (){
                if($('.option-row').length > 0){
                    $(this).closest('.option-row').remove();

                }
            });

            $('#passage-selector').on('change', function (){
                const passageId = $(this).val();

                $('.question-group-container').removeClass('d-none');

                $.ajax({
                    url: "{{ route('passage.question-groups') }}",
                    type: 'GET',
                    data: {id: passageId},
                    success: function (response){


                        currentGroups = response.groups;
                        renderGroupTable(currentGroups);

                    },
                    error: function (xhr){
                        if(xhr.status === 422){
                            showToast('error', 'پسیج مورد نظر پیدا نشد.');
                        }else{
                            showToast('error', 'خطایی در سرور رخ داده است.');
                        }
                    }
                });

            });

            $('#submitQuestionGroup').on('click', function (){
                let passageId = $('#passage-selector').val();

                if(!passageId) {
                    showToast('error', 'لطفاً ابتدا یک پسیج انتخاب کنید.');
                    return;
                }

                let formElement = document.querySelector('#groupForm');
                let formData = new FormData(formElement);
                formData.append('passage_id', passageId);

                let url = "{{ route('add.question-group') }}";

                if(formMode === 'edit'){
                    let groupId = $('#group_id').val();
                    url = "/admin/updateGroup/" + groupId;
                    formData.append('_method', 'PUT');

                }

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
                        resetGroupForm();
                        showToast('success', response.message);
                        $('#questionGroupModal').modal('hide');

                        $('#passage-selector').trigger('change');

                    },

                    error : function (xhr){
                        if (xhr.status === 422){
                            let errors = xhr.responseJSON.errors;
                            let errorMessage = "";

                            $.each(errors, function (key, value){
                                errorMessage += value[0] + "<br>";
                            });

                            showToast('error', errorMessage);
                        }else{
                            showToast('error', 'خطایی در سرور رخ داده است.');
                        }
                    }

                });
            });
        });
    </script>
@endpush
