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
        'new' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'pending_payment' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        'paid' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'in_progress' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        'finished' => 'bg-gray-100 text-gray-700 dark:bg-slate-800 dark:text-slate-400',
        'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
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
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web4Grade | Dashboard</title>
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
<body class="bg-gray-50 dark:bg-slate-950 flex flex-col min-h-screen font-sans transition-colors duration-300">

    <?php include 'includes/header.php'; ?>

    <main class="max-w-7xl mx-auto px-6 w-full flex-grow py-12">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                <?= $role === 'admin' ? 'Správa projektů' : 'Moje zakázky' ?>
            </h1>
            
            <?php if ($role === 'admin'): ?>
            <form method="GET" class="flex items-center gap-3">
                <label class="text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest">Řadit dle:</label>
                <select name="sort" onchange="this.form.submit()" class="text-sm bg-white dark:bg-slate-900 text-gray-700 dark:text-slate-300 border border-gray-100 dark:border-slate-800 rounded-xl p-2.5 shadow-sm outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <option value="default" <?= $sort=='default'?'selected':'' ?>>⚡ Aktivity</option>
                    <option value="date_desc" <?= $sort=='date_desc'?'selected':'' ?>>📅 Nejnovější</option>
                    <option value="price_desc" <?= $sort=='price_desc'?'selected':'' ?>>💰 Ceny</option>
                    <option value="status" <?= $sort=='status'?'selected':'' ?>>📌 Stavu</option>
                </select>
            </form>
            <?php endif; ?>
        </div>

        <?php if ($role === 'admin'): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-blue-50 dark:bg-blue-900/10 p-6 rounded-[2rem] border border-blue-100 dark:border-blue-900/20 shadow-sm transition-all">
                <p class="text-blue-600 dark:text-blue-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2">Nové poptávky</p>
                <p class="text-4xl font-black text-blue-800 dark:text-blue-300"><?= $stats['new'] ?></p>
            </div>
            <div class="bg-purple-50 dark:bg-purple-900/10 p-6 rounded-[2rem] border border-purple-100 dark:border-purple-900/20 shadow-sm transition-all">
                <p class="text-purple-600 dark:text-purple-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2">Rozpracováno</p>
                <p class="text-4xl font-black text-purple-800 dark:text-purple-300"><?= $stats['active'] ?></p>
            </div>
            <div class="bg-green-50 dark:bg-green-900/10 p-6 rounded-[2rem] border border-green-100 dark:border-green-900/20 shadow-sm transition-all">
                <p class="text-green-600 dark:text-green-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2">Dokončeno</p>
                <p class="text-4xl font-black text-green-800 dark:text-green-300"><?= $stats['finished'] ?></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="hidden md:block bg-white dark:bg-slate-900 shadow-xl shadow-blue-900/5 rounded-[2rem] overflow-hidden border border-gray-100 dark:border-slate-800">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-slate-800/50 text-gray-400 dark:text-slate-500 text-[10px] uppercase font-black tracking-widest border-b dark:border-slate-800">
                        <th class="py-5 px-8 text-left uppercase tracking-widest">ID</th>
                        <?php if ($role === 'admin'): ?> <th class="py-5 px-8 text-left">Klient</th> <?php endif; ?>
                        <th class="py-5 px-8 text-left">Projekt / Téma</th>
                        <th class="py-5 px-8 text-left">Stav</th>
                        <th class="py-5 px-8 text-left text-right">Cena</th>
                        <th class="py-5 px-8 text-center">Akce</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-slate-800">
                    <?php foreach ($orders as $order): ?>
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition group">
                        <td class="py-5 px-8 text-xs font-bold text-gray-300 dark:text-slate-600 tracking-tighter">#<?= $order['id'] ?></td>
                        <?php if ($role === 'admin'): ?>
                            <td class="py-5 px-8 text-sm font-black text-gray-700 dark:text-slate-300"><?= htmlspecialchars($order['client_name']) ?></td>
                        <?php endif; ?>
                        <td class="py-5 px-8">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-gray-800 dark:text-slate-200 group-hover:text-blue-600 transition"><?= htmlspecialchars($order['title']) ?></span>
                                <?php if ($order['unread_count'] > 0): ?>
                                    <span class="flex h-2 w-2 rounded-full bg-red-500 animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.5)]"></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="py-5 px-8">
                            <span class="<?= getStatusBadge($order['status']) ?> px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest">
                                <?= str_replace('_', ' ', $order['status']) ?>
                            </span>
                        </td>
                        <td class="py-5 px-8 text-right text-sm font-black text-gray-900 dark:text-white">
                            <?= number_format($order['price'], 0, ',', ' ') ?> Kč
                        </td>
                        <td class="py-5 px-8 text-center">
                            <a href="order_detail.php?id=<?= $order['id'] ?>" class="inline-block bg-blue-600 text-white px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-blue-700 transition shadow-lg shadow-blue-200 dark:shadow-none">Detail</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-4">
            <?php foreach ($orders as $order): ?>
            <a href="order_detail.php?id=<?= $order['id'] ?>" class="block bg-white dark:bg-slate-900 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 active:scale-[0.98] transition">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black text-gray-300 dark:text-slate-600 tracking-widest">#<?= $order['id'] ?></span>
                        <span class="<?= getStatusBadge($order['status']) ?> px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-wider">
                            <?= str_replace('_', ' ', $order['status']) ?>
                        </span>
                    </div>
                    <?php if ($order['unread_count'] > 0): ?>
                        <span class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg text-[8px] font-black uppercase">Nová zpráva</span>
                    <?php endif; ?>
                </div>
                
                <h3 class="text-lg font-black text-gray-900 dark:text-white mb-1"><?= htmlspecialchars($order['title']) ?></h3>
                <?php if ($role === 'admin'): ?>
                    <p class="text-xs text-gray-500 dark:text-slate-500 font-bold italic mb-4">Klient: <?= htmlspecialchars($order['client_name']) ?></p>
                <?php endif; ?>
                
                <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-50 dark:border-slate-800">
                    <span class="text-[9px] text-gray-400 dark:text-slate-600 uppercase font-black tracking-[0.2em]">Cena</span>
                    <span class="text-xl font-black text-gray-900 dark:text-white"><?= number_format($order['price'], 0, ',', ' ') ?> Kč</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

    </main>

    <?php if ($role === 'client'): ?>
        <a href="create_order.php" title="Nová objednávka" class="fixed bottom-10 right-10 w-16 h-16 bg-blue-600 text-white rounded-2xl shadow-2xl flex items-center justify-center hover:bg-blue-700 hover:scale-110 transition-all z-50">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </a>
    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>
</body>
</html>