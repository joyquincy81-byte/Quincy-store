<?php
declare(strict_types=1);
final class Review
{
 private PDO $db;
 public function __construct(){ $this->db=Database::getInstance()->getConnection(); }
 public function forProduct(int $productId,bool $approvedOnly=true):array{$q='SELECT r.*,u.first_name,u.last_name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.product_id=?';if($approvedOnly)$q.=' AND r.status="approved"';$s=$this->db->prepare($q.' ORDER BY r.created_at DESC');$s->execute([$productId]);return $s->fetchAll();}
 public function create(int $userId,int $productId,int $rating,string $review):int{if($rating<1||$rating>5||trim($review)==='')throw new InvalidArgumentException('Rating and review are required.');$s=$this->db->prepare('SELECT 1 FROM orders o JOIN order_items oi ON oi.order_id=o.id WHERE o.user_id=? AND oi.product_id=? AND o.status NOT IN ("cancelled","failed") LIMIT 1');$s->execute([$userId,$productId]);if(!$s->fetchColumn())throw new RuntimeException('Product must be purchased before reviewing.');$s=$this->db->prepare('INSERT INTO reviews(user_id,product_id,rating,review,status) VALUES(?,?,?,?,"pending")');$s->execute([$userId,$productId,$rating,trim($review)]);return (int)$this->db->lastInsertId();}
 public function moderate(int $id,string $status):bool{if(!in_array($status,['approved','rejected','pending'],true))return false;$s=$this->db->prepare('UPDATE reviews SET status=? WHERE id=?');$s->execute([$status,$id]);return $s->rowCount()>0;}
}