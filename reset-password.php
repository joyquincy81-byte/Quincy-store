<?php
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/csrf.php';
$token = preg_replace('/[^a-f0-9]/', '', (string) ($_GET['token'] ?? ''));
$valid = false;
if (strlen($token) === 64) {
    $stmt = Database::getInstance()->getConnection()->prepare('SELECT id FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1');
    $stmt->execute([hash('sha256', hex2bin($token))]);
    $valid = (bool) $stmt->fetch();
}
$error = consumeFlash('error');
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Reset password - <?= e(SITE_NAME) ?></title><link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css"></head>
<body class="auth-page"><main class="auth-layout">
<section class="auth-brand-panel"><a class="auth-brand" href="<?= e(BASE_URL) ?>/"><span class="brand-mark">Q</span><span><strong>QUINCY</strong><small>STORE</small></span></a><div class="auth-brand-content"><p class="eyebrow">SECURE ACCESS</p><h1>Choose a new password.</h1><p>Create a strong password to protect your Quincy Store account.</p></div><div class="auth-panel-footer">Keep your account protected.</div></section>
<section class="auth-form-panel"><div class="auth-form-wrap recovery-form-wrap"><div class="auth-heading"><p class="eyebrow">PASSWORD RESET</p><h2>Set a new password</h2><p>Use at least 8 characters.</p></div><?php if ($error): ?><div class="auth-alert auth-alert-error" role="alert"><?= e($error) ?></div><?php endif; ?><?php if ($valid): ?><form class="auth-form" method="post" action="<?= e(BASE_URL) ?>/api/auth/reset-password.php"><?= csrfField() ?><input type="hidden" name="token" value="<?= e($token) ?>"><div class="field-group"><label for="password">New password</label><input id="password" type="password" name="password" required minlength="8" autocomplete="new-password"></div><div class="field-group"><label for="confirm_password">Confirm password</label><input type="password" name="confirm_password" required minlength="8" autocomplete="new-password"></div><button class="auth-submit" type="submit">Reset password <span>&rarr;</span></button></form><?php else: ?><div class="auth-alert auth-alert-error">This reset link is invalid or has expired.</div><a class="auth-submit recovery-link" href="<?= e(BASE_URL) ?>/forgot-password.php">Request a new link</a><?php endif; ?><p class="auth-signup"><a href="<?= e(BASE_URL) ?>/login.php">&larr; Back to sign in</a></p></div></section>
</main></body></html>