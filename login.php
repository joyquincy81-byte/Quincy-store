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
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in - <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
    <script src="<?= e(BASE_URL) ?>/assets/js/auth.js" defer></script>
</head>
<body class="auth-page">
<main class="auth-layout">
    <section class="auth-brand-panel">
        <a class="auth-brand" href="<?= e(BASE_URL) ?>/"><span class="brand-mark">Q</span><span><strong>QUINCY</strong><small>STORE</small></span></a>
        <div class="auth-brand-content">
            <p class="eyebrow">WELCOME BACK</p>
            <h1>Trade with confidence.</h1>
            <p>Access your marketplace account and keep your business moving with trusted suppliers, simple sourcing, and secure orders.</p>
            <div class="auth-benefits">
                <span><b>01</b> Verified local suppliers</span>
                <span><b>02</b> Flexible B2B sourcing</span>
                <span><b>03</b> Secure, trackable orders</span>
            </div>
        </div>
        <div class="auth-panel-footer">Cameroon&rsquo;s marketplace for business and retail.</div>
    </section>
    <section class="auth-form-panel">
        <div class="auth-form-wrap">
            <div class="auth-mobile-brand"><a class="auth-brand" href="<?= e(BASE_URL) ?>/"><span class="brand-mark">Q</span><span><strong>QUINCY</strong><small>STORE</small></span></a></div>
            <div class="auth-heading">
                <p class="eyebrow">ACCOUNT ACCESS</p>
                <h2>Sign in to Quincy Store</h2>
                <p>Enter your details to continue.</p>
            </div>
            <?php if ($error): ?><div class="auth-alert auth-alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="auth-alert auth-alert-success" role="status"><?= e($success) ?></div><?php endif; ?>
            <form id="login-form" class="auth-form" method="post" action="<?= e(BASE_URL) ?>/api/auth/login-process.php">
                <?= csrfField() ?>
                <div class="field-group"><label for="email">Email address</label><input id="email" type="email" name="email" required autocomplete="email" placeholder="you@example.com"></div>
                <div class="field-group"><div class="field-label-row"><label for="password">Password</label><a href="<?= e(BASE_URL) ?>/forgot-password.php">Forgot password?</a></div><div class="password-input"><input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password"><button type="button" class="password-toggle" data-password-toggle aria-label="Show password">Show</button></div></div>
                <label class="remember-option"><input type="checkbox" name="remember" value="1"><span>Keep me signed in</span></label>
                <div class="slider-captcha" data-slider-captcha>
                    <div class="slider-captcha-heading"><span>Security check</span><small data-captcha-status>Slide to verify</small></div>
                    <div class="slider-track" data-slider-track><span class="slider-progress" data-slider-progress></span><span class="slider-label">Slide the arrow to the right</span><button type="button" class="slider-handle" data-slider-handle aria-label="Slide to verify" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">&#8594;</button></div>
                    <input type="hidden" name="human_verified" value="0" data-human-verified>
                </div>
                <button class="auth-submit" type="submit" disabled data-login-submit>Sign in <span>&rarr;</span></button>
            </form>
            <p class="auth-signup">New to Quincy Store? <a href="<?= e(BASE_URL) ?>/register.php">Create an account</a></p>
            <p class="auth-legal">By signing in, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</p>
        </div>
    </section>
</main>
</body>
</html>