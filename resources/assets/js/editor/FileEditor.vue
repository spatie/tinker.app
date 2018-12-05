<template lang="html">
    <div id="editor"></div>
</template>
<script>
    import * as ace from 'brace';
    import 'brace/mode/php';
    import 'brace/theme/github';
    import 'brace/theme/monokai';
    import { WebSocketConnection } from '../WebSocketConnection';

    export default {
        data() {
            return {
                editor: null,
                lastDelta: null,
            };
        },

        computed: {
            darkMode() {
                return this.$store.state.darkMode;
            },
        },

        watch: {
            darkMode(darkMode) {
                this.editor.setTheme((darkMode ? 'ace/theme/monokai' : 'ace/theme/github'));
            },
        },

        created() {
            WebSocketConnection.$on('message-received', this.onWebSocketMessage);
        },

        mounted() {
            this.editor = ace.edit('editor');

            this.editor.getSession().setMode({
                path: 'ace/mode/php',
                inline: true,
            });

            this.editor.setTheme('ace/theme/github');

            this.editor.setValue(
                `//use cmd+s or ctrl+s to save and run.

$n = 100;

for ($i = 1; $i <= $n; $i++) {  //numbers to be checked as prime
    $counter = 0;

    for ($j = 1; $j <= $i; $j++) { //all divisible factors

            if ($i % $j == 0){
                $counter++;
            }
    }

    //prime requires 2 rules (divisible by 1 and divisible by itself)

    if ($counter == 2 ) {
        print $i." is Prime\\n";
    }
}
`, 1);

            this.editor.on('change', delta => {
                if (this.lastDelta != delta) {
                    // console.log(JSON.stringify(delta));
                    WebSocketConnection.send('buffer-change', delta);
                }
            });

            this.editor.commands.addCommand({
                name: 'save',
                bindKey: {
                    win: 'Ctrl-S',
                    mac: 'Command-S',
                },
                exec: this.saveFile,
                readOnly: true,
            });
        },

        methods: {
            saveFile(editor) {
                WebSocketConnection.send('buffer-run', editor.getValue());
            },
            onWebSocketMessage({ type, payload }) {
                if (type === 'buffer-change') {
                    this.lastDelta = JSON.parse(payload);
                    this.editor.getSession().getDocument().applyDeltas([this.lastDelta]);
                }
            },
        },
    };
</script>
