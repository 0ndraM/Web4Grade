<?php
session_start();
require_once 'config/db.php';

// Pokud už je přihlášen, pošleme ho na dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Základní validace
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Všechna pole jsou povinná.";
    } elseif ($password !== $password_confirm) {
        $error = "Hesla se neshodují.";
    } elseif (strlen($password) < 6) {
        $error = "Heslo musí mít alespoň 6 znaků.";
    } else {
        // Kontrola, zda uživatel nebo email již neexistuje
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "Uživatelské jméno nebo e-mail již existuje.";
        } else {
            // Hashování hesla a uložení uživatele
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'client')");
            
            try {
                $stmt->execute([$username, $email, $hashed_password]);
                
                // Automatické přihlášení po registraci
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'client';

                header("Location: dashboard.php");
                exit;
            } catch (PDOException $e) {
                $error = "Chyba při registraci. Zkuste to prosím znovu.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrace | Objednávkový systém</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">

    <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Vytvořit účet</h1>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Uživatelské jméno</label>
                <input type="text" name="username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">E-mail</label>
                <input type="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Heslo</label>
                <input type="password" name="password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Potvrzení hesla</label>
                <input type="password" name="password_confirm" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <button type="submit" class="w-full bg-green-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-700 transition duration-300">
                Zaregistrovat se
            </button>
        </form>

        <p class="mt-6 text-center text-gray-600 text-sm">
            Již máte účet? <a href="index.php" class="text-blue-500 hover:underline">Přihlaste se</a>
        </p>
    </div>

</body>
</html>