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
                    (SELECT pi.image
                     FROM product_images pi
                     WHERE pi.product_id = p.id
                     ORDER BY pi.is_primary DESC, pi.sort_order
                     LIMIT 1) AS image
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN inventory i ON i.product_id = p.id AND i.variant_id IS NULL
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY p.created_at DESC
             LIMIT ' . $limit
        );

        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = ? AND p.status = 'active'
             LIMIT 1"
        );

        $stmt->execute([$id]);

        $product = $stmt->fetch();

        return $product ?: null;
    }

    public function wholesaleSearch(
        string $term = '',
        ?int $categoryId = null,
        int $minimumQuantity = 0,
        int $limit = 24
    ): array {
        $where = [
            "p.status = 'active'",
            "p.wholesale_price IS NOT NULL",
            "p.minimum_order_quantity > 0"
        ];

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
            'SELECT p.id,
                    p.supplier_id,
                    p.manufacturer_id,
                    p.category_id,
                    p.name,
                    p.description,
                    p.sku,
                    p.retail_price,
                    p.wholesale_price,
                    p.minimum_order_quantity,
                    p.unit,
                    c.name AS category_name,
                    s.supplier_name,
                    s.city,
                    s.country,
                    s.verification_status,
                    s.rating,
                    COALESCE(i.quantity - i.reserved_quantity, 0) AS available_quantity,
                    (SELECT pi.image
                     FROM product_images pi
                     WHERE pi.product_id = p.id
                     ORDER BY pi.is_primary DESC, pi.sort_order
                     LIMIT 1) AS image
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             INNER JOIN suppliers s
                ON s.id = p.supplier_id
               AND s.status = "active"
             LEFT JOIN inventory i
                ON i.product_id = p.id
               AND i.variant_id IS NULL
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY s.verification_status = "verified" DESC,
                      s.rating DESC,
                      p.created_at DESC
             LIMIT ' . $limit
        );

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function findWholesaleById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*,
                    c.name AS category_name,
                    s.supplier_name,
                    s.description AS supplier_description,
                    s.city,
                    s.country,
                    s.verification_status,
                    s.rating,
                    COALESCE(i.quantity - i.reserved_quantity, 0) AS available_quantity
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             INNER JOIN suppliers s
                ON s.id = p.supplier_id
               AND s.status = "active"
             LEFT JOIN inventory i
                ON i.product_id = p.id
               AND i.variant_id IS NULL
             WHERE p.id = ?
               AND p.status = "active"
               AND p.wholesale_price IS NOT NULL
             LIMIT 1'
        );

        $stmt->execute([$id]);

        $product = $stmt->fetch();

        return $product ?: null;
    }

    public function create(int $ownerId, array $data): int
    {
        return $this->createOwnedProduct($ownerId, null, $data);
    }

    public function createForSupplier(int $supplierId, array $data): int
    {
        return $this->createOwnedProduct($supplierId, null, $data);
    }

    public function createForManufacturer(int $manufacturerId, array $data): int
    {
        return $this->createOwnedProduct(null, $manufacturerId, $data);
    }

    private function createOwnedProduct(
        ?int $supplierId,
        ?int $manufacturerId,
        array $data
    ): int {
        if (($supplierId === null && $manufacturerId === null)
            || ($supplierId !== null && $manufacturerId !== null)) {
            throw new InvalidArgumentException(
                'A product must have exactly one owner.'
            );
        }

        $name = trim((string)($data['name'] ?? ''));

        if ($name === '') {
            throw new InvalidArgumentException(
                'Product name is required.'
            );
        }

        $categoryId = (int)($data['category_id'] ?? 0);

        if ($categoryId < 1) {
            throw new InvalidArgumentException(
                'Please select a valid category.'
            );
        }

        $categoryStmt = $this->db->prepare(
            'SELECT id
             FROM categories
             WHERE id = ?
               AND status = "active"
             LIMIT 1'
        );

        $categoryStmt->execute([$categoryId]);

        if (!$categoryStmt->fetchColumn()) {
            throw new InvalidArgumentException(
                'The selected category is not available.'
            );
        }

        $retailPrice = (float)($data['retail_price'] ?? 0);

        if ($retailPrice <= 0) {
            throw new InvalidArgumentException(
                'Retail price must be greater than zero.'
            );
        }

        $wholesalePrice = null;

        if (
            isset($data['wholesale_price']) &&
            trim((string)$data['wholesale_price']) !== ''
        ) {
            $wholesalePrice = (float)$data['wholesale_price'];

            if ($wholesalePrice <= 0) {
                throw new InvalidArgumentException(
                    'Wholesale price must be greater than zero.'
                );
            }
        }

        $minimumOrderQuantity =
            (int)($data['minimum_order_quantity'] ?? 1);

        if ($minimumOrderQuantity < 1) {
            throw new InvalidArgumentException(
                'Minimum order quantity must be at least 1.'
            );
        }

        $quantity = (int)($data['quantity'] ?? 0);

        if ($quantity < 0) {
            throw new InvalidArgumentException(
                'Stock quantity cannot be negative.'
            );
        }

        $reorderLevel = (int)($data['reorder_level'] ?? 10);

        if ($reorderLevel < 0) {
            throw new InvalidArgumentException(
                'Reorder level cannot be negative.'
            );
        }

        $status = (string)($data['status'] ?? 'active');

        $allowedStatuses = [
            'draft',
            'active',
            'out_of_stock',
            'discontinued'
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException(
                'Invalid product status.'
            );
        }

        $description =
            trim((string)($data['description'] ?? ''));

        $sku =
            trim((string)($data['sku'] ?? ''));

        $unit =
            trim((string)($data['unit'] ?? 'piece'));

        if ($unit === '') {
            $unit = 'piece';
        }

        $weight = null;

        if (
            isset($data['weight']) &&
            trim((string)$data['weight']) !== ''
        ) {
            $weight = (float)$data['weight'];

            if ($weight < 0) {
                throw new InvalidArgumentException(
                    'Weight cannot be negative.'
                );
            }
        }

        $country =
            trim((string)($data['country_of_origin'] ?? 'Cameroon'));

        if ($country === '') {
            $country = 'Cameroon';
        }

        $slug = $this->generateUniqueSlug($name);

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                'INSERT INTO products
                (
                    supplier_id,
                    manufacturer_id,
                    category_id,
                    name,
                    slug,
                    description,
                    sku,
                    retail_price,
                    wholesale_price,
                    minimum_order_quantity,
                    unit,
                    weight,
                    country_of_origin,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $supplierId,
                $manufacturerId,
                $categoryId,
                $name,
                $slug,
                $description !== '' ? $description : null,
                $sku !== '' ? $sku : null,
                $retailPrice,
                $wholesalePrice,
                $minimumOrderQuantity,
                $unit,
                $weight,
                $country,
                $status
            ]);

            $productId = (int)$this->db->lastInsertId();

            $inventoryStmt = $this->db->prepare(
                'INSERT INTO inventory
                (
                    product_id,
                    variant_id,
                    quantity,
                    reserved_quantity,
                    reorder_level
                )
                VALUES (?, NULL, ?, 0, ?)'
            );

            $inventoryStmt->execute([
                $productId,
                $quantity,
                $reorderLevel
            ]);

            $this->db->commit();

            return $productId;

        } catch (Throwable $exception) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = strtolower(trim($name));

        $base = preg_replace(
            '/[^a-z0-9]+/i',
            '-',
            $base
        ) ?? '';

        $base = trim($base, '-');

        if ($base === '') {
            $base = 'product';
        }

        $base = substr($base, 0, 180);

        $slug = $base;
        $counter = 1;

        while ($this->slugExists($slug)) {
            $counter++;

            $suffix = '-' . $counter;

            $slug =
                substr(
                    $base,
                    0,
                    220 - strlen($suffix)
                ) . $suffix;
        }

        return $slug;
    }

    private function slugExists(string $slug): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id
             FROM products
             WHERE slug = ?
             LIMIT 1'
        );

        $stmt->execute([$slug]);

        return (bool)$stmt->fetchColumn();
    }

    public function update(
        int $ownerId,
        int $id,
        array $data
    ): bool {

        $fields = [];
        $params = [];

        foreach (
            [
                'category_id',
                'name',
                'description',
                'sku',
                'retail_price',
                'wholesale_price',
                'minimum_order_quantity',
                'unit',
                'status'
            ] as $field
        ) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (!$fields) {
            return false;
        }

        $params[] = $id;
        $params[] = $ownerId;

        $stmt = $this->db->prepare(
            'UPDATE products
             SET ' . implode(', ', $fields) . '
             WHERE id = ?
               AND supplier_id = ?'
        );

        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $ownerId, int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE products
             SET status = "discontinued"
             WHERE id = ?
               AND supplier_id = ?'
        );

        $stmt->execute([
            $id,
            $ownerId
        ]);

        return $stmt->rowCount() > 0;
    }
}
