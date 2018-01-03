<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <title>Tinker.js</title>
    </head>
    <body class="w-screen h-screen">
        @yield('content')

        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>