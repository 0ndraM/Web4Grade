<?php
session_start();
require_once 'config/db.php';

// Ochrana: Pokud uživatel není přihlášen, přesměrujeme na login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// 1. Načtení aktuálních dat uživatele (včetně cesty k avataru)
$stmt = $pdo->prepare("SELECT username, role, avatar_path FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 2. Zpracování formuláře (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A) Nahrávání AVATARA
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $file_name = $_FILES['avatar']['name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Vytvoříme unikátní název pro avatar
            $new_avatar_name = "avatar_" . $user_id . "_" . time() . "." . $ext;
            $target_path = "./uploads/" . $new_avatar_name;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path)) {
                // Smažeme starý fyzický soubor, pokud existoval
                if (!empty($user['avatar_path']) && file_exists("./uploads/" . $user['avatar_path'])) {
                    unlink("./uploads/" . $user['avatar_path']);
                }
                
                // Uložíme nový název do DB
                $stmt = $pdo->prepare("UPDATE users SET avatar_path = ? WHERE id = ?");
                $stmt->execute([$new_avatar_name, $user_id]);
                
                $success = "Profilový obrázek byl úspěšně změněn.";
                $user['avatar_path'] = $new_avatar_name; // Aktualizace pro zobrazení v náhledu
            } else {
                $error = "Chyba při ukládání obrázku na server.";
            }
        } else {
            $error = "Nepovolený formát obrázku (povoleno: JPG, PNG, WEBP).";
        }
    }

    // B) Změna HESLA
    if (!empty($_POST['new_password'])) {
        $new_pass = $_POST['new_password'];
        $conf_pass = $_POST['confirm_password'];

        if ($new_pass === $conf_pass) {
            if (strlen($new_pass) >= 6) {
                $hashed_password = password_hash($new_pass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
                $success = "Heslo bylo úspěšně změněno.";
            } else {
                $error = "Heslo musí mít alespoň 6 znaků.";
            }
        } else {
            $error = "Nová hesla se neshodují.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nastavení účtu</title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 md:p-8">
    
    <div class="max-w-2xl mx-auto mb-6 flex items-center justify-between px-4 md:px-0">
        <a href="dashboard.php" class="text-blue-600 hover:underline flex items-center gap-2 font-bold">
            <span>←</span> Zpět na Dashboard
        </a>
        <h1 class="text-xl font-bold text-gray-800">Nastavení účtu</h1>
    </div>

    <div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
        
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-8 text-white">
            <div class="flex flex-col md:flex-row items-center gap-6">
                <div class="relative group cursor-pointer" onclick="document.getElementById('avatar-input').click()">
                    <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/30 shadow-xl">
                        <?php if (!empty($user['avatar_path'])): ?>
                            <img src="uploads/<?= htmlspecialchars($user['avatar_path']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <span class="text-4xl font-bold"><?= strtoupper(substr($user['username'], 0, 1)) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="absolute inset-0 bg-black/40 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-[10px] font-bold uppercase tracking-wider">
                        Změnit foto
                    </div>
                </div>
                
                <div class="text-center md:text-left">
                    <h2 class="text-3xl font-bold"><?= htmlspecialchars($user['username']) ?></h2>
                    <p class="text-blue-100 opacity-80 uppercase text-xs font-bold tracking-widest mt-1">
                        <?= $user['role'] === 'admin' ? 'Administrátor systému' : 'Zákaznický účet' ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="p-6 md:p-8">
            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-2xl mb-6 flex items-center gap-3 animate-pulse">
                    <span>✅</span> <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl mb-6 flex items-center gap-3">
                    <span>⚠️</span> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="avatar-form" class="hidden">
                <input type="file" name="avatar" id="avatar-input" onchange="document.getElementById('avatar-form').submit()" accept="image/*">
            </form>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Uživatelské jméno</label>
                    <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled 
                           class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-gray-500 cursor-not-allowed font-medium">
                </div>

                <hr class="border-gray-100">

                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="text-blue-600">🔒</span> Změna hesla
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nové heslo</label>
                            <input type="password" name="new_password" placeholder="••••••••"
                                   class="w-full border border-gray-200 rounded-2xl px-4 py-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Potvrzení hesla</label>
                            <input type="password" name="confirm_password" placeholder="••••••••"
                                   class="w-full border border-gray-200 rounded-2xl px-4 py-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 active:scale-[0.98]">
                        Aktualizovat nastavení
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="max-w-2xl mx-auto mt-8 flex justify-center items-center gap-6">
        <a href="logout.php" class="text-red-500 font-bold hover:text-red-700 transition">Odhlásit se</a>
    </div>

</body>
</html>