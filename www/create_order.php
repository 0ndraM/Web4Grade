<?php
session_start();
require_once 'config/db.php';

// Kontrola přihlášení
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php#login");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user_id'];

    if (!empty($title) && !empty($description)) {
        $pdo->beginTransaction();
        try {
            // 1. Vložení objednávky
            $stmt = $pdo->prepare("INSERT INTO orders (client_id, title, description, status) VALUES (?, ?, ?, 'new')");
            $stmt->execute([$user_id, $title, $description]);
            $order_id = $pdo->lastInsertId();

            // 2. Zpracování souborů
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'docx', 'zip'];
                
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    $originalName = $_FILES['images']['name'][$key];
                    $fileError = $_FILES['images']['error'][$key];
                    
                    if ($fileError === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                        if (in_array($ext, $allowed)) {
                            $newName = bin2hex(random_bytes(8)) . "_" . time() . "_" . $key . "." . $ext;
                            $destPath = "./assets/uploads/" . $newName;

                            if (move_uploaded_file($tmp_name, $destPath)) {
                                $stmtFile = $pdo->prepare("INSERT INTO order_files (order_id, file_path, file_name) VALUES (?, ?, ?)");
                                $stmtFile->execute([$order_id, $newName, $originalName]);
                            }
                        }
                    }
                }
            }

            $pdo->commit();
            header("Location: dashboard.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Chyba při ukládání: " . $e->getMessage();
        }
    } else {
        $error = "Vyplňte prosím název i popis projektu.";
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web4Grade | Nová zakázka</title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-slate-950 flex flex-col min-h-screen transition-colors duration-300">

    <?php include 'includes/header.php'; ?>

    <main class="flex-grow py-12 px-4">
        <div class="max-w-2xl mx-auto bg-white dark:bg-slate-900 p-8 md:p-10 rounded-[2rem] shadow-xl shadow-blue-900/5 border border-gray-100 dark:border-slate-800 transition-colors">
            <div class="flex justify-between items-center mb-10">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Nová zakázka</h2>
                    <p class="text-gray-400 dark:text-slate-500 text-sm mt-1 font-medium">Popiš nám své zadání</p>
                </div>
                <a href="dashboard.php" class="bg-gray-50 dark:bg-slate-800 text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition uppercase tracking-widest">
                    Zpět
                </a>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/50 text-red-600 dark:text-red-400 px-5 py-4 rounded-2xl mb-8 text-sm font-bold flex items-center gap-3">
                    <span class="text-xl">⚠️</span> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" class="space-y-8">
                <div>
                    <label class="block text-gray-400 dark:text-slate-500 text-[10px] uppercase tracking-[0.2em] font-black mb-3 ml-1">Název projektu / tématu</label>
                    <input type="text" name="title" placeholder="Např. Osobní portfolio s kontaktním formulářem" 
                           class="w-full px-5 py-4 bg-gray-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-2xl focus:ring-2 focus:ring-blue-500 dark:text-white outline-none transition font-medium">
                </div>

                <div>
                    <label class="block text-gray-400 dark:text-slate-500 text-[10px] uppercase tracking-[0.2em] font-black mb-3 ml-1">Podrobné zadání</label>
                    <textarea name="description" rows="6" placeholder="Zkopíruj sem zadání od učitele nebo popiš svou představu..." 
                              class="w-full px-5 py-4 bg-gray-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-2xl focus:ring-2 focus:ring-blue-500 dark:text-white outline-none transition font-medium resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-blue-600 dark:text-blue-400 text-[10px] uppercase tracking-[0.2em] font-black mb-3 ml-1">Přílohy (Zadání v PDF, obrázky...)</label>
                    <div class="relative group">
                        <input type="file" name="images[]" id="file-input" multiple
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div class="w-full border-2 border-dashed border-gray-200 dark:border-slate-700 p-10 rounded-3xl bg-gray-50 dark:bg-slate-800/50 group-hover:bg-blue-50/50 dark:group-hover:bg-blue-900/20 group-hover:border-blue-300 dark:group-hover:border-blue-800 transition-all text-center">
                            <div class="w-12 h-12 bg-white dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm text-blue-500 dark:text-blue-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Klikni nebo přetáhni soubory</p>
                            <p class="text-[11px] text-gray-400 dark:text-slate-500 font-medium tracking-wide">Podporujeme PDF, ZIP, Obrázky a Dokumenty</p>
                        </div>
                    </div>
                    
                    <div id="file-list" class="mt-5 space-y-3"></div>

                    <button type="button" onclick="resetFiles()" id="reset-btn" class="hidden mt-4 text-[10px] text-red-500 font-black uppercase tracking-widest hover:text-red-600 flex items-center gap-2 px-2">
                        <span class="text-xs">✕</span> Vymazat vybrané soubory
                    </button>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-black py-5 rounded-[1.5rem] hover:bg-blue-700 transition-all shadow-xl shadow-blue-200 dark:shadow-none transform hover:-translate-y-1 active:scale-95 uppercase tracking-widest text-sm">
                    Odeslat zakázku ke schválení
                </button>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

<script>
function updateFileList() {
    const input = document.getElementById('file-input');
    const list = document.getElementById('file-list');
    const resetBtn = document.getElementById('reset-btn');

    list.innerHTML = '';

    if (input.files.length > 0) {
        resetBtn.classList.remove('hidden');

        Array.from(input.files).forEach(file => {
            const item = document.createElement('div');
            item.className = 'text-xs text-gray-600 dark:text-slate-400 flex justify-between items-center bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 p-4 rounded-xl shadow-sm animate-fade-in';
            item.innerHTML = `
                <span class="flex items-center gap-3">
                    <span class="w-8 h-8 bg-blue-50 dark:bg-blue-900/30 text-blue-500 dark:text-blue-400 rounded-lg flex items-center justify-center font-bold">📄</span>
                    <strong class="text-gray-800 dark:text-slate-200">${file.name}</strong>
                </span>
                <span class="text-gray-400 dark:text-slate-500 font-bold uppercase tracking-tighter">${(file.size/1024).toFixed(0)} KB</span>
            `;
            list.appendChild(item);
        });
    } else {
        resetBtn.classList.add('hidden');
    }
}

function resetFiles() {
    const input = document.getElementById('file-input');
    input.value = '';
    updateFileList();
}

document.getElementById('file-input').addEventListener('change', updateFileList);
</script>
</body>
</html>