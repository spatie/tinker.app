<template>
    <div></div>
</template>
<script>
import Terminal from 'xterm';
import { WebSocketConnection } from '../WebSocketConnection';

export default {
    created() {
        Terminal.loadAddon('fit');

        this.terminal = new Terminal();

        this.terminal.on('data', this.send);

        WebSocketConnection.$on('message-received', this.onWebSocketMessage);
    },

    mounted() {
        this.terminal.open(this.$el, true);

        this.terminal.fit();
    },

    methods: {
        send(data) {
            WebSocketConnection.send('terminal-data', data);
        },
        onWebSocketMessage({type, payload }) {
            if (type === 'terminal-data') {
                this.terminal.write(payload);
            }
        }
    },
}
</script>