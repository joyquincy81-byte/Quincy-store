<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/admin.php';

$db = Database::getInstance()->getConnection();

$message = '';
$error = '';
$editProduct = null;

$uploadDir = ROOT_PATH . '/assets/images/products/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $retailPrice = (float) ($_POST['retail_price'] ?? 0);
        $wholesalePrice = (float) ($_POST['wholesale_price'] ?? 0);
        $sku = trim((string) ($_POST['sku'] ?? ''));
        $minimumOrder = max(1, (int) ($_POST['minimum_order_quantity'] ?? 1));
        $unit = trim((string) ($_POST['unit'] ?? 'piece'));
        $country = trim((string) ($_POST['country_of_origin'] ?? 'Cameroon'));
        $status = (string) ($_POST['status'] ?? 'draft');

        if ($name === '' || $categoryId <= 0) {
            throw new RuntimeException('Product name and category are required.');
        }

        $image = '';

        if ($id > 0) {
            $stmt = $db->prepare('SELECT image FROM products WHERE id = ?');
            $stmt->execute([$id]);
            $old = $stmt->fetch(PDO::FETCH_ASSOC);
            $image = (string) ($old['image'] ?? '');
        }

        /* IMAGE UPLOAD */
        if (
            isset($_FILES['image_file']) &&
            $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE
        ) {
            if ($_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Image upload failed.');
            }

            if ($_FILES['image_file']['size'] > 5 * 1024 * 1024) {
                throw new RuntimeException('Image must not exceed 5MB.');
            }

            $info = @getimagesize($_FILES['image_file']['tmp_name']);

            if ($info === false) {
                throw new RuntimeException('Please select a valid image.');
            }

            $extensions = [
                IMAGETYPE_JPEG => 'jpg',
                IMAGETYPE_PNG => 'png',
                IMAGETYPE_WEBP => 'webp',
                IMAGETYPE_GIF => 'gif'
            ];

            $type = $info[2];

            if (!isset($extensions[$type])) {
                throw new RuntimeException(
                    'Only JPG, PNG, WEBP and GIF images are allowed.'
                );
            }

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $base = strtolower(
                trim(
                    preg_replace(
                        '/[^a-z0-9]+/i',
                        '-',
                        pathinfo(
                            $_FILES['image_file']['name'],
                            PATHINFO_FILENAME
                        )
                    ),
                    '-'
                )
            );

            $fileName =
                ($base ?: 'product') .
                '-' .
                time() .
                '.' .
                $extensions[$type];

            if (!move_uploaded_file(
                $_FILES['image_file']['tmp_name'],
                $uploadDir . $fileName
            )) {
                throw new RuntimeException('Could not save the image.');
            }

            $image = 'assets/images/products/' . $fileName;
        }

        $slug = strtolower(
            trim(
                preg_replace(
                    '/[^a-z0-9]+/i',
                    '-',
                    $name
                ),
                '-'
            )
        );

        /* UPDATE */
        if ($id > 0) {

            $stmt = $db->prepare(
                'UPDATE products
                 SET category_id=?,
                     name=?,
                     slug=?,
                     description=?,
                     image=?,
                     sku=?,
                     retail_price=?,
                     wholesale_price=?,
                     minimum_order_quantity=?,
                     unit=?,
                     country_of_origin=?,
                     status=?
                 WHERE id=?'
            );

            $stmt->execute([
                $categoryId,
                $name,
                $slug . '-' . $id,
                $description ?: null,
                $image ?: null,
                $sku ?: null,
                $retailPrice,
                $wholesalePrice ?: null,
                $minimumOrder,
                $unit ?: 'piece',
                $country ?: 'Cameroon',
                $status,
                $id
            ]);

            if ($image !== '') {

                $db->prepare(
                    'DELETE FROM product_images WHERE product_id=?'
                )->execute([$id]);

                $db->prepare(
                    'INSERT INTO product_images
                     (product_id,image,is_primary,sort_order)
                     VALUES (?, ?, 1, 0)'
                )->execute([$id, $image]);
            }

            $message = 'Product updated successfully.';
        }

        /* ADD */
        else {

            $stmt = $db->prepare(
                'INSERT INTO products
                 (category_id,name,slug,description,image,sku,
                  retail_price,wholesale_price,minimum_order_quantity,
                  unit,country_of_origin,status)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
            );

            $stmt->execute([
                $categoryId,
                $name,
                $slug . '-' . time(),
                $description ?: null,
                $image ?: null,
                $sku ?: null,
                $retailPrice,
                $wholesalePrice ?: null,
                $minimumOrder,
                $unit ?: 'piece',
                $country ?: 'Cameroon',
                $status
            ]);

            $newId = (int) $db->lastInsertId();

            if ($image !== '') {

                $db->prepare(
                    'INSERT INTO product_images
                     (product_id,image,is_primary,sort_order)
                     VALUES (?, ?, 1, 0)'
                )->execute([$newId, $image]);
            }

            $message = 'Product added successfully.';
        }

    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

