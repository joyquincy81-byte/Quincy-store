<?php

define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL', rtrim((string) (getenv('APP_URL') ?: '/quincy-store'), '/'));
define('SITE_NAME', (string) (getenv('APP_NAME') ?: 'Quincy Store'));
define('BCRYPT_COST', 12);
define('RATE_LIMIT_ATTEMPTS', 5);
define('RATE_LIMIT_WINDOW', 600);

define('DB_HOST', (string) (getenv('DB_HOST') ?: '127.0.0.1'));
define('DB_PORT', (string) (getenv('DB_PORT') ?: '3306'));
define('DB_NAME', (string) (getenv('DB_NAME') ?: 'ecommerce_db'));
define('DB_USER', (string) (getenv('DB_USER') ?: 'root'));
define('DB_PASS', (string) (getenv('DB_PASS') ?: ''));
define('DB_CHARSET', 'utf8mb4');

define('MAIL_HOST', (string) (getenv('MAIL_HOST') ?: 'localhost'));
define('MAIL_PORT', (int) (getenv('MAIL_PORT') ?: 587));
define('MAIL_USERNAME', (string) (getenv('MAIL_USERNAME') ?: ''));
define('MAIL_PASSWORD', (string) (getenv('MAIL_PASSWORD') ?: ''));
define('MAIL_ENCRYPTION', (string) (getenv('MAIL_ENCRYPTION') ?: 'tls'));
define('MAIL_FROM_EMAIL', (string) (getenv('MAIL_FROM_EMAIL') ?: 'noreply@quincystore.cm'));
define('MAIL_FROM_NAME', (string) (getenv('MAIL_FROM_NAME') ?: SITE_NAME));