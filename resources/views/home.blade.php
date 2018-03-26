@extends('layouts.app')

@section('content')

<div id="app">
    <section>
        <header>
            <div class="header">
                <nav class="flex h-26 items-center m-auto w-grid">
                    <h1 class="logo"><span class="hidden">artisan.sh</span></h1>
                    <ul class="menu">
                        <li class="menu-item">
                            <a href="#toggle">
                                <span><img src="/images/icon-toggle.svg" alt="toggle ui"></span>
                                <span class="hidden | sm:inline">dark ui</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="#settings">
                                <span><img src="/images/icon-syntax.svg" alt="syntax settings"></span>
                                <span class="hidden | sm:inline">syntax settings</span>
                            </a>
                            <modal class="hidden"></modal>
                        </li>
                        <li class="menu-item">
                            <a href="#share">
                                <span><img src="/images/icon-share.svg" alt="share session"></span>
                                <span class="hidden | sm:inline">share session</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </header>
    </section>
    <section>
        <main>
            <div class="flex flex-col w-screen | md:flex-row" style="height: calc(100vh - 6.5rem);">
                <terminal class="h-1/2 w-full | md:h-full md:w-1/2"></terminal>
                <file-editor class="h-1/2 w-full | md:h-full md:w-1/2"></file-editor>
            </div>
        </main>
    </section>
</div>

<script>
    window.webSocket = @json(config('websockets'));

    window.sessionId = @json($sessionId);
</script>

@endsection
