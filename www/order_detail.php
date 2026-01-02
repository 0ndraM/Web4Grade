<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$order_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// 1. Načtení detailu objednávky
$stmt = $pdo->prepare("SELECT o.*, u.username as client_name FROM orders o JOIN users u ON o.client_id = u.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order || ($role !== 'admin' && $order['client_id'] !== $user_id)) {
    die("Objednávka nenalezena nebo k ní nemáte přístup.");
}

// 2. Aktualizace času přečtení (pro červenou tečku v dashboardu)
if ($role === 'admin') {
    $stmt = $pdo->prepare("UPDATE orders SET admin_last_read_at = NOW() WHERE id = ?");
} else {
    $stmt = $pdo->prepare("UPDATE orders SET client_last_read_at = NOW() WHERE id = ?");
}
$stmt->execute([$order_id]);

// 3. Logika pro Admina: Aktualizace stavu a ceny
if ($role === 'admin' && isset($_POST['update_order'])) {
    $new_status = $_POST['status'];
    $new_price = $_POST['price'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ?, price = ? WHERE id = ?");
    $stmt->execute([$new_status, $new_price, $order_id]);
    header("Location: order_detail.php?id=" . $order_id);
    exit;
}

// 4. Logika pro Chat: Odeslání zprávy (Vylepšená verze s kontrolou přípon)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['message']) || isset($_FILES['chat_file']))) {
    $msg = isset($_POST['message']) ? trim($_POST['message']) : '';
    $file_name = null;

    // Kontrola, zda dorazil soubor přes pole chat_file
    if (isset($_FILES['chat_file']) && $_FILES['chat_file']['error'] === UPLOAD_ERR_OK) {
        $original_name = $_FILES['chat_file']['name'];
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        // --- SEM PATŘÍ POLE ALLOWED ---
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'zip', 'rar', 'docx', 'txt', 'jar'];
        
        if (in_array($ext, $allowed)) {
            // Vygenerování unikátního názvu
            $file_name = bin2hex(random_bytes(8)) . "_chat_" . time() . "." . $ext;
            $target_path = "./uploads/" . $file_name;

            if (!move_uploaded_file($_FILES['chat_file']['tmp_name'], $target_path)) {
                $file_name = null; 
            }
        }
    }

    // Uložíme jen pokud je tam text NEBO povolený soubor
    if (!empty($msg) || $file_name !== null) {
        $stmt = $pdo->prepare("INSERT INTO messages (order_id, sender_id, message_text, file_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $user_id, $msg, $file_name]);
    }
    
    if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
        exit;
    }
    
    header("Location: order_detail.php?id=" . $order_id);
    exit;
}

// 5. Načtení souborů (příloh)
$stmtFiles = $pdo->prepare("SELECT * FROM order_files WHERE order_id = ?");
$stmtFiles->execute([$order_id]);
$files = $stmtFiles->fetchAll();

// Pomocná funkce pro QR Platbu
function getQRPlatba($iban, $amount, $currency = 'CZK', $message = '') {
    $iban = str_replace(' ', '', $iban);
    $bankCode = substr($iban, 4, 4);
    $accountNumber = ltrim(substr($iban, 8), '0');
    $params = [
        'accountNumber' => $accountNumber,
        'bankCode' => $bankCode,
        'amount' => number_format((float)$amount, 2, '.', ''),
        'currency' => $currency,
        'message' => mb_substr($message, 0, 60),
        'size' => 250
    ];
    return "https://api.paylibo.com/paylibo/generator/czech/image?" . http_build_query($params);
}

