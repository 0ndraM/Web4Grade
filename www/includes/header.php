<?php
$headerUser = null;
if (isset($_SESSION['user_id'])) {
    // Načtení dat aktuálního uživatele
    $stmtU = $pdo->prepare("SELECT avatar_path, username FROM users WHERE id = ?");
    $stmtU->execute([$_SESSION['user_id']]);
    $headerUser = $stmtU->fetch();
}
?>
<nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        
        <a href="index.php" class="text-2xl font-black text-blue-600 tracking-tight">WEB<span class="text-gray-800">MASTER</span></a>
        
        <div class="flex items-center gap-4">
            <?php if ($headerUser): ?>
            
            <a href="dashboard.php" class="text-gray-600 hover:text-blue-600 font-medium mr-2 hidden md:inline">Dashboard</a>
            
            <a href="account_settings.php" class="flex items-center gap-2 group">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold text-gray-800 leading-none"><?= htmlspecialchars($headerUser['username']) ?></p>
                    <p class="text-[9px] text-blue-500 font-bold uppercase mt-1">Můj profil</p>
                </div>
                <div class="w-9 h-9 rounded-full bg-gray-100 border-2 border-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
                    <?php if ($headerUser['avatar_path']): ?>
                        <img src="uploads/<?= $headerUser['avatar_path'] ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-blue-600 text-white text-xs font-bold">
                            <?= strtoupper(substr($headerUser['username'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </a>
            <a href="logout.php" class="p-2 text-gray-400 hover:text-red-500 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            </a>
            
            <?php else: ?>
                <div class="space-x-4">
                    <a href="index.php#vlastnosti" class="text-gray-600 hover:text-blue-600 hidden md:inline">Jak to funguje</a>
                    <a href="index.php#login" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition shadow-lg shadow-blue-200">Přihlásit se</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>