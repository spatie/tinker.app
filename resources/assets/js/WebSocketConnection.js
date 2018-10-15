import Vue from 'vue';
import store from './store';

export const WebSocketConnection = new Vue({
    store,

    computed: {
        sessionId() {
            return this.$store.state.session.id;
        },
    },

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
            await this.$store.dispatch('fetchSession');

            this.webSocket = new WebSocket(`${window.webSocket.protocol}://localhost/ws/${this.sessionId}`);

            this.webSocket.onmessage = (message) => {
                const data = JSON.parse(message.data);

                this.$emit('message-received', data);
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
