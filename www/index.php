<?php
session_start();
require_once 'config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Neplatné údaje.";
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weby pro spolužáky | Zakázkový systém</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hero-gradient { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); }
    </style>
</head>
<body class="bg-gray-50 font-sans">

    <?php include 'includes/header.php'; ?>

    <header class="hero-gradient text-white py-20 px-6">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between">
            <div class="md:w-1/2 mb-10 md:mb-0">
                <h1 class="text-5xl md:text-6xl font-extrabold leading-tight mb-6">
                    Potřebuješ web do školy? <br><span class="text-blue-200">Mám tě pod křídly.</span>
                </h1>
                <p class="text-xl text-blue-100 mb-8">
                    Profesionální školní weby na míru. Nahraj zadání, zaplať QR kódem a sleduj progress v reálném čase.
                </p>
                <div class="flex gap-4">
                    <a href="register.php" class="bg-white text-blue-600 px-8 py-3 rounded-full text-lg font-bold hover:bg-blue-50 transition">Začít hned</a>
                    <a href="#vlastnosti" class="border border-white/30 bg-white/10 px-8 py-3 rounded-full text-lg font-bold hover:bg-white/20 transition">Více informací</a>
                </div>
            </div>
            <div class="md:w-1/2 flex justify-center">
                <div class="bg-white/10 p-4 rounded-2xl backdrop-blur-sm border border-white/20 shadow-2xl">
                    <img src="https://img.freepik.com/free-vector/website-setup-concept-illustration_114360-4256.jpg" alt="Web development illustration" class="rounded-xl max-w-sm">
                </div>
            </div>
        </div>
    </header>

    <section id="vlastnosti" class="py-20 max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-gray-900">Proč si nechat udělat web u mě?</h2>
            <div class="h-1 w-20 bg-blue-600 mx-auto mt-4"></div>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-6 text-2xl">💬</div>
                <h3 class="text-xl font-bold mb-3 text-gray-800">Integrovaný Chat</h3>
                <p class="text-gray-600">Žádné Instagram DMka. Všechno řešíme přímo u tvé zakázky na jednom místě.</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition">
                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center mb-6 text-2xl">📱</div>
                <h3 class="text-xl font-bold mb-3 text-gray-800">QR Platby</h3>
                <p class="text-gray-600">Naskenuješ, zaplatíš. Rychle, bezpečně a bez přepisování čísel účtů.</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition">
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center mb-6 text-2xl">🚀</div>
                <h3 class="text-xl font-bold mb-3 text-gray-800">Sledování Progressu</h3>
                <p class="text-gray-600">Vidíš, v jakém stavu tvůj web je. Od zadání až po finální nahrání na hosting.</p>
            </div>
        </div>
    </section>

    <section id="login" class="bg-gray-100 py-20 px-6">
        <div class="w-[90%] max-w-md mx-auto bg-white p-6 md:p-10 rounded-3xl shadow-2xl">
            <?php if (isset($_SESSION['user_id'])): ?>
                <h2 class="text-3xl font-bold mb-2 text-center text-gray-800">Jste přihlášen</h2>
                <p class="text-center text-gray-500 mb-8">Vítejte zpět, <?= htmlspecialchars($_SESSION['username']) ?></p>
                <a href="dashboard.php" class="block text-center w-full bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    Přejít na Dashboard
                </a>
            <?php else: ?>
            <h2 class="text-3xl font-bold mb-2 text-center text-gray-800">Vítejte zpět</h2>
            <p class="text-center text-gray-500 mb-8">Přihlaste se ke svému účtu</p>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm font-medium border border-red-100">
                    ⚠️ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-5">
                    <label class="block text-gray-700 text-sm font-bold mb-2 pl-1">Uživatelské jméno</label>
                    <input type="text" name="username" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                </div>
                <div class="mb-8">
                    <label class="block text-gray-700 text-sm font-bold mb-2 pl-1">Heslo</label>
                    <input type="password" name="password" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                </div>
                <button type="submit" name="login" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    Vstoupit do dashboardu
                </button>
            </form>
            <p class="mt-8 text-center text-gray-600">
                Nemáš účet? <a href="register.php" class="text-blue-600 font-bold hover:underline">Zaregistruj se</a>
            </p>
            <?php endif; ?>
        </div>
    </section>

    <footer class="py-10 text-center text-gray-400 text-sm">
        &copy; <?= date('Y') ?> WEBMASTER. Všechna práva vyhrazena.
    </footer>

</body>
</html>