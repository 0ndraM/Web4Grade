<?php
session_start();
require_once 'config/db.php';

// Kontrola přihlášení
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$username = $_SESSION['username'];

// Řazení
$sort = $_GET['sort'] ?? 'default';
$orderBy = "unread_count DESC, o.created_at DESC";

if ($role === 'admin') {
    switch ($sort) {
        case 'date_desc': $orderBy = "o.created_at DESC"; break;
        case 'date_asc': $orderBy = "o.created_at ASC"; break;
        case 'price_desc': $orderBy = "o.price DESC"; break;
        case 'price_asc': $orderBy = "o.price ASC"; break;
        case 'status': $orderBy = "o.status ASC"; break;
    }
}

// Načtení objednávek podle role
if ($role === 'admin') {
    $stmt = $pdo->query("SELECT o.*, u.username as client_name,
        (SELECT COUNT(*) FROM messages m WHERE m.order_id = o.id AND m.sent_at > o.admin_last_read_at AND m.sender_id != $user_id) as unread_count
        FROM orders o 
        JOIN users u ON o.client_id = u.id 
        ORDER BY $orderBy");
} else {
    $stmt = $pdo->prepare("SELECT o.*,
        (SELECT COUNT(*) FROM messages m WHERE m.order_id = o.id AND m.sent_at > o.client_last_read_at AND m.sender_id != ?) as unread_count
        FROM orders o 
        WHERE o.client_id = ? 
        ORDER BY $orderBy");
    $stmt->execute([$user_id, $user_id]);
}
$orders = $stmt->fetchAll();

// Pomocná funkce pro barvy stavů
function getStatusBadge($status) {
    $colors = [
        'new' => 'bg-blue-100 text-blue-800',
        'pending_payment' => 'bg-yellow-100 text-yellow-800',
        'paid' => 'bg-green-100 text-green-800',
        'in_progress' => 'bg-purple-100 text-purple-800',
        'finished' => 'bg-gray-100 text-gray-800',
        'cancelled' => 'bg-red-100 text-red-800'
    ];
    return $colors[$status] ?? 'bg-gray-100 text-gray-800';
}

// Statistiky pro admina
$stats = ['new' => 0, 'active' => 0, 'finished' => 0];
if ($role === 'admin') {
    foreach ($orders as $o) {
        if ($o['status'] === 'new') $stats['new']++;
        elseif ($o['status'] === 'finished') $stats['finished']++;
        else $stats['active']++;
    }
}

// Načtení dat aktuálního uživatele pro horní lištu
$stmtU = $pdo->prepare("SELECT avatar_path FROM users WHERE id = ?");
$stmtU->execute([$user_id]);
$currentUser = $stmtU->fetch();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Objednávkový systém</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

 <?php include 'includes/header.php'; ?>

    <main class="max-w-7xl mx-auto px-4">
         <h1 class="text-lg md:text-xl font-bold text-gray-800 tracking-tight"><?= $role === 'admin' ? 'Všechny objednávky' : 'Moje objednávky' ?></h1>
        <?php if ($role === 'admin'): ?>
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 text-center">
                <p class="text-blue-600 text-xs font-bold uppercase tracking-wider">Nové</p>
                <p class="text-3xl font-black text-blue-800"><?= $stats['new'] ?></p>
            </div>
            <div class="bg-purple-50 p-4 rounded-xl border border-purple-100 text-center">
                <p class="text-purple-600 text-xs font-bold uppercase tracking-wider">V procesu</p>
                <p class="text-3xl font-black text-purple-800"><?= $stats['active'] ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-center">
                <p class="text-green-600 text-xs font-bold uppercase tracking-wider">Hotovo</p>
                <p class="text-3xl font-black text-green-800"><?= $stats['finished'] ?></p>
            </div>
        </div>
        <?php endif; ?>

<div class="max-w-7xl mx-auto px-4">
    
    <?php if ($role === 'admin'): ?>
    <div class="flex justify-end mb-4">
        <form method="GET" class="flex items-center gap-2">
            <label class="text-xs font-bold text-gray-500 uppercase">Řadit:</label>
            <select name="sort" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-lg p-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="default" <?= $sort=='default'?'selected':'' ?>>⚡ Dle aktivity</option>
                <option value="date_desc" <?= $sort=='date_desc'?'selected':'' ?>>📅 Nejnovější</option>
                <option value="date_asc" <?= $sort=='date_asc'?'selected':'' ?>>📅 Nejstarší</option>
                <option value="price_desc" <?= $sort=='price_desc'?'selected':'' ?>>💰 Nejdražší</option>
                <option value="status" <?= $sort=='status'?'selected':'' ?>>📌 Dle stavu</option>
            </select>
        </form>
    </div>
    <?php endif; ?>

    <div class="hidden md:block bg-white shadow-md rounded-xl overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50 border-b">
                <tr class="text-gray-500 text-xs uppercase font-bold">
                    <th class="py-4 px-6 text-left">ID</th>
                    <?php if ($role === 'admin'): ?> <th class="py-4 px-6 text-left">Klient</th> <?php endif; ?>
                    <th class="py-4 px-6 text-left">Název / Téma</th>
                    <th class="py-4 px-6 text-left">Stav</th>
                    <th class="py-4 px-6 text-left">Cena</th>
                    <th class="py-4 px-6 text-center">Akce</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($orders as $order): ?>
                <tr class="hover:bg-blue-50/50 transition">
                    <td class="py-4 px-6 text-sm font-medium text-gray-400">#<?= $order['id'] ?></td>
                    <?php if ($role === 'admin'): ?>
                        <td class="py-4 px-6 text-sm font-bold text-gray-700"><?= htmlspecialchars($order['client_name']) ?></td>
                    <?php endif; ?>
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($order['title']) ?></span>
                            <?php if ($order['unread_count'] > 0): ?>
                                <span class="h-2 w-2 rounded-full bg-red-500 animate-pulse"></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <span class="<?= getStatusBadge($order['status']) ?> px-3 py-1 rounded-full text-[10px] font-bold uppercase">
                            <?= $order['status'] ?>
                        </span>
                    </td>
                    <td class="py-4 px-6 text-sm font-bold text-gray-900"><?= number_format($order['price'], 0, ',', ' ') ?> Kč</td>
                    <td class="py-4 px-6 text-center">
                        <a href="order_detail.php?id=<?= $order['id'] ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 transition shadow-sm">Detail</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="md:hidden space-y-4">
        <?php foreach ($orders as $order): ?>
        <a href="order_detail.php?id=<?= $order['id'] ?>" class="block bg-white p-5 rounded-2xl shadow-sm border border-gray-200 active:scale-[0.98] transition">
            <div class="flex justify-between items-start mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-400">#<?= $order['id'] ?></span>
                    <span class="<?= getStatusBadge($order['status']) ?> px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-wider">
                        <?= $order['status'] ?>
                    </span>
                    <?php if ($order['unread_count'] > 0): ?>
                        <span class="px-2 py-1 bg-red-100 text-red-600 rounded-md text-[9px] font-black uppercase">Nová zpráva</span>
                    <?php endif; ?>
                </div>
                <span class="text-blue-600 font-bold text-sm italic">Detail →</span>
            </div>
            
            <h3 class="text-lg font-bold text-gray-800 mb-1"><?= htmlspecialchars($order['title']) ?></h3>
            <?php if ($role === 'admin'): ?>
                <p class="text-xs text-gray-500 mb-3 italic font-medium">Klient: <?= htmlspecialchars($order['client_name']) ?></p>
            <?php endif; ?>
            
            <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-50">
                <span class="text-xs text-gray-400 uppercase font-bold tracking-widest">Cena</span>
                <span class="text-lg font-black text-gray-900"><?= number_format($order['price'], 0, ',', ' ') ?> Kč</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

</div>
</div>
    </main>

    <?php if ($role === 'client'): ?>
        <a href="create_order.php" title="Nová objednávka" class="fixed bottom-8 right-8 w-16 h-16 bg-blue-600 text-white rounded-full shadow-2xl flex items-center justify-center hover:bg-blue-700 hover:scale-110 transition-all z-50">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </a>
    <?php endif; ?>

</body>
</html>