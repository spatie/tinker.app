<template>
    <div class="m-4 absolute pin"></div>
</template>
<script>
import Terminal from 'xterm';

export default {
    created() {
        Terminal.loadAddon('fit');

        this.terminal = new Terminal();

        this.terminal.on('data', this.send);

        this.socket = new WebSocket(`ws://${window.webSocket.host}:${window.webSocket.port}/${window.sessionId}`);

        this.socket.onmessage = (message) => {
            const data = JSON.parse(message.data);

            if (data.type === 'terminal-data') {
                this.terminal.write(data.payload);
            }
        };
    },

    mounted() {
        this.terminal.open(this.$el, true);

        this.terminal.fit();
    },

    methods: {
        send(data) {
            if(this.socket.readyState === this.socket.OPEN){
                const jsonData = JSON.stringify({
                    'type': 'terminal-data',
                    'payload': data,
                });

                this.socket.send(jsonData);
            }
        }
    },
}
</script>