$muj_iban = 'CZ6208000000003016704153';
$pure_iban = str_replace(' ', '', $muj_iban);
$bankCode = substr($pure_iban, 4, 4);
$accountNumber = ltrim(substr($pure_iban, 8), '0');
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zakázka #<?= $order['id'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 md:p-8">
    <div class="bg-white p-4 border-b md:hidden flex justify-between items-center">
        <a href="dashboard.php" class="text-blue-600 font-bold">← Zpět</a>
        <h1 class="font-bold text-gray-800">Detail #<?= $order['id'] ?></h1>
    </div>

    <div class="max-w-6xl mx-auto flex flex-col-reverse lg:grid lg:grid-cols-3 gap-4 md:gap-8">
        
        <div class="p-4 md:p-0 lg:col-span-1 space-y-4">
            <a href="dashboard.php" class="hidden md:inline-block text-blue-600 hover:underline mb-2">← Zpět na dashboard</a>
            
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                <h1 class="hidden md:block text-2xl font-bold mb-4">Zakázka #<?= $order['id'] ?></h1>
                
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-gray-500 text-[10px] uppercase font-bold tracking-wider">Téma</p>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($order['title']) ?></p>
                    </div>
                    <span class="uppercase font-bold text-[10px] px-2 py-1 rounded bg-blue-100 text-blue-800"><?= $order['status'] ?></span>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg text-sm text-gray-700 mb-6 border border-gray-100 italic">
                    <?= nl2br(htmlspecialchars($order['description'])) ?>
                </div>

                <?php if (!empty($files)): ?>
                <div class="mb-6">
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-3">Přílohy (<?= count($files) ?>):</p>
                    <div class="grid grid-cols-2 gap-2">
                        <?php foreach ($files as $file): ?>
                            <?php 
                                $ext = strtolower(pathinfo($file['file_path'], PATHINFO_EXTENSION));
                                $isImg = in_array($ext, ['jpg','jpeg','png','webp']); 
                            ?>
                            <a href="uploads/<?= $file['file_path'] ?>" target="_blank" class="block border rounded-lg overflow-hidden bg-white hover:ring-2 ring-blue-500 transition shadow-sm">
                                <?php if ($isImg): ?>
                                    <img src="uploads/<?= $file['file_path'] ?>" class="w-full h-20 object-cover">
                                <?php else: ?>
                                    <div class="h-20 flex flex-col items-center justify-center p-2 text-center bg-gray-50">
                                        <span class="text-xl">📄</span>
                                        <span class="text-[8px] font-bold text-gray-400 uppercase truncate w-full"><?= $ext ?></span>
                                    </div>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="flex justify-between items-center pt-4 border-t">
                    <span class="text-gray-500 text-sm">Cena:</span>
                    <span class="font-bold text-green-600 text-xl"><?= number_format($order['price'], 0, ',', ' ') ?> Kč</span>
                </div>
            </div>

            <?php if ($order['status'] === 'pending_payment' && $order['price'] > 0): ?>
            <div class="bg-white p-5 rounded-xl shadow-md border border-blue-200">
                <h3 class="font-bold mb-3 flex items-center gap-2 text-blue-600 uppercase text-xs"><span>💳</span> Platba</h3>
                <div class="bg-gray-50 p-3 rounded-lg mb-4 text-center">
                    <img src="<?= getQRPlatba($muj_iban, $order['price'], 'CZK', 'Platba #' . $order['id']) ?>" alt="QR" class="mx-auto w-40 h-40 border shadow-sm rounded">
                </div>
                <div class="text-[11px] space-y-2 bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <div class="flex justify-between"><span class="text-blue-700">Účet:</span><span class="font-mono font-bold"><?= $accountNumber ?> / <?= $bankCode ?></span></div>
                    <div class="flex justify-between"><span class="text-blue-700">Částka:</span><span class="font-bold"><?= number_format($order['price'], 2, ',', ' ') ?> Kč</span></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
            <div class="bg-yellow-50 p-5 rounded-xl border border-yellow-200 shadow-sm">
                <h3 class="font-bold mb-3 text-yellow-800 text-xs uppercase tracking-widest">Admin</h3>
                <form method="POST" class="space-y-3">
                    <select name="status" class="w-full border-gray-300 p-2 rounded-lg text-sm outline-none">
                        <option value="new" <?= $order['status']=='new'?'selected':'' ?>>Nový</option>
                        <option value="pending_payment" <?= $order['status']=='pending_payment'?'selected':'' ?>>Čeká na platbu</option>
                        <option value="paid" <?= $order['status']=='paid'?'selected':'' ?>>Zaplaceno</option>
                        <option value="in_progress" <?= $order['status']=='in_progress'?'selected':'' ?>>V práci</option>
                        <option value="finished" <?= $order['status']=='finished'?'selected':'' ?>>Hotovo</option>
                    </select>
                    <input type="number" name="price" value="<?= $order['price'] ?>" class="w-full border-gray-300 p-2 rounded-lg text-sm outline-none">
                    <button type="submit" name="update_order" class="w-full bg-yellow-600 text-white py-2 rounded-lg font-bold text-sm">Aktualizovat</button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-2 flex flex-col h-[75vh] md:h-[85vh] bg-white md:rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-blue-600 p-3 md:p-4 flex items-center justify-between text-white shadow-md z-10">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                    <span class="font-bold">Diskuze k zakázce</span>
                </div>
                <span class="text-[10px] opacity-80 uppercase font-medium tracking-widest">Live Chat</span>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50" id="chat-box">
                </div>

         <form id="chat-form" class="p-3 bg-white border-t flex items-end gap-2" enctype="multipart/form-data">
    <input type="file" id="chat-file" name="chat_file" class="hidden" onchange="previewFile()">
    
    <button type="button" onclick="document.getElementById('chat-file').click()" class="p-2 text-gray-400 hover:text-blue-600 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
    </button>

    <div class="flex-1 flex flex-col gap-1">
        <div id="file-preview" class="hidden text-[10px] text-blue-600 font-bold bg-blue-50 px-2 py-1 rounded flex justify-between">
            <span id="file-name"></span>
            <button type="button" onclick="resetChatFile()" class="text-red-500">✕</button>
        </div>
        <textarea id="msg-input" name="message" placeholder="Zpráva..." rows="1" class="w-full bg-gray-100 border-none rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 resize-none outline-none"></textarea>
    </div>

    <button type="submit" class="bg-blue-600 text-white p-2 rounded-xl hover:bg-blue-700 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
    </button>
