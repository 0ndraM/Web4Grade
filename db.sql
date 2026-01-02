-- 1. Tabulka pro uživatele
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('client','admin') DEFAULT 'client',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabulka pro objednávky
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  -- Cena v haléřích/int, aby se předešlo chybám s float, nebo decimal
  `price` decimal(10,2) DEFAULT '0.00',
  `status` enum('new','pending_payment','paid','in_progress','finished','cancelled') DEFAULT 'new',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_order_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabulka pro chat (zprávy)
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `sent_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_message_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_message_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Vytvoření tvého admin účtu
-- Heslo je 'admin123' (po přihlášení si ho v systému změň)
-- Pro PHP hashování se používá password_hash('heslo', PASSWORD_DEFAULT)
INSERT INTO `users` (`username`, `email`, `password_hash`, `role`) VALUES 
('admin', 'tvuj@email.cz', '$2y$10$fM/O9.E2EAn8vB4Xo.p6XuzmYFf9y5S0fXvP.D.x3P.m1F3R.m1Fe', 'admin');