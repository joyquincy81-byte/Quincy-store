<?php declare(strict_types=1);
require_once dirname(__DIR__) . '/_bootstrap.php';
apiMethod('POST');
$in = apiInput();
$reference = trim((string) ($in['reference'] ?? $in['transaction_reference'] ?? ''));
$requestedStatus = (string) ($in['status'] ?? '');
$statusMap = ['completed' => 'successful', 'successful' => 'successful', 'failed' => 'failed'];
if ($reference === '' || !isset($statusMap[$requestedStatus])) apiError('Invalid callback', 422);
$db = apiDb();
try {
    $db->beginTransaction();
    $status = $statusMap[$requestedStatus];
    $s = $db->prepare('UPDATE payments SET status=? WHERE transaction_reference=? AND status="pending"');
    $s->execute([$status, $reference]);
    if (!$s->rowCount()) apiError('Payment not found or already processed', 404);
    if ($status === 'successful') {
        $db->prepare('UPDATE orders o JOIN payments p ON p.order_id=o.id SET o.payment_status="Paid" WHERE p.transaction_reference=?')->execute([$reference]);
    }
    $db->commit();
    apiResponse(['reference' => $reference, 'status' => $status]);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    apiException($e);
}