</form>
        </div>
<script>
let lastMessageId = 0;
const orderId = <?= $order_id ?>;
const userId = <?= $user_id ?>;
const chatBox = document.getElementById('chat-box');

async function fetchMessages() {
    try {
        const response = await fetch(`fetch_messages.php?order_id=${orderId}&last_id=${lastMessageId}`);
        const newMessages = await response.json();

        if (newMessages.length > 0) {
            newMessages.forEach(msg => {
                appendMessage(msg);
                // Důležité: aktualizujeme lastMessageId na ID poslední zprávy
                lastMessageId = Math.max(lastMessageId, msg.id);
            });
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    } catch (err) { console.error("Chyba chatu:", err); }
}

function appendMessage(msg) {
    const isMe = (msg.sender_id == userId);
    const time = msg.sent_at ? msg.sent_at.substring(11, 16) : '--:--';
    
    let fileHtml = '';
    if (msg.file_path) {
        const ext = msg.file_path.split('.').pop().toLowerCase();
        const isImg = ['jpg', 'jpeg', 'png', 'webp'].includes(ext);
        
        if (isImg) {
            fileHtml = `<a href="uploads/${msg.file_path}" target="_blank"><img src="uploads/${msg.file_path}" class="rounded-lg mb-2 max-h-48 w-full object-cover"></a>`;
        } else {
            fileHtml = `<a href="uploads/${msg.file_path}" target="_blank" class="block bg-black/10 p-2 rounded-lg mb-2 text-[11px] underline font-bold">📎 Soubor .${ext}</a>`;
        }
    }

    const msgHtml = `
        <div class="flex ${isMe ? 'justify-end' : 'justify-start'}">
            <div class="max-w-[85%] md:max-w-[70%]">
                <div class="p-3 shadow-sm text-sm ${isMe ? 'bg-blue-600 text-white rounded-2xl rounded-tr-none' : 'bg-white border rounded-2xl rounded-tl-none text-gray-800'}">
                    ${fileHtml}
                    ${msg.message_text ? msg.message_text.replace(/\n/g, '<br>') : ''}
                </div>
                <p class="text-[9px] text-gray-400 mt-1 ${isMe ? 'text-right' : 'text-left'}">${time}</p>
            </div>
        </div>`;
    chatBox.insertAdjacentHTML('beforeend', msgHtml);
}

document.getElementById('chat-form').onsubmit = async function(e) {
    e.preventDefault();
    
    const msgInput = document.getElementById('msg-input');
    const fileInput = document.getElementById('chat-file');
    
    // Pokud je obojí prázdné, nic neposíláme
    if (msgInput.value.trim() === '' && fileInput.files.length === 0) return;

    const formData = new FormData(this); // 'this' odkazuje na form
    formData.append('ajax', '1');

    // Vyčistíme UI hned po odeslání
    msgInput.value = '';
    msgInput.style.height = 'auto';
    resetChatFile();

    try {
        await fetch(`order_detail.php?id=${orderId}`, { 
            method: 'POST', 
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        fetchMessages(); // Okamžitě načteme, co jsme poslali
    } catch (err) {
        console.error("Chyba při odesílání:", err);
    }
};

function previewFile() {
    const file = document.getElementById('chat-file').files[0];
    if (file) {
        document.getElementById('file-name').innerText = "📎 " + file.name;
        document.getElementById('file-preview').classList.remove('hidden');
    }
}

function resetChatFile() {
    document.getElementById('chat-file').value = '';
    document.getElementById('file-preview').classList.add('hidden');
}

// SPOUŠTĚČE
fetchMessages(); // Načte historii hned po otevření
setInterval(fetchMessages, 3000); // Kontrola nových zpráv každé 3 vteřiny
</script>
</body>
</html>