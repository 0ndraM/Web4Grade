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
   $stmtFiles = $pdo->prepare("SELECT * FROM order_files WHERE order_id = ?");
   $stmtFiles->execute([$order_id]);
   $files = $stmtFiles->fetchAll();
   
   if (!$order || ($role !== 'admin' && $order['client_id'] !== $user_id)) {
       die("Objednávka nenalezena nebo k ní nemáte přístup.");
   }
   
   // Aktualizace času přečtení
   if ($role === 'admin') {
    $stmt = $pdo->prepare("UPDATE orders SET admin_last_read_at = NOW() WHERE id = ?");
   } else {
    $stmt = $pdo->prepare("UPDATE orders SET client_last_read_at = NOW() WHERE id = ?");
   }
   $stmt->execute([$order_id]);
   // 2. Logika pro Admina: Aktualizace stavu a ceny
   if ($role === 'admin' && isset($_POST['update_order'])) {
       $new_status = $_POST['status'];
       $new_price = $_POST['price'];
       $stmt = $pdo->prepare("UPDATE orders SET status = ?, price = ? WHERE id = ?");
       $stmt->execute([$new_status, $new_price, $order_id]);
       header("Location: order_detail.php?id=" . $order_id);
       exit;
   }
   
   // 3. Logika pro Chat: Odeslání zprávy
   if (isset($_POST['send_message']) && !empty(trim($_POST['message']))) {
       $msg = trim($_POST['message']);
       $stmt = $pdo->prepare("INSERT INTO messages (order_id, sender_id, message_text) VALUES (?, ?, ?)");
       $stmt->execute([$order_id, $user_id, $msg]);
       header("Location: order_detail.php?id=" . $order_id);
       exit;
   }
   
   // 4. Načtení zpráv chatu
   $stmt = $pdo->prepare("SELECT m.*, u.username, u.role as sender_role FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.order_id = ? ORDER BY m.sent_at ASC");
   $stmt->execute([$order_id]);
   $messages = $stmt->fetchAll();
   
   // Funkce pro QR Platbu
   function getQRPlatba($iban, $amount, $currency = 'CZK', $message = '') {
       $iban = str_replace(' ', '', $iban);
       $bankCode = substr($iban, 4, 4);
       $accountNumber = ltrim(substr($iban, 8), '0');
       $amount = number_format((float)$amount, 2, '.', '');
       
       $params = [
           'accountNumber' => $accountNumber,
           'bankCode'      => $bankCode,
           'amount'        => $amount,
           'currency'      => $currency,
           'message'       => mb_substr($message, 0, 60),
           'size'          => 250
       ];
       return "https://api.paylibo.com/paylibo/generator/czech/image?" . http_build_query($params);
   }
   
   $muj_iban = 'CZ6208000000003016704153';
   
   // PŘÍPRAVA PROMĚNNÝCH PRO VÝPIS (Důležité umístit sem!)
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
                     <p class="text-gray-500 text-[10px] uppercase font-bold">Téma</p>
                     <p class="font-semibold text-gray-800"><?= htmlspecialchars($order['title']) ?></p>
                  </div>
                  <span class="uppercase font-bold text-[10px] px-2 py-1 rounded bg-blue-100 text-blue-800"><?= $order['status'] ?></span>
               </div>
               <div class="bg-gray-50 p-3 rounded-lg text-sm text-gray-700 mb-4 border border-gray-100">
                  <p class="font-bold mb-1 text-[10px] text-gray-400 uppercase">Zadání:</p>
                  <?= nl2br(htmlspecialchars($order['description'])) ?>
               </div>
               <?php if (!empty($files)): ?>
