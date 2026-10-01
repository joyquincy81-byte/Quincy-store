<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/functions.php';

$term = trim((string) ($_GET['q'] ?? ''));
$minimumQuantity = isset($_GET['quantity']) && ctype_digit((string) $_GET['quantity']) ? (int) $_GET['quantity'] : 0;
$products = [];
$categories = [];
$marketplaceError = null;

try {
    $products = (new Product())->wholesaleSearch($term, null, $minimumQuantity, 30);
    $categories = (new Category())->all();
} catch (PDOException $exception) {
    error_log('B2B marketplace unavailable: ' . $exception->getMessage());
    $marketplaceError = 'Wholesale listings are temporarily unavailable. Please try again shortly.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wholesale marketplace - <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
</head>
<body class="b2b-marketplace-page">
<header class="directory-header">
    <div class="container directory-header-row">
        <a class="brand" href="<?= e(BASE_URL) ?>/"><span class="brand-mark">Q</span><span><strong>QUINCY</strong><small>BUSINESS</small></span></a>
        <nav><a href="<?= e(BASE_URL) ?>/b2b/marketplace.php" class="directory-active">Wholesale marketplace</a><a href="<?= e(BASE_URL) ?>/b2b/suppliers.php">Find suppliers</a><a href="<?= e(BASE_URL) ?>/b2b/request-quotation.php">Post an RFQ</a><a href="<?= e(BASE_URL) ?>/login.php">Sign in</a></nav>
    </div>
</header>
<main class="container b2b-main">
    <div class="directory-breadcrumb"><a href="<?= e(BASE_URL) ?>/">Home</a><span>/</span><span>For business</span><span>/</span>Wholesale marketplace</div>
    <section class="b2b-hero">
        <div><p class="eyebrow">SOURCE FOR BUSINESS</p><h1>Find products and suppliers built for your next order.</h1><p>Compare wholesale prices, minimum order quantities, and supplier verification before you request a quote.</p></div>
        <div class="b2b-hero-stats"><span><strong>Wholesale</strong><small>pricing visibility</small></span><span><strong>Verified</strong><small>supplier signals</small></span><span><strong>RFQ</strong><small>direct negotiation</small></span></div>
    </section>
    <form class="b2b-search" method="get">
        <label><span>What do you need?</span><input name="q" value="<?= e($term) ?>" placeholder="Search products, materials, or suppliers"></label>
        <label><span>Order quantity</span><input type="number" min="0" name="quantity" value="<?= $minimumQuantity ?: '' ?>" placeholder="Any quantity"></label>
        <button class="button button-primary" type="submit">Find sourcing options</button>
    </form>
    <div class="b2b-layout">
        <aside class="b2b-sidebar">
            <h2>Browse wholesale</h2>
            <a class="b2b-sidebar-link active" href="<?= e(BASE_URL) ?>/b2b/marketplace.php">All products</a>
            <?php foreach (array_slice($categories, 0, 12) as $category): ?><a class="b2b-sidebar-link" href="<?= e(BASE_URL) ?>/shop/products.php?category=<?= (int) $category['id'] ?>"><?= e($category['name']) ?></a><?php endforeach; ?>
            <div class="b2b-help"><strong>Need something specific?</strong><p>Tell suppliers what you need and receive offers.</p><a href="<?= e(BASE_URL) ?>/b2b/request-quotation.php">Post an RFQ →</a></div>
        </aside>
        <section class="b2b-results">
            <div class="b2b-results-head"><div><p class="eyebrow">WHOLESALE LISTINGS</p><h2><?= $term ? 'Results for “' . e($term) . '”' : 'Recommended for business buyers' ?></h2></div><span><?= count($products) ?> options</span></div>
            <?php if ($marketplaceError): ?><div class="catalog-notice"><?= e($marketplaceError) ?></div><?php elseif (!$products): ?><div class="supplier-empty"><h2>No wholesale listings found</h2><p>Try another search or post an RFQ so suppliers can respond.</p><a href="<?= e(BASE_URL) ?>/b2b/request-quotation.php">Post an RFQ</a></div><?php else: ?>
                <div class="b2b-product-grid">
                    <?php foreach ($products as $product): ?>
                        <article class="b2b-product-card">
                            <div class="b2b-product-image"><?= e(strtoupper(substr((string) $product['name'], 0, 1))) ?><span>Wholesale</span></div>
                            <div class="b2b-product-body"><small><?= e($product['category_name'] ?? 'Business supply') ?></small><h3><a href="<?= e(BASE_URL) ?>/b2b/product-details.php?id=<?= (int) $product['id'] ?>"><?= e($product['name']) ?></a></h3><div class="b2b-price"><strong><?= e(formatPrice($product['wholesale_price'])) ?></strong><span>/ <?= e($product['unit'] ?: 'unit') ?></span></div><p class="b2b-moq">MOQ: <strong><?= (int) $product['minimum_order_quantity'] ?> <?= e($product['unit'] ?: 'units') ?></strong></p><div class="b2b-supplier"><span class="supplier-mini-avatar"><?= e(strtoupper(substr((string) $product['supplier_name'], 0, 1))) ?></span><span><strong><?= e($product['supplier_name']) ?></strong><small><?= e(implode(', ', array_filter([$product['city'], $product['country']]))) ?></small></span><?php if ($product['verification_status'] === 'verified'): ?><b class="verified-badge">Verified</b><?php endif; ?></div><div class="b2b-card-actions"><a class="button button-primary" href="<?= e(BASE_URL) ?>/b2b/request-quotation.php?product=<?= (int) $product['id'] ?>">Request quote</a><a href="<?= e(BASE_URL) ?>/b2b/suppliers.php?q=<?= urlencode($product['supplier_name']) ?>">Supplier profile</a></div></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
</body>
</html>