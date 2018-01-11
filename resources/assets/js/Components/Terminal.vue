<template>
    <div class="m-4 absolute pin"></div>
</template>
<script>
import Terminal from 'xterm';

export default {
    mounted() {
        Terminal.loadAddon('fit');
        Terminal.loadAddon('attach');

        const xterm = new Terminal();

        xterm.open(this.$el, true);

        const socket = new WebSocket(`ws://${window.webSocket.host}:${window.webSocket.port}/${window.sessionId}`);

        xterm.attach(socket);
        xterm.fit();
    }
}
</script>