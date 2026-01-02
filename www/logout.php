<?php
session_start();

// Smaže všechna data ze session
$_SESSION = array();

// Pokud chcete úplně zničit session, smažte i session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Zničí session
session_destroy();

// Přesměruje na úvodní stránku
header("Location: index.php");
exit;