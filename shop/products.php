<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once __DIR__ . '/_layout.php';

$term = trim((string)($_GET['q'] ?? ''));
$categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);

$products = [];
$error = null;
$category = null;

try {
    $products = (new Product())->search($term, $categoryId ?: null, 60);

    if ($categoryId) {
        $category = (new Category())->find($categoryId);
    }
} catch (PDOException $exception) {
    error_log('Shop catalogue unavailable: ' . $exception->getMessage());
    $error = 'Our catalogue is temporarily unavailable. Please try again shortly.';
}

$title = $category['name'] ?? ($term ? 'Search results' : 'Marketplace products');

shopHeader($title);
?>

<main class="container shop-main">

    <div class="shop-breadcrumb">
        <a href="<?= e(BASE_URL) ?>/">Home</a>
        /
        <a href="<?= e(BASE_URL) ?>/shop/products.php">Marketplace</a>
        <?php if ($category): ?>
            / <?= e($category['name']) ?>
        <?php endif; ?>
    </div>

    <div class="shop-title">
        <div>
            <h1><?= e($title) ?></h1>

            <?php if ($term): ?>
                <p>Showing products matching "<?= e($term) ?>"</p>
            <?php else: ?>
                <p>Browse products from verified businesses.</p>
            <?php endif; ?>
        </div>

        <span><?= count($products) ?> products</span>
    </div>

    <?php if ($error): ?>

        <div class="shop-alert">
            <?= e($error) ?>
        </div>

    <?php elseif (!$products): ?>

        <div class="shop-empty">
            <h2>No products found</h2>
            <p>Try another search or browse all marketplace products.</p>
            <a href="<?= e(BASE_URL) ?>/shop/products.php">View all products</a>
        </div>

    <?php else: ?>

        <div class="market-grid">
            <?php foreach ($products as $product): ?>
                <?php productCard($product); ?>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>

<?php shopFooter(); ?>
