<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/business.php';

$userId = (int) currentUserId();
$metrics = ['rfqs' => 0, 'open' => 0, 'negotiations' => 0];
$recent = [];
$error = null;
try {
    $db = Database::getInstance()->getConnection();
    foreach ([
        'rfqs' => 'SELECT COUNT(*) FROM quotation_requests WHERE buyer_id=?',
        'open' => 'SELECT COUNT(*) FROM quotation_requests WHERE buyer_id=? AND status IN ("pending","negotiating","quoted")',
        'negotiations' => 'SELECT COUNT(*) FROM negotiations n JOIN quotation_requests q ON q.id=n.quotation_request_id WHERE q.buyer_id=? AND n.status="open"',
    ] as $key => $sql) { $s=$db->prepare($sql); $s->execute([$userId]); $metrics[$key]=(int)$s->fetchColumn(); }
    $s=$db->prepare('SELECT q.id,q.status,q.created_at,COALESCE(p.name,"Custom sourcing request") AS product_name
        FROM quotation_requests q LEFT JOIN quotation_items qi ON qi.quotation_request_id=q.id
        LEFT JOIN products p ON p.id=qi.product_id WHERE q.buyer_id=? ORDER BY q.created_at DESC LIMIT 6');
    $s->execute([$userId]); $recent=$s->fetchAll();
} catch (PDOException $e) { error_log('B2B dashboard: '.$e->getMessage()); $error='Your workspace is temporarily unavailable.'; }
$user=$_SESSION['user']??[];
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Business workspace - <?=e(SITE_NAME)?></title><link rel="stylesheet" href="<?=e(BASE_URL)?>/assets/css/style.css"></head>
<body class="b2b-page"><header class="directory-header"><div class="container directory-header-row"><a class="brand" href="<?=e(BASE_URL)?>/b2b/"><span class="brand-mark">Q</span><span><strong>QUINCY</strong><small>BUSINESS</small></span></a><nav><a href="<?=e(BASE_URL)?>/b2b/marketplace.php">Marketplace</a><a href="<?=e(BASE_URL)?>/b2b/suppliers.php">Suppliers</a><a href="<?=e(BASE_URL)?>/b2b/quotations.php">My RFQs</a><a href="<?=e(BASE_URL)?>/b2b/negotiations.php">Negotiations</a><a href="<?=e(BASE_URL)?>/logout.php">Sign out</a></nav></div></header>
<main class="container b2b-main"><div class="directory-breadcrumb">Business workspace</div><section class="b2b-hero"><div><p class="eyebrow">BUYER WORKSPACE</p><h1>Source smarter with Quincy.</h1><p>Find trusted suppliers, request pricing, and keep every business conversation in one place.</p><a class="button button-primary" href="<?=e(BASE_URL)?>/b2b/marketplace.php">Explore marketplace →</a></div><div class="b2b-hero-stats"><span><strong><?= $metrics['rfqs'] ?></strong><small>Total RFQs</small></span><span><strong><?= $metrics['open'] ?></strong><small>Active requests</small></span><span><strong><?= $metrics['negotiations'] ?></strong><small>Open negotiations</small></span></div></section>
<?php if($error): ?><div class="catalog-notice"><?=e($error)?></div><?php endif; ?>
<div class="b2b-layout"><section class="b2b-results"><div class="b2b-results-head"><div><p class="eyebrow">QUICK ACTIONS</p><h2>Move your next order forward</h2></div></div><div class="b2b-product-grid"><a class="b2b-product-card" href="<?=e(BASE_URL)?>/b2b/request-quotation.php"><div class="b2b-product-body"><h3>Post an RFQ</h3><p>Describe your quantity, destination, and terms to receive an offer.</p></div></a><a class="b2b-product-card" href="<?=e(BASE_URL)?>/b2b/suppliers.php"><div class="b2b-product-body"><h3>Find suppliers</h3><p>Compare verification, ratings, locations, and active product lines.</p></div></a><a class="b2b-product-card" href="<?=e(BASE_URL)?>/b2b/quotations.php"><div class="b2b-product-body"><h3>Review quotations</h3><p>Track status and open a negotiation when an offer needs refining.</p></div></a></div>
<div class="b2b-results-head"><div><p class="eyebrow">RECENT ACTIVITY</p><h2>Your latest RFQs</h2></div><a href="<?=e(BASE_URL)?>/b2b/quotations.php">View all →</a></div><?php if(!$recent): ?><div class="supplier-empty"><h2>No requests yet</h2><p>Start by browsing wholesale products or posting an RFQ.</p></div><?php else: ?><div class="b2b-table"><?php foreach($recent as $row): ?><div class="b2b-table-row"><div><strong><?=e($row['product_name'])?></strong><small><?=e(date('M j, Y',strtotime((string)$row['created_at'])))?></small></div><span class="status-badge status-<?=e($row['status'])?>"><?=e(ucfirst($row['status']))?></span><a href="<?=e(BASE_URL)?>/b2b/quotations.php?id=<?= (int)$row['id']?>">Open</a></div><?php endforeach; ?></div><?php endif; ?></section></div></main></body></html>