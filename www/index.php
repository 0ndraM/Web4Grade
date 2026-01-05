<?php
session_start();
require_once 'config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Neplatné přihlašovací údaje. Zkuste to prosím znovu.";
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weby pro studenty | Profesionální řešení školních projektů</title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hero-gradient { background: linear-gradient(135deg, #0f172a 0%, #2563eb 100%); }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-900">

    <?php include 'includes/header.php'; ?>

    <header class="hero-gradient text-white py-24 px-6">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between">
            <div class="md:w-3/5 mb-12 md:mb-0">
                <span class="bg-blue-500/20 text-blue-200 px-4 py-1 rounded-full text-sm font-semibold mb-6 inline-block border border-blue-400/30">
                    Projekty, které ti zajistí jedničku
                </span>
                <h1 class="text-5xl md:text-7xl font-extrabold leading-tight mb-6">
                    Máš zadání na web? <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-200 to-white">Nech to na mně.</span>
                </h1>
                <p class="text-xl text-blue-100 mb-10 max-w-xl leading-relaxed">
                    Soustřeď se na to důležité, zatímco já postavím tvůj školní web. Kompletní kód, moderní design a případná dokumentace v ceně.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="register.php" class="bg-white text-blue-700 px-10 py-4 rounded-xl text-lg font-bold hover:bg-blue-50 transition shadow-xl">Chci zadat projekt</a>
                    <a href="#jak-to-funguje" class="border border-white/30 bg-white/5 px-10 py-4 rounded-xl text-lg font-bold hover:bg-white/10 backdrop-blur-md transition">Jak to funguje?</a>
                </div>
            </div>
            <div class="md:w-2/5 flex justify-center relative">
                <div class="absolute -inset-4 bg-blue-500/20 blur-3xl rounded-full"></div>
                <div class="relative bg-white/10 p-2 rounded-3xl backdrop-blur-sm border border-white/20 shadow-2xl">
                    <img src="https://img.freepik.com/free-vector/website-setup-concept-illustration_114360-4256.jpg" alt="Web development" class="rounded-2xl max-w-full h-auto shadow-inner">
                </div>
            </div>
        </div>
    </header>

<section id="vlastnosti" class="py-20 max-w-7xl mx-auto px-6">
    <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            </div>
            <h3 class="text-xl font-bold mb-3 text-gray-800">Integrovaný Chat</h3>
            <p class="text-gray-600 text-sm leading-relaxed">Žádné Instagram DMka. Všechno řešíme přímo u tvé zakázky na jednom místě.</p>
        </div>
        
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            </div>
            <h3 class="text-xl font-bold mb-3 text-gray-800">Bezpečná platba</h3>
            <p class="text-gray-600 text-sm leading-relaxed">Platíš až ve chvíli, kdy vidíš funkční demo na GitHubu. Stačí naskenovat QR kód.</p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition">
            <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <h3 class="text-xl font-bold mb-3 text-gray-800">Dodržení termínu</h3>
            <p class="text-gray-600 text-sm leading-relaxed">Sleduješ progress v reálném čase. Vždy víš, v jaké fázi se tvůj projekt nachází.</p>
        </div>
    </div>
</section>

<section id="jak-to-funguje" class="py-24 bg-white px-6">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-extrabold text-gray-900 mb-4">Jak spolupráce probíhá?</h2>
            <p class="text-gray-500 text-lg mx-auto max-w-2xl">Celý proces od tvého zadání až po ostrý web pod tvojí správou.</p>
            <div class="h-1.5 w-24 bg-blue-600 mx-auto mt-6 rounded-full"></div>
        </div>

        <div class="grid md:grid-cols-5 gap-6 relative">
            <div class="flex flex-col items-center text-center p-4 group">
                <div class="w-14 h-14 bg-gray-50 text-gray-600 rounded-2xl flex items-center justify-center mb-6 shadow-sm border border-gray-100 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">1. Registrace</h3>
                <p class="text-gray-500 text-sm">Vytvoříš si účet, abychom mohli komunikovat v chatu.</p>
            </div>

            <div class="flex flex-col items-center text-center p-4 group">
                <div class="w-14 h-14 bg-gray-50 text-gray-600 rounded-2xl flex items-center justify-center mb-6 shadow-sm border border-gray-100 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">2. Zadání</h3>
                <p class="text-gray-500 text-sm">Nahraješ požadavky. Po schválení se pustím do práce.</p>
            </div>

            <div class="flex flex-col items-center text-center p-4 group">
                <div class="w-14 h-14 bg-gray-50 text-gray-600 rounded-2xl flex items-center justify-center mb-6 shadow-sm border border-gray-100 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">3. Testovací verze</h3>
                <p class="text-gray-500 text-sm">Web nahraji na link, kde si můžeš všechno proklikat.</p>
            </div>

            <div class="flex flex-col items-center text-center p-4 group">
                <div class="w-14 h-14 bg-gray-50 text-gray-600 rounded-2xl flex items-center justify-center mb-6 shadow-sm border border-gray-100 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">4. Platba</h3>
                <p class="text-gray-500 text-sm">Pokud web odpovídá zadání, zaplatíš přes QR kód.</p>
            </div>

            <div class="flex flex-col items-center text-center p-4 group">
                <div class="w-14 h-14 bg-blue-600 text-white rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-blue-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">5. Předání</h3>
                <p class="text-gray-500 text-sm">Web nasadím na tvůj hosting a projekt je hotový.</p>
            </div>
        </div>
    </div>
</section>

    <section id="login" class="bg-gray-100 py-24 px-6 border-y border-gray-200">
        <div class="max-w-md mx-auto">
            <div class="bg-white p-8 md:p-12 rounded-[2rem] shadow-2xl border border-white">
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="text-center">
                        <div class="w-20 h-20 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">👋</div>
                        <h2 class="text-3xl font-bold mb-2 text-gray-800 text-center">Rádi tě vidíme!</h2>
                        <p class="text-center text-gray-500 mb-10 text-lg">Vítej zpět, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>.</p>
                        <a href="dashboard.php" class="block text-center w-full bg-blue-600 text-white font-bold py-5 rounded-2xl hover:bg-blue-700 transition shadow-xl shadow-blue-200 transform hover:-translate-y-1">
                            Vstoupit do dashboardu
                        </a>
                    </div>
                <?php else: ?>
                    <h2 class="text-3xl font-bold mb-2 text-center text-gray-800">Vítej zpět</h2>
                    <p class="text-center text-gray-500 mb-10">Pokračuj do svého klientského centra</p>

                    <?php if ($error): ?>
                        <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-8 text-sm font-medium border border-red-100 flex items-center">
                            <span class="mr-3 text-lg">⚠️</span> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2 ml-1 italic text-xs uppercase tracking-wider">Uživatelské jméno</label>
                            <input type="text" name="username" placeholder="např. karel123" class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition" required>
                        </div>
                        <div class="mb-10">
                            <label class="block text-gray-700 text-sm font-bold mb-2 ml-1 italic text-xs uppercase tracking-wider">Heslo</label>
                            <input type="password" name="password" placeholder="••••••••" class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition" required>
                        </div>
                        <button type="submit" name="login" class="w-full bg-blue-600 text-white font-bold py-5 rounded-2xl hover:bg-blue-700 transition shadow-xl shadow-blue-200 transform hover:-translate-y-1">
                            Přihlásit se k projektům
                        </button>
                    </form>
                    <p class="mt-10 text-center text-gray-600">
                        Ještě nemáš účet? <a href="register.php" class="text-blue-600 font-bold hover:underline decoration-2">Založ si ho zde</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
                    </section>
   <section id="faq" class="py-24 bg-white px-6">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">Často kladené otázky</h2>
            <p class="text-gray-500 text-lg">Vše, co potřebuješ vědět o WebGrade.</p>
            <div class="h-1.5 w-16 bg-blue-600 mx-auto mt-6 rounded-full"></div>
        </div>

        <div class="space-y-4">
            <div class="faq-item border border-gray-100 rounded-2xl overflow-hidden transition-all duration-300 hover:border-blue-200">
                <button class="faq-question w-full flex items-center justify-between p-6 text-left bg-gray-50/50 hover:bg-white transition-colors">
                    <span class="font-bold text-gray-900">Jak probíhá platba a je to bezpečné?</span>
                    <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div class="faq-answer hidden p-6 pt-0 bg-white text-gray-600 leading-relaxed text-sm">
                    Platba probíhá až ve chvíli, kdy ti nasdílím <strong>testovací verzi webu</strong>. Ty si vše proklikáš, a pokud web odpovídá zadání, zaplatíš pomocí QR kódu. Teprve poté ti nahraji web na hosting a předám zdrojové kódy. Neriskuješ tedy, že zaplatíš za něco, co nefunguje.
                </div>
            </div>

            <div class="faq-item border border-gray-100 rounded-2xl overflow-hidden transition-all duration-300 hover:border-blue-200">
                <button class="faq-question w-full flex items-center justify-between p-6 text-left bg-gray-50/50 hover:bg-white transition-colors">
                    <span class="font-bold text-gray-900">Co když budu chtít na webu něco změnit?</span>
                    <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div class="faq-answer hidden p-6 pt-0 bg-white text-gray-600 leading-relaxed text-sm">
                    V rámci testovací verze máš nárok na drobné úpravy, aby web přesně seděl na tvoje zadání. Vše řešíme rychle přímo v našem <strong>integrovaném chatu</strong> v dashboardu, takže o každé změně hned víš.
                </div>
            </div>

            <div class="faq-item border border-gray-100 rounded-2xl overflow-hidden transition-all duration-300 hover:border-blue-200">
                <button class="faq-question w-full flex items-center justify-between p-6 text-left bg-gray-50/50 hover:bg-white transition-colors">
                    <span class="font-bold text-gray-900">Pomůžeš mi i s nahráním na hosting?</span>
                    <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div class="faq-answer hidden p-6 pt-0 bg-white text-gray-600 leading-relaxed text-sm">
                    Ano, to je poslední krok naší spolupráce. Jakmile je web zaplacen, postarám se o jeho <strong>zprovoznění na tvém hostingu</strong>, aby byl dostupný na tvojí adrese a připraven k odevzdání.
                </div>
            </div>

            <div class="faq-item border border-gray-100 rounded-2xl overflow-hidden transition-all duration-300 hover:border-blue-200">
                <button class="faq-question w-full flex items-center justify-between p-6 text-left bg-gray-50/50 hover:bg-white transition-colors">
                    <span class="font-bold text-gray-900">Jak rychle bude web hotový?</span>
                    <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div class="faq-answer hidden p-6 pt-0 bg-white text-gray-600 leading-relaxed text-sm">
                    Vše záleží na složitosti zadání. Standardní školní weby obvykle zvládám dodat do <strong>3–5 pracovních dnů</strong>. Přesný termín ti ale potvrdím hned po schválení tvého zadání v systému.
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.querySelectorAll('.faq-question').forEach(button => {
        button.addEventListener('click', () => {
            const answer = button.nextElementSibling;
            const icon = button.querySelector('svg');
            
            // Zavření ostatních otevřených otázek (volitelné)
            
            document.querySelectorAll('.faq-answer').forEach(el => {
                if (el !== answer) el.classList.add('hidden');
            });
            document.querySelectorAll('.faq-question svg').forEach(svg => {
                if (svg !== icon) svg.classList.remove('rotate-180');
            });
           

            // Toggle aktuální otázky
            answer.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
            
            // Jemná animace pozadí u otevřené otázky
            const item = button.closest('.faq-item');
            if (!answer.classList.contains('hidden')) {
                item.classList.add('ring-2', 'ring-blue-100', 'border-blue-200');
            } else {
                item.classList.remove('ring-2', 'ring-blue-100', 'border-blue-200');
            }
        });
    });
</script>
</section>
    <?php include 'includes/footer.php'; ?>
</body>
</html>