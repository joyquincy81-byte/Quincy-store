<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/middleware/logistics.php';
require_once ROOT_PATH . '/middleware/csrf.php';

$logisticsDb = null;
$logisticsError = null;
try { $logisticsDb = Database::getInstance()->getConnection(); }
catch (PDOException $e) { $logisticsError = 'Logistics data is temporarily unavailable.'; error_log('Logistics: '.$e->getMessage()); }

function logisticsRows(string $sql, array $params = []): array {
    global $logisticsDb;
    if (!$logisticsDb) return [];
    try { $s = $logisticsDb->prepare($sql); $s->execute($params); return $s->fetchAll() ?: []; }
    catch (PDOException $e) { return []; }
}
function logisticsOne(string $sql, array $params = []): ?array { $r = logisticsRows($sql, $params); return $r[0] ?? null; }
function logisticsScalar(string $sql, array $params = [], mixed $fallback = 0): mixed {
    global $logisticsDb;
    if (!$logisticsDb) return $fallback;
    try { $s=$logisticsDb->prepare($sql); $s->execute($params); return $s->fetchColumn() ?? $fallback; } catch (PDOException $e) { return $fallback; }
}
function logisticsName(): string { $u=$_SESSION['user']??[]; return trim(($u['first_name']??'').' '.($u['last_name']??'')) ?: 'Operations team'; }
function logisticsStatus(string $status): string { return ucwords(str_replace('_',' ', strtolower($status ?: 'pending'))); }
function logisticsPage(string $title, string $active): void {
    global $logisticsError;
    $links=['dashboard.php'=>'Control tower','shipments.php'=>'Shipments','tracking.php'=>'Tracking','profile.php'=>'Profile'];
    ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=e($title)?> · <?=e(SITE_NAME)?></title><link rel="stylesheet" href="<?=e(BASE_URL)?>/logistics/logistics.css"></head><body class="lx">
    <div class="lx-shell"><aside class="lx-side"><a class="lx-brand" href="dashboard.php"><b>Q</b><span>QUINCY<small>LOGISTICS CONTROL</small></span></a><nav><?php foreach($links as $url=>$label): ?><a class="<?= $active===$url?'active':''?>" href="<?=e($url)?>"><span><?=['dashboard.php'=>'⌂','shipments.php'=>'▤','tracking.php'=>'⌁','profile.php'=>'◉'][$url]?></span><?=e($label)?></a><?php endforeach; ?></nav><a class="lx-signout" href="<?=e(BASE_URL)?>/logout.php">↪ Sign out</a></aside><main class="lx-main"><header class="lx-header"><div><p class="lx-eyebrow">OPERATIONS / <?=e(strtoupper($title))?></p><h1><?=e($title)?></h1></div><div class="lx-user"><span class="lx-avatar"><?=e(strtoupper(substr(logisticsName(),0,1)))?></span><span><?=e(logisticsName())?><small>Logistics operator</small></span></div></header><?php if($logisticsError): ?><div class="lx-alert"><?=e($logisticsError)?></div><?php endif; ?><div class="lx-content"><?php
}
function logisticsEnd(): void { ?></div></main></div><script src="<?=e(BASE_URL)?>/logistics/logistics.js"></script></body></html><?php }
function logisticsValue(array $row, array $keys, string $fallback='—'): string { foreach($keys as $k) if(isset($row[$k]) && $row[$k] !== '') return (string)$row[$k]; return $fallback; }
