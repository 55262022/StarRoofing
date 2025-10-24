<?php
session_start();
require_once '../../database/starroofing_db.php';

if (!isset($_SESSION['account_id'])) {
    header('Location: ../../authentication/login.php');
    exit;
}

$account_id = intval($_SESSION['account_id']);

$stmt = $conn->prepare("SELECT email FROM accounts WHERE id = ?");
$stmt->bind_param('i', $account_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die('User not found');
}

$email = $user['email'];

// Fetch user's conversation
$stmt = $conn->prepare("
    SELECT c.*, 
           (SELECT COUNT(*) FROM inquiries WHERE conversation_id = c.id) as inquiry_count,
           (SELECT message FROM replies WHERE conversation_id = c.id ORDER BY sent_at DESC LIMIT 1) as last_message,
           (SELECT sent_at FROM replies WHERE conversation_id = c.id ORDER BY sent_at DESC LIMIT 1) as last_message_time,
           (SELECT COUNT(*) FROM replies WHERE conversation_id = c.id AND sender = 'admin' AND is_read = 0) as unread_count
    FROM conversations c
    WHERE c.email = ?
    LIMIT 1
");
$stmt->bind_param('s', $email);
$stmt->execute();
$conversation = $stmt->get_result()->fetch_assoc();
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
    --primary: #1a365d;
    --accent: #1e88ff;
    --bg: #f6f8fb;
    --card: #ffffff;
    --border: #e6edf6;
    --muted: #6b7280;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Montserrat', sans-serif; background: var(--bg); }

.container { max-width: 1200px; margin: 0 auto; padding: 20px; }
.header { background: var(--card); padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.header h1 { color: var(--primary); margin-bottom: 5px; }
.header p { color: var(--muted); font-size: 14px; }

.conversation-card {
    background: var(--card);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border-left: 4px solid var(--accent);
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
    color: inherit;
    display: block;
    position: relative;
}
.conversation-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.conversation-info h3 { color: var(--primary); margin-bottom: 8px; font-size: 18px; }
.conversation-meta { display: flex; gap: 20px; flex-wrap: wrap; font-size: 13px; color: var(--muted); margin-bottom: 10px; }
.conversation-meta span { display: flex; align-items: center; gap: 5px; }
.message-preview { color: var(--muted); font-size: 14px; margin-top: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
    position: absolute;
    top: 20px;
    right: 20px;
}
.status-pending { background: #fef3c7; color: #92400e; }
.status-accepted { background: #d1fae5; color: #065f46; }

.unread-badge {
    position: absolute;
    top: 50%;
    right: 20px;
    transform: translateY(-50%);
    background: #ef4444;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--card);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.empty-state i { font-size: 64px; color: var(--muted); opacity: 0.3; margin-bottom: 20px; }
.empty-state h3 { color: var(--primary); margin-bottom: 10px; }
.empty-state p { color: var(--muted); }
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-comments"></i> My Messages</h1>
        <p>Your conversation with Star Roofing</p>
    </div>

    <?php if (!$conversation): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No Conversation Yet</h3>
            <p>Submit an inquiry from a product page to start a conversation with us.</p>
        </div>
    <?php else: ?>
        <a href="client_chat.php?conversation_id=<?= $conversation['id'] ?>" class="conversation-card">
            <div class="conversation-info">
                <h3><i class="fas fa-comments"></i> Conversation with Star Roofing</h3>
                <div class="conversation-meta">
                    <span><i class="fas fa-envelope"></i> <?= $conversation['inquiry_count'] ?> inquiries</span>
                    <?php if ($conversation['last_message_time']): ?>
                        <span><i class="far fa-clock"></i> <?= date('M j, Y g:i A', strtotime($conversation['last_message_time'])) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($conversation['last_message']): ?>
                    <div class="message-preview"><?= htmlspecialchars($conversation['last_message']) ?></div>
                <?php endif; ?>
            </div>
            <?php if ($conversation['is_accepted']): ?>
                <span class="status-badge status-accepted">
                    <i class="fas fa-check-circle"></i> Active
                </span>
            <?php else: ?>
                <span class="status-badge status-pending">
                    <i class="fas fa-clock"></i> Pending
                </span>
            <?php endif; ?>
            <?php if ($conversation['unread_count'] > 0): ?>
                <span class="unread-badge"><?= $conversation['unread_count'] ?></span>
            <?php endif; ?>
        </a>
    <?php endif; ?>
</div>

</body>
</html>