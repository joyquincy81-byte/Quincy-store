<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/business.php';
require_once ROOT_PATH . '/middleware/csrf.php';

$products = [];
$error = null;
try {
    $products = (new Quotation())->productsForRequest();
} catch (PDOException $exception) {
    error_log('RFQ product list unavailable: ' . $exception->getMessage());
    $error = 'Product options are temporarily unavailable.';
}
$selectedProduct = isset($_GET['product']) ? (int) $_GET['product'] : 0;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Request a quotation - <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
</head>
<body class="rfq-page">
<header class="directory-header"><div class="container directory-header-row"><a class="brand" href="<?= e(BASE_URL) ?>/"><span class="brand-mark">Q</span><span><strong>QUINCY</strong><small>BUSINESS</small></span></a><nav><a href="<?= e(BASE_URL) ?>/b2b/marketplace.php">Marketplace</a><a href="<?= e(BASE_URL) ?>/shop/suppliers.php">Find suppliers</a><a class="directory-active" href="<?= e(BASE_URL) ?>/b2b/request-quotation.php">Request quotation</a><a href="<?= e(BASE_URL) ?>/logout.php">Sign out</a></nav></div></header>
<main class="container rfq-main">
    <div class="directory-breadcrumb"><a href="<?= e(BASE_URL) ?>/b2b/marketplace.php">Wholesale marketplace</a><span>/</span>Request a quotation</div>
    <section class="rfq-heading"><p class="eyebrow">B2B SOURCING REQUEST</p><h1>Tell suppliers what you need.</h1><p>Submit one structured request and receive a supplier offer you can review, negotiate, and accept.</p></section>
    <div class="rfq-progress"><span class="rfq-progress-active"><b>1</b> Request details</span><span><b>2</b> Supplier offers</span><span><b>3</b> Negotiate and order</span></div>
    <?php if ($error): ?><div class="catalog-notice"><?= e($error) ?></div><?php endif; ?>
    <form class="rfq-form" method="post" action="<?= e(BASE_URL) ?>/api/quotations/create.php">
        <?= csrfField() ?>
        <section class="rfq-section"><div class="rfq-section-title"><span>01</span><div><h2>Product requirements</h2><p>Describe the product and quantity you want to source.</p></div></div>
            <div class="rfq-fields">
                <label class="rfq-field rfq-wide">Product or listing<select name="product_id" required><option value="">Select a wholesale product</option><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" <?= $selectedProduct === (int) $product['id'] ? 'selected' : '' ?>><?= e($product['name']) ?> — <?= e(formatPrice($product['wholesale_price'])) ?> / <?= e($product['unit'] ?: 'unit') ?></option><?php endforeach; ?></select></label>
                <label class="rfq-field">Required quantity<input type="number" name="quantity" min="1" required placeholder="e.g. 500"></label>
                <label class="rfq-field">Target price per unit<input type="number" name="requested_price" min="0" step="0.01" placeholder="Optional"></label>
                <label class="rfq-field">Preferred supplier ID<input type="number" name="supplier_id" min="1" value="<?= isset($_GET['supplier']) ? (int) $_GET['supplier'] : '' ?>" placeholder="Optional"></label>
            </div>
        </section>
        <section class="rfq-section"><div class="rfq-section-title"><span>02</span><div><h2>Delivery requirements</h2><p>Help suppliers calculate a complete offer.</p></div></div>
            <div class="rfq-fields"><label class="rfq-field rfq-wide">Delivery location<input name="delivery_location" required placeholder="City, region, and country"></label><label class="rfq-field rfq-wide">Additional notes<textarea name="notes" rows="5" placeholder="Quality requirements, packaging, delivery timeline, certifications, or other details"></textarea></label></div>
        </section>
        <section class="rfq-section rfq-submit-section"><div><h2>Ready to request offers?</h2><p>Your request will be shared with the selected supplier or routed for supplier responses.</p></div><button class="button button-primary" type="submit">Submit RFQ <span>→</span></button></section>
    </form>
</main>
</body>
</html>