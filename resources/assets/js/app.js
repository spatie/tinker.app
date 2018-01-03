import 'babel-polyfill';

import Terminal from 'xterm';

Terminal.loadAddon('fit');
Terminal.loadAddon('attach');

const xterm = new Terminal({ focus: true });
xterm.open(document.getElementById('#terminal'), true);

const socket = new WebSocket('ws://localhost:8080/');

xterm.attach(socket);
xterm.fit();
