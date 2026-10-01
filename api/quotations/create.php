<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/business.php';
require_once ROOT_PATH . '/includes/input-validator.php';
require_once ROOT_PATH . '/middleware/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/b2b/request-quotation.php');
}
verifyCsrfToken();

$validator = new InputValidator($_POST);
$validator->required('product_id', 'Product')
    ->required('quantity', 'Quantity')
    ->positiveNumber('quantity', 'Quantity')
    ->required('delivery_location', 'Delivery location')
    ->maxLength('delivery_location', 255, 'Delivery location')
    ->maxLength('notes', 2000, 'Notes');

if ($validator->fails()) {
    flashMessage('error', $validator->firstError());
    redirect(BASE_URL . '/b2b/request-quotation.php');
}

$db = Database::getInstance()->getConnection();
$productId = $validator->int('product_id');
$quantity = $validator->int('quantity');
$requestedPrice = isset($_POST['requested_price']) && $_POST['requested_price'] !== ''
    ? (float) $_POST['requested_price'] : null;
$supplierId = isset($_POST['supplier_id']) && ctype_digit((string) $_POST['supplier_id'])
    ? (int) $_POST['supplier_id'] : null;

$productStmt = $db->prepare(
    "SELECT id, supplier_id, minimum_order_quantity, status
     FROM products WHERE id = ? AND status = 'active' LIMIT 1"
);
$productStmt->execute([$productId]);
$product = $productStmt->fetch();
if (!$product) {
    flashMessage('error', 'The selected product is no longer available.');
    redirect(BASE_URL . '/b2b/request-quotation.php');
}
if ($quantity < (int) $product['minimum_order_quantity']) {
    flashMessage('error', 'Quantity must meet the product minimum order quantity of ' . $product['minimum_order_quantity'] . '.');
    redirect(BASE_URL . '/b2b/request-quotation.php?product=' . $productId);
}
if ($supplierId !== null && $supplierId !== (int) $product['supplier_id']) {
    flashMessage('error', 'The selected supplier does not own this product.');
    redirect(BASE_URL . '/b2b/request-quotation.php?product=' . $productId);
}
$supplierId ??= (int) $product['supplier_id'];

try {
    $db->beginTransaction();
    $requestStmt = $db->prepare(
        'INSERT INTO quotation_requests
            (buyer_id, supplier_id, status, delivery_location, notes, expires_at)
         VALUES (?, ?, "pending", ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))'
    );
    $requestStmt->execute([
        currentUserId(),
        $supplierId,
        trim((string) $_POST['delivery_location']),
        trim((string) ($_POST['notes'] ?? '')),
    ]);
    $requestId = (int) $db->lastInsertId();
    $itemStmt = $db->prepare(
        'INSERT INTO quotation_items
            (quotation_request_id, product_id, quantity, requested_price)
         VALUES (?, ?, ?, ?)'
    );
    $itemStmt->execute([$requestId, $productId, $quantity, $requestedPrice]);
    $db->commit();
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('RFQ creation failed: ' . $exception->getMessage());
    flashMessage('error', 'We could not submit your RFQ. Please try again.');
    redirect(BASE_URL . '/b2b/request-quotation.php');
}

flashMessage('success', 'Your RFQ has been submitted. Suppliers can now review and respond.');
redirect(BASE_URL . '/business/quotations.php?id=' . $requestId);
