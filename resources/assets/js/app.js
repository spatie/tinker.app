import 'babel-polyfill';

import Terminal from 'xterm';

Terminal.loadAddon('fit');
Terminal.loadAddon('attach');

const xterm = new Terminal();
xterm.open(document.getElementById('terminal'), true);

const socket = new WebSocket(`ws://${window.webSocket.host}:${window.webSocket.port}/`);

xterm.attach(socket);
xterm.fit();
