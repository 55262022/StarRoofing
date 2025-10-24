<?php
session_start();
require_once '../../database/starroofing_db.php';

if (!isset($_SESSION['account_id'])) {
    header('Location: ../../authentication/login.php');
    exit;
}

$account_id = intval($_SESSION['account_id']);

// Get user email
$stmt = $conn->prepare("SELECT email FROM accounts WHERE id = ?");
$stmt->bind_param('i', $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) die('User not found');

$email = $user['email'];

// Get conversation ID from query string
$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;
if (!$conversation_id) die('Invalid conversation ID');

// Fetch conversation (make sure it belongs to this user)
$stmt = $conn->prepare("SELECT * FROM conversations WHERE id = ? AND email = ?");
$stmt->bind_param('is', $conversation_id, $email);
$stmt->execute();
$conversation = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$conversation) die('Conversation not found or access denied');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat - Star Roofing</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --primary: #1a365d;
    --accent: #1e88ff;
    --bg: #f6f8fb;
    --card: #ffffff;
    --border: #e6edf6;
    --muted: #6b7280;
}
body { font-family: 'Montserrat', sans-serif; background: var(--bg); margin: 0; }
.header { background: var(--card); padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px; }
.header a { color: var(--primary); font-size: 18px; text-decoration: none; }
.header h1 { margin: 0; color: var(--primary); font-size: 18px; flex: 1; }
.chat-container { max-width: 800px; margin: 20px auto; background: var(--card); border-radius: 12px; display: flex; flex-direction: column; height: 80vh; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.chat-messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; }
.message { max-width: 70%; padding: 12px 16px; border-radius: 12px; line-height: 1.4; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.message.client { align-self: flex-end; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-bottom-right-radius: 4px; }
.message.admin { align-self: flex-start; background: #f3f4f6; color: #1f2937; border-bottom-left-radius: 4px; }
.message .author { font-size: 11px; opacity: 0.8; margin-bottom: 4px; font-weight: 600; }
.message .time { font-size: 10px; opacity: 0.7; margin-top: 6px; text-align: right; }
.message .product-ref { font-size: 11px; opacity: 0.9; margin-top: 4px; padding: 4px 8px; background: rgba(255,255,255,0.2); border-radius: 4px; display: inline-block; }
.message.admin .product-ref { background: rgba(0,0,0,0.05); }
.chat-input { display: flex; padding: 12px; border-top: 1px solid var(--border); gap: 10px; }
.chat-input textarea { flex: 1; padding: 10px; border-radius: 10px; border: 1px solid var(--border); resize: none; font-size: 14px; font-family: inherit; }
.send-btn { background: var(--accent); color: white; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.2s; }
.send-btn:hover { background: #1565c0; }
.send-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.pending-notice { padding: 20px; text-align: center; color: var(--muted); background: #fef3c7; border-radius: 8px; margin: 10px; }
</style>
</head>
<body>

<div class="header">
    <a href="client-messages.php"><i class="fas fa-arrow-left"></i></a>
    <h1><i class="fas fa-comments"></i> Chat with Star Roofing</h1>
</div>

<div class="chat-container">
    <div class="chat-messages" id="chatMessages"></div>
    <?php if ($conversation['is_accepted']): ?>
    <div class="chat-input">
        <textarea id="messageInput" placeholder="Type your message..." rows="2"></textarea>
        <button class="send-btn" onclick="sendMessage()"><i class="fas fa-paper-plane"></i> Send</button>
    </div>
    <?php else: ?>
    <div class="pending-notice">
        <i class="fas fa-hourglass-half"></i> Your conversation is pending admin approval. You'll be able to chat once approved.
    </div>
    <?php endif; ?>
</div>

<script>
const conversationId = <?= $conversation_id ?>;
const isAccepted = <?= $conversation['is_accepted'] ? 'true' : 'false' ?>;

async function loadMessages() {
    try {
        const res = await fetch('../process/fetch_client_thread.php?conversation_id=' + conversationId);
        const data = await res.json();
        if (!data.success) return;

        const messagesDiv = document.getElementById('chatMessages');
        const messages = data.messages;

        messagesDiv.innerHTML = messages.map(m => {
            const productRef = m.product_name ? `<div class="product-ref"><i class="fas fa-tag"></i> Re: ${m.product_name}</div>` : '';
            return `
                <div class="message ${m.sender}">
                    <div class="author">${m.sender === 'admin' ? 'Star Roofing Support' : 'You'}</div>
                    <div class="text">${m.message.replace(/\n/g,'<br>')}</div>
                    ${productRef}
                    <div class="time">${new Date(m.sent_at).toLocaleString()}</div>
                </div>
            `;
        }).join('');

        messagesDiv.scrollTop = messagesDiv.scrollHeight;

    } catch(e) {
        console.error('Error loading messages:', e);
    }
}

async function sendMessage() {
    const textarea = document.getElementById('messageInput');
    const message = textarea.value.trim();
    if (!message) return;

    const btn = document.querySelector('.send-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

    try {
        const res = await fetch('../process/send_client_reply.php', {
            method: 'POST',
            headers: { 'Content-Type':'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ conversation_id: conversationId, message })
        });
        const data = await res.json();
        if (data.success) {
            textarea.value = '';
            loadMessages();
        } else {
            alert(data.message || 'Failed to send message');
        }
    } catch(e) {
        console.error(e);
        alert('Network error.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send';
    }
}

if (isAccepted) setInterval(loadMessages, 3000);
loadMessages();

// Enter to send
document.getElementById('messageInput')?.addEventListener('keypress', e=>{
    if(e.key==='Enter' && !e.shiftKey){ e.preventDefault(); sendMessage(); }
});
</script>

</body>
</html>