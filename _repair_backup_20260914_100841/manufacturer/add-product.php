<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/auth.php';
require_once ROOT_PATH . '/middleware/csrf.php';

requireRole('manufacturer');

$db = Database::getInstance()->getConnection();

$profileStmt = $db->prepare(
    'SELECT id, manufacturer_name
     FROM manufacturers
     WHERE user_id = ?
       AND status = "active"
     LIMIT 1'
);

$profileStmt->execute([(int)currentUserId()]);
$profile = $profileStmt->fetch();

if (!$profile) {
    http_response_code(404);
    exit('Manufacturer profile not found.');
}

$categories = $db->query(
    'SELECT id, name
     FROM categories
     WHERE status = "active"
     ORDER BY name'
)->fetchAll();

$errors = [];

$old = [
    'name' => '',
    'category_id' => '',
    'description' => '',
    'sku' => '',
    'retail_price' => '',
    'wholesale_price' => '',
    'minimum_order_quantity' => '1',
    'unit' => 'piece',
    'weight' => '',
    'country_of_origin' => 'Cameroon',
    'quantity' => '0',
    'reorder_level' => '10',
    'status' => 'active'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrfToken();

    foreach ($old as $field => $value) {
        if (
            isset($_POST[$field]) &&
            is_scalar($_POST[$field])
        ) {
            $old[$field] =
                trim((string)$_POST[$field]);
        }
    }

    if ($old['name'] === '') {
        $errors[] = 'Product name is required.';
    }

    if ((int)$old['category_id'] < 1) {
        $errors[] = 'Please select a category.';
    }

    if ((float)$old['retail_price'] <= 0) {
        $errors[] =
            'Retail price must be greater than zero.';
    }

    if (
        $old['wholesale_price'] !== '' &&
        (float)$old['wholesale_price'] <= 0
    ) {
        $errors[] =
            'Wholesale price must be greater than zero.';
    }

    if ((int)$old['minimum_order_quantity'] < 1) {
        $errors[] =
            'Minimum order quantity must be at least 1.';
    }

    if ((int)$old['quantity'] < 0) {
        $errors[] =
            'Stock quantity cannot be negative.';
    }

    if ((int)$old['reorder_level'] < 0) {
        $errors[] =
            'Reorder level cannot be negative.';
    }

    if (!$errors) {

        try {

            $productId =
                (new Product())->createForManufacturer(
                    (int)$profile['id'],
                    $old
                );

            redirect(
                BASE_URL .
                '/manufacturer/products.php?created=' .
                $productId
            );

        } catch (Throwable $exception) {

            error_log(
                'Manufacturer product creation failed: ' .
                $exception->getMessage()
            );

            $errors[] =
                'The product could not be created. Check your information and try again.';
        }
    }
}
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
Add Product - Manufacturer Centre
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
href="<?= e(BASE_URL) ?>/manufacturer/dashboard.php"
>

<span class="brand-mark">Q</span>

<span>
<strong>QUINCY</strong>
<small>MANUFACTURER CENTRE</small>
</span>

</a>

<div class="supplier-top-actions">

<a href="<?= e(BASE_URL) ?>/manufacturer/dashboard.php">
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
    (string)$profile['manufacturer_name'],
    0,
    1
))) ?>

</span>

<div>

<strong>
<?= e($profile['manufacturer_name']) ?>
</strong>

<small>
Manufacturer account
</small>

</div>

</div>

<nav>

<p>OPERATIONS</p>

<a href="<?= e(BASE_URL) ?>/manufacturer/dashboard.php">
Overview
</a>

<a
class="supplier-nav-active"
href="<?= e(BASE_URL) ?>/manufacturer/products.php"
>
Products
</a>

<a href="<?= e(BASE_URL) ?>/manufacturer/inventory.php">
Inventory
</a>

<a href="<?= e(BASE_URL) ?>/manufacturer/orders.php">
Orders
</a>

<p>BUYER PIPELINE</p>

<a href="<?= e(BASE_URL) ?>/manufacturer/quotations.php">
Quotation requests
</a>

<a href="<?= e(BASE_URL) ?>/manufacturer/negotiations.php">
Negotiations
</a>

<p>ACCOUNT</p>

<a href="<?= e(BASE_URL) ?>/manufacturer/profile.php">
Company profile
</a>

