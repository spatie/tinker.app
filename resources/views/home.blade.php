@extends('layouts.app')

@section('content')

<div id=app class="w-screen h-screen bg-black flex flex-row">
    <terminal class="w-1/2"></terminal>
    <file-editor class="w-1/2 h-full"></file-editor>
</div>

<script>
    window.webSocket = @json(config('websockets'));

    window.sessionId = @json($sessionId);
</script>

@endsection