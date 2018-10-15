<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Code+Pro:400,500,600">
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <title>Tinker.app</title>
    </head>
    <body class="w-screen h-screen">
        @yield('content')

        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
