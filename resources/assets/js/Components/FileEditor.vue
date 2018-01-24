<template>
    <div id="editor"></div>
</template>
<script>
    import * as ace from 'brace';
    import 'brace/mode/php';
    import 'brace/theme/tomorrow_night';
    import { WebSocketConnection } from '../WebSocketConnection';

    export default {
        data() {
            return {
                editor: null,
            };
        },

        created() {
        },

        mounted() {
            this.editor = ace.edit('editor');

            this.editor.getSession().setMode('ace/mode/php');

            this.editor.setTheme('ace/theme/tomorrow_night');

            this.editor.commands.addCommand({
                name: 'save',
                bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
                exec: this.saveFile,
                readOnly: true // false if this command should not apply in readOnly mode
            });
        },

        methods: {
            saveFile(editor) {
                WebSocketConnection.send('file-data', editor.getValue());
            }
        }
    }
</script>