<a href="<?= e(BASE_URL) ?>/manufacturer/settings.php">
Settings
</a>

</nav>

</aside>

<main class="supplier-workspace">

<div class="supplier-breadcrumb">

Manufacturer centre
<span>/</span>
Products
<span>/</span>
Add product

</div>

<div class="supplier-welcome">

<div>

<p class="eyebrow">
MANUFACTURING CATALOGUE
</p>

<h1>
Add product
</h1>

<p>
Create a manufactured product listing for Quincy Store.
</p>

</div>

</div>

<?php if ($errors): ?>

<div class="catalog-notice">

<strong>
Please correct the following:
</strong>

<ul>

<?php foreach ($errors as $error): ?>

<li><?= e($error) ?></li>

<?php endforeach; ?>

</ul>

</div>

<?php endif; ?>

<section class="supplier-panel">

<form method="post">

<?= csrfField() ?>

<label for="name">
Product name
</label>

<input
id="name"
name="name"
type="text"
maxlength="200"
required
value="<?= e($old['name']) ?>"
>

<label for="category_id">
Category
</label>

<select
id="category_id"
name="category_id"
required
>

<option value="">
Select category
</option>

<?php foreach ($categories as $category): ?>

<option
value="<?= (int)$category['id'] ?>"
<?= (string)$old['category_id'] ===
    (string)$category['id']
    ? 'selected'
    : '' ?>
>

<?= e($category['name']) ?>

</option>

<?php endforeach; ?>

</select>

<label for="description">
Description
</label>

<textarea
id="description"
name="description"
rows="5"
><?= e($old['description']) ?></textarea>

<label for="sku">
SKU
</label>

<input
id="sku"
name="sku"
type="text"
maxlength="100"
value="<?= e($old['sku']) ?>"
>

<label for="retail_price">
Retail price
</label>

<input
id="retail_price"
name="retail_price"
type="number"
min="0.01"
step="0.01"
required
value="<?= e($old['retail_price']) ?>"
>

<label for="wholesale_price">
Wholesale price
</label>

<input
id="wholesale_price"
name="wholesale_price"
type="number"
min="0.01"
step="0.01"
value="<?= e($old['wholesale_price']) ?>"
>

<label for="minimum_order_quantity">
Minimum order quantity
</label>

<input
id="minimum_order_quantity"
name="minimum_order_quantity"
type="number"
min="1"
required
value="<?= e($old['minimum_order_quantity']) ?>"
>

<label for="unit">
Unit
</label>

<input
id="unit"
name="unit"
type="text"
maxlength="50"
value="<?= e($old['unit']) ?>"
>

<label for="quantity">
Initial stock quantity
</label>

<input
id="quantity"
name="quantity"
type="number"
min="0"
required
value="<?= e($old['quantity']) ?>"
>

<label for="reorder_level">
Reorder level
</label>

<input
id="reorder_level"
name="reorder_level"
type="number"
min="0"
value="<?= e($old['reorder_level']) ?>"
>

<label for="weight">
Weight
</label>

<input
id="weight"
name="weight"
type="number"
min="0"
step="0.01"
value="<?= e($old['weight']) ?>"
>

<label for="country_of_origin">
Country of origin
</label>

<input
id="country_of_origin"
name="country_of_origin"
type="text"
maxlength="100"
value="<?= e($old['country_of_origin']) ?>"
>

<label for="status">
Status
</label>

<select id="status" name="status">

<option value="active"
<?= $old['status'] === 'active'
    ? 'selected'
    : '' ?>>
Active
</option>

<option value="draft"
<?= $old['status'] === 'draft'
    ? 'selected'
    : '' ?>>
Draft
</option>

<option value="out_of_stock"
<?= $old['status'] === 'out_of_stock'
    ? 'selected'
    : '' ?>>
Out of stock
</option>

<option value="discontinued"
<?= $old['status'] === 'discontinued'
    ? 'selected'
    : '' ?>>
Discontinued
</option>

</select>

<p>

<button
class="button button-primary"
type="submit"
>
Create product
</button>

<a
class="button"
href="<?= e(BASE_URL) ?>/manufacturer/products.php"
>
Cancel
</a>

</p>

</form>

</section>

</main>

</div>

</body>
</html>
