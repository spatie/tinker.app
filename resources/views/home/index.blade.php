  <!doctype html>
  <html>
    <head>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/xterm/2.9.2/xterm.css" />
      <script src="https://cdnjs.cloudflare.com/ajax/libs/xterm/2.9.2/xterm.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xterm/2.9.2/addons/attach/attach.js"></script>
    </head>
    <body>
      <div id="terminal"></div>
      <script>
      	var term = new Terminal();
        term.open(document.getElementById('#terminal'));
        term.write('Hello from \033[1;3;31mxterm.js\033[0m $ ')
        

        var socket = new WebSocket('ws://165.227.172.206/containers/tinker-FpGKTCmcAlY9npg0/attach/ws?stdin=1&stdout=1&stream=1');

        term.attach(socket);  // Attach the above socket to `term`
      </script>
    </body>
  </html>