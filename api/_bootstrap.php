<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/csrf.php';

header('Content-Type: application/json; charset=utf-8');

function apiResponse(mixed $data = null, int $status = 200): never
{
    http_response_code($status);
    echo json_encode(['success' => $status < 400, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}
function apiError(string $message, int $status = 400): never
{
    http_response_code($status);
    echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}
function apiMethod(string ...$methods): void
{
    if (!in_array($_SERVER['REQUEST_METHOD'] ?? '', $methods, true)) {
        header('Allow: ' . implode(', ', $methods));
        apiError('Method not allowed', 405);
    }
}
function apiInput(): array
{
    $raw = file_get_contents('php://input');
    $json = json_decode($raw ?: '', true);
    return is_array($json) ? $json : $_POST;
}
function apiAuth(array $roles = []): int
{
    if (!isLoggedIn() || !currentUserId()) apiError('Authentication required', 401);
    if ($roles && !in_array((string) currentRole(), $roles, true)) apiError('Forbidden', 403);
    return (int) currentUserId();
}
function apiCsrf(): void
{
    if (isLoggedIn() || isset($_SERVER['HTTP_ORIGIN'], $_SERVER['HTTP_REFERER'])) {
        $token = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? ''));
        if ($token === '' || !hash_equals((string)($_SESSION['csrf_token'] ?? ''), $token)) apiError('Invalid CSRF token', 419);
    }
}
function apiDb(): PDO { return Database::getInstance()->getConnection(); }
function apiId(mixed $v): int { if (!is_numeric($v) || (int)$v < 1) apiError('Invalid identifier', 422); return (int)$v; }
function apiException(Throwable $e): never { error_log('API error: ' . $e->getMessage()); apiError('Request could not be completed', 500); }
