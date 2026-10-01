<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/business.php';

$business = null;
$error = null;
$metrics = [
    'quotations' => 0,
    'orders' => 0,
    'pending_orders' => 0,
    'payments' => 0,
    'spending' => 0.0,
];
$quotations = [];
$orders = [];
$payments = [];

try {
    $db = Database::getInstance()->getConnection();
    $business = (new Business())->findByUserId((int) currentUserId());

    if ($business) {
        $userId = (int) currentUserId();
        $businessId = (int) $business['id'];

        $metricQueries = [
            'quotations' => ['SELECT COUNT(*) FROM quotation_requests WHERE buyer_id = ?', [$userId]],
            'orders' => ['SELECT COUNT(*) FROM orders WHERE user_id = ?', [$userId]],
            'pending_orders' => [
                'SELECT COUNT(*) FROM orders
                 WHERE user_id = ? AND order_status IN ("Pending", "Processing", "Out for Delivery")',
                [$userId],
            ],
            'payments' => [
                'SELECT COUNT(*) FROM payments WHERE user_id = ? AND status = "successful"',
                [$userId],
            ],
            'spending' => [
                'SELECT COALESCE(SUM(total_amount), 0)
                 FROM orders
                 WHERE user_id = ? AND payment_status = "Paid"',
                [$userId],
            ],
        ];

        foreach ($metricQueries as $key => [$sql, $params]) {
            $statement = $db->prepare($sql);
            $statement->execute($params);
            $metrics[$key] = $key === 'spending'
                ? (float) $statement->fetchColumn()
                : (int) $statement->fetchColumn();
        }

        $quotationStmt = $db->prepare(
            'SELECT qr.id, qr.status, qr.delivery_location, qr.created_at,
                    s.supplier_name
             FROM quotation_requests qr
             LEFT JOIN suppliers s ON s.id = qr.supplier_id
             WHERE qr.buyer_id = ?
             ORDER BY qr.created_at DESC
             LIMIT 5'
        );
        $quotationStmt->execute([$userId]);
        $quotations = $quotationStmt->fetchAll();

        $orderStmt = $db->prepare(
            'SELECT id, order_number, total_amount, payment_status,
                    order_status, created_at
             FROM orders
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT 5'
        );
        $orderStmt->execute([$userId]);
        $orders = $orderStmt->fetchAll();

        $paymentStmt = $db->prepare(
            'SELECT p.id, p.amount, p.currency, p.payment_method, p.status,
                    p.created_at, o.order_number
             FROM payments p
             INNER JOIN orders o ON o.id = p.order_id
             WHERE p.user_id = ?
             ORDER BY p.created_at DESC
             LIMIT 5'
        );
        $paymentStmt->execute([$userId]);
        $payments = $paymentStmt->fetchAll();
    }
} catch (PDOException $exception) {
    error_log('Business dashboard unavailable: ' . $exception->getMessage());
    $error = 'Some dashboard data is temporarily unavailable.';
}

