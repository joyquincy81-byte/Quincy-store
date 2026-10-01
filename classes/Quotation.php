<?php

declare(strict_types=1);

final class Quotation
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function productsForRequest(): array
    {
        return (new Product())->wholesaleSearch('', null, 0, 100);
    }

    public function find(int $id, int $userId = 0): ?array
    {
        $q = 'SELECT q.*, s.supplier_name FROM quotation_requests q LEFT JOIN suppliers s ON s.id=q.supplier_id WHERE q.id=?';
        $p = [$id];
        if ($userId > 0) { $q .= ' AND (q.buyer_id=? OR q.supplier_id IN (SELECT id FROM suppliers WHERE user_id=?))'; $p[]=$userId; $p[]=$userId; }
        $s = $this->db->prepare($q . ' LIMIT 1'); $s->execute($p); return $s->fetch() ?: null;
    }

    public function items(int $id, int $userId = 0): array
    {
        $q = 'SELECT qi.*, p.name, p.sku FROM quotation_items qi JOIN products p ON p.id=qi.product_id JOIN quotation_requests q ON q.id=qi.quotation_request_id WHERE qi.quotation_request_id=?';
        $p = [$id];
        if ($userId > 0) { $q .= ' AND (q.buyer_id=? OR q.supplier_id IN (SELECT id FROM suppliers WHERE user_id=?))'; $p[]=$userId; $p[]=$userId; }
        $s=$this->db->prepare($q); $s->execute($p); return $s->fetchAll();
    }

    public function create(int $buyerId, int $productId, int $quantity, string $location, ?float $requestedPrice=null, ?string $notes=null): int
    {
        if ($buyerId<1 || $productId<1 || $quantity<1 || trim($location)==='') throw new InvalidArgumentException('Invalid quotation request.');
        $s=$this->db->prepare('SELECT supplier_id,minimum_order_quantity FROM products WHERE id=? AND status="active"'); $s->execute([$productId]); $product=$s->fetch();
        if (!$product || $quantity < (int)$product['minimum_order_quantity']) throw new RuntimeException('Product or quantity is invalid.');
        $this->db->beginTransaction();
        try {
            $s=$this->db->prepare('INSERT INTO quotation_requests(buyer_id,supplier_id,status,delivery_location,notes,expires_at) VALUES(?,?, "pending",?,?,DATE_ADD(NOW(),INTERVAL 7 DAY))');
            $s->execute([$buyerId,$product['supplier_id'],trim($location),$notes]); $id=(int)$this->db->lastInsertId();
            $s=$this->db->prepare('INSERT INTO quotation_items(quotation_request_id,product_id,quantity,requested_price) VALUES(?,?,?,?)'); $s->execute([$id,$productId,$quantity,$requestedPrice]);
            $this->db->commit(); return $id;
        } catch (Throwable $e) { if($this->db->inTransaction())$this->db->rollBack(); throw $e; }
    }

    public function updateStatus(int $id, int $userId, string $status): bool
    {
        if (!in_array($status,['pending','negotiating','quoted','accepted','rejected','expired','cancelled'],true)) return false;
        $s=$this->db->prepare('UPDATE quotation_requests SET status=? WHERE id=? AND (buyer_id=? OR supplier_id IN (SELECT id FROM suppliers WHERE user_id=?))');
        $s->execute([$status,$id,$userId,$userId]); return $s->rowCount()>0;
    }
}