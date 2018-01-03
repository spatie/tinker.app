import 'babel-polyfill';

import Terminal from 'xterm';

Terminal.loadAddon('fit');
Terminal.loadAddon('attach');

const xterm = new Terminal();
xterm.open(document.getElementById('terminal'), true);

const socket = new WebSocket(`ws://${window.webSocket.host}:${window.webSocket.port}/`);
// const socket = new WebSocket(`ws://165.227.172.206:8080/`); // ez debug

xterm.attach(socket);
xterm.fit();
