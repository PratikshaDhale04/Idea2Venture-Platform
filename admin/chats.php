<?php
require_once '../config/database.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('../index.php');
}

$conversations = $conn->query("
    SELECT 
        CASE WHEN m.sender_id < m.receiver_id THEN m.sender_id ELSE m.receiver_id END as user1,
        CASE WHEN m.sender_id < m.receiver_id THEN m.receiver_id ELSE m.sender_id END as user2,
        (SELECT name FROM users WHERE id = user1) as user1_name,
        (SELECT name FROM users WHERE id = user2) as user2_name,
        (SELECT message FROM messages WHERE 
            (sender_id = user1 AND receiver_id = user2) OR 
            (sender_id = user2 AND receiver_id = user1)
        ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages WHERE 
            (sender_id = user1 AND receiver_id = user2) OR 
            (sender_id = user2 AND receiver_id = user1)
        ORDER BY created_at DESC LIMIT 1) as last_time
    FROM messages m
    GROUP BY user1, user2
    ORDER BY last_time DESC
");

$selected_conversation = null;
$chat_messages = [];

if (isset($_GET['chat'])) {
    $chat_id = sanitize($_GET['chat']);
    $parts = explode('-', $chat_id);
    $user1 = (int)$parts[0];
    $user2 = (int)$parts[1];
    
    $user1_data = $conn->query("SELECT id, name FROM users WHERE id = $user1")->fetch_assoc();
    $user2_data = $conn->query("SELECT id, name FROM users WHERE id = $user2")->fetch_assoc();
    
    if ($user1_data && $user2_data) {
        $selected_conversation = [
            'user1' => $user1_data,
            'user2' => $user2_data
        ];
        
        $chat_messages = $conn->query("SELECT m.*, u.name as sender_name 
            FROM messages m 
            JOIN users u ON m.sender_id = u.id
            WHERE (m.sender_id = $user1 AND m.receiver_id = $user2) 
               OR (m.sender_id = $user2 AND m.receiver_id = $user1)
            ORDER BY m.created_at ASC");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Monitoring - Idea2Venture Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font-bootstrap-icons.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="../css/admin_panel.css">
    <style>
        .chat-conversation-list {
            max-height: 500px;
            overflow-y: auto;
        }
        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .conversation-item:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            transform: translateX(5px);
        }
        .conversation-item.active {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border-left: 3px solid #667eea;
        }
        .chat-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }
        .chat-display {
            height: 500px;
            background: white;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            animation: fadeIn 0.5s ease-out;
        }
        .chat-messages-display {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 20px 20px 0 0;
        }
        .chat-msg {
            max-width: 70%;
            padding: 12px 18px;
            border-radius: 18px;
            margin-bottom: 15px;
            animation: fadeIn 0.3s ease;
        }
        .chat-msg-sent {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 5px;
        }
        .chat-msg-received {
            background: white;
            border: 1px solid #e0e0e0;
            border-bottom-left-radius: 5px;
        }
        .chat-header-display {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 20px 20px 0 0;
        }
        .chat-read-only {
            padding: 20px;
            background: white;
            border-top: 1px solid #f0f0f0;
            text-align: center;
            color: #888;
            border-radius: 0 0 20px 20px;
        }
    </style>
</head>
<body class="admin-body">
    <div class="d-flex admin-layout">
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h4 class="fw-bold mb-0"><i class="bi bi-shield-check me-2"></i>Admin Panel</h4>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="sidebar-link"><i class="bi bi-speedometer2 me-3"></i>Dashboard</a>
                <a href="users.php" class="sidebar-link"><i class="bi bi-people me-3"></i>Users</a>
                <a href="ideas.php" class="sidebar-link"><i class="bi bi-lightbulb me-3"></i>Ideas</a>
                <a href="requests.php" class="sidebar-link"><i class="bi bi-person-plus me-3"></i>Requests</a>
                <a href="chats.php" class="sidebar-link active"><i class="bi bi-chat-dots me-3"></i>Chats</a>
                <div class="sidebar-divider"></div>
                <a href="../index.php" class="sidebar-link"><i class="bi bi-house me-3"></i>Back to Site</a>
                <a href="../auth/logout.php" class="sidebar-link text-danger"><i class="bi bi-box-arrow-right me-3"></i>Logout</a>
            </nav>
        </div>
        
        <div class="admin-main">
            <h2 class="fw-bold mb-4" style="background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><i class="bi bi-chat-dots me-2"></i>Chat Monitoring</h2>
            
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="admin-card fade-in animate-delay-1">
                        <h5 class="fw-bold mb-3">Conversations</h5>
                        <div class="chat-conversation-list">
                            <?php if ($conversations->num_rows > 0): ?>
                                <?php while ($conv = $conversations->fetch_assoc()): 
                                    $chat_id = $conv['user1'] . '-' . $conv['user2'];
                                ?>
                                <a href="?chat=<?php echo $chat_id; ?>" class="text-decoration-none">
                                    <div class="conversation-item <?php echo isset($_GET['chat']) && $_GET['chat'] == $chat_id ? 'active' : ''; ?>">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="chat-avatar">
                                                <?php echo strtoupper($conv['user1_name'][0]); ?>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="d-flex justify-content-between">
                                                    <strong class="text-dark"><?php echo htmlspecialchars($conv['user1_name']); ?></strong>
                                                    <small class="text-muted">vs</small>
                                                    <strong class="text-dark"><?php echo htmlspecialchars($conv['user2_name']); ?></strong>
                                                </div>
                                                <p class="mb-0 text-muted small text-truncate"><?php echo htmlspecialchars($conv['last_message']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No conversations yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8">
                    <?php if ($selected_conversation): ?>
                    <div class="chat-display">
                        <div class="chat-header-display">
                            <div class="d-flex align-items-center gap-3">
                                <div class="chat-avatar"><?php echo strtoupper($selected_conversation['user1']['name'][0]); ?></div>
                                <div>
                                    <strong class="text-white"><?php echo htmlspecialchars($selected_conversation['user1']['name']); ?></strong>
                                    <span class="text-white opacity-75 mx-2">↔</span>
                                    <strong class="text-white"><?php echo htmlspecialchars($selected_conversation['user2']['name']); ?></strong>
                                </div>
                            </div>
                        </div>
                        
                        <div class="chat-messages-display">
                            <?php if ($chat_messages->num_rows > 0): ?>
                                <?php while ($msg = $chat_messages->fetch_assoc()): ?>
                                <div class="chat-msg <?php echo $msg['sender_id'] == $selected_conversation['user1']['id'] ? 'chat-msg-sent' : 'chat-msg-received'; ?>">
                                    <small class="opacity-75 d-block"><?php echo htmlspecialchars($msg['sender_name']); ?></small>
                                    <p class="mb-0"><?php echo htmlspecialchars($msg['message']); ?></p>
                                    <small class="opacity-50"><?php echo date('M d, h:i A', strtotime($msg['created_at'])); ?></small>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-5">No messages in this conversation</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="chat-read-only">
                            <i class="bi bi-eye me-2"></i> Read-only monitoring - Admin cannot participate in chats
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="admin-card d-flex align-items-center justify-content-center fade-in animate-delay-2" style="min-height: 500px;">
                        <div class="text-center text-muted">
                            <i class="bi bi-chat-dots" style="font-size: 4rem; opacity: 0.3;"></i>
                            <h4 class="mt-3">Select a conversation</h4>
                            <p>Click on a conversation to view messages</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>