<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get all users except current user for chat list
$users = $conn->query("SELECT id, name, skills, bio FROM users WHERE id != $user_id ORDER BY name");

// Get recent conversations
$conversations = $conn->query("
    SELECT DISTINCT 
        CASE WHEN m.sender_id = $user_id THEN m.receiver_id ELSE m.sender_id END as partner_id,
        u.name as partner_name,
        (SELECT message FROM messages WHERE 
            (sender_id = $user_id AND receiver_id = partner_id) OR 
            (sender_id = partner_id AND receiver_id = $user_id)
        ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages WHERE 
            (sender_id = $user_id AND receiver_id = partner_id) OR 
            (sender_id = partner_id AND receiver_id = $user_id)
        ORDER BY created_at DESC LIMIT 1) as last_time
    FROM messages m
    JOIN users u ON u.id = CASE WHEN m.sender_id = $user_id THEN m.receiver_id ELSE m.sender_id END
    WHERE m.sender_id = $user_id OR m.receiver_id = $user_id
    GROUP BY partner_id
    ORDER BY last_time DESC
");

$selected_user = null;
$messages = [];

if (isset($_GET['user'])) {
    $selected_user_id = (int)$_GET['user'];
    $selected_user = $conn->query("SELECT id, name, skills, bio FROM users WHERE id = $selected_user_id")->fetch_assoc();
    
    if ($selected_user) {
        $messages = $conn->query("SELECT m.*, 
            CASE WHEN m.sender_id = $user_id THEN 'sent' ELSE 'received' END as type
            FROM messages m 
            WHERE (m.sender_id = $user_id AND m.receiver_id = $selected_user_id) 
               OR (m.sender_id = $selected_user_id AND m.receiver_id = $user_id)
            ORDER BY m.created_at ASC");
        
        // Mark messages as read
        $conn->query("UPDATE messages SET is_read = '1' WHERE sender_id = $selected_user_id AND receiver_id = $user_id");
    }
}

// Send message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $receiver_id = (int)$_POST['receiver_id'];
    $message = sanitize($_POST['message']);
    
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param('iis', $user_id, $receiver_id, $message);
        $stmt->execute();
        
        // Add notification logic here if needed
        header("Location: chat.php?user=" . $receiver_id);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Idea2Venture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .chat-layout { display: flex; height: calc(100vh - 200px); background: white; border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-card); }
        .chat-sidebar { width: 350px; border-right: 1px solid #eee; overflow-y: auto; }
        .chat-main { flex: 1; display: flex; flex-direction: column; }
        .chat-list-item { padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; transition: all 0.3s; }
        .chat-list-item:hover, .chat-list-item.active { background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1)); }
        .chat-list-item.active { border-left: 3px solid var(--primary-color); }
        .chat-messages { flex: 1; overflow-y: auto; padding: 20px; background: #f8f9fa; }
        .message { max-width: 70%; padding: 12px 18px; border-radius: 20px; margin-bottom: 15px; animation: fadeIn 0.3s ease; }
        .message-sent { background: var(--gradient-primary); color: white; margin-left: auto; border-bottom-right-radius: 5px; }
        .message-received { background: white; border: 1px solid #e0e0e0; border-bottom-left-radius: 5px; }
        .message-time { font-size: 0.7rem; opacity: 0.7; margin-top: 5px; }
        .chat-input-area { padding: 20px; background: white; border-top: 1px solid #eee; display: flex; gap: 10px; }
        .chat-input-area input { flex: 1; border: 2px solid #e0e0e0; border-radius: 50px; padding: 12px 20px; }
        .chat-input-area input:focus { border-color: var(--primary-color); outline: none; }
        @media (max-width: 768px) { .chat-sidebar { width: 100%; display: none; } .chat-sidebar.show { display: block; } }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <h2 class="fw-bold text-gradient mb-4"><i class="bi bi-chat-dots me-2"></i>Messages</h2>
        
        <div class="chat-layout">
            <!-- Chat List -->
            <div class="chat-sidebar">
                <div class="p-3 border-bottom">
                    <input type="text" class="form-control" placeholder="Search conversations..." onkeyup="filterChat(this.value)">
                </div>
                
                <?php if ($conversations->num_rows > 0): ?>
                    <?php while ($conv = $conversations->fetch_assoc()): ?>
                    <a href="?user=<?php echo $conv['partner_id']; ?>" class="text-decoration-none chat-list-item <?php echo $selected_user && $selected_user['id'] == $conv['partner_id'] ? 'active' : ''; ?>">
                        <div class="d-flex align-items-center gap-3">
                            <div class="chat-avatar" style="width: 50px; height: 50px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                                <?php echo strtoupper($conv['partner_name'][0]); ?>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between">
                                    <strong class="text-dark"><?php echo htmlspecialchars($conv['partner_name']); ?></strong>
                                    <small class="text-muted"><?php echo timeAgo($conv['last_time']); ?></small>
                                </div>
                                <p class="mb-0 text-muted text-truncate small"><?php echo htmlspecialchars($conv['last_message']); ?></p>
                            </div>
                        </div>
                    </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-chat" style="font-size: 2rem;"></i>
                        <p class="mt-2">No conversations yet</p>
                    </div>
                <?php endif; ?>
                
                <!-- All Users -->
                <div class="border-top mt-3 p-3">
                    <h6 class="text-muted mb-3">All Users</h6>
                    <?php while ($u = $users->fetch_assoc()): ?>
                    <a href="?user=<?php echo $u['id']; ?>" class="text-decoration-none">
                        <div class="chat-list-item">
                            <div class="d-flex align-items-center gap-3">
                                <div class="chat-avatar" style="width: 40px; height: 40px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                                    <?php echo strtoupper($u['name'][0]); ?>
                                </div>
                                <div>
                                    <strong class="text-dark small"><?php echo htmlspecialchars($u['name']); ?></strong>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <!-- Chat Area -->
            <div class="chat-main">
                <?php if ($selected_user): ?>
                <div class="chat-header p-3 border-bottom bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="chat-avatar" style="width: 45px; height: 45px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                            <?php echo strtoupper($selected_user['name'][0]); ?>
                        </div>
                        <div>
                            <strong><?php echo htmlspecialchars($selected_user['name']); ?></strong>
                            <small class="text-muted d-block"><?php echo htmlspecialchars($selected_user['skills'] ?: 'No skills listed'); ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <?php if ($messages->num_rows > 0): ?>
                        <?php while ($msg = $messages->fetch_assoc()): ?>
                        <div class="message message-<?php echo $msg['type']; ?>">
                            <p class="mb-1"><?php echo htmlspecialchars($msg['message']); ?></p>
                            <small class="message-time"><?php echo date('M d, h:i A', strtotime($msg['created_at'])); ?></small>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-chat-square-text" style="font-size: 3rem;"></i>
                            <p>Start a conversation!</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <form method="POST" class="chat-input-area">
                    <input type="hidden" name="receiver_id" value="<?php echo $selected_user['id']; ?>">
                    <input type="text" name="message" placeholder="Type a message..." required autocomplete="off">
                    <button type="submit" name="send_message" class="btn btn-gradient">
                        <i class="bi bi-send"></i>
                    </button>
                </form>
                <?php else: ?>
                <div class="chat-messages d-flex align-items-center justify-content-center">
                    <div class="text-center text-muted">
                        <i class="bi bi-chat-dots" style="font-size: 4rem; opacity: 0.3;"></i>
                        <h4 class="mt-3">Select a conversation</h4>
                        <p>Choose a user to start messaging</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
    <script>
        // Auto-scroll to bottom of chat
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function filterChat(query) {
            const items = document.querySelectorAll('.chat-list-item');
            items.forEach(item => {
                const name = item.textContent.toLowerCase();
                item.style.display = name.includes(query.toLowerCase()) ? 'block' : 'none';
            });
        }
        
        // Auto-refresh messages every 5 seconds
        <?php if ($selected_user): ?>
        setInterval(() => {
            fetch('api/messages.php?receiver_id=<?php echo $selected_user['id']; ?>')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        updateMessages(data.messages);
                    }
                });
        }, 5000);
        
        function updateMessages(messages) {
            const container = document.getElementById('chatMessages');
            const currentUserId = <?php echo $user_id; ?>;
            
            container.innerHTML = messages.map(msg => `
                <div class="message ${msg.sender_id === currentUserId ? 'message-sent' : 'message-received'}">
                    <p class="mb-1">${msg.message}</p>
                    <small class="message-time">${msg.time_ago}</small>
                </div>
            `).join('');
            
            container.scrollTop = container.scrollHeight;
        }
        <?php endif; ?>
    </script>
</body>
</html>

<?php
function timeAgo($timestamp) {
    if (!$timestamp) return '';
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm';
    if ($diff < 86400) return floor($diff / 3600) . 'h';
    return floor($diff / 86400) . 'd';
}
?>