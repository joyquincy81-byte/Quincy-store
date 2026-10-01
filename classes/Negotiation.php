<?php
declare(strict_types=1);
final class Negotiation
{
 private PDO $db;
 public function __construct(){ $this->db=Database::getInstance()->getConnection(); }
 private function owned(int $id,int $userId):bool{$s=$this->db->prepare('SELECT n.id FROM negotiations n JOIN quotation_requests q ON q.id=n.quotation_request_id LEFT JOIN suppliers sp ON sp.id=q.supplier_id WHERE n.id=? AND (q.buyer_id=? OR sp.user_id=?)');$s->execute([$id,$userId,$userId]);return (bool)$s->fetchColumn();}
 public function find(int $id,int $userId=0):?array{$q='SELECT n.*,q.buyer_id,q.supplier_id FROM negotiations n JOIN quotation_requests q ON q.id=n.quotation_request_id WHERE n.id=?';$p=[$id];if($userId>0){$q.=' AND (q.buyer_id=? OR q.supplier_id IN (SELECT id FROM suppliers WHERE user_id=?))';$p[]=$userId;$p[]=$userId;}$s=$this->db->prepare($q.' LIMIT 1');$s->execute($p);return $s->fetch()?:null;}
 public function create(int $quotationId,int $userId):int{$s=$this->db->prepare('SELECT q.id FROM quotation_requests q LEFT JOIN suppliers sp ON sp.id=q.supplier_id WHERE q.id=? AND (q.buyer_id=? OR sp.user_id=?)');$s->execute([$quotationId,$userId,$userId]);if(!$s->fetchColumn())throw new RuntimeException('Quotation not found.');$s=$this->db->prepare('INSERT INTO negotiations(quotation_request_id,status) VALUES(?,"open")');$s->execute([$quotationId]);return (int)$this->db->lastInsertId();}
 public function addMessage(int $id,int $userId,string $message):int{if(!$this->owned($id,$userId)||trim($message)==='')throw new InvalidArgumentException('Invalid negotiation message.');$s=$this->db->prepare('INSERT INTO negotiation_messages(negotiation_id,user_id,message) VALUES(?,?,?)');$s->execute([$id,$userId,trim($message)]);return (int)$this->db->lastInsertId();}
 public function updateStatus(int $id,int $userId,string $status):bool{if(!$this->owned($id,$userId)||!in_array($status,['open','accepted','rejected','closed'],true))return false;$s=$this->db->prepare('UPDATE negotiations SET status=? WHERE id=?');$s->execute([$status,$id]);return $s->rowCount()>0;}
}