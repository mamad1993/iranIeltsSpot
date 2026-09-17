@extends('main')


@section('css')
    <link rel="stylesheet" href="{{ asset('/css/details.css') }}">
    {{--<link rel="stylesheet" href="{{ asset('/css/details.css') }}?v={{ filemtime(base_path('../public_html/css/details.css')) }}">--}}

@endsection

@section('content')
    <div class="container-fluid py-5">
        <h5>passage: {{ $passage->title }}</h5>
        <label for="">paragraphs</label>
        <div class="table-wrapper">
            <table class="paragraph-table table table-bordered">
                <thead>
                <tr>
                    <th>paragraph number</th>
                    <th>english paragraph</th>
                    <th dir="rtl">ترجمه فارسی</th>
                </tr>
                </thead>
                <tbody id="paragraphTableBody">
                @forelse($passage->paragraphs as $paragraph)
                    <tr data-number="{{ $paragraph->paragraph_number }}" data-toggle="modal" data-target="#paragraph-{{ $paragraph->id }}">

                        <td>{{ $paragraph->paragraph_number }}</td>
                        <td>{{ Str::words($paragraph->text, 20, '...') }}</td>

                        <td dir="rtl">{{ Str::words($paragraph->persian_translation, 20, '...') }}</td>

                    </tr>
                    <div class="modal fade" id="paragraph-{{ $paragraph->id }}">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">salam</h5>
                                    <button type="button" data-dismiss="modal"  class="close">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ url('/details/update/paragraph/' . $paragraph->id) }}" method="POST">
                                        @method('PUT')
                                        @csrf
                                        <input type="text" value="{{ $paragraph->paragraph_number }}">
                                        <textarea name="updateEnglish" id="" cols="30" rows="10">{{ $paragraph->text }}</textarea>
                                        <textarea name="updatePersian" id="" cols="30" rows="10">{{ $paragraph->persian_translation }}</textarea>
                                        <div class="p-modal-Buttons mt-2 d-flex align-items-center justify-content-between">
                                            <button class="btn btn-info" type="submit">Update</button>

                                        </div>
                                    </form>

                                    <form action="{{ url('/details/delete/paragraph/' . $paragraph->id) }}" method="POST">
                                        @method('DELETE')
                                        @csrf

                                        <button type="submit" class="btn btn-danger">delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                @empty
                    <tr class="no-data-row" dir="rtl">
                        <td colspan="3" class="text-center text-muted py-3">هیچ پاراگرافی برای این passage ثبت نشده است.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <button class="btn btn-info" type="button" id="addNewParagraph">
            <i class="fas fa-plus"></i> add paragraph
        </button>
        <div id="newParagraph" class="d-none">
            <input type="text" class="form-control" id="paragraphNumber" placeholder="enter paragraph number">
            <textarea name="" id="english-part" cols="30" rows="10" placeholder="english"></textarea>
            <textarea name="" id="persian-part" cols="30" rows="10" placeholder="فارسی" dir="rtl"></textarea>

            <div>
                <button class="btn btn-warning" type="button" id="submitParagraph">Add</button>
                <button class="btn btn-light" id="removeNewParagraph">cancel</button>
            </div>
        </div>
    </div>
@endsection


