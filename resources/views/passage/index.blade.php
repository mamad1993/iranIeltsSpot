@extends('main')





@section('content')



    <div class="container-fluid py-5">

        <a href="{{ url('/admin/exams') }}">exams</a>

        <div class="passage-container">
            <h5>create passage</h5>
            <div class="input-container">
                <label for="">title(english)</label>
                <form action="{{ route('create.passage') }}" method="POST">
                    @csrf
                    <input type="text" class="form-control" name="passageTitle">
                    <button type="submit" class="btn btn-primary">save passage</button>
                </form>
                {{--<div class="successMessage" id="successMessage">
                    @if(session('success'))
                        <i class="fas fa-check-circle"><span>{{ session('success') }}</span></i>
                    @endif

                </div>--}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>

    </script>
@endsection
