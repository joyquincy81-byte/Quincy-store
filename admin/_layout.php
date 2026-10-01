<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/admin.php';

$adminPage = $adminPage ?? 'overview';
$adminTitle = $adminTitle ?? ucfirst($adminPage);
$adminError = null;
$adminRows = [];
$adminStats = [];
$adminDefinitions = [
    'overview' => ['title' => 'Dashboard', 'stats' => [['label'=>'Users','sql'=>'SELECT COUNT(*) FROM users'],['label'=>'Products','sql'=>"SELECT COUNT(*) FROM products WHERE status='active'"],['label'=>'Orders','sql'=>'SELECT COUNT(*) FROM orders'],['label'=>'Paid sales','sql'=>"SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status='paid'"]], 'sql'=>"SELECT order_number AS reference,total_amount,order_status,created_at FROM orders ORDER BY created_at DESC LIMIT 12"],
    'users' => ['title'=>'Users', 'stats'=>[['label'=>'Registered users','sql'=>'SELECT COUNT(*) FROM users'],['label'=>'Active accounts','sql'=>"SELECT COUNT(*) FROM users WHERE status='active'"]], 'sql'=>'SELECT id,first_name,last_name,email,role_id,status,created_at FROM users ORDER BY created_at DESC LIMIT 100'],
    'businesses' => [
    'title'=>'Businesses',
    'stats'=>[
        ['label'=>'Businesses','sql'=>'SELECT COUNT(*) FROM businesses'],
        ['label'=>'Verified','sql'=>"SELECT COUNT(*) FROM businesses WHERE verification_status='verified'"]
    ],
    'sql'=>'SELECT id,business_name,business_type,verification_status,created_at FROM businesses ORDER BY created_at DESC LIMIT 100'
],
'suppliers' => ['title'=>'Suppliers', 'stats'=>[['label'=>'Suppliers','sql'=>'SELECT COUNT(*) FROM suppliers'],['label'=>'Active suppliers','sql'=>"SELECT COUNT(*) FROM suppliers WHERE status='active'"]], 'sql'=>'SELECT id,supplier_name,verification_status,rating,status,created_at FROM suppliers ORDER BY created_at DESC LIMIT 100'],
    'manufacturers' => ['title'=>'Manufacturers', 'stats'=>[['label'=>'Manufacturers','sql'=>'SELECT COUNT(*) FROM manufacturers'],['label'=>'Active','sql'=>"SELECT COUNT(*) FROM manufacturers WHERE status='active'"]], 'sql'=>'SELECT id,manufacturer_name,verification_status,status,created_at FROM manufacturers ORDER BY created_at DESC LIMIT 100'],
    'products' => ['title'=>'Products', 'stats'=>[['label'=>'Total products','sql'=>'SELECT COUNT(*) FROM products'],['label'=>'Live listings','sql'=>"SELECT COUNT(*) FROM products WHERE status='active'"],['label'=>'Drafts','sql'=>"SELECT COUNT(*) FROM products WHERE status<>'active'"]], 'sql'=>'SELECT p.id,p.name,p.sku,p.retail_price,p.status,c.name AS category FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.updated_at DESC LIMIT 100'],
    'categories' => ['title'=>'Categories', 'stats'=>[['label'=>'Categories','sql'=>'SELECT COUNT(*) FROM categories'],['label'=>'Live categories','sql'=>"SELECT COUNT(*) FROM categories WHERE status='active'"]], 'sql'=>'SELECT id,name,status,created_at FROM categories ORDER BY name LIMIT 100'],
    'orders' => ['title'=>'Orders', 'stats'=>[['label'=>'Orders','sql'=>'SELECT COUNT(*) FROM orders'],['label'=>'Open orders','sql'=>"SELECT COUNT(*) FROM orders WHERE order_status NOT IN ('completed','cancelled')"]], 'sql'=>'SELECT order_number,total_amount,payment_status,order_status,created_at FROM orders ORDER BY created_at DESC LIMIT 100'],
    'payments' => ['title'=>'Payments', 'stats'=>[['label'=>'Payments','sql'=>'SELECT COUNT(*) FROM payments'],['label'=>'Successful','sql'=>"SELECT COUNT(*) FROM payments WHERE status='successful'"]], 'sql'=>'SELECT id,amount,payment_method,status,transaction_reference,created_at FROM payments ORDER BY created_at DESC LIMIT 100'],
    'quotations' => ['title'=>'Quotations / RFQs', 'stats'=>[['label'=>'RFQs','sql'=>'SELECT COUNT(*) FROM quotation_requests'],['label'=>'Open RFQs','sql'=>"SELECT COUNT(*) FROM quotation_requests WHERE status='pending'"]], 'sql'=>'SELECT id,buyer_id,supplier_id,status,delivery_location,created_at FROM quotation_requests ORDER BY created_at DESC LIMIT 100'],
    'negotiations' => ['title'=>'Negotiations', 'stats'=>[['label'=>'Negotiations','sql'=>'SELECT COUNT(*) FROM negotiations'],['label'=>'Open','sql'=>"SELECT COUNT(*) FROM negotiations WHERE status='open'"]], 'sql'=>'SELECT id,status,created_at FROM negotiations ORDER BY created_at DESC LIMIT 100'],
    'shipments' => ['title'=>'Shipments', 'stats'=>[['label'=>'Shipments','sql'=>'SELECT COUNT(*) FROM shipments'],['label'=>'In transit','sql'=>"SELECT COUNT(*) FROM shipments WHERE status='in_transit'"]], 'sql'=>'SELECT id,tracking_number,status,carrier,created_at FROM shipments ORDER BY created_at DESC LIMIT 100'],
    'reviews' => ['title'=>'Reviews', 'stats'=>[['label'=>'Reviews','sql'=>'SELECT COUNT(*) FROM reviews'],['label'=>'Pending moderation','sql'=>"SELECT COUNT(*) FROM reviews WHERE status='pending'"]], 'sql'=>'SELECT id,rating,status,created_at FROM reviews ORDER BY created_at DESC LIMIT 100'],
    'promotions' => ['title'=>'Promotions', 'stats'=>[['label'=>'Promotions','sql'=>'SELECT COUNT(*) FROM promotions'],['label'=>'Active','sql'=>"SELECT COUNT(*) FROM promotions WHERE status='active'"]], 'sql'=>'SELECT id,name,discount_type,discount_value,status,start_date,end_date FROM promotions ORDER BY start_date DESC LIMIT 100'],
    'support' => ['title'=>'Support desk', 'stats'=>[['label'=>'Tickets','sql'=>'SELECT COUNT(*) FROM support_tickets'],['label'=>'Open tickets','sql'=>"SELECT COUNT(*) FROM support_tickets WHERE status='open'"]], 'sql'=>'SELECT id,subject,status,priority,created_at FROM support_tickets ORDER BY created_at DESC LIMIT 100'],
    'reports' => ['title'=>'Reports & analytics', 'stats'=>[['label'=>'Gross sales','sql'=>"SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status='paid'"],['label'=>'Orders','sql'=>'SELECT COUNT(*) FROM orders'],['label'=>'Avg order','sql'=>'SELECT COALESCE(AVG(total_amount),0) FROM orders']], 'sql'=>"SELECT DATE(created_at) AS day,COUNT(*) AS orders,COALESCE(SUM(total_amount),0) AS sales FROM orders GROUP BY DATE(created_at) ORDER BY day DESC LIMIT 30"],
    'settings' => ['title'=>'Settings', 'stats'=>[], 'sql'=>'SELECT `key` AS setting_key, `value` AS setting_value, updated_at FROM settings ORDER BY `key` LIMIT 100'],
];
$definition = $adminDefinitions[$adminPage] ?? $adminDefinitions['overview'];
$adminTitle = $definition['title'];
try {
    $db = Database::getInstance()->getConnection();
    if ($adminPage === 'products' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
        $stmt = $db->prepare('UPDATE products SET status=? WHERE id=?');
        $stmt->execute([substr((string) $_POST['status'], 0, 30), (int) $_POST['id']]);
    }
    foreach ($definition['stats'] as $stat) {
        $stmt = $db->prepare($stat['sql']); $stmt->execute(); $adminStats[] = ['label'=>$stat['label'], 'value'=>$stmt->fetchColumn()];
    }
    $stmt = $db->prepare($definition['sql']); $stmt->execute();
    $adminRows = $stmt->fetchAll();
} catch (PDOException $exception) {
    error_log('Admin page unavailable: ' . $exception->getMessage());
    $adminError = 'Some data is temporarily unavailable. Please try again shortly.';
}
$nav = ['overview'=>'Dashboard','users'=>'Users','businesses'=>'Businesses','products'=>'Products','categories'=>'Categories','suppliers'=>'Suppliers','manufacturers'=>'Manufacturers','orders'=>'Orders','payments'=>'Payments','quotations'=>'RFQ centre','negotiations'=>'Negotiations','shipments'=>'Shipments','reviews'=>'Reviews','promotions'=>'Promotions','support'=>'Support','reports'=>'Reports','settings'=>'Settings'];
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($adminTitle) ?> Â· <?= e(SITE_NAME) ?></title><link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css"><style>
body.admin-suite{margin:0;background:#f4f6fa;color:#13213f;font:14px Arial,sans-serif}.admin-shell{display:flex;min-height:100vh}.admin-side{width:235px;background:#081a3a;color:#fff;padding:22px 14px;flex:none}.admin-brand{font-size:20px;font-weight:bold;letter-spacing:2px;padding:8px 12px 26px}.admin-brand small{display:block;font-size:10px;color:#99acd0;letter-spacing:1px;margin-top:4px}.admin-side a{display:block;color:#c9d5eb;text-decoration:none;padding:10px 12px;border-radius:4px;margin:2px 0}.admin-side a:hover,.admin-side a.active{background:#1b427c;color:#fff}.admin-main{flex:1;padding:28px 34px;max-width:1400px}.admin-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}.admin-top h1{margin:3px 0;font-size:30px}.admin-muted{color:#687794}.admin-search{padding:9px;border:1px solid #ccd5e5;border-radius:3px}.admin-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin:20px 0}.admin-kpi,.admin-card{background:#fff;border:1px solid #e0e6f0;border-radius:5px;padding:18px;box-shadow:0 2px 8px #14264d0a}.admin-kpi span{display:block;color:#66738b;font-size:12px;text-transform:uppercase}.admin-kpi strong{display:block;font-size:25px;margin-top:8px}.admin-card{overflow:auto}.admin-card h2{margin-top:0;font-size:18px}.admin-table{width:100%;border-collapse:collapse;min-width:620px}.admin-table th{text-align:left;background:#eef2f8;color:#4a5b78;font-size:12px;text-transform:uppercase}.admin-table th,.admin-table td{padding:12px;border-bottom:1px solid #e8ecf3}.admin-pill{padding:4px 9px;border-radius:12px;background:#e8effa;color:#1b4b88;font-size:12px}.admin-pill.ok{background:#e2f5ed;color:#14734a}.admin-pill.warn{background:#fff3db;color:#976415}.admin-chart{height:90px;display:flex;align-items:end;gap:7px;margin:12px 0}.admin-chart i{display:block;background:#2e64a4;min-height:8px;flex:1}.admin-actions{display:flex;gap:10px;align-items:center}.admin-actions select,.admin-actions button{padding:6px;border:1px solid #bbc8dc;background:#fff}.admin-notice{background:#fff5df;border-left:4px solid #d49628;padding:12px;margin-bottom:18px}@media(max-width:800px){.admin-side{width:52px;padding:12px 5px}.admin-brand{font-size:0}.admin-side a{font-size:0;text-align:center}.admin-main{padding:18px}.admin-top{display:block}}
</style></head><body class="admin-suite"><div class="admin-shell"><aside class="admin-side"><div class="admin-brand">Q<small>ADMIN CONTROL</small></div><?php foreach($nav as $key=>$label): ?><a class="<?= $adminPage===$key?'active':'' ?>" href="<?= e(BASE_URL) ?>/admin/<?= $key==='overview'?'dashboard':$key ?>.php"><?= e($label) ?></a><?php endforeach; ?><a href="<?= e(BASE_URL) ?>/logout.php">Sign out</a></aside><main class="admin-main"><div class="admin-top"><div><div class="admin-muted">Admin / <?= e($adminTitle) ?></div><h1><?= e($adminTitle) ?></h1></div><form method="get"><input class="admin-search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search records"></form></div><?php if($adminError): ?><div class="admin-notice"><?= e($adminError) ?></div><?php endif; ?><section class="admin-kpis"><?php foreach($adminStats as $stat): ?><article class="admin-kpi"><span><?= e($stat['label']) ?></span><strong><?= is_numeric($stat['value']) && (float)$stat['value']>999 ? e(number_format((float)$stat['value'])) : e($stat['value']) ?></strong></article><?php endforeach; ?></section><section class="admin-card"><h2><?= $adminPage==='quotations'?'Inquiry and RFQ pipeline':($adminPage==='products'?'Product catalogue status':($adminPage==='reports'?'Trend summary':'Latest '.$adminTitle)) ?></h2><?php if($adminPage==='reports'): ?><div class="admin-chart"><?php $max=1;foreach($adminRows as $r){$max=max($max,(float)($r['sales']??0));} foreach(array_reverse($adminRows) as $r): ?><i style="height:<?= max(8,(int)(((float)($r['sales']??0)/$max)*82)) ?>px" title="<?= e($r['day']??'') ?>"></i><?php endforeach; ?></div><?php endif; ?><table class="admin-table"><thead><tr><?php if($adminRows): foreach(array_keys($adminRows[0]) as $head): ?><th><?= e(ucwords(str_replace('_',' ',$head))) ?></th><?php endforeach; ?><?php else: ?><th>Information</th><?php endif; ?></tr></thead><tbody><?php if(!$adminRows): ?><tr><td><?= $adminError?'Data could not be loaded.':'No records found.' ?></td></tr><?php else: foreach($adminRows as $row): ?><tr><?php foreach($row as $key=>$value): ?><td><?php if($key==='status'): ?><span class="admin-pill <?= in_array((string)$value,['active','completed','paid','open'],true)?'ok':(in_array((string)$value,['pending','negotiating','in_transit'],true)?'warn':'') ?>"><?= e(ucwords(str_replace('_',' ',(string)$value))) ?></span><?php elseif($adminPage==='products' && $key==='id'): ?><?= e($value) ?><?php else: ?><?= e($value) ?><?php endif; ?></td><?php endforeach; ?><?php if($adminPage==='products'): ?><td><form class="admin-actions" method="post"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><select name="status"><option value="active">Active</option><option value="draft">Draft</option><option value="archived">Archived</option></select><button>Update</button></form></td><?php endif; ?></tr><?php endforeach; endif; ?></tbody></table></section></main></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.querySelector('.admin-search');
    const table = document.querySelector('.admin-table');

    if (!search || !table) return;

    search.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(function (row) {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });
});
</script>

</body></html>

