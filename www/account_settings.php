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

// 1. Načtení aktuálních dat uživatele
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
            $new_avatar_name = "avatar_" . $user_id . "_" . time() . "." . $ext;
            // Sjednocení cesty do assets/pfp/ pro konzistenci s chatem
            $target_path = "./assets/pfp/" . $new_avatar_name;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path)) {
                // Smazání starého souboru
                if (!empty($user['avatar_path']) && file_exists("./assets/pfp/" . $user['avatar_path'])) {
                    unlink("./assets/pfp/" . $user['avatar_path']);
                }
                
                $stmt = $pdo->prepare("UPDATE users SET avatar_path = ? WHERE id = ?");
                $stmt->execute([$new_avatar_name, $user_id]);
                
                $success = "Profilový obrázek byl úspěšně změněn.";
                $user['avatar_path'] = $new_avatar_name; 
            } else {
                $error = "Chyba při ukládání obrázku.";
            }
        } else {
            $error = "Nepovolený formát (JPG, PNG, WEBP).";
        }
    }

    // B) Změna HESLA
    if (!empty($_POST['new_password'])) {
        $new_pass = $_POST['new_password'];
        $conf_pass = $_POST['confirm_password'];

        if ($new_pass === $conf_pass) {
            if (strlen($new_pass) >= 6) {
                $hashed_password = password_hash($new_pass, PASSWORD_BCRYPT);
                // POZOR: V DB tabulce máš pravděpodobně název sloupce password_hash (dle login skriptu)
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
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
    <title>Web4Grade | Nastavení účtu</title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <?php include 'includes/header.php'; ?>

    <main class="flex-grow py-12 px-4">
        <div class="max-w-2xl mx-auto mb-8 flex items-center justify-between">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-700 flex items-center gap-2 font-black text-xs uppercase tracking-widest transition">
                <span>←</span> Zpět na přehled
            </a>
        </div>

        <div class="max-w-2xl mx-auto bg-white rounded-[2.5rem] shadow-xl shadow-blue-900/5 border border-gray-100 overflow-hidden">
            
            <div class="bg-slate-900 p-10 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-blue-600/20 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row items-center gap-8">
                    <div class="relative group cursor-pointer" onclick="document.getElementById('avatar-input').click()">
                        <div class="w-28 h-28 bg-white/10 rounded-[2rem] flex items-center justify-center overflow-hidden border-2 border-white/20 backdrop-blur-md shadow-2xl transition-transform group-hover:scale-105">
                            <?php if (!empty($user['avatar_path'])): ?>
                                <img src="assets/pfp/<?= htmlspecialchars($user['avatar_path']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <span class="text-5xl font-black text-blue-400"><?= strtoupper(substr($user['username'], 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="absolute -bottom-2 -right-2 bg-blue-600 p-2 rounded-xl border-4 border-slate-900 text-white shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    
                    <div class="text-center md:text-left">
                        <h2 class="text-3xl font-black tracking-tight"><?= htmlspecialchars($user['username']) ?></h2>
                        <div class="inline-flex items-center gap-2 bg-blue-500/20 px-3 py-1 rounded-lg border border-blue-400/30 mt-3">
                            <span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span>
                            <span class="text-[10px] font-black uppercase tracking-widest text-blue-200">
                                <?= $user['role'] === 'admin' ? 'Administrátor' : 'Student / Klient' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-8 md:p-12">
                <?php if ($success): ?>
                    <div class="bg-green-50 border border-green-100 text-green-600 px-6 py-4 rounded-2xl mb-8 flex items-center gap-3 font-bold text-sm">
                        <span>✅</span> <?= $success ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-100 text-red-600 px-6 py-4 rounded-2xl mb-8 flex items-center gap-3 font-bold text-sm">
                        <span>⚠️</span> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" id="avatar-form" class="hidden">
                    <input type="file" name="avatar" id="avatar-input" onchange="document.getElementById('avatar-form').submit()" accept="image/*">
                </form>

                <form method="POST" class="space-y-10">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1">Přihlašovací jméno</label>
                        <div class="flex items-center gap-4 bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4">
                            <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                            <span class="text-gray-500 font-bold"><?= htmlspecialchars($user['username']) ?></span>
                            <span class="ml-auto text-[10px] font-black text-gray-300 uppercase italic">Nelze měnit</span>
                        </div>
                    </div>

                    <div class="h-px bg-gray-100"></div>

                    <div>
                        <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-3">
                            Zabezpečení účtu
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1">Nové heslo</label>
                                <input type="password" name="new_password" placeholder="••••••••"
                                       class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 focus:bg-white outline-none transition-all font-medium">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1">Potvrzení hesla</label>
                                <input type="password" name="confirm_password" placeholder="••••••••"
                                       class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 focus:bg-white outline-none transition-all font-medium">
                            </div>
                        </div>
                        <p class="mt-4 text-[11px] text-gray-400 italic font-medium">Ponechte prázdné, pokud heslo nechcete měnit. Minimální délka je 6 znaků.</p>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-blue-600 text-white font-black py-5 rounded-2xl hover:bg-blue-700 transition-all shadow-xl shadow-blue-200 transform active:scale-[0.98] uppercase tracking-widest text-sm">
                            Uložit veškeré změny
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>