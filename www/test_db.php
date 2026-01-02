<?php
require_once 'config/db.php';

if ($pdo) {
    echo "Připojení k databázi bylo úspěšné!<br>";
    
    // Zkusíme vytáhnout toho admina, co jsme tam vložili přes SQL
    $stmt = $pdo->query("SELECT username FROM users");
    $user = $stmt->fetch();
    echo "Nalezený uživatel v DB: " . htmlspecialchars($user['username']);
}