<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/auth.php';
require_once ROOT_PATH . '/middleware/csrf.php';

requireRole('supplier');

$error = null;

$db = Database::getInstance()->getConnection();
$userId = (int) currentUserId();

/*
|--------------------------------------------------------------------------
| Load supplier profile
|--------------------------------------------------------------------------
*/
$profileStmt = $db->prepare(
    'SELECT *
     FROM suppliers
     WHERE user_id = ?
       AND status = "active"
     LIMIT 1'
);

$profileStmt->execute([$userId]);
$supplier = $profileStmt->fetch();

if (!$supplier) {
    http_response_code(403);
    exit('Supplier profile not found.');
}

/*
|--------------------------------------------------------------------------
| Load active categories
|--------------------------------------------------------------------------
*/
$categoryStmt = $db->query(
    "SELECT id, name
     FROM categories
     WHERE status = 'active'
     ORDER BY name"
);

$categories = $categoryStmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Create product
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verifyCsrfToken();

        $product = new Product();

        $productId = $product->createForSupplier(
            (int) $supplier['id'],
            $_POST
        );

        redirect(
            BASE_URL . '/supplier/products.php?created=' . $productId
        );

    } catch (Throwable $e) {
        error_log('Supplier add product: ' . $e->getMessage());
        $error = $e->getMessage();
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

    <title>Add Product - Quincy Store Supplier Centre</title>

    <link
        rel="stylesheet"
        href="<?= e(BASE_URL) ?>/assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="<?= e(BASE_URL) ?>/assets/css/supplier.css"
    >

    <style>
        /* =====================================================
           QUINCY STORE - ADD PRODUCT PAGE
           Page-specific presentation only
           ===================================================== */

        .add-product-page {
            background: #f5f7fb;
            min-height: 100vh;
        }

        .add-product-page .supplier-workspace {
            background: #f5f7fb;
            min-height: calc(100vh - 70px);
            padding-bottom: 50px;
        }

        /* Breadcrumb */
        .add-product-page .supplier-breadcrumb {
            margin-bottom: 18px;
            color: #64748b;
            font-size: 13px;
        }

        .add-product-page .supplier-breadcrumb span {
            margin: 0 8px;
            color: #cbd5e1;
        }

        /* Header */
        .add-product-page .supplier-welcome {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .add-product-page .eyebrow {
            margin: 0 0 7px;
            color: #2563eb;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.4px;
        }

        .add-product-page .supplier-welcome h1 {
            margin: 0 0 7px;
            color: #0a192f;
            font-size: 30px;
            font-weight: 800;
            line-height: 1.2;
        }

        .add-product-page .supplier-welcome p:not(.eyebrow) {
            margin: 0;
            color: #64748b;
            font-size: 14px;
        }

        /* Back button */
        .add-product-page .back-products {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            color: #0a192f;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            transition: .2s ease;
        }

        .add-product-page .back-products:hover {
            background: #0a192f;
            border-color: #0a192f;
            color: #ffffff;
        }

        /* Error */
        .add-product-page .catalog-notice {
            margin-bottom: 20px;
            padding: 14px 16px;
            border: 1px solid #fecaca;
            border-left: 4px solid #dc2626;
            border-radius: 8px;
            background: #fff7f7;
            color: #991b1b;
            font-size: 13px;
            font-weight: 600;
        }

        /* Main card */
        .add-product-page .product-form-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, .06);
            overflow: hidden;
        }

        /* Card header */
        .add-product-page .form-card-header {
            padding: 23px 30px;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .add-product-page .form-card-header h2 {
            margin: 0 0 5px;
            color: #0a192f;
            font-size: 17px;
            font-weight: 800;
        }

        .add-product-page .form-card-header p {
            margin: 0;
            color: #64748b;
            font-size: 13px;
        }

        /* Form */
        .add-product-page .product-form {
            padding: 30px;
        }

        .add-product-page .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px 24px;
        }

        .add-product-page .field {
            min-width: 0;
        }

        .add-product-page .field label {
            display: block;
            margin-bottom: 8px;
            color: #1e293b;
            font-size: 13px;
            font-weight: 700;
        }

        .add-product-page .field input,
        .add-product-page .field select,
        .add-product-page .field textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            color: #0f172a;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition:
                border-color .2s ease,
                box-shadow .2s ease;
        }

        .add-product-page .field input,
        .add-product-page .field select {
            height: 46px;
            padding: 0 13px;
        }

        .add-product-page .field textarea {
            min-height: 145px;
            padding: 12px 13px;
            resize: vertical;
            line-height: 1.55;
        }

        .add-product-page .field input:hover,
        .add-product-page .field select:hover,
        .add-product-page .field textarea:hover {
            border-color: #94a3b8;
        }

        .add-product-page .field input:focus,
        .add-product-page .field select:focus,
        .add-product-page .field textarea:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        }

        .add-product-page .field input::placeholder,
        .add-product-page .field textarea::placeholder {
            color: #94a3b8;
        }

        /* Description */
        .add-product-page .description-field {
            margin-top: 24px;
        }

        /* Footer */
        .add-product-page .form-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }

        .add-product-page .cancel-button,
        .add-product-page .create-button {
            min-height: 46px;
            padding: 0 22px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 750;
            cursor: pointer;
            text-decoration: none;
            transition: .2s ease;
        }

        .add-product-page .cancel-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
        }

        .add-product-page .cancel-button:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        .add-product-page .create-button {
            border: 0;
            background: #0a192f;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(10, 25, 47, .14);
        }

        .add-product-page .create-button:hover {
            background: #112a4a;
            transform: translateY(-1px);
            box-shadow: 0 7px 16px rgba(10, 25, 47, .18);
        }

        /* Tablet */
        @media (max-width: 900px) {
            .add-product-page .supplier-welcome {
                align-items: flex-start;
            }

            .add-product-page .form-grid {
                grid-template-columns: 1fr;
            }

            .add-product-page .product-form {
                padding: 24px;
            }
        }

        /* Mobile */
        @media (max-width: 640px) {
            .add-product-page .supplier-welcome {
                flex-direction: column;
                align-items: stretch;
            }

            .add-product-page .supplier-welcome h1 {
                font-size: 25px;
            }

            .add-product-page .back-products {
                width: 100%;
            }

            .add-product-page .form-card-header {
                padding: 20px;
            }

            .add-product-page .product-form {
                padding: 20px 16px;
            }

            .add-product-page .field input,
            .add-product-page .field select,
            .add-product-page .field textarea {
                font-size: 16px;
            }

            .add-product-page .form-actions {
                flex-direction: column-reverse;
                align-items: stretch;
            }

            .add-product-page .cancel-button,
            .add-product-page .create-button {
                width: 100%;
            }
        }
    </style>
