@extends('main')

@section('content')

    <div class="container-fluid py-5">

        <div class="exams-container">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExam">add exam</button>
            <div class="modal fade" id="addExam">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">add exam</h5>
                            <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                        </div>

                        <div class="modal-body">
                            <form action="{{ route('add.exam') }}" method="POST">
                                @csrf
                                <select name="exam_level">
                                    <option value="B1">intermediate</option>
                                    <option value="B2">upper intermediate</option>
                                    <option value="C1">Advance</option>
                                </select>
                                <button class="btn btn-primary" type="submit">add</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <select name="" id="exams" class="form-control">
                <option value="" selected>select an exam</option>
                @foreach($exams as $exam)
                    <option value="{{ $exam->id }}">exam{{ $exam->number }} ( from {{ $exam->min_mark }} to {{ $exam->max_mark }} )</option>
                @endforeach
            </select>

            <div class="update-exam">
                <input type="text" id="examNumberInput" placeholder="enter exam number">
                <button type="submit" id="fetchThisExam" class="btn btn-primary">Find</button>
                <div class="modal fade" id="updateExam">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="modal-header">
                                    <div class="modal-title">update</div>
                                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('update.exam') }}" method="POST">
                                        @method('PUT')
                                        @csrf
                                        <input type="hidden" name="exam_id" id="examId">
                                        <input readonly type="text" name="exam_number" id="exam_number">
                                        <select id="exam_level" name="exam_level">
                                            <option value="B1">Intermediate</option>
                                            <option value="B2">Upper intermediate</option>
                                            <option value="C1">Advance</option>
                                        </select>
                                        <button class="btn btn-primary">update</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.exam.passages')
        @include('admin.exam.paragraphs')
        @include('admin.exam.question-groups')
        @include('admin.exam.questions')
    </div>

@endsection


@push('script')
    <script>
        $(document).ready(function (){
            $('#exams').select2({
                placeholder: 'select an exam',
                allowClear: true,
                width: '100%',
            });

            $('#fetchThisExam').on('click', function (){

                let examNumber = parseInt($('#examNumberInput').val());

                $.ajax({
                    url: "{{ route('updateModal') }}",
                    type: 'GET',
                    data: {
                        'examNumber': examNumber
                    },
                    success: function (response){
                        $('#examId').val(response.exam.id);
                        $('#exam_number').val(response.exam.number);
                        $('#exam_level').val(response.exam.level);


                        let modal = new bootstrap.Modal(document.getElementById('updateExam'));
                        modal.show();
                    },

                    error: function (xhr){
                        let message = 'یه خطایی پیش اومد.';

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.messages) {
                                message = xhr.responseJSON.messages;
                            } else if (xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                        }

                        showToast('error', message);
                    }
                });
            });

            @if(session('toast_error'))
                showToast('error', @json(session('toast_error')));
            @endif

            @if(session('toast_success'))
                showToast('success', @json(session('toast_success')));
            @endif


        });
    </script>
@endpush
