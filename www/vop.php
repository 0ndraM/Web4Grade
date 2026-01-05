<?php
session_start();
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web4Grade | Obchodní podmínky</title>
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
                <h1 class="text-4xl font-black text-gray-900 dark:text-white tracking-tight mb-4">Všeobecné obchodní podmínky</h1>
                <p class="text-gray-500 dark:text-slate-500 font-medium">Poslední aktualizace: <?= date('d. m. Y') ?></p>
                <div class="h-1.5 w-20 bg-blue-600 mt-6 rounded-full"></div>
            </div>

            <div class="space-y-10 text-gray-700 dark:text-slate-300 leading-relaxed text-sm md:text-base">
                
                <section>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">1. Úvodní ustanovení</h3>
                    <p>
                        Tyto všeobecné obchodní podmínky (dále jen „podmínky“) upravují práva a povinnosti mezi provozovatelem projektu 
                        <strong>Web4Grade</strong> (dále jen „poskytovatel“) a uživatelem služeb (dále jen „objednatel“). 
                        Odesláním zakázky prostřednictvím webového rozhraní objednatel potvrzuje, že se s těmito podmínkami seznámil 
                        a v plném rozsahu s nimi souhlasí.
                    </p>
                </section>

                <section>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">2. Předmět služby</h3>
                    <p>
                        Poskytovatel se zavazuje zhotovit pro objednatele webovou prezentaci nebo aplikaci na základě specifikace (zadání) 
                        dodané objednatelem. Služba zahrnuje programování, tvorbu designu, dodání zdrojových kódů a případné nasazení 
                        na webhosting objednatele.
                    </p>
                </section>

                <section>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">3. Platební podmínky a QR platby</h3>
                    <ul class="list-disc pl-5 space-y-2">
                        <li>Cena za službu je sjednána individuálně v klientské sekci na základě náročnosti zadání.</li>
                        <li>Platba probíhá bezhotovostně prostřednictvím bankovního převodu za využití generovaného QR kódu v systému.</li>
                        <li><strong>Platba je vyžadována po schválení testovací verze díla objednatelem.</strong></li>
                        <li>Dílo (zdrojové kódy) bude předáno až po úplném připsání sjednané částky na účet poskytovatele.</li>
                    </ul>
                </section>

                <section>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">4. Testovací verze a akceptace</h3>
                    <p>
                        Před finálním předáním je objednateli zpřístupněn funkční náhled (testovací verze). Objednatel je povinen si tuto verzi 
                        řádně prozkoumat. Zaplacením sjednané ceny objednatel vyjadřuje souhlas s podobou a funkčností díla a považuje jej 
                        za řádně dodané.
                    </p>
                </section>

                <section class="bg-blue-50 dark:bg-blue-900/10 p-6 rounded-2xl border border-blue-100 dark:border-blue-900/30">
                    <h3 class="text-xl font-bold text-blue-900 dark:text-blue-400 mb-4">5. Odstoupení od smlouvy</h3>
                    <p class="mb-4">
                        Vzhledem k tomu, že předmětem plnění je dodání digitálního obsahu vytvořeného podle individuálních požadavků objednatele 
                        (dílo na zakázku), <strong>nelze od smlouvy odstoupit bez udání důvodu</strong> ve standardní lhůtě 14 dnů 
                        (v souladu s § 1837 písm. d) občanského zákoníku).
                    </p>
                    <p>
                        Uživatel má však právo ukončit spolupráci ve fázi testování – v takovém případě nebude vyžadována platba, 
                        ale dílo (zdrojové kódy) nebude objednateli předáno.
                    </p>
                </section>

                <section>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">6. Autorská práva a odpovědnost</h3>
                    <p class="mb-4">
                        Vlastnické právo k dílu a právo jej užívat přechází na objednatele okamžikem úplného zaplacení kupní ceny. 
                    </p>
                    <p>
                        <strong>Omezení odpovědnosti:</strong> Produkty Web4Grade jsou určeny výhradně pro studijní nebo soukromé účely. 
                        Poskytovatel neodpovídá za způsob, jakým objednatel s dílem naloží u třetích stran (např. v rámci školního hodnocení), 
                        ani za případné porušení pravidel vzdělávacích institucí objednatelem.
                    </p>
                </section>

                <section>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">7. Ochrana osobních údajů</h3>
                    <p>
                        Poskytovatel zpracovává osobní údaje (uživatelské jméno a nahrané soubory) pouze za účelem realizace zakázky a komunikace. 
                        Data nejsou poskytována třetím stranám. Objednatel může kdykoliv požádat o smazání svého účtu a souvisejících dat.
                    </p>
                </section>

                <section>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">8. Závěrečná ustanovení</h3>
                    <p>
                        Poskytovatel si vyhrazuje právo tyto podmínky kdykoliv změnit. Nové znění podmínek bude vždy zveřejněno na této stránce. 
                        Právní vztahy těmito podmínkami neupravené se řídí platnými zákony České republiky.
                    </p>
                </section>

            </div>

            <div class="mt-16 pt-8 border-t dark:border-slate-800 text-center">
                <a href="register.php" class="inline-block bg-blue-600 text-white font-black py-4 px-10 rounded-2xl hover:bg-blue-700 transition shadow-lg shadow-blue-200 dark:shadow-none uppercase tracking-widest text-xs">
                    Rozumím a chci začít projekt
                </a>
            </div>

        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>