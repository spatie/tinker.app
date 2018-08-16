<template lang="html">
    <div></div>
</template>
<script>
import { Terminal } from 'xterm';
import * as fit from 'xterm/lib/addons/fit/fit';
import { WebSocketConnection } from '../WebSocketConnection';
import darkTheme from './themes/dark';
import lightTheme from './themes/light';

export default {
    computed: {
        darkMode() {
            return this.$store.state.darkMode;
        },
    },

    watch: {
        darkMode(darkMode) {
            this.setDarkMode(darkMode);
        },
    },

    created() {
        Terminal.applyAddon(fit);

        this.terminal = new Terminal({
            cursorBlink: true,
            cursorStyle: 'underline',
        });

        this.setDarkMode(this.darkMode);

        this.terminal.on('data', this.send);

        WebSocketConnection.$on('message-received', this.onWebSocketMessage);
    },

    mounted() {
        this.terminal.open(this.$el, true);

        this.terminal.fit();
    },

    methods: {
        setDarkMode(darkMode) {
            this.terminal.setOption('theme', (darkMode ? darkTheme : lightTheme));
        },
        send(data) {
            WebSocketConnection.send('terminal-data', data);
        },
        onWebSocketMessage({
            type,
            payload
        }) {
            if (type === 'terminal-data') {
                this.terminal.write(payload);
            }
        }
    },
}
</script>
