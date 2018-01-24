import Vue from 'vue';

export const WebSocketConnection = new Vue({
    data() {
        return {
            webSocket: null,
        };
    },

    created() {
        this.webSocket = new WebSocket(`ws://${window.webSocket.host}:${window.webSocket.port}/${window.sessionId}`);

        this.webSocket.onmessage = (message) => {
            const data = JSON.parse(message.data);

            this.$emit('message-received', data);
        };
    },

    methods: {
        send(type, payload) {
            if (this.webSocket.readyState === this.webSocket.OPEN) {
                const jsonData = JSON.stringify({
                    type, payload,
                });

                this.webSocket.send(jsonData);
            }
        },
    },
});