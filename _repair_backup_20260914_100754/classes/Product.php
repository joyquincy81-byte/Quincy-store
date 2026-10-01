<?php

declare(strict_types=1);

final class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function search(string $term = '', ?int $categoryId = null, int $limit = 24): array
    {
        $where = ["p.status = 'active'"];
        $params = [];
        if ($term !== '') {
            $where[] = '(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)';
            $like = '%' . $term . '%';
            array_push($params, $like, $like, $like);
        }
        if ($categoryId !== null) {
            $where[] = 'p.category_id = ?';
            $params[] = $categoryId;
        }
        $limit = max(1, min($limit, 100));
        $stmt = $this->db->prepare(
            'SELECT p.*, c.name AS category_name,
                    COALESCE(i.quantity - i.reserved_quantity, 0) AS available_quantity,
                    (SELECT pi.image FROM product_images pi
                     WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order LIMIT 1) AS image
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN inventory i ON i.product_id = p.id AND i.variant_id IS NULL
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY p.created_at DESC LIMIT ' . $limit
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.name AS category_name
             FROM products p LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = ? AND p.status = 'active' LIMIT 1"
        );
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        return $product ?: null;
    }

    public function wholesaleSearch(string $term = '', ?int $categoryId = null, int $minimumQuantity = 0, int $limit = 24): array
    {
        $where = ["p.status = 'active'", "p.wholesale_price IS NOT NULL", "p.minimum_order_quantity > 0"];
        $params = [];
        if ($term !== '') {
            $where[] = '(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ? OR s.supplier_name LIKE ?)';
            $like = '%' . $term . '%';
            array_push($params, $like, $like, $like, $like);
        }
        if ($categoryId !== null) {
            $where[] = 'p.category_id = ?';
            $params[] = $categoryId;
        }
        if ($minimumQuantity > 0) {
            $where[] = 'p.minimum_order_quantity <= ?';
            $params[] = $minimumQuantity;
        }
        $limit = max(1, min($limit, 100));
        $stmt = $this->db->prepare(
            'SELECT p.id, p.supplier_id, p.category_id, p.name, p.description, p.sku,
                    p.retail_price, p.wholesale_price, p.minimum_order_quantity, p.unit,
                    c.name AS category_name, s.supplier_name, s.city, s.country,
                    s.verification_status, s.rating,
                    COALESCE(i.quantity - i.reserved_quantity, 0) AS available_quantity,
                    (SELECT pi.image FROM product_images pi
                     WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order LIMIT 1) AS image
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             INNER JOIN suppliers s ON s.id = p.supplier_id AND s.status = "active"
             LEFT JOIN inventory i ON i.product_id = p.id AND i.variant_id IS NULL
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY s.verification_status = "verified" DESC, s.rating DESC, p.created_at DESC
             LIMIT ' . $limit
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findWholesaleById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.name AS category_name, s.supplier_name, s.description AS supplier_description,
                    s.city, s.country, s.verification_status, s.rating,
                    COALESCE(i.quantity - i.reserved_quantity, 0) AS available_quantity
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             INNER JOIN suppliers s ON s.id = p.supplier_id AND s.status = "active"
             LEFT JOIN inventory i ON i.product_id = p.id AND i.variant_id IS NULL
             WHERE p.id = ? AND p.status = "active" AND p.wholesale_price IS NOT NULL
             LIMIT 1'
        );
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        return $product ?: null;
    }

    public function create(int $ownerId, array $data): int
    {
        $name=trim((string)($data['name']??'')); if($ownerId<1||$name==='') throw new InvalidArgumentException('Product name is required.');
        $s=$this->db->prepare('INSERT INTO products (supplier_id,category_id,name,description,sku,retail_price,wholesale_price,minimum_order_quantity,unit,status) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $s->execute([$ownerId,$data['category_id']??null,$name,$data['description']??null,$data['sku']??null,(float)($data['retail_price']??0),$data['wholesale_price']??null,(int)($data['minimum_order_quantity']??1),$data['unit']??'piece',$data['status']??'active']); return (int)$this->db->lastInsertId();
    }
    public function update(int $ownerId,int $id,array $data): bool
    { $f=[];$p=[];foreach(['category_id','name','description','sku','retail_price','wholesale_price','minimum_order_quantity','unit','status'] as $k)if(array_key_exists($k,$data)){$f[]="$k=?";$p[]=$data[$k];}if(!$f)return false;$p[]=$id;$p[]=$ownerId;$s=$this->db->prepare('UPDATE products SET '.implode(',',$f).' WHERE id=? AND supplier_id=?');$s->execute($p);return $s->rowCount()>0; }
    public function delete(int $ownerId,int $id): bool { $s=$this->db->prepare('UPDATE products SET status="inactive" WHERE id=? AND supplier_id=?');$s->execute([$id,$ownerId]);return $s->rowCount()>0; }
}