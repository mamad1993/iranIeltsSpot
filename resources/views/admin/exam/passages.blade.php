<div class="passages-container d-none">
    <h2>passages</h2>
    <div class="passage-selector">
        <select name="passage_id" id="passage-selector" class="form-control">

        </select>
    </div>

    <div class="passage-title-form d-none">
        <form action="">
            <span>title: </span>
            <input type="text" id="titleInput">
            <input type="checkbox" id="passageWithLabel">
            <button type="button" class="btn btn-primary"></button>
        </form>

    </div>

    {{--<div class="passages-list">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>number</th>
                <th>title</th>
            </tr>
            </thead>
            <tbody id="passages-table-body"></tbody>
        </table>
    </div>--}}
</div>



@push('script')
    <script>
        $(document).ready(function () {

            let currentPassages = [];
            let currentExamId = null;
            $('#exams').on('select2:select', function (e){

                $('#titleInput').val('');
                $('.passage-title-form').addClass('d-none');

                $('.paragraph-container').addClass('d-none');
                $('#paragraphTableBody').empty();

                $('.question-group-container').addClass('d-none');
                $('#gqTableBody').empty();


                $('.questions-container').addClass('d-none');





                const id = e.params.data.id;
                currentExamId = id;

                $.ajax({
                    url: "{{ route('select.passage') }}",
                    type: 'GET',
                    data: {
                        passageId: id,
                    },

                    success: function (response){
                        currentPassages = response.passages;
                        renderPassages(response.passages);


                    },
                    error: function (xhr){
                        showToast('error', 'something went wrong');
                    }

                });


            });

            function renderPassages(passages){
                const select = $('#passage-selector');
                /*const tableBody = $('#passages-table-body');*/
                $('.passages-container').removeClass('d-none');

                select.html('<option value="">Select a Passage</option>');
                /*tableBody.html();*/



                for (let i = 1; i <= 3; i++){
                    const passage = passages.find(p => Number(p.number) === i);

                    if(passage){
                        select.append(
                            `<option value="${passage.id}">Passage ${i}
                                ${passage.title}
                            </option>`
                        );

                    }else{
                        select.append(
                            `<option value="new-${i}" class="text-muted">Passage ${i}:
                                (Not inserted yet)
                            </option>`
                        );
                    }
                }
            }


            $('#passage-selector').on('change', function (){
                 const selectId = $(this).val();
                 const form = $('.passage-title-form');
                 const input = $('#titleInput');
                 const button = $('.passage-title-form button');
                 const checkbox = $('#passageWithLabel');

                 form.removeClass('d-none');

                 const passage = currentPassages.find(p => String(p.id) === String(selectId));





                 if ($(this).val() === ''){
                     form.addClass('d-none');
                 }

                 if(passage){
                     input.val(passage.title);
                     checkbox.prop('checked', passage.label === true || String(passage.label) === '1');
                     button.text('update');
                 }else{
                     input.val('');
                     input.attr('placeholder', 'enter new passage title');
                     checkbox.prop('checked', false);
                     button.text('add');
                 }

            });

            $('.passage-title-form button').on('click', function (){

                const selectId = $('#passage-selector').val();
                const title = $('#titleInput').val();

                const labelStatus = $('#passageWithLabel').is(':checked') ? 1 : 0;




                $.ajax({
                    url: "{{ route('upsert.passage') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        'id': selectId,
                        'title': title,
                        'exam_id': currentExamId,
                        'label': labelStatus,

                    },
                    success: function (response){

                        const savedPassage = response.passage;
                        const index = currentPassages.findIndex(
                          p => String(p.id) === String(savedPassage.id)
                        );

                        if(index !== -1){
                            currentPassages[index] = savedPassage;
                        }else{
                            currentPassages.push(savedPassage);
                        }

                        renderPassages(currentPassages);

                        $('#passage-selector').val(String(savedPassage.id)).trigger('change');


                        showToast('success', response.message);
                    },

                    error: function (xhr){
                        if(xhr.status === 422){
                            showToast('error', xhr.responseJSON.message || 'invalid input');
                            return;
                        }

                        showToast('error', 'somethings wrong!');
                    }
                });
            });

        });
    </script>
@endpush
