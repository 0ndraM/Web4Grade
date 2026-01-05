<?php
session_start();
require_once 'config/db.php';

// Kontrola oprávnění (pouze admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Načtení logů (posledních 200 záznamů)
$stmt = $pdo->query("SELECT * FROM acces_logy ORDER BY id DESC LIMIT 200");
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web4Grade | Systémové logy</title>
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
        
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Systémové logy</h1>
                <p class="text-gray-400 dark:text-slate-500 text-sm mt-1 font-medium">Přehled přístupů a akcí v systému</p>
            </div>
            <a href="dashboard.php" class="bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-400 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-slate-700 transition">
                Zpět na dashboard
            </a>
        </div>

        <div class="bg-white dark:bg-slate-900 shadow-xl shadow-blue-900/5 rounded-[2rem] overflow-hidden border border-gray-100 dark:border-slate-800">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-slate-800/50 text-gray-400 dark:text-slate-500 text-[10px] uppercase font-black tracking-widest border-b dark:border-slate-800">
                            <th class="py-5 px-6 text-left w-20">ID</th>
                            <th class="py-5 px-6 text-left w-48">Autor</th>
                            <th class="py-5 px-6 text-left">Akce</th>
                            <th class="py-5 px-6 text-right w-48">Čas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-slate-800">
                        <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition">
                            <td class="py-4 px-6 text-xs font-bold text-gray-300 dark:text-slate-600 tracking-tighter">#<?= $log['id'] ?></td>
                            <td class="py-4 px-6 text-sm font-bold text-blue-600 dark:text-blue-400">
                                <?= htmlspecialchars($log['autor']) ?>
                            </td>
                            <td class="py-4 px-6 text-sm font-medium text-gray-700 dark:text-slate-300">
                                <?= htmlspecialchars($log['akce']) ?>
                                <?php
                                if (preg_match('/IP: ([0-9\.]+)/', $log['akce'], $matches)) {
                                    $ip = $matches[1];
                                    $file = __DIR__ . '/includes/blocked_ips.txt';
                                    $blocked = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
                                    $blocked = array_map('trim', $blocked);
                                    if (in_array($ip, $blocked)) {
                                        echo " <a href='includes/odblokuj_ip.php?ip=" . urlencode($ip) . "' onclick=\"return confirm('Odblokovat IP $ip?')\" class='ml-2 inline-flex items-center justify-center px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-green-200 dark:hover:bg-green-900/50 transition'>Odblokovat</a>";
                                    } else {
                                        echo " <a href='includes/blokuj_ip.php?ip=" . urlencode($ip) . "' onclick=\"return confirm('Zablokovat IP $ip?')\" class='ml-2 inline-flex items-center justify-center px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-red-200 dark:hover:bg-red-900/50 transition'>Blokovat</a>";
                                    }
                                }
                                ?>
                            </td>
                            <td class="py-4 px-6 text-right text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">
                                <?= isset($log['cas']) ? date('d.m.Y H:i', strtotime($log['cas'])) : (isset($log['created_at']) ? date('d.m.Y H:i', strtotime($log['created_at'])) : '-') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="4" class="py-12 text-center text-gray-400 dark:text-slate-600 text-sm font-medium">
                                Žádné záznamy k zobrazení.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>