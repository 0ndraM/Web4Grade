<?php
$headerUser = null;
if (isset($_SESSION['user_id'])) {
    // Načtení dat aktuálního uživatele
    $stmtU = $pdo->prepare("SELECT avatar_path, username FROM users WHERE id = ?");
    $stmtU->execute([$_SESSION['user_id']]);
    $headerUser = $stmtU->fetch();
}
?>
<nav class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md shadow-sm border-b border-gray-100 dark:border-slate-800 sticky top-0 z-50 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        
        <a href="index.php" class="text-2xl font-black tracking-tighter hover:opacity-80 transition flex items-center gap-1">
            <span class="text-blue-600">Web4</span><span class="text-gray-900 dark:text-white">Grade</span>
        </a>
        
        <div class="flex items-center gap-2 md:gap-4">
           
            <?php if ($headerUser): ?>
                <div class="hidden md:flex items-center gap-6 mr-4 border-r border-gray-100 dark:border-slate-800 pr-6">
                    <a href="dashboard.php" class="text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-blue-600 transition">Moje zakázky</a>
                    <a href="index.php#jak-to-funguje" class="text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-blue-600 transition">Jak to funguje</a>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="account_settings.php" class="flex items-center gap-3 group">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-gray-900 dark:text-white leading-none group-hover:text-blue-600 transition">
                                <?= htmlspecialchars($headerUser['username']) ?>
                            </p>
                            <p class="text-[10px] text-blue-500 font-extrabold uppercase mt-1 tracking-wider">Můj profil</p>
                        </div>
                        
                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-slate-800 border-2 border-white dark:border-slate-700 shadow-sm ring-1 ring-gray-100 dark:ring-slate-800 overflow-hidden transform group-hover:scale-105 transition duration-200">
                            <?php if ($headerUser['avatar_path']): ?>
                                <img src="assets/pfp/<?= $headerUser['avatar_path'] ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-blue-600 text-white text-sm font-black">
                                    <?= strtoupper(substr($headerUser['username'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>

                    <a href="logout.php" class="ml-2 p-2.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all" title="Odhlásit se">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </a>
                </div>
            
            <?php else: ?>
                <div class="flex items-center gap-6">
                    <a href="index.php#jak-to-funguje" class="text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-blue-600 hidden sm:inline transition">Jak to funguje</a>
                    <a href="index.php#login" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-200 transition-all transform active:scale-95">
                        Přihlásit se
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    // Inicializace Dark Mode při načtení
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    function toggleDarkMode() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
    }
</script>