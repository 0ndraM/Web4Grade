<?php
@ini_set('upload_max_filesize', '20M');
@ini_set('post_max_size', '25M');
// Nastavení připojení (v Dockeru)
$host = 'db';
$db   = 'objednavkovy_system';
$user = 'user';
$pass = 'user_password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Hlásí chyby jako výjimky
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Výsledky budou jako asociativní pole
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Vypne emulaci pro vyšší bezpečnost
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // V produkci (na Endoře) bys neměl vypisovat $e->getMessage() kvůli bezpečnosti
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>