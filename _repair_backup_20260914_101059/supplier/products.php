<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/auth.php';

requireRole('supplier');

$db = Database::getInstance()->getConnection();

$profileStmt = $db->prepare(
    'SELECT id, supplier_name
     FROM suppliers
     WHERE user_id = ?
       AND status = "active"
     LIMIT 1'
);

$profileStmt->execute([(int)currentUserId()]);
$profile = $profileStmt->fetch();

if (!$profile) {
    http_response_code(404);
    exit('Supplier profile not found.');
}

$productStmt = $db->prepare(
    'SELECT p.*,
            c.name AS category_name,
            COALESCE(i.quantity - i.reserved_quantity, 0)
                AS available_quantity
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     LEFT JOIN inventory i
        ON i.product_id = p.id
       AND i.variant_id IS NULL
     WHERE p.supplier_id = ?
     ORDER BY p.created_at DESC'
);

$productStmt->execute([
    (int)$profile['id']
]);

$products = $productStmt->fetchAll();

$createdId =
    isset($_GET['created'])
    ? (int)$_GET['created']
    : 0;
?>
<!doctype html>
<html lang="en">

<head>

<meta charset="utf-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1"
>

<title>
Products - Supplier Centre
</title>

<link
rel="stylesheet"
href="<?= e(BASE_URL) ?>/assets/css/style.css"
>

<link
rel="stylesheet"
href="<?= e(BASE_URL) ?>/assets/css/supplier.css"
>

</head>

<body class="supplier-dashboard-page">

<header class="supplier-topbar">

<div class="container supplier-topbar-inner">

<a
class="brand"
href="<?= e(BASE_URL) ?>/supplier/dashboard.php"
>

<span class="brand-mark">Q</span>

<span>
<strong>QUINCY</strong>
<small>SUPPLIER CENTRE</small>
</span>

</a>

<div class="supplier-top-actions">

<a href="<?= e(BASE_URL) ?>/supplier/dashboard.php">
Dashboard
</a>

<a href="<?= e(BASE_URL) ?>/logout.php">
Sign out
</a>

</div>

</div>

</header>

<div class="supplier-shell container">

<aside class="supplier-sidebar">

<div class="supplier-profile">

<span class="supplier-profile-avatar">

<?= e(strtoupper(substr(
    (string)$profile['supplier_name'],
    0,
    1
))) ?>

</span>

<div>

<strong>
<?= e($profile['supplier_name']) ?>
</strong>

<small>
Supplier account
</small>

</div>

</div>

<nav>

<p>OPERATIONS</p>

<a href="<?= e(BASE_URL) ?>/supplier/dashboard.php">
Overview
</a>

<a
class="supplier-nav-active"
href="<?= e(BASE_URL) ?>/supplier/products.php"
>
Products
</a>

<a href="<?= e(BASE_URL) ?>/supplier/inventory.php">
Inventory
</a>

<a href="<?= e(BASE_URL) ?>/supplier/orders.php">
Orders
</a>

<p>BUYER PIPELINE</p>

<a href="<?= e(BASE_URL) ?>/supplier/quotations.php">
Quotation requests
</a>

<a href="<?= e(BASE_URL) ?>/supplier/negotiations.php">
Negotiations
</a>

<p>ACCOUNT</p>

<a href="<?= e(BASE_URL) ?>/supplier/profile.php">
Company profile
</a>

<a href="<?= e(BASE_URL) ?>/supplier/settings.php">
Settings
</a>

</nav>

</aside>

<main class="supplier-workspace">

<div class="supplier-breadcrumb">
Supplier centre
<span>/</span>
Products
</div>

<div class="supplier-welcome">

<div>

<p class="eyebrow">
CATALOGUE
</p>

<h1>
Products
</h1>

<p>
Manage products offered by your company.
</p>

</div>

<a
class="button button-primary"
href="<?= e(BASE_URL) ?>/supplier/add-product.php"
>
+ Add product
</a>

</div>

<?php if ($createdId > 0): ?>

<div class="catalog-notice">
Product #<?= $createdId ?> was created successfully.
</div>

<?php endif; ?>

<section class="supplier-panel">

<div class="supplier-panel-heading">

<div>

<p class="eyebrow">
YOUR CATALOGUE
</p>

<h2>
<?= count($products) ?> product(s)
</h2>

</div>

</div>

<?php if (!$products): ?>

<p class="admin-muted">
No products have been added yet.
</p>

<?php else: ?>

<div class="supplier-product-list">

<?php foreach ($products as $product): ?>

<article>

<div>

<strong>
<?= e($product['name']) ?>
</strong>

<small>
<?= e($product['category_name'] ?? 'Uncategorised') ?>
· SKU <?= e($product['sku'] ?: 'No SKU') ?>
· Stock <?= number_format(
    (int)$product['available_quantity']
) ?>
</small>

</div>

<span
class="supplier-status supplier-status-<?= e($product['status']) ?>"
>
<?= e(
    ucfirst(
        str_replace(
            '_',
            ' ',
            $product['status']
        )
    )
) ?>
</span>

</article>

<?php endforeach; ?>

</div>

<?php endif; ?>

</section>

</main>

</div>

</body>
</html>