</head>

<body class="supplier-dashboard-page add-product-page">

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
            <a href="<?= e(BASE_URL) ?>/supplier/products.php">
                Products
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
                <?= e(
                    strtoupper(
                        substr(
                            (string) $supplier['supplier_name'],
                            0,
                            1
                        )
                    )
                ) ?>
            </span>

            <div>
                <strong>
                    <?= e($supplier['supplier_name']) ?>
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
            Supplier Centre
            <span>/</span>
            Products
            <span>/</span>
            Add Product
        </div>

        <div class="supplier-welcome">

            <div>

                <p class="eyebrow">
                    CATALOGUE MANAGEMENT
                </p>

                <h1>
                    Add a Product
                </h1>

                <p>
                    Create a new product listing for your supplier catalogue.
                </p>

            </div>

            <a
                class="back-products"
                href="<?= e(BASE_URL) ?>/supplier/products.php"
            >
                Back to Products
            </a>

        </div>

        <?php if ($error): ?>

            <div class="catalog-notice">
                <?= e($error) ?>
            </div>

        <?php endif; ?>

        <section class="product-form-card">

            <div class="form-card-header">

                <h2>
                    Product Information
                </h2>

                <p>
                    Enter the details buyers will see in your catalogue.
                </p>

            </div>

            <form
                method="post"
                class="product-form"
                autocomplete="off"
            >

                <?= csrfField() ?>

                <div class="form-grid">

                    <div class="field">

                        <label for="product-name">
                            Product Name
                        </label>

                        <input
                            id="product-name"
                            type="text"
                            name="name"
                            required
                            maxlength="200"
                            value="<?= e($_POST['name'] ?? '') ?>"
                            placeholder="Enter product name"
                        >

                    </div>

                    <div class="field">

                        <label for="category">
                            Category
                        </label>

                        <select
                            id="category"
                            name="category_id"
                            required
                        >

                            <option value="">
                                Select category
                            </option>

                            <?php foreach ($categories as $category): ?>

                                <option
                                    value="<?= (int) $category['id'] ?>"
                                    <?= (
                                        (int) (
                                            $_POST['category_id'] ?? 0
                                        ) ===
                                        (int) $category['id']
                                    ) ? 'selected' : '' ?>
                                >
                                    <?= e($category['name']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="field">

                        <label for="sku">
                            SKU
                        </label>

                        <input
                            id="sku"
                            type="text"
                            name="sku"
                            maxlength="100"
                            value="<?= e($_POST['sku'] ?? '') ?>"
                            placeholder="Optional SKU"
                        >

                    </div>

                    <div class="field">

                        <label for="unit">
                            Unit
                        </label>

                        <input
                            id="unit"
                            type="text"
                            name="unit"
                            value="<?= e($_POST['unit'] ?? 'piece') ?>"
                            placeholder="piece"
                        >

                    </div>

                    <div class="field">

                        <label for="retail-price">
                            Retail Price
                        </label>

                        <input
                            id="retail-price"
                            type="number"
                            name="retail_price"
                            min="0.01"
                            step="0.01"
                            required
                            value="<?= e($_POST['retail_price'] ?? '') ?>"
                            placeholder="0.00"
                        >

                    </div>

                    <div class="field">

                        <label for="wholesale-price">
                            Wholesale Price
                        </label>

                        <input
                            id="wholesale-price"
                            type="number"
                            name="wholesale_price"
                            min="0.01"
                            step="0.01"
                            value="<?= e($_POST['wholesale_price'] ?? '') ?>"
                            placeholder="Optional"
                        >

                    </div>

                    <div class="field">

                        <label for="minimum-order">
                            Minimum Order Quantity
                        </label>

                        <input
                            id="minimum-order"
                            type="number"
                            name="minimum_order_quantity"
                            min="1"
                            value="<?= e(
                                $_POST['minimum_order_quantity'] ?? '1'
                            ) ?>"
                        >

                    </div>

                    <div class="field">

                        <label for="quantity">
                            Initial Stock Quantity
                        </label>

                        <input
                            id="quantity"
                            type="number"
                            name="quantity"
                            min="0"
                            value="<?= e($_POST['quantity'] ?? '0') ?>"
                        >

                    </div>

                    <div class="field">

                        <label for="reorder-level">
                            Reorder Level
                        </label>

                        <input
                            id="reorder-level"
                            type="number"
                            name="reorder_level"
                            min="0"
                            value="<?= e(
                                $_POST['reorder_level'] ?? '10'
                            ) ?>"
                        >

                    </div>

                    <div class="field">

                        <label for="weight">
                            Weight
                        </label>

                        <input
                            id="weight"
                            type="number"
                            name="weight"
                            min="0"
                            step="0.01"
                            value="<?= e($_POST['weight'] ?? '') ?>"
                            placeholder="0.00"
                        >

                    </div>

                    <div class="field">

                        <label for="country">
                            Country of Origin
                        </label>

                        <input
                            id="country"
                            type="text"
                            name="country_of_origin"
                            value="<?= e(
                                $_POST['country_of_origin'] ?? 'Cameroon'
                            ) ?>"
                        >

                    </div>

                    <div class="field">

                        <label for="status">
                            Product Status
                        </label>

                        <select
                            id="status"
                            name="status"
                        >

                            <option
                                value="active"
                                <?= (
                                    ($_POST['status'] ?? 'active') ===
                                    'active'
                                ) ? 'selected' : '' ?>
                            >
                                Active
                            </option>

                            <option
                                value="draft"
                                <?= (
                                    ($_POST['status'] ?? '') ===
                                    'draft'
                                ) ? 'selected' : '' ?>
                            >
                                Draft
                            </option>

                            <option
                                value="out_of_stock"
                                <?= (
                                    ($_POST['status'] ?? '') ===
                                    'out_of_stock'
                                ) ? 'selected' : '' ?>
                            >
                                Out of Stock
                            </option>

                            <option
                                value="discontinued"
                                <?= (
                                    ($_POST['status'] ?? '') ===
                                    'discontinued'
                                ) ? 'selected' : '' ?>
                            >
                                Discontinued
                            </option>

                        </select>

                    </div>

                </div>

                <div class="field description-field">

                    <label for="description">
                        Product Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="6"
                        placeholder="Describe the product, features, specifications, packaging, or other information buyers should know..."
                    ><?= e($_POST['description'] ?? '') ?></textarea>

                </div>

                <div class="form-actions">

                    <a
                        class="cancel-button"
                        href="<?= e(BASE_URL) ?>/supplier/products.php"
                    >
                        Cancel
                    </a>

                    <button
                        class="create-button"
                        type="submit"
                    >
                        Create Product
                    </button>

                </div>

            </form>

        </section>

    </main>

</div>

</body>
</html>