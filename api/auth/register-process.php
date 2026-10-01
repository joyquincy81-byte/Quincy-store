<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/input-validator.php';
require_once ROOT_PATH . '/middleware/csrf.php';

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/register.php');
}

verifyCsrfToken();

// ── Validate ─────────────────────────────────────────────────
$v = new InputValidator($_POST);
$v->required('first_name', 'First name')
  ->required('last_name',  'Last name')
  ->required('email',      'Email')
  ->email('email')
  ->required('phone',      'Phone number')
  ->phone('phone')
  ->required('password',   'Password')
  ->minLength('password',  8, 'Password')
  ->required('confirm_password', 'Confirm password')
  ->matches('confirm_password', 'password', 'Passwords')
  ->required('role',  'Account type')
  ->inArray('role', ['customer','supplier','manufacturer','business_buyer'], 'Account type')
  ->custom('terms', fn($v) => !empty($v), 'You must accept the Terms of Service.');

if ($v->fails()) {
    flashMessage('error', $v->firstError());
    $qs = http_build_query([
        'first_name' => $v->str('first_name'),
        'last_name'  => $v->str('last_name'),
        'email'      => $v->str('email'),
        'phone'      => $v->str('phone'),
        'role'       => $v->str('role'),
    ]);
    redirect(BASE_URL . '/register.php?' . $qs);
}

$firstName = $v->str('first_name');
$lastName  = $v->str('last_name');
$email     = strtolower(trim($v->str('email')));
$phone     = $v->str('phone');
$role      = $v->str('role');
$password  = $_POST['password'];

$db = Database::getInstance()->getConnection();

// ── Check email uniqueness ────────────────────────────────────
$existStmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$existStmt->execute([$email]);
if ($existStmt->fetch()) {
    flashMessage('error', 'An account with that email address already exists. Please sign in or use a different email.');
    redirect(BASE_URL . '/register.php?' . http_build_query([
        'first_name' => $firstName,
        'last_name'  => $lastName,
        'phone'      => $phone,
        'role'       => $role,
    ]));
}

// ── Get role ID ───────────────────────────────────────────────
$roleStmt = $db->prepare('SELECT id FROM roles WHERE name = ? LIMIT 1');
$roleStmt->execute([$role]);
$roleRow = $roleStmt->fetch();

if (!$roleRow) {
    flashMessage('error', 'Invalid account type selected. Please try again.');
    redirect(BASE_URL . '/register.php');
}

// ── Create user inside a transaction ─────────────────────────
try {
    $db->beginTransaction();

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);

    // New users are 'active' for customers; 'pending' for suppliers/manufacturers
    $status = in_array($role, ['supplier', 'manufacturer']) ? 'pending' : 'active';

    $userStmt = $db->prepare(
        'INSERT INTO users
           (role_id, first_name, last_name, email, phone, password, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $userStmt->execute([
        $roleRow['id'],
        $firstName,
        $lastName,
        $email,
        $phone,
        $hashedPassword,
        $status,
    ]);
    $userId = (int) $db->lastInsertId();

    // ── Create role-specific profile row ─────────────────────
    switch ($role) {

        case 'supplier':
            $businessStmt = $db->prepare(
                'INSERT INTO businesses
                   (user_id, business_name, business_type, verification_status)
                 VALUES (?, ?, "supplier", "pending")'
            );
            $businessStmt->execute([$userId, trim($firstName . ' ' . $lastName) . "'s Business"]);
            $businessId = (int) $db->lastInsertId();

            $db->prepare(
                'INSERT INTO suppliers
                   (user_id, business_id, supplier_name, verification_status, status)
                 VALUES (?, ?, ?, "pending", "active")'
            )->execute([$userId, $businessId, trim($firstName . ' ' . $lastName)]);
            break;

        case 'manufacturer':
            $businessStmt = $db->prepare(
                'INSERT INTO businesses
                   (user_id, business_name, business_type, verification_status)
                 VALUES (?, ?, "manufacturer", "pending")'
            );
            $businessStmt->execute([$userId, trim($firstName . ' ' . $lastName) . "'s Business"]);
            $businessId = (int) $db->lastInsertId();

            $db->prepare(
                'INSERT INTO manufacturers
                   (user_id, business_id, manufacturer_name, verification_status, status)
                 VALUES (?, ?, ?, "pending", "active")'
            )->execute([$userId, $businessId, trim($firstName . ' ' . $lastName)]);
            break;

        case 'business_buyer':
            $db->prepare(
                'INSERT INTO businesses
                   (user_id, business_name, business_type, verification_status)
                 VALUES (?, ?, "buyer", "pending")'
            )->execute([$userId, trim($firstName . ' ' . $lastName) . "'s Business"]);
            break;

        // 'customer' and 'logistics' need no extra profile row
    }

    // ── Audit log ─────────────────────────────────────────────
    $db->prepare(
        'INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values, ip_address)
         VALUES (?, "user.register", "users", ?, ?, ?)'
    )->execute([
        $userId,
        $userId,
        json_encode(['email' => $email, 'role' => $role]),
        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    ]);

    $db->commit();

} catch (\Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Registration error: ' . $e->getMessage());
    flashMessage('error', 'Registration failed due to a server error. Please try again.');
    redirect(BASE_URL . '/register.php');
}

// ── Send welcome email (non-blocking — failure doesn't stop registration) ──
try {
    $mailer = new Mailer();
    $mailer->sendWelcome($email, $firstName, $role);
} catch (\Exception $e) {
    error_log('Welcome email failed: ' . $e->getMessage());
}

// ── Auto-login customers; send suppliers/manufacturers to pending notice ──
if ($role === 'customer') {

    // Fetch the full user row to set session
    $newUser = $db->prepare(
        'SELECT u.*, r.name AS role_name
         FROM users u JOIN roles r ON r.id = u.role_id
         WHERE u.id = ? LIMIT 1'
    );
    $newUser->execute([$userId]);
    $userRow = $newUser->fetch();

    setUserSession($userRow);

    // Update last_login
    $db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$userId]);

    flashMessage('success', 'Welcome to ' . SITE_NAME . ', ' . $firstName . '! Your account is ready.');
    redirect(BASE_URL . '/customer/dashboard.php');

} elseif ($role === 'business_buyer') {

    $newUser = $db->prepare(
        'SELECT u.*, r.name AS role_name
         FROM users u JOIN roles r ON r.id = u.role_id
         WHERE u.id = ? LIMIT 1'
    );
    $newUser->execute([$userId]);
    $userRow = $newUser->fetch();

    setUserSession($userRow);
    $db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$userId]);

    flashMessage('success', 'Welcome! Your business account has been created. Complete your profile to get started.');
    redirect(BASE_URL . '/business/dashboard.php');

} else {
    // Supplier / manufacturer — account pending verification
    flashMessage('success',
        'Your account has been created! Our team will review and verify your ' .
        ucfirst($role) . ' account within 24–48 hours. You will receive an email once approved.'
    );
    redirect(BASE_URL . '/login.php');
}
