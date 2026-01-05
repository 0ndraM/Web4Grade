<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$order_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// 1. Načtení detailu objednávky
$stmt = $pdo->prepare("SELECT o.*, u.username as client_name FROM orders o JOIN users u ON o.client_id = u.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order || ($role !== 'admin' && $order['client_id'] !== $user_id)) {
    die("Objednávka nenalezena.");
}

// 2. LOGIKA: SMAZÁNÍ ZAKÁZKY (POUZE ADMIN)
if ($role === 'admin' && isset($_POST['delete_order'])) {
    // Smazání souborů z disku (přílohy zadání)
    $stmtF = $pdo->prepare("SELECT file_path FROM order_files WHERE order_id = ?");
    $stmtF->execute([$order_id]);
    $orderFiles = $stmtF->fetchAll(PDO::FETCH_COLUMN);
    foreach ($orderFiles as $f) {
        if (!empty($f) && file_exists("./assets/uploads/" . $f)) unlink("./assets/uploads/" . $f);
    }

    // Smazání souborů z chatu
    $stmtC = $pdo->prepare("SELECT file_path FROM messages WHERE order_id = ? AND file_path IS NOT NULL");
    $stmtC->execute([$order_id]);
    $chatFiles = $stmtC->fetchAll(PDO::FETCH_COLUMN);
    foreach ($chatFiles as $cf) {
        if (!empty($cf) && file_exists("./assets/uploads/" . $cf)) unlink("./assets/uploads/" . $cf);
    }

    // Smazání z databáze
    $stmtD = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmtD->execute([$order_id]);

    header("Location: dashboard.php");
    exit;
}

// 3. Aktualizace času přečtení (pro notifikace)
if ($role === 'admin') {
    $stmt = $pdo->prepare("UPDATE orders SET admin_last_read_at = NOW() WHERE id = ?");
} else {
    $stmt = $pdo->prepare("UPDATE orders SET client_last_read_at = NOW() WHERE id = ?");
}
$stmt->execute([$order_id]);

// 4. Admin: Update stavu, ceny a URL
if ($role === 'admin' && isset($_POST['update_order'])) {
    $new_status = $_POST['status'];
    $new_price = $_POST['price'];
    $new_url = trim($_POST['web_url'] ?? '');
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ?, price = ?, web_url = ? WHERE id = ?");
    $stmt->execute([$new_status, $new_price, $new_url, $order_id]);
    
    header("Location: order_detail.php?id=" . $order_id);
    exit;
}

// 5. Chat: Odeslání zprávy (AJAX i POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['message']) || isset($_FILES['chat_file']))) {
    $msg = isset($_POST['message']) ? trim($_POST['message']) : '';
    $file_name = null;

    if (isset($_FILES['chat_file']) && $_FILES['chat_file']['error'] === UPLOAD_ERR_OK) {
        $original_name = $_FILES['chat_file']['name'];
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'zip', 'rar', 'docx', 'txt'];
        
        if (in_array($ext, $allowed)) {
            $file_name = bin2hex(random_bytes(8)) . "_chat_" . time() . "." . $ext;
            $target_path = "./assets/uploads/" . $file_name;
            if (!move_uploaded_file($_FILES['chat_file']['tmp_name'], $target_path)) $file_name = null;
        }
    }

    if (!empty($msg) || $file_name !== null) {
        $stmt = $pdo->prepare("INSERT INTO messages (order_id, sender_id, message_text, file_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $user_id, $msg, $file_name]);
    }
    
    if (isset($_POST['ajax'])) exit;
    header("Location: order_detail.php?id=" . $order_id);
    exit;
}

// 6. Načtení příloh zadání
$stmtFiles = $pdo->prepare("SELECT * FROM order_files WHERE order_id = ?");
$stmtFiles->execute([$order_id]);
$files = $stmtFiles->fetchAll();

// 7. QR Platba - Výpočet údajů
$muj_iban = 'CZ6208000000003016704153'; // Tvůj IBAN
$pure_iban = str_replace(' ', '', $muj_iban);
$bankCode = substr($pure_iban, 4, 4);
$accountNumber = ltrim(substr($pure_iban, 8), '0');

