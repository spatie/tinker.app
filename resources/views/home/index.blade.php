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


        var socket = new WebSocket('ws://{{ config('websockets.host') }}:{{ config('websockets.port') }}/');

        term.attach(socket);  // Attach the above socket to `term`
      </script>
    </body>
  </html>