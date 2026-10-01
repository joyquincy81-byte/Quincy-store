<?php
require_once __DIR__ . '/_bootstrap.php';
$products = b2cRows("SELECT p.*, c.name category_name, COALESCE(i.quantity-i.reserved_quantity,0) available_quantity,
 (SELECT pi.image FROM product_images pi WHERE pi.product_id=p.id ORDER BY pi.is_primary DESC,pi.sort_order LIMIT 1) image
 FROM products p LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN inventory i ON i.product_id=p.id AND i.variant_id IS NULL
 WHERE p.status='active' ORDER BY p.created_at DESC LIMIT 8");
$categories = b2cRows("SELECT c.id,c.name,c.description,COUNT(p.id) product_count FROM categories c LEFT JOIN products p ON p.category_id=c.id AND p.status='active' WHERE c.status='active' GROUP BY c.id ORDER BY c.name LIMIT 6");
b2cPage('Shop smarter, live better');
?>
<section class="hero"><span class="eyebrow">Welcome to Quincy Store</span><h1>Good products.<br>Better everyday.</h1><p>Discover trusted essentials from local sellers, with secure checkout and delivery across Cameroon.</p><a class="btn gold" href="products.php">Explore the collection →</a></section>
<div class="section-head"><div><h2>Shop by category</h2><p>Find what you need, faster.</p></div><a class="btn light" href="categories.php">View all</a></div>
<section class="grid"><?php foreach($categories as $c): ?><a class="category-card" href="products.php?category=<?= (int)$c['id'] ?>"><h3><?= e($c['name']) ?> →</h3><p class="muted"><?= (int)$c['product_count'] ?> products</p></a><?php endforeach; ?></section>
<div class="section-head"><div><h2>Fresh arrivals</h2><p>Popular picks, ready to ship.</p></div><a class="btn light" href="products.php">See all products</a></div>
<?php if (!$products): ?><div class="empty">Our shelves are being stocked. Check back soon.</div><?php else: ?><section class="grid"><?php foreach($products as $p): ?><article class="card"><a href="product-details.php?id=<?= (int)$p['id'] ?>"><div class="product-image"><?php if($p['image']): ?><img src="<?= e(b2cImage($p['image'])) ?>" alt="<?= e($p['name']) ?>"><?php else: ?>✦<?php endif; ?></div><div class="card-body"><span class="muted"><?= e($p['category_name']??'Collection') ?></span><h3><?= e($p['name']) ?></h3><span class="price"><?= e(formatPrice($p['retail_price'])) ?></span></div></a></article><?php endforeach; ?></section><?php endif; ?>
<?php b2cEnd(); ?>