$name = trim((string) ($_SESSION['user']['first_name'] ?? 'Buyer'));
$businessName = (string) ($business['business_name'] ?? 'Business account');
$verificationStatus = (string) ($business['verification_status'] ?? 'pending');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Business buyer centre - <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
    <style>
        .buyer-page{min-height:100vh;background:#f5f8fc;color:#14213d}
        .buyer-topbar{background:#07152f;color:#fff}.buyer-topbar-inner{display:flex;align-items:center;justify-content:space-between;min-height:72px}
        .buyer-actions{display:flex;gap:18px;color:#c9d8eb;font-size:13px}.buyer-shell{display:grid;grid-template-columns:230px 1fr;gap:24px;padding-top:25px;padding-bottom:70px}
        .buyer-sidebar,.buyer-panel,.buyer-kpis article{background:#fff;border:1px solid #d8e1ed}.buyer-sidebar{align-self:start;padding:18px 13px}
        .buyer-profile{display:flex;align-items:center;gap:10px;padding:0 6px 18px;border-bottom:1px solid #d8e1ed}.buyer-avatar{display:grid;width:38px;height:38px;place-items:center;background:#eaf2fb;color:#1b4b86;border-radius:5px;font-weight:800}
        .buyer-profile strong,.buyer-profile small{display:block;max-width:145px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.buyer-profile strong{font-size:12px;color:#07152f}.buyer-profile small{margin-top:3px;color:#8a5a00;font-size:10px}
        .buyer-sidebar nav p{margin:20px 7px 7px;color:#94a3b8;font-size:10px;font-weight:700;letter-spacing:1px}.buyer-sidebar nav a{display:flex;justify-content:space-between;padding:10px 8px;color:#64748b;border-radius:3px;font-size:13px}.buyer-sidebar nav a:hover,.buyer-sidebar nav a.active{color:#1b4b86;background:#eaf2fb;font-weight:700}
        .buyer-breadcrumb{margin-bottom:20px;color:#64748b;font-size:12px}.buyer-breadcrumb span{margin:0 8px}.buyer-heading{display:flex;align-items:end;justify-content:space-between;gap:20px;margin-bottom:25px}.buyer-heading h1{margin:0 0 5px;color:#07152f;font-size:32px}.buyer-heading p:not(.eyebrow){margin:0;color:#64748b}
        .buyer-kpis{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:18px}.buyer-kpis article{padding:17px;border-top:3px solid #1b4b86}.buyer-kpis span,.buyer-kpis small{display:block;color:#64748b;font-size:11px}.buyer-kpis strong{display:block;margin:6px 0 2px;color:#07152f;font-size:22px}
        .buyer-actions-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px}.buyer-actions-grid a{display:flex;align-items:center;gap:10px;padding:14px;color:#14213d;background:#fff;border:1px solid #d8e1ed}.buyer-actions-grid a:hover{border-color:#1b4b86;box-shadow:0 6px 18px #07152f0a}.buyer-actions-grid a>span{display:grid;width:30px;height:30px;place-items:center;background:#eaf2fb;color:#1b4b86;border-radius:4px}.buyer-actions-grid a div{display:grid;flex:1}.buyer-actions-grid strong{font-size:12px}.buyer-actions-grid small{margin-top:3px;color:#64748b;font-size:10px}
        .buyer-grid{display:grid;grid-template-columns:1.1fr 1fr;gap:18px;margin-bottom:18px}.buyer-panel{padding:20px}.buyer-panel-heading{display:flex;align-items:end;justify-content:space-between;gap:12px;margin-bottom:15px}.buyer-panel-heading h2{margin:0;color:#07152f;font-size:17px}.buyer-panel-heading a{color:#1b4b86;font-size:11px;font-weight:700}
        .buyer-list article{display:flex;align-items:center;gap:10px;padding:11px 0;border-bottom:1px solid #edf1f5}.buyer-list article>div{display:grid;min-width:0;flex:1}.buyer-list strong{overflow:hidden;color:#07152f;font-size:12px;text-overflow:ellipsis;white-space:nowrap}.buyer-list small{color:#64748b;font-size:10px}.buyer-badge{padding:4px 7px;border-radius:3px;color:#1b4b86;background:#eaf2fb;font-size:9px;font-weight:700;white-space:nowrap}.buyer-badge-pending{color:#8a5a00;background:#fff5d9}.buyer-badge-paid,.buyer-badge-successful,.buyer-badge-delivered{color:#176447;background:#eaf8f0}.buyer-badge-cancelled,.buyer-badge-failed{color:#8b2635;background:#fff0f1}
        .buyer-table{overflow-x:auto}.buyer-table table{width:100%;min-width:560px;border-collapse:collapse;font-size:11px}.buyer-table th{color:#64748b;background:#f5f8fc;text-align:left;text-transform:uppercase}.buyer-table th,.buyer-table td{padding:11px 10px;border-bottom:1px solid #edf1f5}.buyer-empty{padding:32px 15px;text-align:center;color:#64748b;border:1px dashed #cbd5e1}.buyer-notice{padding:12px 15px;margin-bottom:18px;color:#8a5a00;background:#fff5d9;border:1px solid #f0d890}
        @media(max-width:1000px){.buyer-kpis{grid-template-columns:repeat(3,1fr)}}@media(max-width:850px){.buyer-shell{grid-template-columns:1fr}.buyer-sidebar{order:1}.buyer-workspace{order:0}.buyer-grid{grid-template-columns:1fr}}@media(max-width:620px){.buyer-topbar-inner{align-items:flex-start;flex-direction:column;gap:12px;padding:15px 0}.buyer-heading{align-items:flex-start;flex-direction:column}.buyer-kpis{grid-template-columns:repeat(2,1fr);gap:8px}.buyer-actions-grid{grid-template-columns:1fr}.buyer-panel{padding:15px}}
    </style>
</head>
<body class="buyer-page">
<header class="buyer-topbar"><div class="container buyer-topbar-inner"><a class="brand" href="<?= e(BASE_URL) ?>/"><span class="brand-mark">Q</span><span><strong>QUINCY</strong><small>BUSINESS BUYER CENTRE</small></span></a><div class="buyer-actions"><a href="<?= e(BASE_URL) ?>/b2b/marketplace.php">Marketplace</a><a href="<?= e(BASE_URL) ?>/logout.php">Sign out</a></div></div></header>
<div class="buyer-shell container">
    <aside class="buyer-sidebar"><div class="buyer-profile"><span class="buyer-avatar"><?= e(strtoupper(substr($businessName, 0, 1))) ?></span><div><strong><?= e($businessName) ?></strong><small><?= e(ucfirst($verificationStatus)) ?> verification</small></div></div><nav><p>WORKSPACE</p><a class="active" href="<?= e(BASE_URL) ?>/business/dashboard.php">Overview</a><a href="<?= e(BASE_URL) ?>/business/products.php">Find products</a><a href="<?= e(BASE_URL) ?>/business/suppliers.php">Find suppliers</a><p>TRADE</p><a href="<?= e(BASE_URL) ?>/business/quotation-request.php">Request quotation</a><a href="<?= e(BASE_URL) ?>/business/quotations.php">Quotations</a><a href="<?= e(BASE_URL) ?>/business/orders.php">Orders</a><a href="<?= e(BASE_URL) ?>/business/payments.php">Payments</a><p>ACCOUNT</p><a href="<?= e(BASE_URL) ?>/business/profile.php">Business profile</a><a href="<?= e(BASE_URL) ?>/business/settings.php">Settings</a></nav></aside>
    <main class="buyer-workspace">
        <div class="buyer-breadcrumb">Business buyer centre <span>/</span> Overview</div>
        <div class="buyer-heading"><div><p class="eyebrow">WHOLESALE OPERATIONS</p><h1>Good day, <?= e($name) ?>.</h1><p>Manage sourcing, quotations, orders, and payments from one workspace.</p></div><a class="button button-primary" href="<?= e(BASE_URL) ?>/business/quotation-request.php">+ Request a quotation</a></div>
        <?php if ($error): ?><div class="buyer-notice"><?= e($error) ?></div><?php endif; ?>
        <?php if (!$business): ?><section class="buyer-panel"><h2>Business profile not found</h2><p>Your buyer account needs a business profile before trade activity can be displayed.</p></section><?php else: ?>
        <section class="buyer-kpis"><article><span>Quotations</span><strong><?= number_format($metrics['quotations']) ?></strong><small>Requests submitted</small></article><article><span>Total orders</span><strong><?= number_format($metrics['orders']) ?></strong><small>Purchase history</small></article><article><span>Pending orders</span><strong><?= number_format($metrics['pending_orders']) ?></strong><small>Need attention</small></article><article><span>Successful payments</span><strong><?= number_format($metrics['payments']) ?></strong><small>Confirmed transactions</small></article><article><span>Total spending</span><strong><?= e(formatPrice($metrics['spending'])) ?></strong><small>Paid orders</small></article></section>
        <section class="buyer-actions-grid"><a href="<?= e(BASE_URL) ?>/business/products.php"><span>⌕</span><div><strong>Source products</strong><small>Browse wholesale listings</small></div><b>→</b></a><a href="<?= e(BASE_URL) ?>/business/quotation-request.php"><span>↗</span><div><strong>Start an RFQ</strong><small>Ask suppliers for their best offer</small></div><b>→</b></a><a href="<?= e(BASE_URL) ?>/business/orders.php"><span>▤</span><div><strong>Track orders</strong><small><?= number_format($metrics['pending_orders']) ?> order(s) in progress</small></div><b>→</b></a></section>
        <div class="buyer-grid"><section class="buyer-panel"><div class="buyer-panel-heading"><div><p class="eyebrow">SOURCING PIPELINE</p><h2>Recent quotation requests</h2></div><a href="<?= e(BASE_URL) ?>/business/quotations.php">View all</a></div><div class="buyer-list"><?php foreach($quotations as $quote): ?><article><div><strong><?= e($quote['supplier_name'] ?: 'Supplier pending') ?></strong><small><?= e($quote['delivery_location']) ?> · <?= e(date('d M Y',strtotime($quote['created_at']))) ?></small></div><span class="buyer-badge buyer-badge-<?= e($quote['status']) ?>"><?= e(ucfirst($quote['status'])) ?></span></article><?php endforeach; ?><?php if(!$quotations): ?><div class="buyer-empty">No quotation requests yet.</div><?php endif; ?></div></section>
        <section class="buyer-panel"><div class="buyer-panel-heading"><div><p class="eyebrow">FULFILMENT</p><h2>Recent orders</h2></div><a href="<?= e(BASE_URL) ?>/business/orders.php">View all</a></div><div class="buyer-list"><?php foreach($orders as $order): ?><article><div><strong>#<?= e($order['order_number']) ?></strong><small><?= e(date('d M Y',strtotime($order['created_at']))) ?> · <?= e(formatPrice($order['total_amount'])) ?></small></div><span class="buyer-badge buyer-badge-<?= e(strtolower((string)$order['order_status'])) ?>"><?= e($order['order_status']) ?></span></article><?php endforeach; ?><?php if(!$orders): ?><div class="buyer-empty">No orders yet.</div><?php endif; ?></div></section></div>
        <section class="buyer-panel"><div class="buyer-panel-heading"><div><p class="eyebrow">PAYMENT ACTIVITY</p><h2>Recent payments</h2></div><a href="<?= e(BASE_URL) ?>/business/payments.php">View all</a></div><div class="buyer-table"><table><thead><tr><th>Order</th><th>Method</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody><?php foreach($payments as $payment): ?><tr><td><strong>#<?= e($payment['order_number']) ?></strong></td><td><?= e(str_replace('_',' ',(string)$payment['payment_method'])) ?></td><td><?= e(formatPrice($payment['amount'])) ?></td><td><span class="buyer-badge buyer-badge-<?= e($payment['status']) ?>"><?= e(ucfirst($payment['status'])) ?></span></td><td><?= e(date('d M Y',strtotime($payment['created_at']))) ?></td></tr><?php endforeach; ?><?php if(!$payments): ?><tr><td colspan="5" class="buyer-empty">No payments recorded yet.</td></tr><?php endif; ?></tbody></table></div></section>
        <?php endif; ?>
    </main>
</div>
</body>
</html>