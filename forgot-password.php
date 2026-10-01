<?php
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/csrf.php';
$error = consumeFlash('error');
$success = consumeFlash('success');
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Forgot password - <?= e(SITE_NAME) ?></title><link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css"></head>
<body class="auth-page"><main class="auth-layout">
<section class="auth-brand-panel"><a class="auth-brand" href="<?= e(BASE_URL) ?>/"><span class="brand-mark">Q</span><span><strong>QUINCY</strong><small>STORE</small></span></a><div class="auth-brand-content"><p class="eyebrow">ACCOUNT RECOVERY</p><h1>Get back to business.</h1><p>Reset your password securely and return to sourcing, selling, and managing your Quincy Store account.</p></div><div class="auth-panel-footer">Your account security matters to us.</div></section>
<section class="auth-form-panel"><div class="auth-form-wrap recovery-form-wrap"><div class="auth-heading"><p class="eyebrow">PASSWORD RESET</p><h2>Forgot your password?</h2><p>Enter the email address linked to your account and we&rsquo;ll send reset instructions.</p></div><?php if ($error): ?><div class="auth-alert auth-alert-error" role="alert"><?= e($error) ?></div><?php endif; ?><?php if ($success): ?><div class="auth-alert auth-alert-success" role="status"><?= e($success) ?></div><?php endif; ?><form class="auth-form" method="post" action="<?= e(BASE_URL) ?>/api/auth/forgot-password.php"><?= csrfField() ?><div class="field-group"><label for="email">Email address</label><input id="email" type="email" name="email" required autocomplete="email" placeholder="you@example.com"></div><button class="auth-submit" type="submit">Send reset link <span>&rarr;</span></button></form><p class="auth-signup"><a href="<?= e(BASE_URL) ?>/login.php">&larr; Back to sign in</a></p></div></section>
</main></body></html>