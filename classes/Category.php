<?php

declare(strict_types=1);

final class Category
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function all(): array
    {
        return $this->db->query(
            "SELECT id, parent_id, name, description, image
             FROM categories WHERE status = 'active' ORDER BY name"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, parent_id, name, description, image
             FROM categories WHERE id = ? AND status = 'active' LIMIT 1"
        );
        $stmt->execute([$id]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    public function adminOverview(): array
    {
        $stmt = $this->db->query(
            "SELECT c.id, c.parent_id, c.name, c.description, c.image, c.status,
                    COUNT(DISTINCT p.id) AS product_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id AND p.status = 'active'
             GROUP BY c.id
             ORDER BY c.parent_id IS NOT NULL, c.parent_id, c.name"
        );
        return $stmt->fetchAll();
    }

    public function analytics(): array
    {
        $queries = [
            'categories' => "SELECT COUNT(*) FROM categories WHERE status = 'active'",
            'products' => "SELECT COUNT(*) FROM products WHERE status = 'active'",
            'with_products' => "SELECT COUNT(DISTINCT category_id) FROM products WHERE status = 'active' AND category_id IS NOT NULL",
            'uncategorized' => "SELECT COUNT(*) FROM products WHERE status = 'active' AND category_id IS NULL",
        ];
        $analytics = [];
        foreach ($queries as $key => $query) {
            $analytics[$key] = (int) $this->db->query($query)->fetchColumn();
        }
        return $analytics;
    }
    public function create(string $name, ?int $parentId=null, ?string $description=null): int { $name=trim($name);if($name==='')throw new InvalidArgumentException('Category name is required.');$s=$this->db->prepare('INSERT INTO categories (parent_id,name,description,status) VALUES (?,?,?,"active")');$s->execute([$parentId,$name,$description]);return (int)$this->db->lastInsertId(); }
    public function update(int $id,array $data): bool { $f=[];$p=[];foreach(['parent_id','name','description','image','status'] as $k)if(array_key_exists($k,$data)){$f[]="$k=?";$p[]=$data[$k];}if(!$f)return false;$p[]=$id;$s=$this->db->prepare('UPDATE categories SET '.implode(',',$f).' WHERE id=?');$s->execute($p);return $s->rowCount()>0; }
    public function delete(int $id): bool { $s=$this->db->prepare('UPDATE categories SET status="inactive" WHERE id=?');$s->execute([$id]);return $s->rowCount()>0; }
}