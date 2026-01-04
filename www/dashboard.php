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

// Načtení objednávek podle role
if ($role === 'admin') {
    $stmt = $pdo->query("SELECT o.*, u.username as client_name,
        (SELECT COUNT(*) FROM messages m WHERE m.order_id = o.id AND m.sent_at > o.admin_last_read_at AND m.sender_id != $user_id) as unread_count
        FROM orders o 
        JOIN users u ON o.client_id = u.id 
        ORDER BY unread_count DESC, o.created_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT o.*,
        (SELECT COUNT(*) FROM messages m WHERE m.order_id = o.id AND m.sent_at > o.client_last_read_at AND m.sender_id != ?) as unread_count
        FROM orders o 
        WHERE o.client_id = ? 
        ORDER BY unread_count DESC, o.created_at DESC");
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

<nav class="bg-white shadow-sm mb-4 md:mb-8">
    <div class="max-w-7xl mx-auto px-4 py-3 md:py-4 flex justify-between items-center">
        <h1 class="text-lg md:text-xl font-bold text-gray-800">Moje Zakázky</h1>
        <div class="flex items-center gap-4">
            <a href="account_settings.php" class="text-gray-500 hover:text-blue-600 transition" title="Nastavení účtu">
                ⚙️ <span class="hidden md:inline ml-1">Nastavení</span>
            </a>
            <a href="logout.php" class="bg-red-50 text-red-500 px-4 py-2 rounded-lg font-bold hover:bg-red-100 transition">Odhlásit</a>
        </div>
    </div>
</nav>

    <main class="max-w-7xl mx-auto px-4">
        
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">
                <?= $role === 'admin' ? 'Všechny objednávky' : 'Moje objednávky' ?>
            </h2>
            <?php if ($role === 'client'): ?>
                <a href="create_order.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 shadow-md transition">
                    + Nová objednávka
                </a>
            <?php endif; ?>
        </div>
<div class="max-w-7xl mx-auto px-4">
    
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

</body>
</html>