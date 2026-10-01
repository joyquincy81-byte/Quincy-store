<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

$firstName = trim((string) ($customer['first_name'] ?? 'Customer')) ?: 'Customer';
customerPageStart('Customer workbench', 'Dashboard');
?>
<section class="cw-card" style="background:linear-gradient(135deg,#0b2147,#1b4b86);color:#fff;padding:34px">
    <div style="max-width:680px">
        <span class="badge" style="background:#e1ae45;color:#07152f">CUSTOMER WORKBENCH</span>
        <h1 style="margin:16px 0 8px;color:#fff;font-size:34px">Good to see you, <?= e($firstName) ?>.</h1>
        <p style="margin:0 0 22px;color:#dce8f7;line-height:1.6">Manage your Quincy Store purchases, keep your account details current, and get support from one secure workspace.</p>
        <div class="cw-actions">
            <a class="cw-btn gold" href="dashboard.php">Open my dashboard</a>
            <a class="cw-btn" style="background:#fff;color:#123566!important" href="<?= e(BASE_URL) ?>/shop/products.php">Browse marketplace</a>
        </div>
    </div>
</section>

<section class="cw-grid cw-grid-3" style="margin-top:20px">
    <a class="cw-card" href="orders.php" style="text-decoration:none">
        <span class="badge">ORDERS</span>
        <h2 style="margin:14px 0 6px">Track purchases</h2>
        <p class="muted" style="margin:0">Review order status, totals, and delivery details.</p>
    </a>
    <a class="cw-card" href="wishlist.php" style="text-decoration:none">
        <span class="badge">SHOPPING LIST</span>
        <h2 style="margin:14px 0 6px">Your wishlist</h2>
        <p class="muted" style="margin:0">Keep products you love close for your next purchase.</p>
    </a>
    <a class="cw-card" href="support.php" style="text-decoration:none">
        <span class="badge">ASSISTANCE</span>
        <h2 style="margin:14px 0 6px">Need a hand?</h2>
        <p class="muted" style="margin:0">Contact support and follow up on your requests.</p>
    </a>
</section>

<section class="cw-card" style="margin-top:20px">
    <div class="cw-title" style="margin-bottom:8px">
        <div><h2>Everything in one place</h2><p>Quick access to the tools you use most.</p></div>
        <a href="dashboard.php">View activity →</a>
    </div>
    <div class="cw-actions">
        <a class="cw-btn alt" href="profile.php">Profile settings</a>
        <a class="cw-btn alt" href="addresses.php">Saved addresses</a>
        <a class="cw-btn alt" href="reviews.php">Your reviews</a>
        <a class="cw-btn alt" href="notifications.php">Notifications</a>
    </div>
</section>
<?php customerPageEnd(); ?>