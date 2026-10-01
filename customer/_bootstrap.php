<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/csrf.php';
require_once ROOT_PATH . '/middleware/customer.php';

$customerId = (int) currentUserId();
$customer = $_SESSION['user'] ?? ['id' => $customerId, 'first_name' => 'Customer', 'last_name' => '', 'email' => ''];
$db = null;
try {
    $db = Database::getInstance()->getConnection();
} catch (PDOException $exception) {
    $db = null;
}

function customerQuery(string $sql, array $params = []): ?PDOStatement
{
    global $db;
    if (!$db) return null;
    try {
        $statement = $db->prepare($sql);
        $statement->execute($params);
        return $statement;
    } catch (PDOException $exception) {
        return null;
    }
}

function customerRows(string $sql, array $params = []): array
{
    $statement = customerQuery($sql, $params);
    return $statement ? ($statement->fetchAll() ?: []) : [];
}

function customerScalar(string $sql, array $params = [], mixed $fallback = 0): mixed
{
    $statement = customerQuery($sql, $params);
    return $statement ? ($statement->fetchColumn() ?? $fallback) : $fallback;
}

function customerPostCsrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') verifyCsrfToken();
}

function customerName(): string
{
    global $customer;
    return trim((string)($customer['first_name'] ?? '') . ' ' . (string)($customer['last_name'] ?? '')) ?: 'Customer';
}

function customerPageStart(string $title, string $active = ''): void
{
    global $customer;
    $links = ['Dashboard'=>'dashboard.php','Orders'=>'orders.php','Wishlist'=>'wishlist.php','Addresses'=>'addresses.php','Reviews'=>'reviews.php','Support'=>'support.php'];
    ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title) ?> | <?= e(SITE_NAME) ?></title><link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
    <style>
    .cw{min-height:100vh;background:#f5f8fc;color:#14213d}.cw a{color:inherit}.cw-header{background:#0b2147;color:#fff}.cw-top{height:34px;background:#07152f;color:#c9d8eb;font-size:12px}.cw-top,.cw-nav{display:flex;align-items:center}.cw-top{justify-content:space-between;padding:0 max(20px,calc((100% - 1180px)/2))}.cw-nav{min-height:70px;gap:24px;max-width:1180px;margin:auto;padding:12px 20px}.cw-brand{font-weight:800;letter-spacing:1px;white-space:nowrap}.cw-brand b{display:inline-grid;place-items:center;background:#e1ae45;color:#0b2147;border-radius:6px;width:32px;height:32px;margin-right:8px}.cw-nav nav{display:flex;gap:4px;overflow:auto;flex:1}.cw-nav nav a{padding:10px 12px;color:#dce8f7;font-size:13px;white-space:nowrap;border-radius:4px}.cw-nav nav a.active,.cw-nav nav a:hover{background:#1b4b86;color:#fff}.cw-user{white-space:nowrap;font-size:13px}.cw-main{max-width:1180px;margin:auto;padding:30px 20px 70px}.cw-title{display:flex;justify-content:space-between;align-items:end;gap:18px;margin-bottom:24px}.cw-title h1{margin:0;color:#07152f;font-size:30px}.cw-title p{margin:5px 0 0;color:#64748b}.cw-card{background:#fff;border:1px solid #d8e1ed;border-radius:8px;padding:22px;box-shadow:0 6px 18px #07152f0a}.cw-grid{display:grid;gap:18px}.cw-grid-2{grid-template-columns:repeat(2,minmax(0,1fr))}.cw-grid-3{grid-template-columns:repeat(3,minmax(0,1fr))}.cw-grid-4{grid-template-columns:repeat(4,minmax(0,1fr))}.cw-stat strong{display:block;font-size:27px;color:#0b2147}.cw-stat span,.muted{color:#64748b;font-size:13px}.cw-table{width:100%;border-collapse:collapse}.cw-table th,.cw-table td{text-align:left;padding:13px 10px;border-bottom:1px solid #e7edf5;font-size:14px}.cw-table th{color:#64748b;font-size:12px;text-transform:uppercase}.cw-btn{display:inline-block;padding:10px 16px;border:0;border-radius:4px;background:#1b4b86;color:#fff!important;font-weight:700;cursor:pointer}.cw-btn.alt{background:#eaf2fb;color:#123566!important}.cw-btn.gold{background:#e1ae45;color:#07152f!important}.cw-form{display:grid;gap:16px}.cw-form label{display:grid;gap:6px;color:#334155;font-size:13px;font-weight:700}.cw-form input,.cw-form select,.cw-form textarea{width:100%;padding:11px 12px;border:1px solid #cbd5e1;border-radius:4px;font:inherit}.cw-form textarea{min-height:110px;resize:vertical}.cw-alert{padding:12px 15px;margin-bottom:18px;border-radius:5px;background:#eefaf4;color:#176447;border:1px solid #bce6d0}.cw-alert.error{background:#fff0f1;color:#8b2635;border-color:#f1c6cc}.cw-empty{text-align:center;padding:42px 20px;color:#64748b;border:1px dashed #cbd5e1;border-radius:6px}.cw-actions{display:flex;gap:10px;flex-wrap:wrap}.badge{display:inline-block;padding:4px 9px;border-radius:20px;background:#eaf2fb;color:#1b4b86;font-size:12px}.cw-footer{background:#07152f;color:#a8bfdd;padding:25px;text-align:center;font-size:12px}@media(max-width:760px){.cw-grid-2,.cw-grid-3,.cw-grid-4{grid-template-columns:1fr 1fr}.cw-nav{flex-wrap:wrap}.cw-nav nav{order:3;flex-basis:100%}.cw-title{align-items:start;flex-direction:column}}@media(max-width:480px){.cw-grid-2,.cw-grid-3,.cw-grid-4{grid-template-columns:1fr}.cw-main{padding:22px 14px 50px}.cw-card{padding:16px;overflow-x:auto}}
    </style></head><body class="cw"><header class="cw-header"><div class="cw-top"><span>Cameroon's trusted marketplace</span><span><a href="<?= e(BASE_URL) ?>/">Marketplace</a> &nbsp;|&nbsp; <a href="<?= e(BASE_URL) ?>/logout.php">Sign out</a></span></div><div class="cw-nav"><a class="cw-brand" href="dashboard.php"><b>Q</b>QUINCY <small>STORE</small></a><nav><?php foreach($links as $label=>$url): ?><a class="<?= $active === $label ? 'active' : '' ?>" href="<?= e($url) ?>"><?= e($label) ?></a><?php endforeach; ?></nav><a class="cw-user" href="profile.php"><?= e(customerName()) ?> ▾</a></div></header><main class="cw-main"><?php
}
function customerPageEnd(): void { ?></main><footer class="cw-footer">© <?= date('Y') ?> Quincy Store · Secure customer workbench</footer></body></html><?php }
