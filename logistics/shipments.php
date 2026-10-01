<?php
require_once __DIR__ . '/_bootstrap.php';
$status=trim((string)($_GET['status']??''));
$allowed=['pending','processing','in_transit','delivered','delayed','exception','cancelled'];
$sql='SELECT * FROM shipments'; $params=[];
if(in_array($status,$allowed,true)){ $sql.=' WHERE status=?'; $params[]=$status; }
$sql.=' ORDER BY created_at DESC LIMIT 100';
$rows=logisticsRows($sql,$params);
logisticsPage('Shipments','shipments.php'); ?>
<section class="lx-card"><div class="lx-filter"><input data-filter type="search" placeholder="Search tracking, carrier or destination"><select onchange="location=this.value"><option value="shipments.php">All statuses</option><?php foreach($allowed as $s): ?><option value="?status=<?=e($s)?>" <?=$status===$s?'selected':''?>><?=e(logisticsStatus($s))?></option><?php endforeach; ?></select></div><?php if(!$rows): ?><div class="lx-empty">No shipments match this view. Try another status or check back when orders are ready.</div><?php else: ?><table class="lx-table"><thead><tr><th>Tracking number</th><th>Carrier</th><th>Destination</th><th>Status</th><th>Created</th><th></th></tr></thead><tbody><?php foreach($rows as $r): ?><tr data-row><td><strong><?=e(logisticsValue($r,['tracking_number','tracking_code','reference']))?></strong></td><td><?=e(logisticsValue($r,['carrier','courier']))?></td><td><?=e(logisticsValue($r,['destination','delivery_address','city']))?></td><td><span class="lx-badge"><?=e(logisticsStatus(logisticsValue($r,['status'],'pending')))?></span></td><td><?=e(logisticsValue($r,['created_at']))?></td><td><a href="shipment-details.php?id=<?=e($r['id']??'')?>">Open</a></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></section>
<?php logisticsEnd(); ?>
