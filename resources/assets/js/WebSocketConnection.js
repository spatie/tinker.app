import Vue from 'vue';
import store from './store';

export const WebSocketConnection = new Vue({
    store,

    data() {
        return {
            webSocket: null,
        };
    },

    created() {
        this.openWebSocket();
    },

    methods: {
        async openWebSocket() {
            this.webSocket = new WebSocket(window.webSocket.public_url);

            this.webSocket.onopen = () => {
                store.dispatch('startSession');
            };

            this.webSocket.onmessage = (message) => {
                const data = JSON.parse(message.data);

                this.$emit('message-received', data);

                if (data.type === 'session-started') {
                    const sessionData = JSON.parse(data.payload);
                    store.dispatch('updateSession', sessionData);
                }
            };
        },

        send(type, payload) {
            if (! this.webSocket) {
                console.error('WebSocket connection still initialising. Couldn\'t send: ', type, payload);

                return;
            }

            if (this.webSocket.readyState === this.webSocket.OPEN) {
                const jsonData = JSON.stringify({
                    type, payload,
                });

                this.webSocket.send(jsonData);
            }
        },
    },
});
