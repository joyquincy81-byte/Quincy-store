<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';

function requireAuth(): void
{
    $userId = $_SESSION['user_id'] ?? null;
    $role = $_SESSION['role'] ?? null;
    $validUserId = (is_int($userId) || (is_string($userId) && ctype_digit($userId)))
        && (int) $userId > 0;
    if (!$validUserId || !is_string($role) || trim($role) === '') {
        authFailure(401, 'Authentication required.', true);
    }
}

/**
 * Return JSON errors to API/AJAX callers, while keeping browser navigation safe.
 */
function authWantsJson(): bool
{
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
    return str_contains($accept, 'application/json')
        || $requestedWith === 'xmlhttprequest'
        || str_starts_with((string) ($_SERVER['REQUEST_URI'] ?? ''), BASE_URL . '/api/');
}

function authFailure(int $status, string $message, bool $loginRedirect = false): never
{
    http_response_code($status);
    if (authWantsJson()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($loginRedirect) {
        flashMessage('error', 'Please sign in to continue.');
        redirect(BASE_URL . '/login.php');
    }

    exit($message);
}

function requireRole(string ...$roles): void
{
    requireAuth();
    if (!in_array(currentRole(), $roles, true)) {
        authFailure(403, 'Forbidden.');
    }
}