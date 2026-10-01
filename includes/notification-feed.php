<?php
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php');
}

$user = getCurrentUser();
$userId = (int) $user['id'];
$filter = $_GET['filter'] ?? 'all';
$validFilters = ['all', 'unread', 'orders', 'payments', 'promotions'];
if (!in_array($filter, $validFilters, true)) {
    $filter = 'all';
}

$sql = 'SELECT id, title, message, type, reference_id, is_read, created_at
        FROM notifications WHERE user_id = ?';
$params = [$userId];

if ($filter === 'unread') {
    $sql .= ' AND is_read = 0';
} elseif ($filter === 'orders') {
    $sql .= " AND type IN ('order', 'shipment')";
} elseif ($filter === 'payments') {
    $sql .= " AND type = 'payment'";
} elseif ($filter === 'promotions') {
    $sql .= " AND type = 'promotion'";
}

$sql .= ' ORDER BY created_at DESC LIMIT 100';
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare($sql);
$stmt->execute($params);
$notifications = $stmt->fetchAll();

function notificationFeedIcon(string $type): array
{
    $type = strtolower($type);
    return match (true) {
        str_contains($type, 'order'), str_contains($type, 'shipment') => ['color' => '#2C5282', 'svg' => '<path d="M3 7l9-4 9 4v10l-9 4-9-4z"/><path d="M3 7l9 4 9-4M12 11v10"/>'],
        str_contains($type, 'payment') => ['color' => '#16805B', 'svg' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/>'],
        str_contains($type, 'quotation'), str_contains($type, 'negotiation') => ['color' => '#8A5A00', 'svg' => '<path d="M5 3h10l4 4v14H5z"/><path d="M15 3v5h4M8 13h8M8 17h6"/>'],
        str_contains($type, 'promo') => ['color' => '#B13B57', 'svg' => '<path d="M20.6 13.5L13.5 20.6a2 2 0 01-2.8 0L3.4 13.3a2 2 0 010-2.8l7.1-7.1A2 2 0 0111.9 3H19a2 2 0 012 2v7.1a2 2 0 01-.4 1.4z"/><circle cx="16" cy="8" r="1"/>'],
        default => ['color' => '#64748B', 'svg' => '<path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/>'],
    };
}

function notificationFeedRoute(string $type, ?int $referenceId): string
{
    $type = strtolower($type);
    $id = (int) $referenceId;
    $role = currentRole();

    if (str_contains($type, 'order') || str_contains($type, 'shipment')) {
        $path = in_array($role, ['customer', 'business_buyer'], true)
            ? '/customer/order-details.php?id=' . $id
            : '/business/order-details.php?id=' . $id;
        return BASE_URL . $path;
    }
    if (str_contains($type, 'payment')) {
        return BASE_URL . (in_array($role, ['customer', 'business_buyer'], true) ? '/customer/orders.php' : '/business/payments.php');
    }
    if (str_contains($type, 'quotation') || str_contains($type, 'negotiation')) {
        return BASE_URL . ($role === 'customer' ? '/b2b/negotiations.php' : '/business/quotation-details.php?id=' . $id);
    }
    return getDashboardUrl($role);
}

function notificationFeedTime(string $createdAt): string
{
    $timestamp = strtotime($createdAt);
    return (time() - $timestamp) < 604800 ? sanitize(timeAgo($createdAt)) : date('M j, Y', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Notifications | <?= sanitize(SITE_NAME) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--navy:#1e3a5f;--pale:#e8eef5;--text:#1a1f29;--muted:#647080;--line:#e3e7ed;--white:#fff}
*{box-sizing:border-box}body{margin:0;background:#f7f8fa;color:var(--text);font-family:'IBM Plex Sans',sans-serif}.notification-page{max-width:780px;margin:auto;padding:32px 20px 80px}.notification-header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px}.notification-header h1{margin:0;color:var(--navy);font:600 30px 'Fraunces',serif}.mark-all{color:var(--navy);font-size:14px;text-decoration:none}.mark-all:hover{text-decoration:underline}.filters{display:flex;gap:4px;overflow-x:auto;border-bottom:1px solid var(--line);margin-bottom:20px}.filters a{padding:10px 15px;border-bottom:2px solid transparent;color:var(--muted);font-size:14px;text-decoration:none;white-space:nowrap}.filters a.active{border-bottom-color:var(--navy);color:var(--navy);font-weight:600}.notification-list{overflow:hidden;border:1px solid var(--line);border-radius:8px;background:var(--white)}.notification-row{position:relative;display:flex;align-items:flex-start;gap:14px;padding:16px 18px;color:inherit;text-decoration:none;border-bottom:1px solid var(--line);transition:background .2s}.notification-row:last-child{border-bottom:0}.notification-row:hover{background:#f0f3f8}.notification-row.unread{padding-left:24px;background:var(--pale)}.unread-dot{position:absolute;left:7px;top:23px;width:7px;height:7px;border-radius:50%;background:var(--navy)}.notification-icon{display:grid;flex:0 0 38px;width:38px;height:38px;place-items:center;border-radius:8px}.notification-icon svg{width:19px;height:19px;fill:none;stroke-width:1.8}.notification-body{min-width:0;flex:1}.notification-title{font-size:15px;font-weight:600;line-height:1.4}.notification-message{margin-top:2px;color:var(--muted);font-size:13.5px;line-height:1.4}.notification-time{margin-top:6px;color:var(--muted);font-size:12.5px}.empty-state{padding:72px 20px;text-align:center;color:var(--muted)}.empty-state svg{width:48px;height:48px;margin-bottom:14px;fill:none;stroke:#b8c0cc;stroke-width:1.5}.empty-state p{margin:0;font-size:15px}@media(max-width:600px){.notification-page{padding:24px 14px 60px}.notification-header h1{font-size:24px}}
</style>
</head>
<body>
<main class="notification-page">
<header class="notification-header"><h1>Notifications</h1><a href="#" class="mark-all" id="markAll">Mark all as read</a></header>
<nav class="filters" aria-label="Notification filters">
<?php foreach (['all' => 'All', 'unread' => 'Unread', 'orders' => 'Orders', 'payments' => 'Payments', 'promotions' => 'Promotions'] as $value => $label): ?>
<a href="?filter=<?= $value ?>" class="<?= $filter === $value ? 'active' : '' ?>"><?= $label ?></a>
<?php endforeach; ?>
</nav>
<section class="notification-list" aria-live="polite">
<?php if (!$notifications): ?>
<div class="empty-state"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg><p>You have no notifications yet</p></div>
<?php else: foreach ($notifications as $notification): $icon = notificationFeedIcon((string) $notification['type']); $unread = !(bool) $notification['is_read']; ?>
<a class="notification-row<?= $unread ? ' unread' : '' ?>" data-id="<?= (int) $notification['id'] ?>" href="<?= htmlspecialchars(notificationFeedRoute((string) $notification['type'], $notification['reference_id']), ENT_QUOTES, 'UTF-8') ?>">
<?php if ($unread): ?><span class="unread-dot" aria-label="Unread"></span><?php endif; ?><span class="notification-icon" style="background:<?= $icon['color'] ?>1A"><svg viewBox="0 0 24 24" style="stroke:<?= $icon['color'] ?>"><?= $icon['svg'] ?></svg></span><span class="notification-body"><span class="notification-title"><?= sanitize((string) $notification['title']) ?></span><?php if ((string) $notification['message'] !== ''): ?><span class="notification-message"><?= sanitize((string) $notification['message']) ?></span><?php endif; ?><span class="notification-time"><?= notificationFeedTime((string) $notification['created_at']) ?></span></span>
</a>
<?php endforeach; endif; ?>
</section>
</main>
<script>
const endpoint = '<?= BASE_URL ?>/api/notifications/mark-read.php';
document.querySelectorAll('.notification-row.unread').forEach(row => row.addEventListener('click', event => { event.preventDefault(); fetch(endpoint, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id:row.dataset.id})}).finally(() => { window.location.href = row.href; }); }));
document.getElementById('markAll').addEventListener('click', event => { event.preventDefault(); fetch(endpoint, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({all:true})}).then(response => response.json()).then(data => { if (!data.success) return; document.querySelectorAll('.notification-row.unread').forEach(row => { row.classList.remove('unread'); row.querySelector('.unread-dot')?.remove(); }); }); });
</script>
</body>
</html>
