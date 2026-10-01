<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/session.php';

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])
        || !preg_match('/\A[0-9a-f]{64}\z/', $_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

function verifyCsrfToken(): void
{
    $submitted = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $token = is_string($submitted) ? $submitted : '';
    $expected = $_SESSION['csrf_token'] ?? null;
    if (!is_string($expected) || $expected === '' || $token === '' || !hash_equals($expected, $token)) {
        http_response_code(419);
        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        $isJson = str_contains($accept, 'application/json')
            || str_starts_with((string) ($_SERVER['REQUEST_URI'] ?? ''), BASE_URL . '/api/');
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        exit('Invalid CSRF token.');
    }
}