<div class="mb-6">
    <p class="text-[11px] font-bold text-gray-400 uppercase mb-2">Přílohy (<?= count($files) ?>):</p>
    <div class="flex flex-wrap gap-3">
        <?php foreach ($files as $file): ?>
            <?php 
                $ext = strtolower(pathinfo($file['file_path'], PATHINFO_EXTENSION));
                $isImg = in_array($ext, ['jpg','jpeg','png','webp']); 
            ?>
            <div class="w-24 md:w-32">
                <a href="uploads/<?= $file['file_path'] ?>" target="_blank" class="block border rounded-lg overflow-hidden bg-gray-50 hover:ring-2 ring-blue-500 transition shadow-sm">
                    <?php if ($isImg): ?>
                        <img src="uploads/<?= $file['file_path'] ?>" class="w-full h-24 object-cover">
                    <?php else: ?>
                        <div class="h-24 flex flex-col items-center justify-center p-2 text-center">
                            <span class="text-2xl">📄</span>
                            <span class="text-[9px] font-bold text-gray-500 truncate w-full uppercase"><?= $ext ?></span>
                        </div>
                    <?php endif; ?>
                </a>
                <p class="text-[10px] text-gray-400 mt-1 truncate w-full"><?= htmlspecialchars($file['file_name']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
               <div class="flex justify-between items-center pt-4 border-t">
                  <span class="text-gray-500 text-sm">Cena:</span>
                  <span class="font-bold text-green-600 text-xl"><?= number_format($order['price'], 0, ',', ' ') ?> Kč</span>
               </div>
            </div>
            <?php if ($role === 'admin'): ?>
            <div class="bg-yellow-50 p-5 rounded-xl border border-yellow-200 shadow-sm">
               <h3 class="font-bold mb-3 text-yellow-800 text-xs uppercase">Admin nastavení</h3>
               <form method="POST" class="space-y-3">
                  <select name="status" class="w-full border-gray-300 p-2 rounded-lg text-sm">
                     <option value="new" <?= $order['status']=='new'?'selected':'' ?>>Nový</option>
                     <option value="pending_payment" <?= $order['status']=='pending_payment'?'selected':'' ?>>Čeká na platbu</option>
                     <option value="paid" <?= $order['status']=='paid'?'selected':'' ?>>Zaplaceno</option>
                     <option value="in_progress" <?= $order['status']=='in_progress'?'selected':'' ?>>V práci</option>
                     <option value="finished" <?= $order['status']=='finished'?'selected':'' ?>>Hotovo</option>
                  </select>
                  <input type="number" name="price" value="<?= $order['price'] ?>" class="w-full border-gray-300 p-2 rounded-lg text-sm">
                  <button type="submit" name="update_order" class="w-full bg-yellow-600 text-white py-2 rounded-lg font-bold text-sm">Uložit</button>
               </form>
            </div>
            <?php endif; ?>
            <?php if ($order['status'] === 'pending_payment' && $order['price'] > 0): ?>
            <div class="bg-white p-5 rounded-xl shadow-md border border-blue-200">
               <h3 class="font-bold mb-3 flex items-center gap-2 text-blue-600 uppercase text-sm tracking-wide">
                  <span>💳</span> Podklady k platbě
               </h3>
               <div class="bg-gray-50 p-4 rounded-lg mb-4 text-center">
                  <img src="<?= getQRPlatba($muj_iban, $order['price'], 'CZK', 'Platba #' . $order['id']) ?>" 
                     alt="QR Platba" class="mx-auto w-40 h-40 border-2 border-white shadow-sm rounded">
               </div>
               <div class="text-xs space-y-2 bg-blue-50 p-4 rounded-lg border border-blue-100">
                  <div class="flex justify-between items-center border-b border-blue-200 pb-1">
                     <span class="text-blue-700">Číslo účtu:</span>
                     <span class="font-mono font-bold text-gray-900"><?= $accountNumber ?> / <?= $bankCode ?></span>
                  </div>
                  <div class="flex justify-between items-center border-b border-blue-200 pb-1">
                     <span class="text-blue-700">Částka:</span>
                     <span class="font-bold text-gray-900"><?= number_format($order['price'], 2, ',', ' ') ?> Kč</span>
                  </div>
                  <div class="flex justify-between items-center">
                     <span class="text-blue-700">Zpráva:</span>
                     <span class="font-bold text-gray-900 text-[10px]">Zakázka #<?= $order['id'] ?></span>
                  </div>
               </div>
            </div>
            <?php endif; ?>
         </div>
         <div class="lg:col-span-2 flex flex-col h-[70vh] md:h-[80vh] bg-white md:rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-blue-600 p-3 md:p-4 flex items-center justify-between text-white shadow-md z-10">
               <div class="flex items-center gap-2">
                  <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                  <span class="font-bold">Diskuze k zakázce</span>
               </div>
               <span class="text-[10px] opacity-80 uppercase font-medium tracking-widest">Live</span>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50" id="chat-box">
               <?php if (empty($messages)): ?>
               <div class="text-center text-gray-400 mt-10 italic text-sm">Zatím žádné zprávy...</div>
               <?php endif; ?>
               <?php foreach ($messages as $m): ?>
               <?php $isMe = ($m['sender_id'] == $user_id); ?>
               <div class="flex <?= $isMe ? 'justify-end' : 'justify-start' ?>">
                  <div class="max-w-[85%] md:max-w-[70%]">
                     <div class="p-3 shadow-sm text-sm <?= $isMe ? 'bg-blue-600 text-white rounded-2xl rounded-tr-none' : 'bg-white border rounded-2xl rounded-tl-none' ?>">
                        <?= nl2br(htmlspecialchars($m['message_text'])) ?>
                     </div>
                     <p class="text-[9px] text-gray-400 mt-1 <?= $isMe ? 'text-right' : 'text-left' ?>">
                        <?= date('H:i', strtotime($m['sent_at'])) ?>
                     </p>
                  </div>
               </div>
               <?php endforeach; ?>
            </div>
            <form method="POST" class="p-3 bg-white border-t flex items-end gap-2">
               <textarea name="message" placeholder="Napište zprávu..." rows="1" 
                  class="flex-1 bg-gray-100 border-none rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 resize-none outline-none"
                  oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'" required></textarea>
               <button type="submit" name="send_message" class="bg-blue-600 text-white p-2 rounded-xl hover:bg-blue-700 transition">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                  </svg>
               </button>
            </form>
         </div>
      </div>
      <script>
         const chatBox = document.getElementById('chat-box');
         chatBox.scrollTop = chatBox.scrollHeight;
         document.querySelector('textarea').addEventListener('keydown', function(e) {
             if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); this.form.submit(); }
         });
      </script>
   </body>
</html>