<?php
declare(strict_types=1);
final class Payment
{
 private PDO $db;
 public function __construct(){ $this->db=Database::getInstance()->getConnection(); }
 public function findByReference(string $reference,int $userId=0):?array{$q='SELECT * FROM payments WHERE transaction_reference=?';$p=[$reference];if($userId>0){$q.=' AND user_id=?';$p[]=$userId;}$q.=' LIMIT 1';$s=$this->db->prepare($q);$s->execute($p);return $s->fetch()?:null;}
 public function initiate(int $userId,int $orderId,string $method):array{$s=$this->db->prepare('SELECT total_amount FROM orders WHERE id=? AND user_id=?');$s->execute([$orderId,$userId]);$amount=$s->fetchColumn();if($amount===false)throw new RuntimeException('Order not found.');if(!in_array($method,['mobile_money','mtn','orange','card','bank'],true))throw new InvalidArgumentException('Invalid payment method.');$ref=bin2hex(random_bytes(16));$s=$this->db->prepare('INSERT INTO payments(order_id,user_id,amount,method,status,transaction_reference) VALUES(?,?,?,?,"pending",?)');$s->execute([$orderId,$userId,$amount,$method,$ref]);return ['id'=>(int)$this->db->lastInsertId(),'reference'=>$ref,'amount'=>(float)$amount];}
 public function updateStatus(string $reference,string $status,?string $providerReference=null):bool{if(!in_array($status,['pending','processing','completed','failed','cancelled','refunded'],true))throw new InvalidArgumentException('Invalid payment status.');$s=$this->db->prepare('UPDATE payments SET status=?,provider_reference=COALESCE(?,provider_reference) WHERE transaction_reference=?');$s->execute([$status,$providerReference,$reference]);return $s->rowCount()>0;}
}