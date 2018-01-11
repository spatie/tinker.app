<template>
    <div class="m-4 absolute pin"></div>
</template>
<script>
import Terminal from 'xterm';

export default {
    created() {
        Terminal.loadAddon('fit');

        this.terminal = new Terminal();

        this.socket = new WebSocket(`ws://${window.webSocket.host}:${window.webSocket.port}/${window.sessionId}`);
    },

    mounted() {
        this.terminal.open(this.$el, true);

        xterm.fit();
    }
}
</script>