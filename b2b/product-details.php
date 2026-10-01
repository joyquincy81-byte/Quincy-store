<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/functions.php';
try { $product = (new Product())->findWholesaleById((int) ($_GET['id'] ?? 0)); }
catch (PDOException $exception) { error_log('B2B product details: '.$exception->getMessage()); http_response_code(503); exit('Product details are temporarily unavailable.'); }
if (!$product) {
    http_response_code(404);
    exit('Wholesale product not found.');
}
$moq = max(1, (int) $product['minimum_order_quantity']);
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($product['name']) ?> - Wholesale - <?= e(SITE_NAME) ?></title><link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css"></head>
<body class="b2b-detail-page">
<header class="directory-header"><div class="container directory-header-row"><a class="brand" href="<?= e(BASE_URL) ?>/"><span class="brand-mark">Q</span><span><strong>QUINCY</strong><small>BUSINESS</small></span></a><nav><a href="<?= e(BASE_URL) ?>/b2b/marketplace.php">Marketplace</a><a href="<?= e(BASE_URL) ?>/b2b/suppliers.php">Find suppliers</a><a href="<?= e(BASE_URL) ?>/b2b/request-quotation.php">Request quotation</a><a href="<?= e(BASE_URL) ?>/login.php">Sign in</a></nav></div></header>
<main class="container b2b-detail-main">
    <div class="directory-breadcrumb"><a href="<?= e(BASE_URL) ?>/b2b/marketplace.php">Wholesale marketplace</a><span>/</span><?= e($product['name']) ?></div>
    <div class="b2b-detail-layout">
        <section class="b2b-detail-card">        <div class="b2b-detail-image"><?= e(strtoupper(substr((string) $product['name'], 0, 1))) ?><span>Wholesale listing</span></div><div class="b2b-detail-summary"><p class="eyebrow"><?= e($product['category_name'] ?? 'Business supply') ?></p><h1><?= e($product['name']) ?></h1><p class="detail-description"><?= e($product['description'] ?: 'Product details available from this supplier upon request.') ?></p><div class="detail-price-row"><strong><?= e(formatPrice($product['wholesale_price'])) ?></strong><span>per <?= e($product['unit'] ?: 'unit') ?></span></div><div class="detail-facts"><span><small>Minimum order</small><strong><?= $moq ?> <?= e($product['unit'] ?: 'units') ?></strong></span><span><small>Available stock</small><strong><?= (int) $product['available_quantity'] ?> <?= e($product['unit'] ?: 'units') ?></strong></span><span><small>SKU</small><strong><?= e($product['sku']) ?></strong></span></div><div class="detail-actions"><a class="button button-primary" href="<?= e(BASE_URL) ?>/b2b/request-quotation.php?product=<?= (int) $product['id'] ?>&supplier=<?= (int) $product['supplier_id'] ?>">Request a quote</a><a class="outline-button" href="<?= e(BASE_URL) ?>/b2b/suppliers.php?q=<?= urlencode($product['supplier_name']) ?>">Contact supplier</a></div><p class="detail-note">Ask for a negotiated offer, delivery timeline, packaging details, or samples in your RFQ.</p></div></section>
        <aside class="supplier-detail-panel"><div class="supplier-detail-heading"><span class="supplier-mini-avatar"><?= e(strtoupper(substr((string) $product['supplier_name'], 0, 1))) ?></span><div><p class="eyebrow">SUPPLIER</p><h2><?= e($product['supplier_name']) ?></h2></div></div><?php if ($product['verification_status'] === 'verified'): ?><span class="verified-badge">Verified supplier</span><?php endif; ?><p class="supplier-location"><?= e(implode(', ', array_filter([$product['city'], $product['country']]))) ?></p><div class="supplier-detail-metrics"><span><strong><?= number_format((float) ($product['rating'] ?? 0), 1) ?></strong> ★ rating</span><span>Responsive on Quincy Store</span></div><p><?= e($product['supplier_description'] ?: 'This supplier is available to discuss your business requirements.') ?></p><a class="supplier-profile-link" href="<?= e(BASE_URL) ?>/shop/suppliers.php?q=<?= urlencode($product['supplier_name']) ?>">View supplier profile →</a></aside>
    </div>
    <section class="communication-strip"><div><p class="eyebrow">BUYER TOOLS</p><h2>Need a different quantity or specification?</h2><p>Send one RFQ to start the conversation. Suppliers can respond with pricing and terms for negotiation.</p></div><a class="button button-primary" href="<?= e(BASE_URL) ?>/b2b/request-quotation.php?product=<?= (int) $product['id'] ?>">Start an RFQ →</a></section>
</main>
</body>
</html>
