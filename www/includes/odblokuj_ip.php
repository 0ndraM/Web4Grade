<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit("Přístup zamítnut.");
}

if (!isset($_GET['ip'])) {
    exit("IP adresa nebyla zadána.");
}

$ip = $_GET['ip'];
$file = __DIR__ . '/blocked_ips.txt';

if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $new_lines = array_filter($lines, function($line) use ($ip) {
        return trim($line) !== $ip;
    });
    file_put_contents($file, implode(PHP_EOL, $new_lines) . PHP_EOL, LOCK_EX);
}

header('Location: ../logs.php');
exit();
