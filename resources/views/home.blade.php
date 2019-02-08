@extends('layouts.app')

@section('content')

    <div id="app" class="layout">
        <header style="grid-area: header" class="header">
            <nav class="flex justify-between items-center px-4 py-2">
                <a href="/">
                    <h1 class="flex items-center">
                        <svg class="w-8 h-8 mr-2" xmlns="http://www.w3.org/2000/svg"
                            xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 43.4 42.7"
                            style="enable-background:new 0 0 43.4 42.7;" xml:space="preserve">
                                <title></title>tinker.app</title>
                                <path class="fill-logo" d="M21.1,42.4c-4.8,0-8.9-3.5-11.1-8.4c-5.5-2-9.6-8.6-9.6-16.6c0-9.4,9.4-17.1,20.9-17.1s20.9,5.8,20.9,13.1
                                c0,5.6-3.6,10.4-8.6,12.3c0.2,1.2,0.3,2.5,0.3,3.6C34,36.6,28.2,42.4,21.1,42.4z M13,34.4c2,3.3,4.8,5.5,8.1,5.5
                                c5.8,0,10.4-4.8,10.4-10.6c0-1-0.2-2.2-0.3-3.1c-1.8,0.3-3.8,0.2-5.8,0c-1.8,4.8-6.6,8.3-12.1,8.3C13.3,34.4,13.1,34.4,13,34.4z
                                M11.8,31.9c0.5,0.2,1.2,0.2,1.7,0.2c4.3,0,7.9-2.5,9.6-6.1c-3.8-0.7-7-2.2-7-4.5c0-2.5,3-4.5,7-5c-1.2-3.1-3.3-5.5-5.8-5.5
                                c-3.1,0-6.6,6-6.6,14.4C10.6,27.8,11.1,29.9,11.8,31.9z M21.2,2.6C11.1,2.6,2.9,9.2,2.9,17.4c0,6,2.5,10.9,6.1,13.2
                                c-0.3-1.7-0.7-3.5-0.7-5.3c0-9.4,4-16.9,9.1-16.9c3.6,0,7,3.1,8.3,7.8c3.1,0.3,6,3.1,7.3,7c4-1.5,6.8-5.5,6.8-9.9
                                C39.8,7.6,31.3,2.6,21.2,2.6z M26.2,23.8c0.8,0,1.8,0.2,3,0.2c0.5,0,1,0,1.5-0.2c-1-2.5-2.5-4.5-4.3-5c0.2,0.8,0.2,1.7,0.2,2.6
                                C26.4,22.3,26.4,23.2,26.2,23.8z M23.7,18.7c-3.1,0.3-5.3,1.7-5.3,2.6c0,0.5,1.8,1.7,5.3,2.2c0.2-0.7,0.2-1.3,0.2-2
                                C24.1,20.5,23.9,19.7,23.7,18.7z"/>
                            </svg>
                        <span class="hidden text-dimmed | sm:inline hover:text-accent">tinker.app</span>
                    </h1>
                </a>
                <ul class="menu">
                    <li>
                        <a href="#settings">
                            settings
                        </a>
                    </li>
                    <li>
                        <share-button></share-button>
                    </li>
                </ul>
            </nav>
        </header>
        <terminal class="max-w-screen" style="grid-area: terminal"></terminal>
        <settings style="grid-area: editor">
        </settings>
        <file-editor style="grid-area: editor"></file-editor>
    </div>

    <script>
        window.webSocket = @json(config('websockets'));

        window.sessionId = @json($sessionId);
    </script>

@endsection
