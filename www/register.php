<?php
session_start();
require_once 'config/db.php';

   function logLoginAttempt($username, $status) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("INSERT INTO acces_logy (autor, akce) VALUES (?, ?)");
    $akce = "Přihlášení uživatele '$username' - $status (IP: $ip)";
    $stmt->execute([$username, $akce]);
}

// Pokud už je uživatel přihlášen, přesměrujeme ho na dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (empty($username) || empty($password)) {
        $error = "Všechna pole jsou povinná.";
    } elseif ($password !== $password_confirm) {
        $error = "Zadaná hesla se neshodují.";
    } elseif (strlen($password) < 6) {
        $error = "Heslo musí mít alespoň 6 znaků.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Toto uživatelské jméno je již obsazené.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'client')");
            
            try {
                $stmt->execute([$username, $hashed_password]);
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'client';
                logLoginAttempt($username, 'úspěšné');
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
    <title>Web4Grade | Registrace studenta</title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Inicializace Dark Mode před vykreslením (zabraňuje probliknutí bílé)
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-slate-950 flex flex-col min-h-screen font-sans transition-colors duration-300">

    <?php include 'includes/header.php'; ?>

    <main class="flex-grow flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            
            <div class="bg-white dark:bg-slate-900 p-8 md:p-10 rounded-[2.5rem] shadow-xl shadow-blue-900/5 dark:shadow-none border border-gray-100 dark:border-slate-800 transition-colors duration-300">
                <div class="text-center mb-10">
                    <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Vytvořit účet</h1>
                    <p class="text-gray-400 dark:text-slate-500 text-sm mt-2 font-medium">Připoj se k WebGrade a získej svůj web</p>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/50 text-red-600 dark:text-red-400 px-5 py-3 rounded-2xl mb-6 text-xs font-bold flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-2 ml-1">Uživatelské jméno</label>
                        <input type="text" name="username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" 
                               placeholder="např. karel_stredni"
                               class="w-full px-5 py-4 bg-gray-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-2xl focus:ring-2 focus:ring-blue-500 dark:text-white outline-none transition font-medium" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-2 ml-1">Heslo</label>
                        <input type="password" name="password" placeholder="••••••••"
                               class="w-full px-5 py-4 bg-gray-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-2xl focus:ring-2 focus:ring-blue-500 dark:text-white outline-none transition font-medium" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-2 ml-1">Potvrzení hesla</label>
                        <input type="password" name="password_confirm" placeholder="••••••••"
                               class="w-full px-5 py-4 bg-gray-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-2xl focus:ring-2 focus:ring-blue-500 dark:text-white outline-none transition font-medium" required>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-blue-600 text-white font-black py-5 rounded-[1.5rem] hover:bg-blue-700 transition-all shadow-xl shadow-blue-200 dark:shadow-none transform active:scale-[0.98] uppercase tracking-widest text-sm">
                            Zaregistrovat se
                        </button>
                    </div>
                </form>

                <div class="mt-8 pt-8 border-t border-gray-50 dark:border-slate-800 text-center transition-colors duration-300">
                    <p class="text-gray-500 dark:text-slate-400 text-sm font-medium">
                        Už máš účet? <a href="index.php#login" class="text-blue-600 dark:text-blue-400 font-black hover:underline underline-offset-4 transition">Přihlas se zde</a>
                    </p>
                </div>
            </div>

            <p class="text-center text-gray-400 dark:text-slate-600 text-[10px] uppercase tracking-[0.3em] font-bold mt-8 transition-colors duration-300">
                Registrací souhlasíš s podmínkami Web4Grade
            </p>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>