<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/auth.php';
requireRole('manufacturer');

$manufacturer = null;
$metrics = [
    'products' => 0,
    'attention_products' => 0,
    'open_orders' => 0,
    'sales' => 0.0,
];
$products = [];
$orders = [];
$error = null;

try {
    $db = Database::getInstance()->getConnection();
    $profileStmt = $db->prepare('SELECT * FROM manufacturers WHERE user_id = ? LIMIT 1');
    $profileStmt->execute([(int) currentUserId()]);
    $manufacturer = $profileStmt->fetch() ?: null;

    if ($manufacturer) {
        $manufacturerId = (int) $manufacturer['id'];

        $queries = [
            'products' => ['SELECT COUNT(*) FROM products WHERE manufacturer_id = ?', [$manufacturerId]],
            'attention_products' => [
                'SELECT COUNT(DISTINCT p.id)
                 FROM products p
                 LEFT JOIN inventory i ON i.product_id = p.id AND i.variant_id IS NULL
                 WHERE p.manufacturer_id = ?
                   AND (p.status <> "active" OR COALESCE(i.quantity - i.reserved_quantity, 0) <= 0)',
                [$manufacturerId],
            ],
            'open_orders' => [
                'SELECT COUNT(DISTINCT o.id)
                 FROM orders o
                 INNER JOIN order_items oi ON oi.order_id = o.id
                 INNER JOIN products p ON p.id = oi.product_id
                 WHERE p.manufacturer_id = ?
                   AND o.order_status NOT IN ("completed", "cancelled")',
                [$manufacturerId],
            ],
            'sales' => [
                'SELECT COALESCE(SUM(oi.subtotal), 0)
                 FROM order_items oi
                 INNER JOIN orders o ON o.id = oi.order_id
                 INNER JOIN products p ON p.id = oi.product_id
                 WHERE p.manufacturer_id = ? AND o.payment_status = "paid"',
                [$manufacturerId],
            ],
        ];

        foreach ($queries as $key => [$sql, $params]) {
            $statement = $db->prepare($sql);
            $statement->execute($params);
            $metrics[$key] = $key === 'sales'
                ? (float) $statement->fetchColumn()
                : (int) $statement->fetchColumn();
        }

        $productStmt = $db->prepare(
            'SELECT p.id, p.name, p.sku, p.status, p.updated_at,
                    COALESCE(i.quantity - i.reserved_quantity, 0) AS available_quantity
             FROM products p
             LEFT JOIN inventory i ON i.product_id = p.id AND i.variant_id IS NULL
             WHERE p.manufacturer_id = ?
             ORDER BY p.updated_at DESC
             LIMIT 5'
        );
        $productStmt->execute([$manufacturerId]);
        $products = $productStmt->fetchAll();

        $orderStmt = $db->prepare(
            'SELECT o.id, o.order_number, o.total_amount, o.payment_status,
                    o.order_status, o.created_at,
                    COUNT(DISTINCT oi.id) AS item_count
             FROM orders o
             INNER JOIN order_items oi ON oi.order_id = o.id
             INNER JOIN products p ON p.id = oi.product_id
             WHERE p.manufacturer_id = ?
             GROUP BY o.id
             ORDER BY o.created_at DESC
             LIMIT 5'
        );
        $orderStmt->execute([$manufacturerId]);
        $orders = $orderStmt->fetchAll();
    }
} catch (PDOException $exception) {
    error_log('Manufacturer dashboard unavailable: ' . $exception->getMessage());
    $error = 'Some dashboard data is temporarily unavailable.';
}

$displayName = (string) ($_SESSION['user']['first_name'] ?? 'Manufacturer');
$companyName = (string) ($manufacturer['manufacturer_name'] ?? 'Manufacturer account');
$initial = strtoupper(substr($companyName, 0, 1));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manufacturer centre - <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/supplier.css">
</head>
<body class="supplier-dashboard-page">
<header class="supplier-topbar">
    <div class="container supplier-topbar-inner">
        <a class="brand" href="<?= e(BASE_URL) ?>/">
            <span class="brand-mark">Q</span><span><strong>QUINCY</strong><small>MANUFACTURER CENTRE</small></span>
        </a>
        <div class="supplier-top-actions"><a href="<?= e(BASE_URL) ?>/manufacturer/profile.php">Company profile</a><a href="<?= e(BASE_URL) ?>/logout.php">Sign out</a></div>
    </div>
