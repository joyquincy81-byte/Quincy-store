<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/auth.php';
require_once ROOT_PATH . '/middleware/csrf.php';

requireRole('manufacturer');

$error = null;

$db = Database::getInstance()->getConnection();
$userId = (int)currentUserId();

$profileStmt = $db->prepare(
    'SELECT *
     FROM manufacturers
     WHERE user_id = ?
       AND status = "active"
     LIMIT 1'
);
$profileStmt->execute([$userId]);
$manufacturer = $profileStmt->fetch();

if (!$manufacturer) {
    http_response_code(403);
    exit('Manufacturer profile not found.');
}

$categoryStmt = $db->query(
    "SELECT id, name
     FROM categories
     WHERE status = 'active'
     ORDER BY name"
);
$categories = $categoryStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verifyCsrfToken();

        $product = new Product();

        $productId = $product->createForManufacturer(
            (int)$manufacturer['id'],
            $_POST
        );

        redirect(
            BASE_URL . '/manufacturer/products.php?created=' . $productId
        );
    } catch (Throwable $e) {
        error_log('Manufacturer add product: ' . $e->getMessage());
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Add Product - Manufacturer Centre</title>
<link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/supplier.css">
</head>
<body class="supplier-dashboard-page">

<header class="supplier-topbar">
<div class="container supplier-topbar-inner">
<a class="brand" href="<?= e(BASE_URL) ?>/manufacturer/dashboard.php">
<span class="brand-mark">Q</span>
<span><strong>QUINCY</strong><small>MANUFACTURER CENTRE</small></span>
</a>
<div class="supplier-top-actions">
<a href="<?= e(BASE_URL) ?>/manufacturer/products.php">Products</a>
<a href="<?= e(BASE_URL) ?>/logout.php">Sign out</a>
</div>
</div>
</header>

<div class="supplier-shell container">

<aside class="supplier-sidebar">
<div class="supplier-profile">
<span class="supplier-profile-avatar">
<?= e(strtoupper(substr((string)$manufacturer['manufacturer_name'], 0, 1))) ?>
</span>
<div>
<strong><?= e($manufacturer['manufacturer_name']) ?></strong>
<small>Manufacturer account</small>
</div>
</div>

<nav>
<p>OPERATIONS</p>
<a href="<?= e(BASE_URL) ?>/manufacturer/dashboard.php">Overview</a>
<a class="supplier-nav-active" href="<?= e(BASE_URL) ?>/manufacturer/products.php">Products</a>
<a href="<?= e(BASE_URL) ?>/manufacturer/inventory.php">Inventory</a>
<a href="<?= e(BASE_URL) ?>/manufacturer/orders.php">Orders</a>
<p>BUYER PIPELINE</p>
<a href="<?= e(BASE_URL) ?>/manufacturer/quotations.php">Quotation requests</a>
<a href="<?= e(BASE_URL) ?>/manufacturer/negotiations.php">Negotiations</a>
<p>ACCOUNT</p>
<a href="<?= e(BASE_URL) ?>/manufacturer/profile.php">Company profile</a>
<a href="<?= e(BASE_URL) ?>/manufacturer/settings.php">Settings</a>
</nav>
</aside>

<main class="supplier-workspace">

<div class="supplier-breadcrumb">
Manufacturer centre <span>/</span> Products <span>/</span> Add product
</div>

<div class="supplier-welcome">
<div>
<p class="eyebrow">MANUFACTURING CATALOGUE</p>
<h1>Add a product</h1>
<p>Create a new product listing for your manufacturing catalogue.</p>
</div>

<a class="button button-outline"
   href="<?= e(BASE_URL) ?>/manufacturer/products.php">
Back to products
</a>
</div>

<?php if ($error): ?>
<div class="catalog-notice"><?= e($error) ?></div>
<?php endif; ?>

<section class="supplier-panel">

<form method="post" class="cw-form">

<?= csrfField() ?>

<div class="cw-grid cw-grid-2">

<label>
Product name
<input type="text" name="name" required maxlength="200"
       value="<?= e($_POST['name'] ?? '') ?>">
</label>

<label>
Category
<select name="category_id" required>
<option value="">Select category</option>
<?php foreach ($categories as $category): ?>
<option value="<?= (int)$category['id'] ?>"
<?= ((int)($_POST['category_id'] ?? 0) === (int)$category['id']) ? 'selected' : '' ?>>
<?= e($category['name']) ?>
</option>
<?php endforeach; ?>
</select>
</label>

<label>
SKU
<input type="text" name="sku" maxlength="100"
       value="<?= e($_POST['sku'] ?? '') ?>">
</label>

<label>
Unit
<input type="text" name="unit"
       value="<?= e($_POST['unit'] ?? 'piece') ?>">
</label>

<label>
Retail price
<input type="number" name="retail_price"
       min="0.01" step="0.01" required
       value="<?= e($_POST['retail_price'] ?? '') ?>">
</label>

<label>
Wholesale price
<input type="number" name="wholesale_price"
       min="0.01" step="0.01"
       value="<?= e($_POST['wholesale_price'] ?? '') ?>">
</label>

<label>
Minimum order quantity
<input type="number" name="minimum_order_quantity"
       min="1"
       value="<?= e($_POST['minimum_order_quantity'] ?? '1') ?>">
</label>

<label>
Initial stock quantity
<input type="number" name="quantity"
       min="0"
       value="<?= e($_POST['quantity'] ?? '0') ?>">
</label>

<label>
Reorder level
<input type="number" name="reorder_level"
       min="0"
       value="<?= e($_POST['reorder_level'] ?? '10') ?>">
</label>

<label>
Weight
<input type="number" name="weight"
       min="0" step="0.01"
       value="<?= e($_POST['weight'] ?? '') ?>">
</label>

<label>
Country of origin
<input type="text" name="country_of_origin"
       value="<?= e($_POST['country_of_origin'] ?? 'Cameroon') ?>">
</label>

<label>
Status
<select name="status">
<option value="active">Active</option>
<option value="draft">Draft</option>
<option value="out_of_stock">Out of stock</option>
<option value="discontinued">Discontinued</option>
</select>
</label>

</div>

<label>
Description
<textarea name="description" rows="6"
placeholder="Describe the manufactured product..."><?= e($_POST['description'] ?? '') ?></textarea>
</label>

<div style="margin-top:20px">
<button class="button button-primary" type="submit">
Create product
</button>
</div>

</form>

</section>

</main>
</div>
</body>
</html>
