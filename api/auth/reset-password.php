<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/input-validator.php';
require_once ROOT_PATH . '/middleware/csrf.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(BASE_URL . '/forgot-password.php');
verifyCsrfToken();
$token = preg_replace('/[^a-f0-9]/', '', (string) ($_POST['token'] ?? ''));
$validator = new InputValidator($_POST);
$validator->required('password', 'Password')->minLength('password', 8, 'Password')->required('confirm_password', 'Confirm password')->matches('confirm_password', 'password', 'Passwords');
if (strlen($token) !== 64 || $validator->fails()) { flashMessage('error', strlen($token) !== 64 ? 'This reset link is invalid or has expired.' : $validator->firstError()); redirect(BASE_URL . '/reset-password.php?token=' . urlencode($token)); }
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1');
$stmt->execute([hash('sha256', hex2bin($token))]);
$reset = $stmt->fetch();
if (!$reset) { flashMessage('error', 'This reset link is invalid or has expired.'); redirect(BASE_URL . '/forgot-password.php'); }
try {
    $db->beginTransaction();
    $db->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?')->execute([password_hash((string) $_POST['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]), $reset['email']]);
    $db->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$reset['email']]);
    $db->commit();
} catch (Throwable $exception) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Password reset failed: ' . $exception->getMessage());
    flashMessage('error', 'We could not reset your password. Please try again.');
    redirect(BASE_URL . '/reset-password.php?token=' . urlencode($token));
}
flashMessage('success', 'Your password has been reset. You can now sign in.');
redirect(BASE_URL . '/login.php');
