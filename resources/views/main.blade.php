<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document</title>

    <link rel="stylesheet" href="{{ asset('/plugins/bootstrap5/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/plugins/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/plugins/select2/css/select2.min.css') }}">
    @yield('css')
</head>
<body>
@yield('content')
</body>

<script src="{{ asset('/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('/plugins/bootstrap5/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('/plugins/select2/js/select2.min.js') }}"></script>

<script>
    function showToast(icon, message){
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: message,
            showConfirmButton:false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.style.direction = 'rtl';
                const titleEl = toast.querySelector('.swal2-title');
                titleEl.style.unicodeBidi = "plaintext";

            }
        });
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
</script>

@stack('script')
@yield('script')
{{--@stack('passageScript')--}}
</html>