/* EDIT */
if (isset($_GET['edit'])) {

    $stmt = $db->prepare(
        'SELECT * FROM products WHERE id=?'
    );

    $stmt->execute([
        (int) $_GET['edit']
    ]);

    $editProduct =
        $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/* CATEGORIES */
$categories = $db->query(
    "SELECT id,name
     FROM categories
     WHERE status='active'
     ORDER BY name"
)->fetchAll(PDO::FETCH_ASSOC);

/* PRODUCTS */
$products = $db->query(
    'SELECT p.*, c.name AS category_name
     FROM products p
     LEFT JOIN categories c
       ON c.id=p.category_id
     ORDER BY p.updated_at DESC
     LIMIT 100'
)->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Products - Quincy Store Admin</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

<style>

body {
    background: #f5f7fb;
    color: #14213d;
}

.page-header {
    background: #0a192f;
    color: white;
    border-radius: 12px;
    padding: 25px 30px;
    margin-bottom: 25px;
}

.page-header h1 {
    margin: 0;
    font-weight: 700;
}

.card {
    border: 0;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(7, 21, 47, .07);
}

.card-header {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 700;
}

.form-label {
    font-weight: 600;
    color: #334155;
}

.product-image {
    width: 65px;
    height: 65px;
    object-fit: contain;
    border-radius: 8px;
    background: #f1f5f9;
}

.product-placeholder {
    width: 65px;
    height: 65px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: #eaf2fb;
    color: #1b4b86;
    font-size: 24px;
    font-weight: 700;
}

.btn-primary {
    background: #1b4b86;
    border-color: #1b4b86;
}

.btn-primary:hover {
    background: #0a192f;
    border-color: #0a192f;
}

.table > :not(caption) > * > * {
    padding: 14px 12px;
    vertical-align: middle;
}

.badge-active {
    background: #dcfce7;
    color: #166534;
}

.badge-draft {
    background: #fef3c7;
    color: #92400e;
}

.badge-out {
    background: #fee2e2;
    color: #991b1b;
}

.badge-discontinued {
    background: #e5e7eb;
    color: #374151;
}

</style>

</head>

<body>

<div class="container-fluid py-4 px-lg-5">

<!-- HEADER -->

<div class="page-header d-flex justify-content-between align-items-center">

<div>

<h1>Products</h1>

<p class="mb-0 text-white-50">
Manage your Quincy Store products
</p>

</div>

<a href="#productForm"
   class="btn btn-light">

Add Product

</a>

</div>


<!-- ALERTS -->

<?php if ($message): ?>

<div class="alert alert-success">
<?= e($message) ?>
</div>

<?php endif; ?>


<?php if ($error): ?>

<div class="alert alert-danger">
<?= e($error) ?>
</div>

<?php endif; ?>


<!-- PRODUCT FORM -->

<div class="card mb-4"
     id="productForm">

<div class="card-header p-3">

<?= $editProduct
    ? 'Edit Product'
    : 'Add New Product' ?>

</div>

<div class="card-body p-4">

<form method="post"
      enctype="multipart/form-data">

<?php if ($editProduct): ?>

<input type="hidden"
       name="id"
       value="<?= (int) $editProduct['id'] ?>">

<?php endif; ?>


<div class="row g-3">

<!-- NAME -->

<div class="col-md-6">

<label class="form-label">
Product Name
</label>

<input
    type="text"
    name="name"
    class="form-control"
    required
    value="<?= e($editProduct['name'] ?? '') ?>">

</div>


<!-- CATEGORY -->

<div class="col-md-6">

<label class="form-label">
Category
</label>

<select
    name="category_id"
    class="form-select"
    required>

<option value="">
Select category
</option>

<?php foreach ($categories as $category): ?>

<option
    value="<?= (int) $category['id'] ?>"
    <?= ((int)($editProduct['category_id'] ?? 0)
        === (int)$category['id'])
        ? 'selected'
        : '' ?>>

<?= e($category['name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>


<!-- DESCRIPTION -->

<div class="col-12">

<label class="form-label">
Description
</label>

<textarea
    name="description"
    class="form-control"
    rows="4"><?= e($editProduct['description'] ?? '') ?></textarea>

</div>


<!-- RETAIL -->

<div class="col-md-4">

<label class="form-label">
Retail Price
</label>

<input
    type="number"
    step="0.01"
    name="retail_price"
    class="form-control"
    required
    value="<?= e($editProduct['retail_price'] ?? '') ?>">

</div>


<!-- WHOLESALE -->

<div class="col-md-4">

<label class="form-label">
Wholesale Price
</label>

<input
    type="number"
    step="0.01"
    name="wholesale_price"
    class="form-control"
    value="<?= e($editProduct['wholesale_price'] ?? '') ?>">

</div>


<!-- SKU -->

<div class="col-md-4">

<label class="form-label">
SKU
</label>

<input
    type="text"
    name="sku"
    class="form-control"
    value="<?= e($editProduct['sku'] ?? '') ?>">

</div>


<!-- MOQ -->

<div class="col-md-4">

<label class="form-label">
Minimum Order Quantity
</label>

<input
    type="number"
    name="minimum_order_quantity"
    min="1"
    class="form-control"
    value="<?= e($editProduct['minimum_order_quantity'] ?? '1') ?>">

</div>


<!-- UNIT -->

<div class="col-md-4">

<label class="form-label">
Unit
</label>

<input
    type="text"
    name="unit"
    class="form-control"
    value="<?= e($editProduct['unit'] ?? 'piece') ?>">

</div>


<!-- COUNTRY -->

<div class="col-md-4">

<label class="form-label">
Country of Origin
</label>

<input
    type="text"
    name="country_of_origin"
    class="form-control"
    value="<?= e($editProduct['country_of_origin'] ?? 'Cameroon') ?>">

</div>


<!-- STATUS -->

<div class="col-md-6">

<label class="form-label">
Status
</label>

<select
    name="status"
    class="form-select">

<?php foreach (
    ['draft','active','out_of_stock','discontinued']
    as $status
): ?>

<option
    value="<?= $status ?>"
    <?= (($editProduct['status'] ?? 'draft') === $status)
        ? 'selected'
        : '' ?>>

<?= ucfirst(str_replace('_', ' ', $status)) ?>

</option>

<?php endforeach; ?>

</select>

</div>


<!-- IMAGE -->

<div class="col-md-6">

<label class="form-label">
Product Image
</label>

<input
    type="file"
    name="image_file"
    class="form-control"
    accept="image/jpeg,image/png,image/webp,image/gif">

<div class="form-text">
Maximum 5MB. JPG, PNG, WEBP or GIF.
</div>

<?php if (!empty($editProduct['image'])): ?>

<div class="mt-3">

<img
    src="<?= e(
        BASE_URL . '/' .
        ltrim($editProduct['image'], '/')
    ) ?>"
    class="product-image">

</div>

<?php endif; ?>

</div>

</div>


<div class="mt-4 d-flex gap-2">

<button
    type="submit"
    class="btn btn-primary px-4">

<?= $editProduct
    ? 'Update Product'
    : 'Add Product' ?>

</button>


<?php if ($editProduct): ?>

<a
    href="<?= e(BASE_URL) ?>/admin/products.php"
    class="btn btn-outline-secondary">

Cancel

</a>

<?php endif; ?>

</div>

</form>

</div>

</div>


<!-- PRODUCT LIST -->

<div class="card">

<div class="card-header p-3">

Product List

</div>

<div class="table-responsive">

<table class="table table-hover mb-0">

<thead class="table-light">

<tr>

<th>Image</th>
<th>Product</th>
<th>Category</th>
<th>Retail Price</th>
<th>Status</th>
<th class="text-end">Action</th>

</tr>

</thead>

<tbody>

<?php foreach ($products as $product): ?>

<tr>

<td>

<?php if (!empty($product['image'])): ?>

<img
    src="<?= e(
        BASE_URL . '/' .
        ltrim($product['image'], '/')
    ) ?>"
    class="product-image">

<?php else: ?>

<div class="product-placeholder">

<?= e(
    strtoupper(
        substr(
            $product['name'],
            0,
            1
        )
    )
) ?>

</div>

<?php endif; ?>

</td>


<td>

<strong>
<?= e($product['name']) ?>
</strong>

<?php if (!empty($product['sku'])): ?>

<div class="text-muted small">
SKU: <?= e($product['sku']) ?>
</div>

<?php endif; ?>

</td>


<td>

<?= e(
    $product['category_name']
    ?? 'No category'
) ?>

</td>


<td>

<strong>
<?= e(
    formatPrice(
        $product['retail_price']
    )
) ?>
</strong>

</td>


<td>

<?php

$statusClass = match (
    $product['status'] ?? 'draft'
) {
    'active' => 'badge-active',
    'out_of_stock' => 'badge-out',
    'discontinued' => 'badge-discontinued',
    default => 'badge-draft'
};

?>

<span class="badge <?= $statusClass ?> px-3 py-2">

<?= e(
    ucfirst(
        str_replace(
            '_',
            ' ',
            $product['status'] ?? 'draft'
        )
    )
) ?>

</span>

</td>


<td class="text-end">

<a
    href="<?= e(BASE_URL) ?>/admin/products.php?edit=<?= (int) $product['id'] ?>"
    class="btn btn-sm btn-outline-primary">

Edit

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>


<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>


