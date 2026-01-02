<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: index.php");
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
            // 1. Vložíme objednávku
            $stmt = $pdo->prepare("INSERT INTO orders (client_id, title, description, status) VALUES (?, ?, ?, 'new')");
            $stmt->execute([$user_id, $title, $description]);
            $order_id = $pdo->lastInsertId();

            // 2. Zpracujeme soubory
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
                
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    $originalName = $_FILES['images']['name'][$key];
                    $fileError = $_FILES['images']['error'][$key];
                    
                    if ($fileError === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                        if (in_array($ext, $allowed)) {
                            // GENERUJEME OPRAVDU UNIKÁTNÍ NÁZEV (náhoda + čas + klíč)
                            $newName = bin2hex(random_bytes(8)) . "_" . time() . "_" . $key . "." . $ext;
                            $destPath = "./uploads/" . $newName;

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
        $error = "Vyplňte prosím název i popis.";
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nová zakázka</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-4 md:p-8">
    <div class="max-w-2xl mx-auto bg-white p-6 md:p-8 rounded-2xl shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Nová zakázka</h2>
            <a href="dashboard.php" class="text-gray-400 hover:text-gray-600 text-sm">Zpět</a>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2 pl-1">Název projektu / tématu</label>
                <input type="text" name="title" placeholder="Např. Web o historii" 
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition" required>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2 pl-1">Podrobné zadání</label>
                <textarea name="description" rows="5" placeholder="Co mám vytvořit..." 
                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition" required></textarea>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2 pl-1 text-blue-600">Přílohy (můžete vybrat více souborů)</label>
                <div class="relative">
                    <input type="file" name="images[]" id="file-input" multiple 
                           class="w-full border-2 border-dashed border-gray-200 p-8 rounded-2xl bg-gray-50 hover:bg-white hover:border-blue-400 transition cursor-pointer text-center text-sm text-gray-500"
                           onchange="updateFileList()">
                </div>
                
                <div id="file-list" class="mt-4 space-y-2"></div>

                <button type="button" onclick="resetFiles()" id="reset-btn" class="hidden mt-3 text-xs text-red-500 font-bold hover:underline flex items-center gap-1">
                    <span>✕</span> Zrušit výběr a začít znovu
                </button>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-100">
                Odeslat zakázku ke zpracování
            </button>
        </form>
    </div>

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
                item.className = 'text-xs text-gray-600 flex justify-between items-center bg-gray-50 border p-3 rounded-lg';
                item.innerHTML = `
                    <span class="flex items-center gap-2">📎 <strong>${file.name}</strong></span>
                    <span class="text-gray-400">${(file.size/1024).toFixed(1)} KB</span>
                `;
                list.appendChild(item);
            });
        } else {
            resetBtn.classList.add('hidden');
        }
    }

    function resetFiles() {
        document.getElementById('file-input').value = "";
        updateFileList();
    }
    </script>
</body>
</html>