function getBadgeStyle($status) {
    $map = [
        'new' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'pending_payment' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        'paid' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'in_progress' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        'finished' => 'bg-gray-100 text-gray-700 dark:bg-slate-800 dark:text-slate-400'
    ];
    return $map[$status] ?? 'bg-gray-100 text-gray-600';
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web4Grade | Zakázka #<?= $order['id'] ?></title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        .chat-container { height: calc(100vh - 200px); }
        @media (max-width: 768px) { .chat-container { height: 500px; } }
    </style>
</head>
<body class="bg-gray-50 dark:bg-slate-950 flex flex-col min-h-screen transition-colors duration-300">

    <?php include 'includes/header.php'; ?>

    <main class="flex-grow max-w-7xl mx-auto w-full px-4 py-8">
        <div class="flex flex-col lg:grid lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1 space-y-6">
                
                <div class="bg-white dark:bg-slate-900 rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-slate-800">
                    <div class="flex justify-between items-start mb-6">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-slate-500">Projekt #<?= $order['id'] ?></span>
                        <span class="<?= getBadgeStyle($order['status']) ?> px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-tighter">
                            <?= str_replace('_', ' ', $order['status']) ?>
                        </span>
                    </div>
                    
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-4 leading-tight"><?= htmlspecialchars($order['title']) ?></h1>
                    <p class="text-gray-500 dark:text-slate-400 text-sm leading-relaxed bg-gray-50 dark:bg-slate-800/50 p-4 rounded-2xl italic mb-6">
                        <?= nl2br(htmlspecialchars($order['description'])) ?>
                    </p>

                    <?php if (!empty($files)): ?>
                    <div class="mb-8">
                        <p class="text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-3 ml-1">Přílohy zadání (<?= count($files) ?>)</p>
                        <div class="grid grid-cols-3 gap-2">
                            <?php foreach ($files as $file): 
                                $ext = strtolower(pathinfo($file['file_path'], PATHINFO_EXTENSION));
                                $isImg = in_array($ext, ['jpg','jpeg','png','webp']);
                                $filePath = "assets/uploads/" . $file['file_path'];
                            ?>
                            <a href="<?= $filePath ?>" target="_blank" class="group relative aspect-square rounded-xl border border-gray-100 dark:border-slate-800 overflow-hidden bg-gray-50 dark:bg-slate-800 hover:ring-2 ring-blue-500 transition-all">
                                <?php if ($isImg): ?>
                                    <img src="<?= $filePath ?>" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                                <?php else: ?>
                                    <div class="w-full h-full flex flex-col items-center justify-center">
                                        <span class="text-xl">📄</span>
                                        <span class="text-[8px] font-black uppercase text-gray-400 mt-1"><?= $ext ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="absolute inset-0 bg-blue-600/90 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity p-2 text-center">
                                    <span class="text-[8px] text-white font-bold break-all leading-tight"><?= htmlspecialchars($file['file_name']) ?></span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($order['web_url'])): ?>
                    <a href="<?= htmlspecialchars($order['web_url']) ?>" target="_blank" 
                       class="flex items-center justify-center gap-3 bg-green-600 dark:bg-green-500/20 text-white dark:text-green-400 font-black py-4 rounded-2xl hover:bg-green-700 dark:hover:bg-green-500/30 transition shadow-lg shadow-green-100 dark:shadow-none mb-6 uppercase tracking-widest text-[10px] border dark:border-green-500/30">
                        🌐 Zobrazit testovací verzi
                    </a>
                    <?php endif; ?>

                    <div class="flex justify-between items-center py-4 border-t dark:border-slate-800 mt-4">
                        <span class="text-gray-400 dark:text-slate-500 font-bold text-[10px] uppercase tracking-widest">Sjednaná cena</span>
                        <span class="text-2xl font-black text-blue-600 dark:text-blue-400"><?= number_format($order['price'], 0, ',', ' ') ?> Kč</span>
                    </div>
                </div>

                <?php if ($order['status'] === 'pending_payment' && $order['price'] > 0): ?>
                <div class="bg-blue-600 dark:bg-blue-900/40 rounded-[2rem] p-6 shadow-xl text-white border dark:border-blue-800/50">
                    <h3 class="font-black text-[10px] uppercase tracking-[0.2em] mb-5 opacity-80 italic">Platební údaje (QR)</h3>
                    <div class="bg-white p-3 rounded-2xl mb-5 shadow-inner">
                      <img src="<?= getQRPlatba($muj_iban, $order['price'], 'CZK', 'Platba #' . $order['id']) ?>" alt="QR" class="mx-auto w-40 h-40 border shadow-sm rounded">

                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center border-b border-white/10 pb-2">
                            <span class="text-[10px] uppercase font-bold opacity-60">Účet:</span>
                            <span class="font-mono font-bold"><?= $accountNumber ?> / <?= $bankCode ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] uppercase font-bold opacity-60">Částka:</span>
                            <span class="font-black text-xl"><?= number_format($order['price'], 2, ',', ' ') ?> Kč</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($role === 'admin'): ?>
                <div class="bg-white dark:bg-slate-900 rounded-[2rem] p-6 border-2 border-yellow-400 dark:border-yellow-500/50 shadow-sm space-y-6">
                    <h3 class="font-black text-[10px] uppercase tracking-[0.2em] text-yellow-600 mb-2 italic">Admin control center</h3>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 ml-1">Stav</label>
                            <select name="status" class="w-full bg-gray-50 dark:bg-slate-800 border-none p-3 rounded-xl text-sm font-bold dark:text-white">
                                <option value="new" <?= $order['status']=='new'?'selected':'' ?>>Nový</option>
                                <option value="pending_payment" <?= $order['status']=='pending_payment'?'selected':'' ?>>Čeká na platbu</option>
                                <option value="paid" <?= $order['status']=='paid'?'selected':'' ?>>Zaplaceno</option>
                                <option value="in_progress" <?= $order['status']=='in_progress'?'selected':'' ?>>V práci</option>
                                <option value="finished" <?= $order['status']=='finished'?'selected':'' ?>>Hotovo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 ml-1">Cena (Kč)</label>
                            <input type="number" name="price" value="<?= $order['price'] ?>" class="w-full bg-gray-50 dark:bg-slate-800 border-none p-3 rounded-xl text-sm font-bold dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 ml-1">URL vyhotovení</label>
                            <input type="url" name="web_url" value="<?= htmlspecialchars($order['web_url'] ?? '') ?>" class="w-full bg-gray-50 dark:bg-slate-800 border-none p-3 rounded-xl text-sm font-bold dark:text-white">
                        </div>
                        <button type="submit" name="update_order" class="w-full bg-yellow-400 text-yellow-900 font-black py-4 rounded-2xl hover:bg-yellow-500 transition shadow-lg uppercase tracking-widest text-[10px]">Uložit změny</button>
                    </form>
                    <div class="pt-4 border-t dark:border-slate-800">
                        <form method="POST" onsubmit="return confirm('Opravdu smazat zakázku i soubory?');">
                            <button type="submit" name="delete_order" class="w-full bg-red-100 dark:bg-red-900/20 text-red-600 font-black py-3 rounded-2xl hover:bg-red-600 hover:text-white transition text-[10px] uppercase tracking-widest border border-red-200 dark:border-red-900/30">🗑️ Smazat zakázku</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-2 flex flex-col bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-slate-800 overflow-hidden chat-container">
                <div class="bg-gray-900 p-5 flex items-center justify-between text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse shadow-[0_0_10px_rgba(34,197,94,0.8)]"></div>
                        <span class="font-black text-xs uppercase tracking-widest">Projektová diskuze</span>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-widest opacity-40 italic">Live Stream</span>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-slate-50/50 dark:bg-slate-950/20" id="chat-box">
                    </div>

                <form id="chat-form" class="p-4 bg-white dark:bg-slate-900 border-t border-gray-100 dark:border-slate-800 flex items-end gap-3" enctype="multipart/form-data">
                    <input type="file" id="chat-file" name="chat_file" class="hidden" onchange="previewFile()">
                    <button type="button" onclick="document.getElementById('chat-file').click()" class="p-3.5 bg-gray-50 dark:bg-slate-800 text-gray-400 hover:text-blue-600 rounded-2xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                    <div class="flex-1">
                        <div id="file-preview" class="hidden text-[10px] text-blue-600 font-black bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded-xl mb-2 flex justify-between">
                            <span id="file-name"></span>
                            <button type="button" onclick="resetChatFile()" class="text-red-500">✕</button>
                        </div>
                        <textarea id="msg-input" name="message" placeholder="Napište zprávu..." rows="1" class="w-full bg-gray-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-3.5 text-sm dark:text-white focus:ring-2 focus:ring-blue-500 resize-none outline-none font-medium transition-all"></textarea>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white p-4 rounded-2xl hover:bg-blue-700 transition shadow-lg shadow-blue-200 dark:shadow-none transform active:scale-95">
                        <svg class="w-5 h-5 transform rotate-90" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        let lastMessageId = 0;
        const orderId = <?= $order_id ?>;
        const userId = <?= $user_id ?>;
        const chatBox = document.getElementById('chat-box');
        
        async function fetchMessages() {
            try {
                const response = await fetch(`includes/fetch_messages.php?order_id=${orderId}&last_id=${lastMessageId}`);
                const newMessages = await response.json();
                if (newMessages.length > 0) {
                    newMessages.forEach(msg => {
                        appendMessage(msg);
                        lastMessageId = Math.max(lastMessageId, msg.id);
                    });
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            } catch (err) { console.error("Chyba chatu:", err); }
        }
        
        function appendMessage(msg) {
            const isMe = msg.sender_id == userId;
            const time = new Date(msg.sent_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            let avatarHtml = msg.avatar_path 
                ? `<img src="assets/pfp/${msg.avatar_path}" class="w-8 h-8 rounded-full object-cover border border-gray-100 dark:border-slate-800 shadow-sm">`
                : `<div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-slate-800 flex items-center justify-center text-[10px] font-black text-blue-600 dark:text-blue-400 border border-gray-100 dark:border-slate-800">${msg.username.charAt(0).toUpperCase()}</div>`;

            const fileHtml = msg.file_path ? 
                `<a href="assets/uploads/${msg.file_path}" target="_blank" class="block mb-2 p-3 bg-black/5 dark:bg-white/5 rounded-xl text-[10px] font-black underline uppercase tracking-widest flex items-center gap-2">
                    📄 Příloha: ${msg.file_path.split('_').pop()}
                </a>` : '';

            const msgHtml = `
                <div class="flex ${isMe ? 'flex-row-reverse' : 'flex-row'} items-end gap-3 mb-2 animate-in fade-in slide-in-from-bottom-2 duration-300">
                    <div class="flex-shrink-0 mb-5">${avatarHtml}</div>
                    <div class="max-w-[75%] md:max-w-[65%]">
                        <p class="text-[9px] font-black text-gray-400 dark:text-slate-600 mb-1 px-1 uppercase tracking-widest ${isMe ? 'text-right' : 'text-left'}">${msg.username}</p>
                        <div class="p-4 shadow-sm text-sm ${isMe ? 'bg-blue-600 text-white rounded-[1.5rem] rounded-tr-none' : 'bg-white dark:bg-slate-800 border dark:border-slate-700 rounded-[1.5rem] rounded-tl-none text-gray-800 dark:text-slate-200'}">
                            ${fileHtml}
                            <div class="leading-relaxed">${msg.message_text ? msg.message_text.replace(/\n/g, '<br>') : ''}</div>
                        </div>
                        <p class="text-[9px] font-bold text-gray-400 dark:text-slate-600 mt-1.5 ${isMe ? 'text-right' : 'text-left'} uppercase tracking-tighter">${time}</p>
                    </div>
                </div>`;
            chatBox.insertAdjacentHTML('beforeend', msgHtml);
        }

        document.getElementById('chat-form').onsubmit = async function(e) {
            e.preventDefault();
            const msgInput = document.getElementById('msg-input');
            const fileInput = document.getElementById('chat-file');
            if (msgInput.value.trim() === '' && fileInput.files.length === 0) return;

            const formData = new FormData(this);
            formData.append('ajax', '1');
            msgInput.value = '';
            resetChatFile();

            try {
                await fetch(`order_detail.php?id=${orderId}`, { method: 'POST', body: formData });
                fetchMessages();
            } catch (err) { console.error(err); }
        };

        function previewFile() {
            const file = document.getElementById('chat-file').files[0];
            if (file) {
                document.getElementById('file-name').innerText = file.name;
                document.getElementById('file-preview').classList.remove('hidden');
            }
        }
        function resetChatFile() {
            document.getElementById('chat-file').value = '';
            document.getElementById('file-preview').classList.add('hidden');
        }

        fetchMessages();
        setInterval(fetchMessages, 3000);
    </script>
</body>
</html>