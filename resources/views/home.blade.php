@extends('layouts.app')

@section('content')

<div id=app class="w-screen h-screen bg-black">
    <terminal></terminal>
</div>

<script>
    window.webSocket = @json(config('websockets'));

    window.sessionId = @json($sessionId);
</script>

@endsection