<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/csrf.php';

$b2cDb = null;
$b2cDbError = false;
try { $b2cDb = Database::getInstance()->getConnection(); } catch (Throwable $e) { $b2cDbError = true; }

function b2cQuery(string $sql, array $params = []): ?PDOStatement {
    global $b2cDb;
    if (!$b2cDb) return null;
    try { $s = $b2cDb->prepare($sql); $s->execute($params); return $s; } catch (Throwable $e) { return null; }
}
function b2cRows(string $sql, array $params = []): array {
    $s = b2cQuery($sql, $params); return $s ? ($s->fetchAll() ?: []) : [];
}
function b2cImage(?string $image): string {
    if (!$image) return '';
    return preg_match('~^https?://~i', $image) ? $image : BASE_URL . '/' . ltrim($image, '/');
}
function b2cCartCount(): int {
    if (!isLoggedIn() || currentRole() !== 'customer') return 0;
    return (int) (b2cQuery('SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE user_id=?', [(int) currentUserId()])?->fetchColumn() ?? 0);
}
function b2cPage(string $title, string $active = ''): void {
    $user = $_SESSION['user'] ?? null;
    $cart = b2cCartCount();
    ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title) ?> · <?= e(SITE_NAME) ?></title><link rel="stylesheet" href="<?= e(BASE_URL) ?>/b2c/b2c.css"></head><body>
    <header class="store-header"><div class="announcement">Free delivery on orders over 50,000 FCFA <span>Secure payments · Trusted sellers</span></div>
    <div class="nav-wrap"><a class="brand" href="<?= e(BASE_URL) ?>/b2c/index.php"><b>Q</b><span>QUINCY<small>STORE</small></span></a>
    <form class="search" action="<?= e(BASE_URL) ?>/b2c/products.php" method="get"><input name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search products, brands and more"><button aria-label="Search">⌕</button></form>
    <nav><a class="<?= $active==='products'?'active':'' ?>" href="products.php">Shop</a><a class="<?= $active==='categories'?'active':'' ?>" href="categories.php">Categories</a><a href="<?= isLoggedIn() ? 'customer/dashboard.php' : BASE_URL.'/login.php?role=customer' ?>">Account</a><a class="cart-link" href="cart.php">Cart <i><?= $cart ?></i></a></nav></div></header><main class="container">
    <?php if ($GLOBALS['b2cDbError']): ?><div class="alert error">We're having trouble connecting to our catalog. Please try again shortly.</div><?php endif; ?><?php
}
function b2cEnd(): void { ?></main><footer><div class="container footer-grid"><div><a class="brand light" href="<?= e(BASE_URL) ?>/b2c/index.php"><b>Q</b><span>QUINCY<small>STORE</small></span></a><p>Everyday essentials, delivered with confidence.</p></div><div><strong>Shop</strong><a href="products.php">All products</a><a href="categories.php">Categories</a></div><div><strong>Help</strong><a href="<?= e(BASE_URL) ?>/login.php?role=customer">Sign in</a><a href="cart.php">Your cart</a></div></div><div class="copyright">© <?= date('Y') ?> Quincy Store · Built for Cameroon</div></footer><script src="<?= e(BASE_URL) ?>/b2c/b2c.js"></script></body></html><?php }
function b2cRequireCustomer(): void { if (!isLoggedIn() || currentRole() !== 'customer') redirect(BASE_URL . '/login.php?role=customer'); }
