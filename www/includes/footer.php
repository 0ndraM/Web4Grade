<footer class="bg-white border-t border-gray-100 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
            
            <div class="md:col-span-1">
                <a href="index.php" class="text-2xl font-black tracking-tighter mb-4 block">
                    <span class="text-blue-600">Web</span><span class="text-gray-900">Grade</span>
                </a>
                <p class="text-gray-500 text-sm leading-relaxed mb-6">
                    Pomáhám studentům s realizací školních webových projektů. Kvalitní kód, moderní design a férový přístup.
                </p>
                <div class="flex gap-4"  style="visibility: hidden">
                    <a href="" class="w-8 h-8 bg-gray-50 text-gray-400 rounded-lg flex items-center justify-center hover:bg-blue-50 hover:text-blue-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                    </a>
                    <a href="#" class="w-8 h-8 bg-gray-50 text-gray-400 rounded-lg flex items-center justify-center hover:bg-blue-50 hover:text-blue-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-gray-900 mb-6 uppercase text-xs tracking-widest">Služby</h4>
                <ul class="space-y-4 text-sm text-gray-500 font-medium">
                    <li><a href="index.php#jak-to-funguje" class="hover:text-blue-600 transition">Jak to funguje</a></li>
                    <li><a href="create_order.php" class="hover:text-blue-600 transition">Založit zakázku</a></li>
                    <li><a href="index.php#faq" class="hover:text-blue-600 transition">Časté dotazy</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-gray-900 mb-6 uppercase text-xs tracking-widest">Uživatel</h4>
                <ul class="space-y-4 text-sm text-gray-500 font-medium">
                    <li><a href="dashboard.php" class="hover:text-blue-600 transition">Moje zakázky</a></li>
                    <li><a href="account_settings.php" class="hover:text-blue-600 transition">Nastavení profilu</a></li>
                    <li><a href="index.php#login" class="hover:text-blue-600 transition">Přihlášení</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-gray-900 mb-6 uppercase text-xs tracking-widest">Kontakt</h4>
                <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100">
                    <p class="text-xs text-blue-800 font-bold mb-1">Potřebuješ poradit?</p>
                    <p class="text-sm text-blue-600 font-medium mb-3">Napiš mi přímo v chatu u zakázky nebo na mail.</p>
                    <a href="mailto:ondrejmuhlhandel@gmail.com" class="text-xs bg-white text-blue-600 px-3 py-1.5 rounded-lg font-bold shadow-sm inline-block">info@webgrade.cz</a>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-gray-400 text-xs font-medium">
                &copy; <?= date('Y') ?> <span class="text-gray-600 font-bold">0ndra_m_</span>. Vytvořeno pro studenty s ❤️.
            </p>
            <div class="flex gap-6 text-[10px] uppercase tracking-widest font-bold text-gray-400">
                <a href="#" class="hover:text-gray-600 transition">Obchodní podmínky</a>
                <a href="#" class="hover:text-gray-600 transition">Ochrana soukromí</a>
            </div>
        </div>
    </div>
</footer>