</header>
<div class="supplier-shell container">
    <aside class="supplier-sidebar">
        <div class="supplier-profile">
            <span class="supplier-profile-avatar"><?= e($initial) ?></span>
            <div><strong><?= e($companyName) ?></strong><small><?= $manufacturer && $manufacturer['verification_status'] === 'verified' ? 'Verified manufacturer' : 'Verification pending' ?></small></div>
        </div>
        <nav>
            <p>OPERATIONS</p>
            <a class="supplier-nav-active" href="<?= e(BASE_URL) ?>/manufacturer/dashboard.php">Overview <b>•</b></a>
            <a href="<?= e(BASE_URL) ?>/manufacturer/products.php">Products</a>
            <a href="<?= e(BASE_URL) ?>/manufacturer/inventory.php">Inventory</a>
            <a href="<?= e(BASE_URL) ?>/manufacturer/orders.php">Orders<?php if ($metrics['open_orders']): ?><b class="nav-count"><?= $metrics['open_orders'] ?></b><?php endif; ?></a>
            <p>BUYER PIPELINE</p>
            <a href="<?= e(BASE_URL) ?>/manufacturer/quotations.php">Quotation requests</a>
            <a href="<?= e(BASE_URL) ?>/manufacturer/negotiations.php">Negotiations</a>
            <p>ACCOUNT</p>
            <a href="<?= e(BASE_URL) ?>/manufacturer/profile.php">Company profile</a>
            <a href="<?= e(BASE_URL) ?>/manufacturer/settings.php">Settings</a>
        </nav>
    </aside>
    <main class="supplier-workspace">
        <div class="supplier-breadcrumb">Manufacturer centre <span>/</span> Overview</div>
        <div class="supplier-welcome">
            <div><p class="eyebrow">MANUFACTURING OPERATIONS</p><h1>Good day, <?= e($displayName) ?>.</h1><p>Monitor your production catalogue, inventory, and customer orders.</p></div>
            <a class="button button-primary" href="<?= e(BASE_URL) ?>/manufacturer/add-product.php">+ Add product</a>
        </div>
        <?php if ($error): ?><div class="catalog-notice"><?= e($error) ?></div><?php endif; ?>
        <?php if (!$manufacturer): ?>
            <section class="supplier-panel"><h2>Manufacturer profile not found</h2><p class="admin-muted">Complete your manufacturer registration before managing products.</p></section>
        <?php else: ?>
            <section class="supplier-kpis">
                <article><span>Total products</span><strong><?= number_format($metrics['products']) ?></strong><small>Catalogue listings</small></article>
                <article><span>Needs attention</span><strong><?= number_format($metrics['attention_products']) ?></strong><small>Stock or status issues</small></article>
                <article><span>Open orders</span><strong><?= number_format($metrics['open_orders']) ?></strong><small>Require fulfilment</small></article>
                <article><span>Paid sales</span><strong><?= e(formatPrice($metrics['sales'])) ?></strong><small>Across your products</small></article>
            </section>
            <section class="supplier-action-grid">
                <a href="<?= e(BASE_URL) ?>/manufacturer/add-product.php"><span>+</span><div><strong>Add a product</strong><small>Publish a new manufactured product</small></div><b>→</b></a>
                <a href="<?= e(BASE_URL) ?>/manufacturer/inventory.php"><span>▦</span><div><strong>Review inventory</strong><small><?= number_format($metrics['attention_products']) ?> listing(s) need attention</small></div><b>→</b></a>
                <a href="<?= e(BASE_URL) ?>/manufacturer/orders.php"><span>↗</span><div><strong>Process orders</strong><small><?= number_format($metrics['open_orders']) ?> open order(s)</small></div><b>→</b></a>
            </section>
            <div class="supplier-dashboard-grid">
                <section class="supplier-panel">
                    <div class="supplier-panel-heading"><div><p class="eyebrow">CATALOGUE HEALTH</p><h2>Recent products</h2></div><a href="<?= e(BASE_URL) ?>/manufacturer/products.php">Manage all</a></div>
                    <div class="supplier-product-list">
                        <?php foreach ($products as $product): ?>
                            <?php $productStatus = $product['status'] === 'active' && (int) $product['available_quantity'] > 0 ? 'active' : 'pending'; ?>
                            <article><div><strong><?= e($product['name']) ?></strong><small><?= e($product['sku'] ?: 'No SKU') ?> · <?= number_format((int) $product['available_quantity']) ?> available</small></div><span class="supplier-status supplier-status-<?= e($productStatus) ?>"><?= e(ucfirst((string) $product['status'])) ?></span></article>
                        <?php endforeach; ?>
                        <?php if (!$products): ?><p class="admin-muted">Add your first product to build your catalogue.</p><?php endif; ?>
                    </div>
                </section>
                <section class="supplier-panel">
                    <div class="supplier-panel-heading"><div><p class="eyebrow">QUICK ACTIONS</p><h2>Keep operations moving</h2></div></div>
                    <div class="supplier-action-grid" style="grid-template-columns:1fr;margin:0">
                        <a href="<?= e(BASE_URL) ?>/manufacturer/profile.php"><span>✓</span><div><strong>Update company profile</strong><small>Strengthen your buyer trust signals</small></div><b>→</b></a>
                        <a href="<?= e(BASE_URL) ?>/manufacturer/quotations.php"><span>?</span><div><strong>Review quotations</strong><small>Respond to buyer requests promptly</small></div><b>→</b></a>
                    </div>
                </section>
            </div>
            <section class="supplier-panel supplier-orders-panel">
                <div class="supplier-panel-heading"><div><p class="eyebrow">FULFILMENT</p><h2>Recent manufacturer orders</h2></div><a href="<?= e(BASE_URL) ?>/manufacturer/orders.php">View all</a></div>
                <div class="supplier-order-table"><table><thead><tr><th>Order</th><th>Items</th><th>Amount</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead><tbody>
                <?php foreach ($orders as $order): ?><tr><td><strong>#<?= e($order['order_number']) ?></strong></td><td><?= (int) $order['item_count'] ?></td><td><?= e(formatPrice($order['total_amount'])) ?></td><td><?= e(ucfirst((string) $order['payment_status'])) ?></td><td><span class="supplier-status supplier-status-<?= e($order['order_status']) ?>"><?= e(ucfirst(str_replace('_', ' ', (string) $order['order_status']))) ?></span></td><td><?= e(date('d M Y', strtotime($order['created_at']))) ?></td></tr><?php endforeach; ?>
                <?php if (!$orders): ?><tr><td colspan="6" class="admin-muted">No orders recorded for your products yet.</td></tr><?php endif; ?>
                </tbody></table></div>
            </section>
        <?php endif; ?>
    </main>
</div>
</body>
</html>