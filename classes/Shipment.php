<?php
declare(strict_types=1);
final class Shipment
{
 private PDO $db;
 public function __construct(){ $this->db=Database::getInstance()->getConnection(); }
 public function find(int $id,int $userId=0):?array{$q='SELECT s.* FROM shipments s';$p=[];if($userId>0){$q.=' JOIN orders o ON o.id=s.order_id WHERE s.id=? AND (o.user_id=? OR s.assigned_to=?)';$p=[$id,$userId,$userId];}else{$q.=' WHERE s.id=?';$p=[$id];}$s=$this->db->prepare($q.' LIMIT 1');$s->execute($p);return $s->fetch()?:null;}
 public function byTracking(string $tracking):?array{$s=$this->db->prepare('SELECT * FROM shipments WHERE tracking_number=? LIMIT 1');$s->execute([trim($tracking)]);return $s->fetch()?:null;}
 public function create(int $orderId,string $carrier,string $address):int{$carrier=trim($carrier);if($orderId<1||$carrier==='')throw new InvalidArgumentException('Order and carrier are required.');$tracking=strtoupper(bin2hex(random_bytes(8)));$s=$this->db->prepare('INSERT INTO shipments(order_id,tracking_number,carrier,status,shipping_address) VALUES(?,?,?,"pending",?)');$s->execute([$orderId,$tracking,$carrier,trim($address)]);return (int)$this->db->lastInsertId();}
 public function updateStatus(int $id,string $status,int $assignee=0):bool{if(!in_array($status,['pending','in_transit','delivered','delayed','exception','cancelled'],true))throw new InvalidArgumentException('Invalid shipment status.');$q='UPDATE shipments SET status=?';$p=[$status];if($assignee>0){$q.=' WHERE id=? AND assigned_to=?';$p[]=$id;$p[]=$assignee;}else{$q.=' WHERE id=?';$p[]=$id;}$s=$this->db->prepare($q);$s->execute($p);return $s->rowCount()>0;}
}