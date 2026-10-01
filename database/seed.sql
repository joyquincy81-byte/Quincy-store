-- Quincy Store — Seed & Missing Tables
-- Run against: ecommerce_db

USE ecommerce_db;

-- ── Missing tables ─────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `settings` (
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `key`        varchar(100) NOT NULL,
  `value`      text         DEFAULT NULL,
  `updated_at` timestamp    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `email`      varchar(100) NOT NULL,
  `token`      varchar(100) NOT NULL,
  `expires_at` datetime     NOT NULL,
  `created_at` timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pr_token` (`token`),
  KEY `idx_pr_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id`           int(11)     NOT NULL AUTO_INCREMENT,
  `ip_address`   varchar(45) NOT NULL,
  `action`       varchar(50) NOT NULL DEFAULT 'login',
  `attempts`     int(11)     NOT NULL DEFAULT 1,
  `last_attempt` timestamp   NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at`   timestamp   NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rate_limit_ip_action` (`ip_address`, `action`),
  KEY `idx_rate_limit_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Seed roles ─────────────────────────────────────────────────

INSERT IGNORE INTO `roles` (`name`, `description`) VALUES
  ('admin',          'Platform administrator with full access'),
  ('supplier',       'Supplier who sells products on the platform'),
  ('manufacturer',   'Manufacturer who produces and sells products'),
  ('business_buyer', 'Business buyer for bulk B2B purchasing'),
  ('customer',       'Regular retail customer for B2C purchases'),
  ('logistics',      'Logistics and delivery staff');

-- ── Seed default admin user ────────────────────────────────────
-- Password: Admin@1234  (bcrypt cost 12)

INSERT IGNORE INTO `users`
  (`role_id`, `first_name`, `last_name`, `email`, `phone`, `password`, `status`, `email_verified_at`)
SELECT
  r.id, 'Quincy', 'Admin', 'admin@quincystore.cm', '699000001',
  '$2y$12$8i5z9i.RPh7Osbhkhp/mVOx6mpTVNTMA5gTkqevOfJKcKpzBQl/BG',
  'active', NOW()
FROM `roles` r WHERE r.name = 'admin' LIMIT 1;

-- ── Seed settings ──────────────────────────────────────────────

INSERT IGNORE INTO `settings` (`key`, `value`) VALUES
  ('site_name',             'Quincy Store'),
  ('site_email',            'info@quincystore.cm'),
  ('site_phone',            '+237 699 000 001'),
  ('site_address',          'Douala, Cameroon'),
  ('currency',              'XAF'),
  ('currency_symbol',       'FCFA'),
  ('maintenance_mode',      '0'),
  ('smtp_host',             'smtp.mailtrap.io'),
  ('smtp_port',             '587'),
  ('smtp_username',         ''),
  ('smtp_password',         ''),
  ('smtp_from_email',       'noreply@quincystore.cm'),
  ('smtp_from_name',        'Quincy Store'),
  ('shipping_fee',          '2000'),
  ('free_shipping_min',     '50000'),
  ('quotation_expiry_days', '7');
