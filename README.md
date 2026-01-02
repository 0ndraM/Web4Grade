### 1. Architektura a Uživatelské Role

Systém bude mít dvě rozhraní postavená na stejném základu:

* **Klient (Žák):** Může vytvořit objednávku, sledovat stav, platit (QR kód) a psát si s tebou.
* **Admin (Ty):** Vidíš seznam všech zakázek, měníš jejich stavy (Čeká na platbu -> Zaplaceno -> Hotovo) a odpovídáš v chatu.

### 2. Návrh Databáze (ER Diagram)

Toto je nejdůležitější část. Pokud máš špatnou DB, kódování bude bolet.

* **`users`**: `id`, `username`, `email`, `password_hash`, `role` (admin/client)
* **`orders`**: `id`, `client_id`, `title`, `description`, `price`, `status` (new, pending_payment, paid, in_progress, finished), `created_at`
* **`messages`**: `id`, `order_id`, `sender_id`, `message_text`, `sent_at`

### 3. Logika Klíčových Funkcí

#### A. Objednávkový proces

1. Klient vyplní formulář (Název webu, popis, spolužák pro odkaz).
2. Vytvoří se záznam v `orders` se stavem `new`.
3. Ty (Admin) se na to podíváš, potvrdíš cenu a změníš stav na `pending_payment`.

#### B. Platba pomocí QR kódu

Místo složité integrace platební brány použij knihovnu pro generování **QR Plateb**.

* V PHP vygeneruješ řetězec pro SPAYD (Standard Payment Data).
* Zobrazíš ho klientovi v detailu objednávky. Jakmile ti peníze přijdou na účet, ručně v adminu klikneš na "Zaplaceno".

#### C. Chat systém

Chat bude vázaný na konkrétní objednávku (`order_id`).

* Při otevření detailu objednávky se načtou všechny zprávy, kde se `order_id` rovná ID dané objednávky.
* Zprávy seřadíš podle `sent_at` vzestupně.

---

### 4. Navigační mapa webu (Sitemap)

| Stránka | Přístup | Funkce |
| --- | --- | --- |
| `index.php` | Veřejné | Landing page + Login/Registrace |
| `dashboard.php` | Klient | Seznam mých objednávek + tlačítko "Nová objednávka" |
| `order_detail.php` | Obě role | Detail zakázky, stav, QR kód, **Chatovací okno** |
| `admin.php` | Jen Ty | Přehled všech zakázek od všech lidí, filtrace podle stavu |
| `profile.php` | Obě role | Změna hesla, nastavení profilu |

---

### 5. Ukázka struktury složek (MVC-ish)

Aby se ti s tím v Dockeru dobře pracovalo, doporučuji toto rozdělení:

```text
www/
├── config/
│   └── db.php         # Připojení k PDO
├── includes/
│   ├── header.php     # Horní menu
│   └── functions.php  # Pomocné funkce (checkLogin, getStatus atd.)
├── css/
│   └── style.css      # Tailwind nebo vlastní CSS
├── views/             # Samotné HTML šablony
│   ├── chat_view.php
│   └── order_form.php
├── index.php          # Rozcestník
└── order.php          # Logika konkrétní objednávky

```

---

### 6. Bezpečnost (Na co si dát pozor)

1. **SQL Injection:** Vždy používej prepared statements (`$stmt->prepare(...)`).
2. **XSS:** Při výpisu chatu a popisu objednávky vždy používej `htmlspecialchars()`.
3. **Autorizace:** V detailu objednávky musíš zkontrolovat: `IF (user_id == order_client_id OR role == 'admin')`. Jinak se ti žáci budou dívat navzájem na zadání.

**Chceš, abych ti napsal SQL skript pro vytvoření těch tabulek, nebo tě zajímá PHP kód pro odesílání zpráv v chatu?**