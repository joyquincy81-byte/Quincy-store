<?php

declare(strict_types=1);

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function flashMessage(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

function consumeFlash(string $type): ?string
{
    $message = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $message;
}

function getDashboardUrl(string $role): string
{
    return match ($role) {
        'admin' => BASE_URL . '/admin/dashboard.php',
        'supplier' => BASE_URL . '/supplier/dashboard.php',
        'manufacturer' => BASE_URL . '/manufacturer/dashboard.php',
        'business_buyer' => BASE_URL . '/business/dashboard.php',
        'logistics' => BASE_URL . '/logistics/dashboard.php',
        default => BASE_URL . '/customer/dashboard.php',
    };
}

function formatPrice(float|int|string $amount): string
{
    return number_format((float) $amount, 0, '.', ' ') . ' FCFA';
}