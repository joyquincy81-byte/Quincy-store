<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/admin.php';

$categoryRows = [];
$analytics = ['categories' => 0, 'products' => 0, 'with_products' => 0, 'uncategorized' => 0];
$error = null;
try {
    $categoryModel = new Category();
    $categoryRows = $categoryModel->adminOverview();
    $analytics = $categoryModel->analytics();
} catch (PDOException $exception) {
    error_log('Category dashboard unavailable: ' . $exception->getMessage());
    $error = 'Category analytics are temporarily unavailable.';
}

$tree = [];
foreach ($categoryRows as $category) {
    $parent = $category['parent_id'] ?? null;
    $tree[$parent][] = $category;
}
$selectedId = isset($_GET['category']) ? (int) $_GET['category'] : 0;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Category control centre - <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/admin.css">
</head>
<body class="admin-categories-page">
<header class="admin-topbar"><div class="container admin-topbar-inner"><a class="brand" href="<?= e(BASE_URL) ?>/admin/dashboard.php"><img src="assets/images/logos/logos.jpeg" alt="quincy store logo" width="50" height="50" ><span><strong>QUINCY</strong><small>ADMIN</small></span></a><nav><a href="<?= e(BASE_URL) ?>/admin/dashboard.php">Dashboard</a><a class="admin-active" href="<?= e(BASE_URL) ?>/admin/categories.php">Categories</a><a href="<?= e(BASE_URL) ?>/admin/products.php">Products</a><a href="<?= e(BASE_URL) ?>/logout.php">Sign out</a></nav></div></header>
<main class="container category-control">
    <div class="admin-breadcrumb"><a href="<?= e(BASE_URL) ?>/admin/dashboard.php">Admin</a><span>/</span>Catalog control<span>/</span>Categories</div>
    <div class="admin-page-heading"><div><p class="eyebrow">CATALOG OPERATIONS</p><h1>Category control centre</h1><p>Organize the wholesale assortment, map products, and monitor category coverage.</p></div><a class="button button-primary" href="#category-editor">+ Add category</a></div>
    <?php if ($error): ?><div class="catalog-notice"><?= e($error) ?></div><?php endif; ?>
    <section class="category-kpi-grid">
        <div class="category-kpi"><span>Active categories</span><strong><?= $analytics['categories'] ?></strong><small>Published taxonomy nodes</small></div>
        <div class="category-kpi"><span>Active products</span><strong><?= $analytics['products'] ?></strong><small>Listings in catalogue</small></div>
        <div class="category-kpi"><span>Category coverage</span><strong><?= $analytics['products'] ? round(($analytics['with_products'] / max(1, $analytics['categories'])) * 100) : 0 ?>%</strong><small>Categories with active listings</small></div>
        <div class="category-kpi category-kpi-warning"><span>Needs mapping</span><strong><?= $analytics['uncategorized'] ?></strong><small>Products without a category</small></div>
    </section>
    <div class="category-control-layout">
        <aside class="category-tree-panel">
            <div class="category-panel-heading"><div><p class="eyebrow">TAXONOMY</p><h2>Category tree</h2></div><span><?= count($categoryRows) ?> nodes</span></div>
            <label class="category-tree-search"><span class="sr-only">Filter categories</span><input type="search" placeholder="Filter categories" data-category-filter></label>
            <div class="category-tree" data-category-tree>
                <?php if (!$categoryRows): ?><p class="admin-muted">No categories found.</p><?php endif; ?>
                <?php foreach ($tree[null] ?? $tree[0] ?? [] as $parent): ?>
                    <div class="category-tree-node" data-category-node data-category-name="<?= e(strtolower($parent['name'])) ?>">
                        <a class="<?= $selectedId === (int) $parent['id'] ? 'selected' : '' ?>" href="?category=<?= (int) $parent['id'] ?>"><span class="tree-chevron">▾</span><strong><?= e($parent['name']) ?></strong><small><?= (int) $parent['product_count'] ?></small></a>
                        <?php foreach ($tree[$parent['id']] ?? [] as $child): ?><a class="category-tree-child <?= $selectedId === (int) $child['id'] ? 'selected' : '' ?>" href="?category=<?= (int) $child['id'] ?>"><span>↳</span><?= e($child['name']) ?><small><?= (int) $child['product_count'] ?></small></a><?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </aside>
        <section class="category-workspace">
            <div class="category-tabs"><a class="active" href="#overview">Overview</a><a href="#assortment">Assortment</a><a href="#mapping">Bulk mapping</a><a href="#seo">SEO metadata</a></div>
            <div class="category-overview" id="overview">
                <div class="category-workspace-heading"><div><p class="eyebrow">ASSORTMENT PERFORMANCE</p><h2><?= $selectedId ? 'Selected category workspace' : 'All catalogue categories' ?></h2></div><button class="outline-button" type="button">Export report</button></div>
                <div class="category-table-wrap"><table class="category-table"><thead><tr><th>Category</th><th>Hierarchy</th><th>Products</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach ($categoryRows as $category): ?><tr><td><strong><?= e($category['name']) ?></strong><small><?= e($category['description'] ?: 'No description added') ?></small></td><td><?= $category['parent_id'] ? 'Subcategory' : 'Top level' ?></td><td><strong><?= (int) $category['product_count'] ?></strong></td><td><span class="status-pill status-active"><?= e(ucfirst((string) $category['status'])) ?></span></td><td><a href="#category-editor">Edit</a></td></tr><?php endforeach; ?><?php if (!$categoryRows): ?><tr><td colspan="5" class="admin-muted">Category data will appear here once the catalogue is seeded.</td></tr><?php endif; ?></tbody></table></div>
            </div>
            <div class="category-tools-grid" id="assortment">
                <article class="category-tool-card"><span class="tool-icon">▦</span><div><h3>Storefront assortment</h3><p>Control which categories appear first in the buyer-facing wholesale marketplace.</p><button class="outline-button" type="button">Manage layout</button></div></article>
                <article class="category-tool-card" id="mapping"><span class="tool-icon">↔</span><div><h3>Bulk product mapping</h3><p>Assign large product groups to a category and identify uncategorized listings.</p><button class="outline-button" type="button">Open mapper</button></div></article>
                <article class="category-tool-card" id="seo"><span class="tool-icon">⌕</span><div><h3>SEO and buyer labels</h3><p>Set search phrases, buyer-facing descriptions, and category aliases.</p><button class="outline-button" type="button">Edit metadata</button></div></article>
            </div>
            <form class="category-editor" id="category-editor" method="post"><div><p class="eyebrow">CATEGORY CONFIGURATION</p><h2>Create or edit category</h2><p>Taxonomy changes should be reviewed before publishing to buyers.</p></div><div class="editor-fields"><label>Category name<input name="name" placeholder="e.g. Agricultural equipment"></label><label>Parent category<select name="parent_id"><option value="">Top-level category</option><?php foreach ($categoryRows as $category): ?><option value="<?= (int) $category['id'] ?>"><?= e($category['name']) ?></option><?php endforeach; ?></select></label><label class="editor-wide">Buyer-facing description<textarea name="description" rows="3" placeholder="Describe the products and sourcing use cases in this category"></textarea></label></div><div class="editor-actions"><button type="button" class="outline-button">Save as draft</button><button type="button" class="button button-primary">Publish category</button></div></form>
        </section>
    </div>
</main>
<script>
document.querySelector('[data-category-filter]')?.addEventListener('input', function () {
    const query = this.value.toLowerCase().trim();
    document.querySelectorAll('[data-category-node]').forEach((node) => {
        node.hidden = query !== '' && !node.dataset.categoryName.includes(query);
    });
});
</script>
</body>
</html>
