<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/auth.php';
requireRole('supplier');

$dashboard = ['supplier' => null, 'metrics' => [], 'products' => [], 'quotations' => [], 'orders' => []];
$error = null;
try {
    $dashboard = (new Supplier())->dashboard((int) currentUserId());
} catch (PDOException $exception) {
    error_log('Supplier dashboard unavailable: ' . $exception->getMessage());
    $error = 'Some dashboard data is temporarily unavailable.';
}
$supplier = $dashboard['supplier'];
$metrics = $dashboard['metrics'] + ['products' => 0, 'pending_quotes' => 0, 'open_orders' => 0, 'sales' => 0];
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Supplier centre - <?= e(SITE_NAME) ?></title><link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css"><link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/supplier.css"></head>
<body class="supplier-dashboard-page">
<header class="supplier-topbar"><div class="container supplier-topbar-inner"><a class="brand" href="<?= e(BASE_URL) ?>/"><span class="brand-mark">Q</span><span><strong>QUINCY</strong><small>SUPPLIER CENTRE</small></span></a><div class="supplier-top-actions"><span>Help centre</span><a href="<?= e(BASE_URL) ?>/logout.php">Sign out</a></div></div></header>
<div class="supplier-shell container">
    <aside class="supplier-sidebar"><div class="supplier-profile"><span class="supplier-profile-avatar"><?= e(strtoupper(substr((string) ($supplier['supplier_name'] ?? 'S'), 0, 1))) ?></span><div><strong><?= e($supplier['supplier_name'] ?? 'Supplier account') ?></strong><small><?= $supplier && $supplier['verification_status'] === 'verified' ? 'Verified supplier' : 'Verification pending' ?></small></div></div><nav><p>OPERATIONS</p><a class="supplier-nav-active" href="<?= e(BASE_URL) ?>/supplier/dashboard.php">Overview <b>•</b></a><a href="<?= e(BASE_URL) ?>/supplier/products.php">Products</a><a href="<?= e(BASE_URL) ?>/supplier/inventory.php">Inventory</a><a href="<?= e(BASE_URL) ?>/supplier/orders.php">Orders</a><p>BUYER PIPELINE</p><a href="<?= e(BASE_URL) ?>/supplier/quotations.php">Quotation requests <?php if ($metrics['pending_quotes']): ?><b class="nav-count"><?= $metrics['pending_quotes'] ?></b><?php endif; ?></a><a href="<?= e(BASE_URL) ?>/supplier/negotiations.php">Negotiations</a><a href="<?= e(BASE_URL) ?>/supplier/sales.php">Sales analytics</a><p>ACCOUNT</p><a href="<?= e(BASE_URL) ?>/supplier/profile.php">Company profile</a><a href="<?= e(BASE_URL) ?>/supplier/settings.php">Settings</a></nav></aside>
    <main class="supplier-workspace">
        <div class="supplier-breadcrumb">Supplier centre <span>/</span> Overview</div>
        <div class="supplier-welcome"><div><p class="eyebrow">BUSINESS OPERATIONS</p><h1>Good day, <?= e($_SESSION['user']['first_name'] ?? 'Supplier') ?>.</h1><p>Here is what needs your attention today.</p></div><a class="button button-primary" href="<?= e(BASE_URL) ?>/supplier/add-product.php">+ Add product</a></div>
        <?php if ($error): ?><div class="catalog-notice"><?= e($error) ?></div><?php endif; ?>
        <?php if (!$supplier): ?><div class="supplier-empty"><h2>Supplier profile not found</h2><p>Complete your supplier registration before managing products.</p></div><?php else: ?>
        <section class="supplier-kpis"><article><span>Active products</span><strong><?= $metrics['products'] ?></strong><small>Published listings</small></article><article><span>Quotation requests</span><strong><?= $metrics['pending_quotes'] ?></strong><small>Need a response</small></article><article><span>Open orders</span><strong><?= $metrics['open_orders'] ?></strong><small>In progress</small></article><article><span>Paid sales</span><strong><?= e(formatPrice($metrics['sales'])) ?></strong><small>All-time captured</small></article></section>
        <section class="supplier-action-grid"><a href="<?= e(BASE_URL) ?>/supplier/quotations.php"><span>↗</span><div><strong>Respond to quote requests</strong><small><?= $metrics['pending_quotes'] ?> buyer requests awaiting action</small></div><b>→</b></a><a href="<?= e(BASE_URL) ?>/supplier/inventory.php"><span>▦</span><div><strong>Review inventory</strong><small>Keep your available stock accurate</small></div><b>→</b></a><a href="<?= e(BASE_URL) ?>/supplier/profile.php"><span>✓</span><div><strong>Improve your storefront</strong><small>Add company details and trust signals</small></div><b>→</b></a></section>
        <div class="supplier-dashboard-grid"><section class="supplier-panel"><div class="supplier-panel-heading"><div><p class="eyebrow">BUYER PIPELINE</p><h2>Recent quotation requests</h2></div><a href="<?= e(BASE_URL) ?>/supplier/quotations.php">View all</a></div><div class="supplier-quote-list"><?php foreach ($dashboard['quotations'] as $quote): ?><article><span class="quote-avatar"><?= e(strtoupper(substr((string) $quote['first_name'], 0, 1))) ?></span><div><strong><?= e(trim($quote['first_name'] . ' ' . $quote['last_name'])) ?></strong><small><?= (int) $quote['item_count'] ?> item(s) · <?= e($quote['delivery_location']) ?></small></div><span class="supplier-status supplier-status-<?= e($quote['status']) ?>"><?= e(ucfirst($quote['status'])) ?></span></article><?php endforeach; ?><?php if (!$dashboard['quotations']): ?><p class="admin-muted">No quotation requests yet.</p><?php endif; ?></div></section><section class="supplier-panel"><div class="supplier-panel-heading"><div><p class="eyebrow">CATALOGUE HEALTH</p><h2>Recent products</h2></div><a href="<?= e(BASE_URL) ?>/supplier/products.php">Manage</a></div><div class="supplier-product-list"><?php foreach ($dashboard['products'] as $product): ?><article><div><strong><?= e($product['name']) ?></strong><small>MOQ <?= (int) $product['minimum_order_quantity'] ?> · <?= e($product['sku']) ?></small></div><span class="supplier-status supplier-status-<?= e($product['status']) ?>"><?= e(ucfirst($product['status'])) ?></span></article><?php endforeach; ?><?php if (!$dashboard['products']): ?><p class="admin-muted">Add your first product to start receiving buyer requests.</p><?php endif; ?></div></section></div>
        <section class="supplier-panel supplier-orders-panel"><div class="supplier-panel-heading"><div><p class="eyebrow">FULFILMENT</p><h2>Recent orders</h2></div><a href="<?= e(BASE_URL) ?>/supplier/orders.php">View orders</a></div><div class="supplier-order-table"><table><thead><tr><th>Order</th><th>Amount</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead><tbody><?php foreach ($dashboard['orders'] as $order): ?><tr><td><strong>#<?= e($order['order_number']) ?></strong></td><td><?= e(formatPrice($order['total_amount'])) ?></td><td><?= e(ucfirst($order['payment_status'])) ?></td><td><span class="supplier-status supplier-status-<?= e($order['order_status']) ?>"><?= e(ucfirst(str_replace('_', ' ', $order['order_status']))) ?></span></td><td><?= e(date('d M Y', strtotime($order['created_at']))) ?></td></tr><?php endforeach; ?><?php if (!$dashboard['orders']): ?><tr><td colspan="5" class="admin-muted">No orders recorded yet.</td></tr><?php endif; ?></tbody></table></div></section>
        <?php endif; ?>
    </main>
</div>
</body>
</html>