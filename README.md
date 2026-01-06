# Web4Grade

**Web4Grade** je webová aplikace pro správu objednávek školních webových projektů. Umožňuje studentům zadávat projekty, komunikovat s vývojářem a sledovat stav jejich zakázek.

## 🚀 Funkce

- **Registrace a přihlášení** - Studenti si mohou vytvořit účet a přihlásit se do systému
- **Správa objednávek** - Vytváření nových objednávek s detailním popisem projektu
- **Integrovaný chat** - Komunikace mezi studentem a vývojářem přímo u každé zakázky
- **Sledování stavu** - Přehled aktuálního stavu zakázky (new, pending_payment, paid, in_progress, finished)
- **Bezpečná platba** - Platba pomocí QR kódu po zhlédnutí testovací verze
- **Admin panel** - Správa všech zakázek, změna stavů a komunikace s klienty
- **Dark mode** - Podpora tmavého režimu
- **Responsive design** - Optimalizováno pro všechna zařízení

## 📋 Požadavky

- Docker & Docker Compose
- PHP 8.2+
- MySQL 8.0+
- Apache/Nginx

## ⚙️ Instalace

### Pomocí Dockeru (doporučeno)

1. Naklonujte repozitář:
```bash
git clone https://github.com/0ndraM/muj-projekt.git
cd muj-projekt
```

2. Spusťte Docker kontejnery:
```bash
docker-compose up -d
```

3. Importujte databázovou strukturu:
```bash
docker exec -i projekt_db mysql -uuser -puser_password objednavkovy_system < db.sql
```

4. Aplikace je dostupná na:
   - **Web:** http://localhost:8080
   - **phpMyAdmin:** http://localhost:8081

### Výchozí přihlašovací údaje

- **Admin účet:**
  - Uživatelské jméno: `admin`
  - Heslo: `admin123` (změňte po prvním přihlášení)

## 🗂️ Struktura projektu

```
muj-projekt/
├── www/                      # Zdrojové soubory aplikace
│   ├── config/              # Konfigurace databáze
│   ├── includes/            # Společné komponenty (header, footer, funkce)
│   ├── assets/              # Statické soubory (CSS, JS, obrázky)
│   ├── index.php           # Hlavní stránka
│   ├── dashboard.php       # Dashboard klienta
│   ├── create_order.php    # Vytvoření nové zakázky
│   ├── order_detail.php    # Detail zakázky s chatem
│   ├── register.php        # Registrace
│   └── ...
├── db.sql                   # SQL skript pro vytvoření databáze
├── docker-compose.yml       # Docker compose konfigurace
└── README.md               # Dokumentace
```

## 💾 Databázová struktura

### Tabulky

- **`users`** - Uživatelské účty (studenti a admin)
- **`orders`** - Objednávky webových projektů
- **`messages`** - Chatové zprávy u jednotlivých zakázek
- **`acces_logy`** - Logy přihlášení a přístupu

## 🔒 Bezpečnost

- **Hesla** jsou hashována pomocí `password_hash()` s BCrypt algoritmem
- **SQL Injection** prevence pomocí prepared statements
- **XSS ochrana** pomocí `htmlspecialchars()`
- **Autorizace** - kontrola přístupu k datům podle role a vlastnictví
- **HTTPS** připojení (doporučeno v produkci)

## 🎨 Technologie

- **Frontend:** HTML5, Tailwind CSS, JavaScript
- **Backend:** PHP 8.2
- **Databáze:** MySQL 8.0
- **Server:** Apache
- **Kontejnerizace:** Docker & Docker Compose

## 📝 Použití

### Pro studenty (klienty)

1. Zaregistrujte se na hlavní stránce
2. Přihlaste se do systému
3. Vytvořte novou zakázku s popisem projektu
4. Komunikujte s vývojářem přes integrovaný chat
5. Sledujte stav vaší zakázky
6. Po dokončení testovací verze zaplaťte pomocí QR kódu

### Pro administrátora

1. Přihlaste se pomocí admin účtu
2. Prohlížejte všechny zakázky v dashboardu
3. Měňte stavy zakázek podle pokroku
4. Komunikujte s klienty přes chat
5. Spravujte systémové logy

## 🛠️ Konfigurace

Databázové připojení lze upravit v souboru `www/config/db.php` nebo v `docker-compose.yml`:

```yaml
environment:
  MYSQL_DATABASE: objednavkovy_system
  MYSQL_USER: user
  MYSQL_PASSWORD: user_password
```

## 📄 Licence

Tento projekt je vytvořen pro studijní účely.

## 👤 Autor

**0ndra_m_**
- Email: ondrejmuhlhandel@gmail.com
- Instagram: [@web4grade](https://www.instagram.com/web4grade/)

## 🤝 Přispívání

Příspěvky jsou vítány! Prosím, vytvořte issue nebo pull request.