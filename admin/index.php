<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/admin.php';

$db = Database::getInstance()->getConnection();
$metrics = ['users' => 0, 'suppliers' => 0, 'products' => 0, 'orders' => 0, 'sales' => 0, 'pending_quotes' => 0];
$queues = ['quotes' => [], 'orders' => [], 'activity' => []];
$dashboardError = null;

try {
    $queries = [
        'users' => 'SELECT COUNT(*) FROM users',
        'suppliers' => 'SELECT COUNT(*) FROM suppliers WHERE status = "active"',
        'products' => 'SELECT COUNT(*) FROM products WHERE status = "active"',
        'orders' => 'SELECT COUNT(*) FROM orders',
        'sales' => 'SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = "paid"',
        'pending_quotes' => 'SELECT COUNT(*) FROM quotation_requests WHERE status IN ("pending", "negotiating")',
    ];
    foreach ($queries as $key => $query) {
        $metrics[$key] = $key === 'sales' ? (float) $db->query($query)->fetchColumn() : (int) $db->query($query)->fetchColumn();
    }
    $queues['quotes'] = $db->query(
        'SELECT qr.id, qr.status, qr.created_at, s.supplier_name,
                CONCAT(u.first_name, " ", u.last_name) AS buyer_name
         FROM quotation_requests qr
         INNER JOIN suppliers s ON s.id = qr.supplier_id
         INNER JOIN users u ON u.id = qr.buyer_id
         WHERE qr.status IN ("pending", "negotiating")
         ORDER BY qr.created_at DESC LIMIT 5'
    )->fetchAll();
    $queues['orders'] = $db->query(
        'SELECT order_number, total_amount, payment_status, order_status, created_at
         FROM orders ORDER BY created_at DESC LIMIT 5'
    )->fetchAll();
    $queues['activity'] = (new AuditLog())->getRecent(6);
} catch (PDOException $exception) {
    error_log('Admin dashboard unavailable: ' . $exception->getMessage());
    $dashboardError = 'Some live dashboard data is temporarily unavailable.';
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin command centre - <?= e(SITE_NAME) ?></title><link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css"><link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/admin.css"></head>
<body class="admin-command-page">
<header class="admin-command-topbar"><div class="container admin-command-topbar-inner"><a class="brand" href="<?= e(BASE_URL) ?>/admin/dashboard.php"><span class="brand-mark">Q</span><span><strong>QUINCY</strong><small>ADMIN COMMAND CENTRE</small></span></a><form class="admin-global-search" action="<?= e(BASE_URL) ?>/admin/products.php" method="get"><input name="q" placeholder="Search products, suppliers, orders"><button type="submit">Search</button></form><div class="admin-user-menu"><span><?= e($_SESSION['user']['first_name'] ?? 'Admin') ?></span><a href="<?= e(BASE_URL) ?>/logout.php">Sign out</a></div></div></header>
<div class="container admin-command-shell">
    <aside class="admin-command-sidebar"><p class="admin-nav-label">COMMAND CENTRE</p><a class="admin-command-active" href="<?= e(BASE_URL) ?>/admin/dashboard.php"><span>▦</span>Overview</a><a href="<?= e(BASE_URL) ?>/admin/users.php"><span>◉</span>Users</a><a href="<?= e(BASE_URL) ?>/admin/businesses.php"><span>▣</span>Businesses</a><p class="admin-nav-label">MARKETPLACE</p><a href="<?= e(BASE_URL) ?>/admin/products.php"><span>□</span>Products</a><a href="<?= e(BASE_URL) ?>/admin/categories.php"><span>⌗</span>Categories</a><a href="<?= e(BASE_URL) ?>/admin/suppliers.php"><span>◈</span>Suppliers</a><a href="<?= e(BASE_URL) ?>/admin/manufacturers.php"><span>◇</span>Manufacturers</a><p class="admin-nav-label">TRADE OPERATIONS</p><a href="<?= e(BASE_URL) ?>/admin/orders.php"><span>▤</span>Orders</a><a href="<?= e(BASE_URL) ?>/admin/quotations.php"><span>↗</span>Quotations<?php if ($metrics['pending_quotes']): ?><b><?= $metrics['pending_quotes'] ?></b><?php endif; ?></a><a href="<?= e(BASE_URL) ?>/admin/payments.php"><span>▣</span>Payments</a><a href="<?= e(BASE_URL) ?>/admin/shipments.php"><span>→</span>Shipments</a><p class="admin-nav-label">CONTROL</p><a href="<?= e(BASE_URL) ?>/admin/reports.php"><span>◒</span>Reports</a><a href="<?= e(BASE_URL) ?>/admin/settings.php"><span>⚙</span>Settings</a></aside>
    <main class="admin-command-workspace">
        <div class="admin-command-breadcrumb">Admin <span>/</span> Command centre</div>
        <div class="admin-command-heading"><div><p class="eyebrow">PLATFORM OPERATIONS</p><h1>Good day, <?= e($_SESSION['user']['first_name'] ?? 'Admin') ?>.</h1><p>Monitor Quincy Store&rsquo;s marketplace, trade pipeline, and service health.</p></div><div class="admin-date-card"><small>Today</small><strong><?= e(date('D, d M Y')) ?></strong></div></div>
        <?php if ($dashboardError): ?><div class="catalog-notice"><?= e($dashboardError) ?></div><?php endif; ?>
        <section class="admin-command-kpis"><article><span>Registered users</span><strong><?= number_format($metrics['users']) ?></strong><small>All account roles</small><b class="kpi-icon">◉</b></article><article><span>Active suppliers</span><strong><?= number_format($metrics['suppliers']) ?></strong><small>Trading profiles</small><b class="kpi-icon">◈</b></article><article><span>Live products</span><strong><?= number_format($metrics['products']) ?></strong><small>Published listings</small><b class="kpi-icon">□</b></article><article><span>Gross paid sales</span><strong><?= e(formatPrice($metrics['sales'])) ?></strong><small><?= number_format($metrics['orders']) ?> total orders</small><b class="kpi-icon">₣</b></article></section>
        <section class="admin-command-actions"><div><p class="eyebrow">PRIORITY QUEUE</p><h2>What needs attention</h2></div><div class="admin-action-cards"><a href="<?= e(BASE_URL) ?>/admin/quotations.php"><span class="admin-action-number"><?= $metrics['pending_quotes'] ?></span><div><strong>Open quotations</strong><small>Buyer requests awaiting action</small></div><b>→</b></a><a href="<?= e(BASE_URL) ?>/admin/categories.php"><span class="admin-action-number">!</span><div><strong>Catalogue governance</strong><small>Review categories and product mapping</small></div><b>→</b></a><a href="<?= e(BASE_URL) ?>/admin/reports.php"><span class="admin-action-number">↗</span><div><strong>Trade performance</strong><small>Review sales and marketplace health</small></div><b>→</b></a></div></section>
        <div class="admin-command-grid"><section class="admin-command-panel"><div class="admin-panel-heading"><div><p class="eyebrow">B2B PIPELINE</p><h2>Quotation activity</h2></div><a href="<?= e(BASE_URL) ?>/admin/quotations.php">View all</a></div><div class="admin-queue-list"><?php foreach ($queues['quotes'] as $quote): ?><article><span class="admin-queue-avatar">Q</span><div><strong><?= e($quote['buyer_name']) ?></strong><small><?= e($quote['supplier_name']) ?> · <?= e(date('d M Y', strtotime($quote['created_at']))) ?></small></div><span class="admin-status admin-status-<?= e($quote['status']) ?>"><?= e(ucfirst($quote['status'])) ?></span></article><?php endforeach; ?><?php if (!$queues['quotes']): ?><p class="admin-muted">No open quotation activity.</p><?php endif; ?></div></section><section class="admin-command-panel"><div class="admin-panel-heading"><div><p class="eyebrow">FULFILMENT</p><h2>Latest orders</h2></div><a href="<?= e(BASE_URL) ?>/admin/orders.php">View all</a></div><div class="admin-orders-list"><?php foreach ($queues['orders'] as $order): ?><article><div><strong>#<?= e($order['order_number']) ?></strong><small><?= e(date('d M Y', strtotime($order['created_at']))) ?></small></div><span><?= e(formatPrice($order['total_amount'])) ?></span><b class="admin-status admin-status-<?= e($order['order_status']) ?>"><?= e(ucfirst(str_replace('_', ' ', $order['order_status']))) ?></b></article><?php endforeach; ?><?php if (!$queues['orders']): ?><p class="admin-muted">No orders recorded yet.</p><?php endif; ?></div></section></div>
        <section class="admin-command-panel admin-activity-panel"><div class="admin-panel-heading"><div><p class="eyebrow">AUDIT TRAIL</p><h2>Recent platform activity</h2></div><a href="<?= e(BASE_URL) ?>/admin/reports.php">Open reports</a></div><div class="admin-activity-grid"><?php foreach ($queues['activity'] as $activity): ?><article><span class="activity-dot"></span><div><strong><?= e($activity['action']) ?></strong><small><?= e($activity['user_name'] ?? 'System') ?> · <?= e(date('d M, H:i', strtotime($activity['created_at']))) ?></small></div></article><?php endforeach; ?><?php if (!$queues['activity']): ?><p class="admin-muted">No audit activity recorded yet.</p><?php endif; ?></div></section>
    </main>
</div>
</body>
</html>