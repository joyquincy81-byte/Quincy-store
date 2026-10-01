<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/input-validator.php';
require_once ROOT_PATH . '/middleware/csrf.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/login.php');
}

verifyCsrfToken();

$v = new InputValidator($_POST);
$v->required('email')->email('email')->required('password');

if ($v->fails()) {
    flashMessage('error', $v->firstError());
    redirect(BASE_URL . '/login.php');
}

$email    = trim(strtolower($_POST['email']));
$password = $_POST['password'];
$remember = isset($_POST['remember']);

// ── Rate limit check ─────────────────────────────────────────
$db      = Database::getInstance()->getConnection();
$ip      = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

$rlStmt  = $db->prepare(
    'SELECT attempts, last_attempt FROM rate_limits
     WHERE ip_address = ? AND action = "login" LIMIT 1'
);
$rlStmt->execute([$ip]);
$rl = $rlStmt->fetch();

if ($rl) {
    $windowStart = time() - RATE_LIMIT_WINDOW;
    if ($rl['attempts'] >= RATE_LIMIT_ATTEMPTS &&
        strtotime($rl['last_attempt']) > $windowStart) {
        flashMessage('error', 'Too many failed login attempts. Please wait 10 minutes and try again.');
        redirect(BASE_URL . '/login.php');
    }
}

// ── Find user ────────────────────────────────────────────────
$userModel = new User();
$user      = $userModel->findByEmail($email);

if (!$user || !password_verify($password, $user['password'])) {
    // Record failed attempt
    $db->prepare(
        'INSERT INTO rate_limits (ip_address, action, attempts)
         VALUES (?, "login", 1)
         ON DUPLICATE KEY UPDATE
           attempts = IF(last_attempt < DATE_SUB(NOW(), INTERVAL ? SECOND), 1, attempts + 1),
           last_attempt = NOW()'
    )->execute([$ip, RATE_LIMIT_WINDOW]);

    flashMessage('error', 'Incorrect email or password. Please try again.');
    redirect(BASE_URL . '/login.php');
}

// ── Check status ─────────────────────────────────────────────
if ($user['status'] === 'suspended') {
    flashMessage('error', 'Your account has been suspended. Please contact support.');
    redirect(BASE_URL . '/login.php');
}

if ($user['status'] === 'inactive' || $user['status'] === 'pending') {
    flashMessage('error', 'Your account is not yet active. Please check your email for a verification link or contact support.');
    redirect(BASE_URL . '/login.php');
}

// ── Login success ────────────────────────────────────────────
// Clear rate limit on success
$db->prepare('DELETE FROM rate_limits WHERE ip_address = ? AND action = "login"')
   ->execute([$ip]);

// Set session
setUserSession($user);

// Update last_login
$userModel->setLastLogin($user['id']);

// Remember me — extend session cookie
if ($remember) {
    $cookieParams = session_get_cookie_params();
    setcookie(
        session_name(),
        session_id(),
        time() + (86400 * 30),  // 30 days
        $cookieParams['path'],
        $cookieParams['domain'],
        $cookieParams['secure'],
        $cookieParams['httponly']
    );
}

// Audit log
$auditLog = new AuditLog();
$auditLog->write('user.login', 'users', $user['id'],
                 null, ['email' => $email, 'role' => $user['role_name']]);

// Redirect to role dashboard
redirect(getDashboardUrl($user['role_name']));
