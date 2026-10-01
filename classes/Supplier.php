<?php

declare(strict_types=1);

final class Supplier
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function search(
        string $term = '',
        string $location = '',
        bool $verifiedOnly = false,
        float $minimumRating = 0,
        string $sort = 'relevance',
        int $limit = 24
    ): array {
        $where = ["s.status = 'active'"];
        $params = [];

        if ($term !== '') {
            $where[] = '(s.supplier_name LIKE ? OR s.description LIKE ? OR s.city LIKE ? OR s.country LIKE ?)';
            $like = '%' . $term . '%';
            array_push($params, $like, $like, $like, $like);
        }
        if ($location !== '') {
            $where[] = '(s.city LIKE ? OR s.country LIKE ?)';
            $like = '%' . $location . '%';
            array_push($params, $like, $like);
        }
        if ($verifiedOnly) {
            $where[] = "s.verification_status = 'verified'";
        }
        if ($minimumRating > 0) {
            $where[] = 's.rating >= ?';
            $params[] = $minimumRating;
        }

        $orderBy = match ($sort) {
            'rating' => 's.rating DESC, s.supplier_name ASC',
            'newest' => 's.created_at DESC',
            'name' => 's.supplier_name ASC',
            default => 's.rating DESC, s.verification_status = "verified" DESC, s.supplier_name ASC',
        };
        $limit = max(1, min($limit, 100));
        $stmt = $this->db->prepare(
            'SELECT s.id, s.supplier_name, s.description, s.address, s.city, s.country,
                    s.verification_status, s.rating, s.created_at,
                    b.business_name, COUNT(DISTINCT p.id) AS product_count
             FROM suppliers s
             LEFT JOIN businesses b ON b.id = s.business_id
             LEFT JOIN products p ON p.supplier_id = s.id AND p.status = "active"
             WHERE ' . implode(' AND ', $where) . '
             GROUP BY s.id
             ORDER BY ' . $orderBy . '
             LIMIT ' . $limit
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function dashboard(int $userId): array
    {
        $supplierStmt = $this->db->prepare('SELECT * FROM suppliers WHERE user_id = ? LIMIT 1');
        $supplierStmt->execute([$userId]);
        $supplier = $supplierStmt->fetch();
        if (!$supplier) {
            return ['supplier' => null, 'metrics' => [], 'products' => [], 'quotations' => [], 'orders' => []];
        }

        $supplierId = (int) $supplier['id'];
        $metrics = [];
        $metricQueries = [
            'products' => 'SELECT COUNT(*) FROM products WHERE supplier_id = ? AND status = "active"',
            'pending_quotes' => 'SELECT COUNT(*) FROM quotation_requests WHERE supplier_id = ? AND status IN ("pending", "negotiating")',
            'open_orders' => 'SELECT COUNT(DISTINCT o.id)
                              FROM orders o
                              INNER JOIN order_items oi ON oi.order_id = o.id
                              INNER JOIN products p ON p.id = oi.product_id
                              WHERE p.supplier_id = ?
                                AND o.order_status NOT IN ("completed", "cancelled")',
            'sales' => 'SELECT COALESCE(SUM(oi.subtotal), 0)
                        FROM order_items oi
                        INNER JOIN orders o ON o.id = oi.order_id
                        INNER JOIN products p ON p.id = oi.product_id
                        WHERE p.supplier_id = ? AND o.payment_status = "paid"',
        ];
        foreach ($metricQueries as $key => $query) {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$supplierId]);
            $metrics[$key] = $key === 'sales' ? (float) $stmt->fetchColumn() : (int) $stmt->fetchColumn();
        }

        $productsStmt = $this->db->prepare(
            'SELECT id, name, sku, wholesale_price, minimum_order_quantity, status
             FROM products WHERE supplier_id = ? ORDER BY updated_at DESC LIMIT 5'
        );
        $productsStmt->execute([$supplierId]);
        $quotesStmt = $this->db->prepare(
            'SELECT qr.id, qr.status, qr.delivery_location, qr.created_at,
                    u.first_name, u.last_name, COUNT(qi.id) AS item_count
             FROM quotation_requests qr
             INNER JOIN users u ON u.id = qr.buyer_id
             LEFT JOIN quotation_items qi ON qi.quotation_request_id = qr.id
             WHERE qr.supplier_id = ?
             GROUP BY qr.id
             ORDER BY qr.created_at DESC LIMIT 5'
        );
        $quotesStmt->execute([$supplierId]);
        $ordersStmt = $this->db->prepare(
            'SELECT o.id, o.order_number, o.total_amount, o.payment_status,
                    o.order_status, o.created_at
             FROM orders o
             INNER JOIN order_items oi ON oi.order_id = o.id
             INNER JOIN products p ON p.id = oi.product_id
             WHERE p.supplier_id = ?
             GROUP BY o.id
             ORDER BY o.created_at DESC
             LIMIT 5'
        );
        $ordersStmt->execute([$supplierId]);

        return [
            'supplier' => $supplier,
            'metrics' => $metrics,
            'products' => $productsStmt->fetchAll(),
            'quotations' => $quotesStmt->fetchAll(),
            'orders' => $ordersStmt->fetchAll(),
        ];
    }
}