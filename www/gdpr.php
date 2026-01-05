<?php
session_start();
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web4Grade | Ochrana osobních údajů (GDPR)</title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Inicializace Dark Mode
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-slate-950 flex flex-col min-h-screen font-sans transition-colors duration-300">

    <?php include 'includes/header.php'; ?>

    <main class="flex-grow py-12 md:py-20 px-6">
        <div class="max-w-4xl mx-auto bg-white dark:bg-slate-900 p-8 md:p-16 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-slate-800 transition-colors">
            
            <div class="mb-12">
                <h1 class="text-4xl font-black text-gray-900 dark:text-white tracking-tight mb-4">Ochrana osobních údajů</h1>
                <p class="text-gray-500 dark:text-slate-500 font-medium italic">Jak nakládáme s tvými daty v rámci Web4Grade</p>
                <div class="h-1.5 w-20 bg-blue-600 mt-6 rounded-full"></div>
            </div>

            <div class="space-y-10 text-gray-700 dark:text-slate-300 leading-relaxed text-sm md:text-base">
                
                <section>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">1. Správce osobních údajů</h3>
                    <p>
                        Správcem osobních údajů v rámci projektu <strong>WebGrade</strong> je provozovatel (dále jen „správce“). 
                        V případě dotazů ohledně tvých dat mě můžeš kontaktovat na e-mailu: 
                        <span class="text-blue-600 dark:text-blue-400 font-bold">ondrejmuhlhandel@gmail.com</span>.
                    </p>
                </section>

                <section>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">2. Jaké údaje sbíráme?</h3>
                    <p class="mb-3">Zpracováváme pouze údaje nezbytné pro fungování služby:</p>
                    <ul class="list-disc pl-5 space-y-2">
                        <li><strong>Registrační údaje:</strong> Uživatelské jméno a heslo (v zašifrované podobě).</li>
                        <li><strong>Profilové údaje:</strong> Profilová fotografie (pokud ji nahraješ).</li>
                        <li><strong>Údaje k zakázce:</strong> Název projektu, popis zadání a veškeré přílohy (PDF, obrázky, archivy), které nahraješ do systému.</li>
                        <li><strong>Komunikace:</strong> Historie zpráv v integrovaném chatu u každé zakázky.</li>
                    </ul>
                </section>

                <section>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">3. Účel zpracování</h3>
                    <p>Tvým datům věnujeme maximální péči a zpracováváme je pouze za účelem:</p>
                    <ul class="list-disc pl-5 space-y-2">
                        <li>Umožnění přístupu do klientské sekce a správy tvých zakázek.</li>
                        <li>Realizace webového projektu podle tvého zadání.</li>
                        <li>Komunikace s tebou v průběhu vývoje (chat, upozornění).</li>
                        <li>Zajištění bezpečnosti systému.</li>
                    </ul>
                </section>

                <section class="bg-gray-50 dark:bg-slate-800/50 p-6 rounded-2xl border dark:border-slate-800 transition-colors">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">4. Doba uchovávání dat</h3>
                    <p>
                        Osobní údaje uchováváme po dobu trvání tvého účtu. Pokud se rozhodneš svůj účet nebo konkrétní zakázku smazat, 
                        dojde k trvalému odstranění veškerých souvisejících souborů z disku i záznamů v databázi (pokud zákon nevyžaduje jinak, např. pro účetnictví).
                    </p>
                </section>

                <section>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">5. Předávání dat třetím stranám</h3>
                    <p>
                        <strong>Tvá data nikomu neprodáváme ani nepředáváme.</strong> Vše zůstává v rámci WebGrade. 
                        Výjimkou mohou být pouze zákonné požadavky orgánů veřejné moci.
                    </p>
                </section>

                <section>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">6. Tvá práva</h3>
                    <p class="mb-3">Podle GDPR máš tato práva:</p>
                    <ul class="list-disc pl-5 space-y-2">
                        <li>Právo na <strong>přístup</strong> k tvým datům a výpis toho, co o tobě evidujeme.</li>
                        <li>Právo na <strong>opravu</strong> chybných údajů.</li>
                        <li>Právo na <strong>výmaz</strong> (právo být zapomenut).</li>
                        <li>Právo na <strong>omezení</strong> zpracování.</li>
                    </ul>
                    <p class="mt-4 italic">Tato práva můžeš uplatnit zasláním e-mailu správci.</p>
                </section>

                <section>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">7. Zabezpečení</h3>
                    <p>
                        Veškerá komunikace probíhá přes zabezpečený protokol HTTPS. Hesla jsou hashována pomocí moderního algoritmu BCrypt. 
                        Přílohy k zakázkám jsou uloženy v neveřejných složkách s unikátními názvy souborů, aby nebyly snadno dohledatelné.
                    </p>
                </section>

            </div>

            <div class="mt-16 pt-8 border-t dark:border-slate-800 text-center">
                <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-6 italic">Web4Grade Ecosystem &bull; <?= date('Y') ?></p>
                <a href="dashboard.php" class="inline-block bg-blue-600 text-white font-black py-4 px-10 rounded-2xl hover:bg-blue-700 transition shadow-lg shadow-blue-200 dark:shadow-none uppercase tracking-widest text-xs">
                    Zpět do systému
                </a>
            </div>

        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>