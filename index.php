<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['roomId'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DevCollab - Code & Chat</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #fafbfc; }
    #editor { width: 100%; height: 60vh; font-family: monospace; font-size: 16px; padding: 12px; border: none; background: #f4f4f4; box-sizing: border-box; }
    #chat { width: 100%; height: 22vh; overflow-y: auto; background: #fff; border-top: 1px solid #eee; padding: 10px; box-sizing: border-box; }
    #message-box { display: flex; align-items: center; border-top: 1px solid #eee; }
    #message { flex: 1; padding: 12px; font-size: 16px; border: none; background: #f9f9f9; }
    #recipient { width: 160px; padding: 10px; font-size: 14px; border: none; background: #f1f1f1; }
  </style>
</head>
<body>
  <textarea id="editor" placeholder="Start coding..."></textarea>
  <div id="chat"></div>
  <div id="message-box">
    <input type="text" id="message" placeholder="Type a message and press Enter...">
    <select id="recipient">
      <option value="0">All</option>
    </select>
  </div>

  <script>
    const ws = new WebSocket('ws://localhost:8080');
    const editor = document.getElementById('editor');
    const message = document.getElementById('message');
    const chat = document.getElementById('chat');
    const recipient = document.getElementById('recipient');
    let suppressNextUpdate = false;
    const userId = <?= json_encode($_SESSION['id']) ?>;
    const roomId = <?= json_encode($_SESSION['roomId']) ?>;

    // Send init message on connection open
    ws.onopen = function () {
      ws.send(JSON.stringify({ type: 'init', user_id: userId, room_id: roomId }));
    };

    // Load code and chat on page load
    window.onload = function () {
      fetch('load_code.php')
        .then(res => res.text())
        .then(data => editor.value = data);

      fetch('load_chat.php')
        .then(res => res.json())
        .then(messages => {
          messages.forEach(msg => appendChat(`[${msg.username}]: ${msg.message}`));
        });

      // Load room members for unicast
      fetch('get_room_members.php')
        .then(res => res.json())
        .then(users => {
          users.forEach(user => {
            if (user.id != userId) {
              const opt = document.createElement('option');
              opt.value = user.id;
              opt.textContent = user.username;
              recipient.appendChild(opt);
            }
          });
        });
    };

    // Auto-save every 5 seconds
    setInterval(() => {
      fetch('save_code.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'code=' + encodeURIComponent(editor.value)
      });
    }, 5000);
    setInterval(() => {
      fetch('load_chat.php')
        .then(res => res.text())
        .then(html => {
          chat.innerHTML = html;
        });
    }, 200);
    editor.addEventListener('input', () => {
      if (!suppressNextUpdate) {
        ws.send(JSON.stringify({ type: 'code', content: editor.value }));
      }
    });

    message.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        const msgText = message.value.trim();
        const targetId = recipient.value;
        if (msgText) {
          // No ws.send here, only send to server via fetch below

          message.value = '';
          fetch('save_chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(msgText) +
            '&fromId=' + encodeURIComponent(userId) +
            '&toId=' + encodeURIComponent(targetId) +
            '&roomId=' + encodeURIComponent(roomId)
          });
          console.log( msgText, userId, targetId, roomId );
        }
      }
    });

    ws.onmessage = (event) => {
      const data = JSON.parse(event.data);
      if (data.type === 'code') {
        suppressNextUpdate = true;
        editor.value = data.content;
        suppressNextUpdate = false;
      }
      // if (data.type === 'chat') {
      //   const sender = data.from == userId ? 'Me' : `User ${data.from}`;
      //   appendChat(`[${sender}]: ${data.content}`);
      // }
    };

    function appendChat(msg) {
      const div = document.createElement('div');
      div.textContent = msg;
      chat.appendChild(div);
      chat.scrollTop = chat.scrollHeight;
    }
  </script>
</body>
</html>
