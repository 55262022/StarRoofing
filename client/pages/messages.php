<?php
session_start();
require_once '../../database/starroofing_db.php';

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
    header('Location: ../login.php');
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

// Get user's conversations
$stmt = $conn->prepare("
    SELECT 
        c.id as conversation_id,
        c.is_accepted,
        c.updated_at,
        i.firstname,
        i.lastname,
        i.message as last_message,
        COUNT(DISTINCT r.id) as total_replies,
        SUM(CASE WHEN r.sender = 'admin' AND r.is_read = 0 THEN 1 ELSE 0 END) as unread_count
    FROM conversations c
    LEFT JOIN inquiries i ON i.conversation_id = c.id
    LEFT JOIN replies r ON r.conversation_id = c.id
    WHERE c.email = ?
    GROUP BY c.id
    ORDER BY c.updated_at DESC
");
$stmt->bind_param('s', $user['email']);
$stmt->execute();
$conversations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Messages - Star Roofing</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a1a2e;
            --accent: #e9b949;
            --bg: #f6f8fb;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Montserrat', sans-serif; 
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            gap: 20px;
        }
        .sidebar {
            width: 300px;
            flex-shrink: 0;
        }
        .conversations-list {
            background: var(--card);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .conversation-item:hover {
            background: #f8fafc;
        }
        .conversation-item.active {
            background: #f1f5f9;
            border-left: 3px solid var(--accent);
        }
        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        .conversation-name {
            font-weight: 600;
            color: var(--primary);
        }
        .conversation-time {
            font-size: 12px;
            color: var(--muted);
        }
        .conversation-preview {
            font-size: 14px;
            color: var(--muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .unread-badge {
            background: var(--accent);
            color: var(--primary);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .chat-area {
            flex: 1;
            background: var(--card);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            height: calc(100vh - 40px);
        }
        .chat-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .message {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 12px;
            position: relative;
        }
        .message.from-admin {
            background: #f1f5f9;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }
        .message.from-client {
            background: var(--accent);
            color: var(--primary);
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        .message-meta {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 4px;
        }
        .message-time {
            font-size: 11px;
            color: var(--muted);
            margin-top: 4px;
            text-align: right;
        }
        .chat-input {
            padding: 20px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 10px;
        }
        .chat-input textarea {
            flex: 1;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            resize: none;
            height: 60px;
            font-family: inherit;
        }
        .chat-input button {
            padding: 0 20px;
            background: var(--accent);
            color: var(--primary);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .chat-input button:hover {
            opacity: 0.9;
        }
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--muted);
            text-align: center;
            padding: 20px;
        }
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--accent);
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                padding: 10px;
            }
            .sidebar {
                width: 100%;
            }
            .chat-area {
                height: calc(100vh - 400px);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="conversations-list">
                <?php foreach ($conversations as $conv): ?>
                <div class="conversation-item" data-id="<?= $conv['conversation_id'] ?>">
                    <div class="conversation-header">
                        <div class="conversation-name"><?= htmlspecialchars($conv['firstname'] . ' ' . $conv['lastname']) ?></div>
                        <div class="conversation-time"><?= date('M j, g:ia', strtotime($conv['updated_at'])) ?></div>
                    </div>
                    <div class="conversation-preview"><?= htmlspecialchars($conv['last_message']) ?></div>
                    <?php if ($conv['unread_count'] > 0): ?>
                    <span class="unread-badge"><?= $conv['unread_count'] ?> new</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if (empty($conversations)): ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h3>No Messages Yet</h3>
                    <p>Your conversations will appear here once you start chatting.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="chat-area">
            <div class="chat-header" id="chatHeader">
                <h2>Select a Conversation</h2>
            </div>
            <div class="chat-messages" id="chatMessages">
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h3>Select a conversation to view messages</h3>
                </div>
            </div>
            <div class="chat-input" id="chatInput" style="display: none;">
                <textarea placeholder="Type your message..." id="messageInput"></textarea>
                <button id="sendButton">Send</button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        let currentConversationId = null;
        const chatMessages = document.getElementById('chatMessages');
        const chatHeader = document.getElementById('chatHeader');
        const chatInput = document.getElementById('chatInput');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');

        // Load conversation when clicked
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', () => {
                const conversationId = item.dataset.id;
                loadConversation(conversationId);
                
                // Update active state
                document.querySelectorAll('.conversation-item').forEach(i => i.classList.remove('active'));
                item.classList.add('active');
            });
        });

        // Load conversation messages
        async function loadConversation(conversationId) {
            try {
                const response = await fetch(`fetch_client_thread.php?conversation_id=${conversationId}`);
                const data = await response.json();
                
                if (data.success) {
                    currentConversationId = conversationId;
                    displayMessages(data);
                    chatInput.style.display = 'flex';
                    
                    // Update header
                    const conv = document.querySelector(`.conversation-item[data-id="${conversationId}"]`);
                    if (conv) {
                        const name = conv.querySelector('.conversation-name').textContent;
                        chatHeader.innerHTML = `<h2>${name}</h2>`;
                    }

                    // Remove unread badge if present
                    const badge = document.querySelector(`.conversation-item[data-id="${conversationId}"] .unread-badge`);
                    if (badge) badge.remove();
                }
            } catch (error) {
                console.error('Error loading conversation:', error);
            }
        }

        // Display messages
        function displayMessages(data) {
            chatMessages.innerHTML = '';
            
            data.messages.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message from-${msg.sender}`;
                messageDiv.innerHTML = `
                    <div class="message-meta">${msg.sender === 'admin' ? 'Admin' : 'You'}</div>
                    <div class="message-content">${msg.message}</div>
                    <div class="message-time">${new Date(msg.sent_at).toLocaleString()}</div>
                `;
                chatMessages.appendChild(messageDiv);
            });

            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Send message
        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        async function sendMessage() {
            if (!currentConversationId) return;
            
            const message = messageInput.value.trim();
            if (!message) return;

            try {
                const response = await fetch('send_client_reply.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        conversation_id: currentConversationId,
                        message: message
                    })
                });

                const data = await response.json();
                if (data.success) {
                    messageInput.value = '';
                    loadConversation(currentConversationId);
                }
            } catch (error) {
                console.error('Error sending message:', error);
            }
        }

        // Poll for updates every 5 seconds
        setInterval(() => {
            if (currentConversationId) {
                loadConversation(currentConversationId);
            }
        }, 5000);
    });
    </script>
</body>
</html>