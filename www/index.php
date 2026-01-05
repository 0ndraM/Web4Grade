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
      <meta name="description" content="WebGrade nabízí profesionální tvorbu webových stránek pro školní projekty. Získejte moderní web s kompletním kódem a dokumentací.">
      <title>Web4Grade | Profesionální řešení školních projektů</title>
      <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
      <script src="https://cdn.tailwindcss.com"></script>
      <script>
         // Inicializace Dark Mode před vykreslením
         if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
             document.documentElement.classList.add('dark');
         } else {
             document.documentElement.classList.remove('dark');
         }
      </script>
      <style>
         .hero-gradient { background: linear-gradient(135deg, #0f172a 0%, #2563eb 100%); }
         html { scroll-behavior: smooth; }
      </style>
   </head>
   <body class="bg-gray-50 dark:bg-slate-950 flex flex-col min-h-screen font-sans transition-colors duration-300">
      <?php include 'includes/header.php'; ?>
      <header class="hero-gradient text-white py-24 px-6 relative overflow-hidden">
         <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between relative z-10">
            <div class="md:w-3/5 mb-12 md:mb-0">
               <span class="bg-blue-500/20 text-blue-200 px-4 py-1 rounded-full text-sm font-semibold mb-6 inline-block border border-blue-400/30 backdrop-blur-md">
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
               <div class="absolute -inset-4 bg-blue-500/20 blur-3xl rounded-full animate-pulse"></div>
               <div class="relative bg-white/10 p-2 rounded-3xl backdrop-blur-sm border border-white/20 shadow-2xl">
                  <img src="https://img.freepik.com/free-vector/website-setup-concept-illustration_114360-4256.jpg" alt="Web development" class="rounded-2xl max-w-full h-auto shadow-inner grayscale-[0.2] contrast-[1.1]">
               </div>
            </div>
         </div>
      </header>
      <section id="vlastnosti" class="py-20 max-w-7xl mx-auto px-6">
         <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 hover:shadow-xl dark:hover:border-blue-500/50 transition duration-300">
               <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center mb-6">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                     <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                  </svg>
               </div>
               <h3 class="text-xl font-bold mb-3 text-gray-800 dark:text-white">Integrovaný Chat</h3>
               <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">Žádné Instagram DMka. Všechno řešíme přímo u tvé zakázky na jednom místě v našem systému.</p>
            </div>
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 hover:shadow-xl dark:hover:border-green-500/50 transition duration-300">
               <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-xl flex items-center justify-center mb-6">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                     <rect x="3" y="3" width="7" height="7"></rect>
                     <rect x="14" y="3" width="7" height="7"></rect>
                     <rect x="14" y="14" width="7" height="7"></rect>
                     <rect x="3" y="14" width="7" height="7"></rect>
                  </svg>
               </div>
               <h3 class="text-xl font-bold mb-3 text-gray-800 dark:text-white">Bezpečná platba</h3>
               <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">Platíš až ve chvíli, kdy vidíš funkční demo na testovací verzi. Stačí naskenovat QR kód.</p>
            </div>
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 hover:shadow-xl dark:hover:border-purple-500/50 transition duration-300">
               <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-xl flex items-center justify-center mb-6">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                     <circle cx="12" cy="12" r="10"></circle>
                     <polyline points="12 6 12 12 16 14"></polyline>
                  </svg>
               </div>
               <h3 class="text-xl font-bold mb-3 text-gray-800 dark:text-white">Dodržení termínu</h3>
               <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">Sleduješ progress v reálném čase. Vždy víš, v jaké fázi se tvůj školní projekt právě nachází.</p>
            </div>
         </div>
      </section>
      <section id="jak-to-funguje" class="py-24 bg-white dark:bg-slate-900 px-6 transition-colors duration-300">
         <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
               <h2 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-4">Jak spolupráce probíhá?</h2>
               <p class="text-gray-500 dark:text-slate-400 text-lg mx-auto max-w-2xl">Celý proces od tvého zadání až po ostrý web pod tvojí správou.</p>
               <div class="h-1.5 w-24 bg-blue-600 mx-auto mt-6 rounded-full"></div>
            </div>
            <div class="grid md:grid-cols-5 gap-6 relative">
               <div class="flex flex-col items-center text-center p-4 group">
                  <div class="w-14 h-14 bg-gray-50 dark:bg-slate-800 text-gray-600 dark:text-slate-400 rounded-2xl flex items-center justify-center mb-6 shadow-sm border border-gray-100 dark:border-slate-700 group-hover:bg-blue-600 group-hover:text-white dark:group-hover:text-white transition-all duration-300">
                     <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <polyline points="16 11 18 13 22 9"/>
                     </svg>
                  </div>
                  <h3 class="font-bold text-gray-900 dark:text-white mb-2">1. Registrace</h3>
                  <p class="text-gray-500 dark:text-slate-400 text-sm">Vytvoříš si účet, abychom mohli komunikovat v chatu.</p>
               </div>
               <div class="flex flex-col items-center text-center p-4 group">
                  <div class="w-14 h-14 bg-gray-50 dark:bg-slate-800 text-gray-600 dark:text-slate-400 rounded-2xl flex items-center justify-center mb-6 shadow-sm border border-gray-100 dark:border-slate-700 group-hover:bg-blue-600 group-hover:text-white dark:group-hover:text-white transition-all duration-300">
                     <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                     </svg>
                  </div>
                  <h3 class="font-bold text-gray-900 dark:text-white mb-2">2. Zadání</h3>
                  <p class="text-gray-500 dark:text-slate-400 text-sm">Nahraješ požadavky, soubory do systému a domluvíme se na ceně.</p>
               </div>
               <div class="flex flex-col items-center text-center p-4 group">
                  <div class="w-14 h-14 bg-gray-50 dark:bg-slate-800 text-gray-600 dark:text-slate-400 rounded-2xl flex items-center justify-center mb-6 shadow-sm border border-gray-100 dark:border-slate-700 group-hover:bg-blue-600 group-hover:text-white dark:group-hover:text-white transition-all duration-300">
                     <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                     </svg>
                  </div>
                  <h3 class="font-bold text-gray-900 dark:text-white mb-2">3. Testovací verze</h3>
                  <p class="text-gray-500 dark:text-slate-400 text-sm">Web nahraji na link, kde si můžeš všechno proklikat.</p>
               </div>
               <div class="flex flex-col items-center text-center p-4 group">
                  <div class="w-14 h-14 bg-gray-50 dark:bg-slate-800 text-gray-600 dark:text-slate-400 rounded-2xl flex items-center justify-center mb-6 shadow-sm border border-gray-100 dark:border-slate-700 group-hover:bg-blue-600 group-hover:text-white dark:group-hover:text-white transition-all duration-300">
                     <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <line x1="2" y1="10" x2="22" y2="10"/>
                     </svg>
                  </div>
                  <h3 class="font-bold text-gray-900 dark:text-white mb-2">4. Platba</h3>
                  <p class="text-gray-500 dark:text-slate-400 text-sm">Pokud web odpovídá zadání, zaplatíš přes QR kód.</p>
               </div>
               <div class="flex flex-col items-center text-center p-4 group">
                  <div class="w-14 h-14 bg-blue-600 text-white rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-blue-200 dark:shadow-blue-900/20">
                     <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                     </svg>
                  </div>
                  <h3 class="font-bold text-gray-900 dark:text-white mb-2">5. Předání</h3>
                  <p class="text-gray-500 dark:text-slate-400 text-sm">Web nasadím na tvůj hosting a projekt je hotový.</p>
               </div>
            </div>
         </div>
      </section>
      <section id="login" class="bg-gray-100 dark:bg-slate-950 py-24 px-6 border-y border-gray-200 dark:border-slate-800 transition-colors duration-300">
         <div class="max-w-md mx-auto">
            <div class="bg-white dark:bg-slate-900 p-8 md:p-12 rounded-[2.5rem] shadow-2xl border border-white dark:border-slate-800 relative overflow-hidden">
               <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 dark:bg-blue-900/10 rounded-full -mr-16 -mt-16 transition-colors"></div>
               <div class="relative z-10">
                  <?php if (isset($_SESSION['user_id'])): ?>
                  <div class="text-center">
                     <div class="w-20 h-20 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">👋</div>
                     <h2 class="text-3xl font-black mb-2 text-gray-900 dark:text-white tracking-tight">Rádi tě vidíme!</h2>
                     <p class="text-center text-gray-500 dark:text-gray-400 mb-10 text-lg font-medium">Vítej zpět, <span class="text-blue-600 dark:text-blue-400 font-bold"><?= htmlspecialchars($_SESSION['username']) ?></span></p>
                     <a href="dashboard.php" class="block text-center w-full bg-blue-600 text-white font-black py-5 rounded-2xl hover:bg-blue-700 transition shadow-xl shadow-blue-200 dark:shadow-none transform hover:-translate-y-1 active:scale-95 uppercase tracking-widest text-sm">
                     Vstoupit do dashboardu
                     </a>
                  </div>
                  <?php else: ?>
                  <div class="text-center mb-10">
                     <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Klientská sekce</h2>
                     <p class="text-gray-500 dark:text-gray-400 font-medium">Přihlas se ke svým projektům</p>
                  </div>
                  <?php if ($error): ?>
                  <div class="bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 p-4 rounded-2xl mb-8 text-xs font-black border border-red-100 dark:border-red-900/30 flex items-center gap-3 uppercase tracking-wider transition-colors">
                     <span class="text-lg">⚠️</span> <?= htmlspecialchars($error) ?>
                  </div>
                  <?php endif; ?>
                  <form method="POST" action="" class="space-y-6">
                     <div>
                        <label class="block text-gray-400 dark:text-slate-500 text-[10px] font-black uppercase tracking-[0.2em] mb-2 ml-1">Uživatelské jméno</label>
                        <input type="text" name="username" placeholder="např. karel123" 
                           class="w-full px-5 py-4 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white transition font-medium">
                     </div>
                     <div>
                        <label class="block text-gray-400 dark:text-slate-500 text-[10px] font-black uppercase tracking-[0.2em] mb-2 ml-1">Heslo</label>
                        <input type="password" name="password" placeholder="••••••••" 
                           class="w-full px-5 py-4 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white transition font-medium">
                     </div>
                     <div class="pt-4">
                        <button type="submit" name="login" class="w-full bg-blue-600 text-white font-black py-5 rounded-2xl hover:bg-blue-700 transition shadow-xl shadow-blue-200 dark:shadow-none transform hover:-translate-y-1 active:scale-95 uppercase tracking-widest text-sm">
                        Přihlásit se
                        </button>
                     </div>
                  </form>
                  <p class="mt-10 text-center text-gray-500 dark:text-gray-400 text-sm font-medium">
                     Ještě nemáš účet? <a href="register.php" class="text-blue-600 dark:text-blue-400 font-black hover:underline decoration-2 underline-offset-4 transition">Zaregistruj se zde</a>
                  </p>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </section>
      <section id="faq" class="py-24 bg-white dark:bg-slate-900 px-6 transition-colors duration-500">
         <div class="max-w-3xl mx-auto">
            <div class="text-center mb-16">
               <h2 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-4 tracking-tight">Často kladené otázky</h2>
               <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">Vše, co potřebuješ vědět o WebGrade.</p>
               <div class="h-1.5 w-16 bg-blue-600 mx-auto mt-6 rounded-full shadow-[0_0_10px_rgba(37,99,235,0.3)]"></div>
            </div>
            <div class="space-y-4">
               <?php 
                  $faqs = [
                      ["Jak probíhá platba a je to bezpečné?", "Platba probíhá až ve chvíli, kdy ti nasdílím testovací verzi webu. Ty si vše proklikáš, a pokud web odpovídá zadání, zaplatíš pomocí QR kódu. Teprve poté ti nahraji web na hosting."],
                      ["Co když budu chtít na webu něco změnit?", "V rámci testovací verze máš nárok na drobné úpravy. Vše řešíme rychle přímo v integrovaném chatu v dashboardu."],
                      ["Pomůžeš mi i s nahráním na hosting?", "Ano, to je poslední krok naší spolupráce. Postarám se o zprovoznění na tvém hostingu, aby byl dostupný na tvojí adrese."],
                      ["Jak rychle bude web hotový?", "Standardní školní weby obvykle zvládám dodat do 3–5 pracovních dnů."]
                  ];
                  foreach($faqs as $index => $faq): ?>
               <div class="faq-item border border-gray-100 dark:border-slate-800 rounded-3xl overflow-hidden transition-all duration-500 hover:border-blue-200 dark:hover:border-blue-500/30 bg-gray-50/50 dark:bg-slate-800/30">
                  <button class="faq-question w-full flex items-center justify-between p-7 text-left transition-all duration-300 group">
                     <span class="font-bold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300"><?= $faq[0] ?></span>
                     <div class="w-8 h-8 rounded-full bg-white dark:bg-slate-700 shadow-sm flex items-center justify-center transition-transform duration-500 group-data-[open=true]:rotate-180">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                        </svg>
                     </div>
                  </button>
                  <div class="faq-answer-wrapper grid transition-[grid-template-rows] duration-500 ease-in-out" style="grid-template-rows: 0fr;">
                     <div class="overflow-hidden">
                        <div class="p-7 pt-0 text-gray-600 dark:text-gray-400 leading-relaxed text-sm antialiased">
                           <div class="h-px w-full bg-gradient-to-r from-transparent via-gray-200 dark:via-slate-700 to-transparent mb-6 opacity-50"></div>
                           <?= $faq[1] ?>
                        </div>
                     </div>
                  </div>
               </div>
               <?php endforeach; ?>
            </div>
         </div>
      </section>
      <?php include 'includes/footer.php'; ?>
      <script>
         document.querySelectorAll('.faq-item').forEach(item => {
             const button = item.querySelector('.faq-question');
             const wrapper = item.querySelector('.faq-answer-wrapper');
             
             button.addEventListener('click', () => {
                 const isOpen = button.getAttribute('data-open') === 'true';
                 
                 // Zavřít všechny ostatní (harmonika efekt)
                 document.querySelectorAll('.faq-item').forEach(otherItem => {
                     if (otherItem !== item) {
                         otherItem.querySelector('.faq-question').setAttribute('data-open', 'false');
                         otherItem.querySelector('.faq-answer-wrapper').style.gridTemplateRows = "0fr";
                         otherItem.classList.remove('ring-4', 'ring-blue-50', 'dark:ring-blue-900/20', 'bg-white', 'dark:bg-slate-800');
                     }
                 });
         
                 // Toggle aktuálního
                 if (isOpen) {
                     button.setAttribute('data-open', 'false');
                     wrapper.style.gridTemplateRows = "0fr";
                     item.classList.remove('ring-4', 'ring-blue-50', 'dark:ring-blue-900/20', 'bg-white', 'dark:bg-slate-800');
                 } else {
                     button.setAttribute('data-open', 'true');
                     wrapper.style.gridTemplateRows = "1fr";
                     item.classList.add('ring-4', 'ring-blue-50', 'dark:ring-blue-900/20', 'bg-white', 'dark:bg-slate-800');
                 }
             });
         });
      </script>
   </body>
</html>