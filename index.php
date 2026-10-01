<?php
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';

$products = [];
$categories = [];
$catalogError = null;

try {
    $products = (new Product())->search('', null, 8);
    $categories = (new Category())->all();
} catch (PDOException $exception) {
    error_log('Storefront catalog unavailable: ' . $exception->getMessage());
    $catalogError = 'The catalog is temporarily unavailable. Please try again shortly.';
}

$categoryCards = array_slice($categories, 0, 6);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Source products from trusted suppliers and manufacturers on Quincy Store.">
    <title><?= e(SITE_NAME) ?> | Source smarter</title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
</head>
<body>
<div class="topbar">
    <div class="container topbar-inner">
        <span>Cameroon's trusted B2B and B2C marketplace</span>
        <nav aria-label="Utility navigation">
            <a href="<?= e(BASE_URL) ?>/b2b/index.php">Source for business</a>
            <a href="<?= e(BASE_URL) ?>/shop/suppliers.php">Find suppliers</a>
            <a href="<?= e(BASE_URL) ?>/login.php">Sign in</a>
        </nav>
    </div>
</div>

<header class="site-header">
    <div class="container header-main">
        <a class="brand" href="<?= e(BASE_URL) ?>/">
            <img src="assets/images/logos/logos.jpeg" alt="Quincy Store Logo" class="brand-mark" width="50" height="50">
            <span><strong>QUINCY</strong><small>STORE</small></span>
        </a>
        <form class="search-form" action="<?= e(BASE_URL) ?>/shop/search.php" method="get">
            <label class="sr-only" for="site-search">Search products and suppliers</label>
            <input id="site-search" name="q" type="search" placeholder="What are you looking for today?" required>
            <button type="submit">Search</button>
        </form>
        <div class="header-actions">
            <a href="<?= e(BASE_URL) ?>/register.php">Join free</a>
            <a class="header-cart" href="<?= e(BASE_URL) ?>/b2c/cart.php">Cart <span>0</span></a>
        </div>
    </div>
    <div class="container category-nav">
        <a class="all-categories" href="<?= e(BASE_URL) ?>/shop/categories.php">All categories</a>
        <a href="<?= e(BASE_URL) ?>/shop/products.php">Products</a>
        <a href="<?= e(BASE_URL) ?>/b2b/marketplace.php">Wholesale marketplace</a>
        <a href="<?= e(BASE_URL) ?>/shop/suppliers.php">Suppliers</a>
        <a href="<?= e(BASE_URL) ?>/b2b/request-quotation.php">Request a quotation</a>
    </div>
</header>

<main>
    <?php if ($catalogError): ?>
        <div class="container catalog-notice" role="status"><?= e($catalogError) ?></div>
    <?php endif; ?>
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-copy">
                <p class="eyebrow">SOURCE WITH CONFIDENCE</p>
                <h1>Everything your business needs, in one place.</h1>
                <p class="hero-text">Connect with verified Cameroonian suppliers, compare products, and grow your business with flexible B2B sourcing.</p>
                <div class="hero-actions">
                    <a class="button button-primary" href="<?= e(BASE_URL) ?>/b2b/marketplace.php">Explore wholesale</a>
                    <a class="button button-light" href="<?= e(BASE_URL) ?>/shop/products.php">Shop retail</a>
                </div>
                <div class="trust-row"><span>Verified suppliers</span><span>Secure payments</span><span>Local delivery</span></div>
            </div>
            <div class="hero-panel">
                <p class="eyebrow">QUINCY BUSINESS</p>
                <h2>Source smarter. Trade better.</h2>
                <p>Get competitive offers from suppliers that understand your market.</p>
                <a href="<?= e(BASE_URL) ?>/register.php?role=business_buyer">Create a business account <span>-></span></a>
            </div>
        </div>
    </section>

    <section class="section container">
        <div class="section-heading"><div><p class="eyebrow">DISCOVER</p><h2>Shop by category</h2></div><a href="<?= e(BASE_URL) ?>/shop/categories.php">View all categories -></a></div>
        <div class="category-grid">
            <?php if ($categoryCards): ?>
                <?php foreach ($categoryCards as $category): ?>
                    <a class="category-card" href="<?= e(BASE_URL) ?>/shop/products.php?category=<?= (int) $category['id'] ?>">
                        <span class="category-icon"><?= e(strtoupper(substr((string) $category['name'], 0, 1))) ?></span>
                        <strong><?= e($category['name']) ?></strong>
                        <small>Explore products</small>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach (['Agriculture', 'Food and beverage', 'Fashion', 'Home and office', 'Electronics', 'Construction'] as $category): ?>
                    <a class="category-card" href="<?= e(BASE_URL) ?>/shop/products.php"><span class="category-icon"><?= e(substr($category, 0, 1)) ?></span><strong><?= e($category) ?></strong><small>Explore products</small></a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="section section-muted">
        <div class="container">
            <div class="section-heading"><div><p class="eyebrow">FEATURED SOURCING</p><h2>Popular products</h2></div><a href="<?= e(BASE_URL) ?>/shop/products.php">See all products -></a></div>
            <div class="product-grid">
                <?php if ($products): ?>
                    <?php foreach ($products as $product): ?>
                        <article class="product-card">
                            <div class="product-image"><?= e(strtoupper(substr((string) $product['name'], 0, 1))) ?></div>
                            <div class="product-body"><small><?= e($product['category_name'] ?? 'Marketplace') ?></small><h3><a href="<?= e(BASE_URL) ?>/shop/product-details.php?id=<?= (int) $product['id'] ?>"><?= e($product['name']) ?></a></h3><strong><?= e(formatPrice($product['retail_price'])) ?></strong><p><?= (int) $product['available_quantity'] ?> available</p></div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state"><h3>Your next supplier is waiting.</h3><p>Browse the marketplace to discover products and wholesale opportunities.</p><a class="button button-primary" href="<?= e(BASE_URL) ?>/shop/products.php">Browse products</a></div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="section container cta-strip"><div><p class="eyebrow">SELL ON QUINCY STORE</p><h2>Reach more customers and grow your business.</h2><p>List your products, manage orders, and connect with serious buyers.</p></div><a class="button button-primary" href="<?= e(BASE_URL) ?>/register.php?role=supplier">Become a supplier</a></section>
</main>

<footer class="site-footer"><div class="container footer-grid"><div><a class="brand brand-footer" href="<?= e(BASE_URL) ?>/"><span class="brand-mark">Q</span><span><strong>QUINCY</strong><small>STORE</small></span></a><p>Trade locally. Grow globally.</p></div><div><h3>Marketplace</h3><a href="<?= e(BASE_URL) ?>/shop/products.php">Products</a><a href="<?= e(BASE_URL) ?>/shop/categories.php">Categories</a><a href="<?= e(BASE_URL) ?>/shop/suppliers.php">Suppliers</a></div><div><h3>Business</h3><a href="<?= e(BASE_URL) ?>/b2b/marketplace.php">Wholesale</a><a href="<?= e(BASE_URL) ?>/b2b/request-quotation.php">Request quotation</a><a href="<?= e(BASE_URL) ?>/register.php?role=supplier">Sell with us</a></div><div><h3>Account</h3><a href="<?= e(BASE_URL) ?>/login.php">Sign in</a><a href="<?= e(BASE_URL) ?>/register.php">Create account</a></div></div><div class="container footer-bottom"><span>&copy; <?= date('Y') ?> Quincy Store</span><span>Built for Cameroon and beyond</span></div></footer>
</body>
</html>