@section('script')
    <script>
        $(document).ready(function (){


            let currentParagraphNumber;

            const hasRealParagraph = $('#paragraphTableBody tr').not('.no-data-row').length > 0;

            if(hasRealParagraph){
                $('#paragraphNumber').val(getLastParagraphNumber() + 1);
            }else{
                $('#paragraphNumber').val(1);
            }



            function showAlert(message, type='warning', title="توجه"){
                return Swal.fire({
                    title: title,
                    text: message,
                    icon: type,
                    confirmButtonText: "تایید",
                    confirmButtonColor: '#ffc107',

                    didOpen: () => {
                        Swal.getPopup().style.direction = 'rtl';
                        Swal.getPopup().style.textAlign = 'right';
                    }
                });
            }

            $('#addNewParagraph').on('click', function (){
                $('#newParagraph').removeClass('d-none');
            });

            $('#removeNewParagraph').on('click', function (){
                $('#english-part').val('');
                $('#persian-part').val('');
                $('#newParagraph').addClass('d-none');
            });

            $('#submitParagraph').on('click', function (){
                const englishPart = $('#english-part').val();
                const persianPart = $('#persian-part').val();
                let paragraphNumber = $('#paragraphNumber').val();


                let enteredNumber = parseInt(paragraphNumber);


                if(isNaN(enteredNumber)){
                    showAlert('لطفا شماره پاراگراف را به صورت عدد وارد کن', 'warning');
                    return;
                }




                if(!englishPart.trim() || !persianPart.trim()){
                    showAlert('لطفا هر دو قسمت را پر کن', 'warning');
                    return;
                }



                const gapExists = hasParagraphGap();

                const duplicate = isParagraphNumberDuplicate(enteredNumber);
                const lastNumber = getLastParagraphNumber();


                if(!gapExists){
                    if(enteredNumber !== lastNumber + 1){
                        showAlert(
                            'ترتیب رعایت نشده. عدد پیشنهادی: ' + (lastNumber + 1),
                            'warning'
                        ).then(() => {
                            $('#paragraphNumber').val(lastNumber + 1);
                        });
                        return;
                    }
                }



                if(duplicate && !gapExists){
                    showAlert(
                        'این قسمت قبلا استفاده شده عدد پیشنهادی: ' + (lastNumber + 1),
                        'warning'
                    ).then(() => {
                        $('#paragraphNumber').val(lastNumber + 1);
                    });
                    return;
                }

                if(duplicate && gapExists){
                    showAlert('عدد ورودی قبلا استفاده شده', 'warning');
                    return;
                }


                $.ajax({
                    url: "{{ route('add.paragraph') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        'passageId': "{{ $passage->id }}",
                        'english': englishPart,
                        'persian': persianPart,
                        'paragraphNumber': paragraphNumber,
                    },
                    success: function (response){
                        const p = response.paragraph;
                        const newRow = `
                            <tr data-number="${p.paragraphNumber}" data-toggle="modal" data-target="#paragraph-${p.id}">
                                <td>${p.paragraphNumber}</td>
                                <td>${$('<div>').text(p.english).html()}</td>
                                <td dir="rtl">${$('<div>').text(p.persian).html()}</td>

                            </tr>

                        `;

                        const newModal = `
                            <div class="modal fade" id="paragraph-${p.id}">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">salam</h5>
                                            <button type="button" data-dismiss="modal" class="close">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/details/update/paragraph/${p.id}" method="POST">
                                                <input type="hidden" name="_method" value="PUT">
                                                <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                                                <input type="text" value="${p.paragraphNumber}">
                                                <textarea name="updateEnglish" cols="30" rows="10">${p.english}</textarea>
                                                <textarea name="updatePersian" cols="30" rows="10">${p.persian}</textarea>
                                                <div class="p-modal-Buttons mt-2 d-flex align-items-center justify-content-between">
                                                    <button class="btn btn-info" type="submit">Update</button>
                                                </div>
                                            </form>

                                            <form action="/details/delete/paragraph/${p.id}" method="POST">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                                                <button type="submit" class="btn btn-danger">delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('body').append(newModal);

                        $('#paragraphTableBody .no-data-row').remove();


                        let inserted = false;
                        $('#paragraphTableBody tr').each(function (){
                            const rowNumber = parseInt($(this).data('number'));
                            if(rowNumber > p.paragraphNumber){
                                $(this).before(newRow);
                                inserted = true;
                                return false;
                            }
                        });

                        if(!inserted){
                            $('#paragraphTableBody').append(newRow);
                            currentParagraphNumber = parseInt($('#paragraphNumber').val());
                            $('#paragraphNumber').val(currentParagraphNumber + 1);

                        }



                        $('#english-part').val('');
                        $('#persian-part').val('');

                    },

                });
            });


            $('#paragraphTableBody').on('click', 'tr[data-toggle="modal"]', function () {
                const target = $(this).data('target');
                $(target).modal('show');
            });
            function getLastParagraphNumber(){

                let lastNumber = 0;
                $('#paragraphTableBody tr').each(function (){

                    const num = parseInt($(this).data('number'));
                    if(num > lastNumber){
                        lastNumber = num;

                    }
                });

                return lastNumber;
            }

            function hasParagraphGap(){
                let numbers = [];
                $('#paragraphTableBody tr').not('.no-data-row').each(function (){
                    const num = parseInt($(this).attr('data-number'));
                    if(!isNaN(num)) numbers.push(num);

                });



                if(numbers.length === 0) return false;



                if(numbers[0] !== 1) {
                    return true;
                }

                for (let i = 0; i<numbers.length; i++){
                    if(numbers[i+1] - numbers[i] > 1){
                        return true;

                    }
                }

                return false;

            }

            function isParagraphNumberDuplicate(number){
                let isDuplicate = false;
                $('#paragraphTableBody tr').each(function (){
                    const rowNumber = parseInt($(this).data('number'));

                    if(rowNumber === number){
                        isDuplicate = true;
                    }
                });

                return isDuplicate;
            }


        });
    </script>
@endsection
