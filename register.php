<?php
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/csrf.php';
$error = consumeFlash('error');
$success = consumeFlash('success');
$selectedRole = (string) ($_GET['role'] ?? 'customer');
$roles = ['customer' => 'Customer', 'business_buyer' => 'Business buyer', 'supplier' => 'Supplier', 'manufacturer' => 'Manufacturer'];
if (!isset($roles[$selectedRole])) {
    $selectedRole = 'customer';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create account - <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
</head>
<body class="auth-page register-page">
<main class="auth-layout">
    <section class="auth-brand-panel">
        <a class="auth-brand" href="<?= e(BASE_URL) ?>/"><span class="brand-mark">Q</span><span><strong>QUINCY</strong><small>STORE</small></span></a>
        <div class="auth-brand-content">
            <p class="eyebrow">JOIN THE MARKETPLACE</p>
            <h1>Build better trade relationships.</h1>
            <p>Create one account to shop retail, source wholesale products, or grow your supplier business.</p>
            <div class="auth-benefits"><span><b>01</b> Discover trusted partners</span><span><b>02</b> Manage orders in one place</span><span><b>03</b> Trade with confidence</span></div>
        </div>
        <div class="auth-panel-footer">Cameroon&rsquo;s marketplace for business and retail.</div>
    </section>
    <section class="auth-form-panel">
        <div class="auth-form-wrap register-form-wrap">
            <div class="auth-heading"><p class="eyebrow">GET STARTED</p><h2>Create an account</h2><p>Set up your Quincy Store profile in a few steps.</p></div>
            <?php if ($error): ?><div class="auth-alert auth-alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="auth-alert auth-alert-success" role="status"><?= e($success) ?></div><?php endif; ?>
            <form class="auth-form register-form" method="post" action="<?= e(BASE_URL) ?>/api/auth/register-process.php">
                <?= csrfField() ?>
                <div class="register-fields">
                    <div class="field-group"><label for="first_name">First name</label><input id="first_name" name="first_name" required autocomplete="given-name" placeholder="Your first name"></div>
                    <div class="field-group"><label for="last_name">Last name</label><input id="last_name" name="last_name" required autocomplete="family-name" placeholder="Your last name"></div>
                    <div class="field-group register-wide"><label for="email">Email address</label><input id="email" type="email" name="email" required autocomplete="email" placeholder="you@example.com"></div>
                    <div class="field-group register-wide"><label for="phone">Phone number</label><input id="phone" type="tel" name="phone" required autocomplete="tel" placeholder="+237 6XX XXX XXX"></div>
                    <div class="field-group"><label for="password">Password</label><input id="register-password" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters"></div>
                    <div class="field-group"><label for="confirm_password">Confirm password</label><input id="confirm_password" type="password" name="confirm_password" required minlength="8" autocomplete="new-password" placeholder="Repeat your password"></div>
                    <div class="field-group register-wide"><label for="role">Account type</label><select id="role" name="role" required><?php foreach ($roles as $value => $label): ?><option value="<?= e($value) ?>" <?= $selectedRole === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
                </div>
                <label class="remember-option terms-option"><input type="checkbox" name="terms" value="1" required><span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</span></label>
                <button class="auth-submit register-submit" type="submit">Create account <span>&rarr;</span></button>
            </form>
            <p class="auth-signup">Already have an account? <a href="<?= e(BASE_URL) ?>/login.php">Sign in</a></p>
        </div>
    </section>
</main>
</body>
</html>