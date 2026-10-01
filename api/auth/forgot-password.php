<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/input-validator.php';
require_once ROOT_PATH . '/middleware/csrf.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(BASE_URL . '/forgot-password.php');
verifyCsrfToken();
$validator = new InputValidator($_POST);
$validator->required('email', 'Email')->email('email');
if ($validator->fails()) { flashMessage('error', $validator->firstError()); redirect(BASE_URL . '/forgot-password.php'); }
$email = strtolower(trim((string) $_POST['email']));
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT first_name, last_name, email FROM users WHERE email = ? AND status != "suspended" LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();
if ($user) {
    $rawToken = bin2hex(random_bytes(32));
    $db->prepare('DELETE FROM password_resets WHERE email = ? OR expires_at < NOW()')->execute([$email]);
    $db->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))')->execute([$email, hash('sha256', $rawToken)]);
    $resetUrl = BASE_URL . '/reset-password.php?token=' . urlencode($rawToken);
    if (!(new Mailer())->sendPasswordReset($email, trim($user['first_name'] . ' ' . $user['last_name']), $resetUrl)) error_log('Password reset email failed for ' . $email);
}
flashMessage('success', 'If an account matches that email, reset instructions have been sent.');
redirect(BASE_URL . '/forgot-password.php');
