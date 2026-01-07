<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    exit;
}

$order_id = (int)$_GET['order_id'];
$user_id = $_SESSION['user_id'];
// Načteme zprávy, které jsou novější než poslední ID, které už prohlížeč má
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

$stmt = $pdo->prepare("SELECT m.id, m.sender_id, m.message_text, m.file_path, m.sent_at, u.username, u.avatar_path 
                       FROM messages m 
                       JOIN users u ON m.sender_id = u.id 
                       WHERE m.order_id = ? AND m.id > ? 
                       ORDER BY m.id ASC");
$stmt->execute([$order_id, $last_id]);
$new_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vrátíme data jako JSON
header('Content-Type: application/json');
echo json_encode($new_messages);