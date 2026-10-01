<?php
declare(strict_types=1);
final class Cart
{
 private PDO $db;
 public function __construct(){ $this->db=Database::getInstance()->getConnection(); }
 private function id(int $u):int{$s=$this->db->prepare('INSERT INTO carts(user_id) VALUES(?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)');$s->execute([$u]);return (int)$this->db->lastInsertId();}
 public function get(int $userId):array{$s=$this->db->prepare('SELECT ci.*,p.name,p.retail_price,p.sku FROM carts c JOIN cart_items ci ON ci.cart_id=c.id JOIN products p ON p.id=ci.product_id WHERE c.user_id=? AND p.status="active"');$s->execute([$userId]);$rows=$s->fetchAll();$total=0;foreach($rows as &$r){$r['subtotal']=(float)$r['retail_price']*(int)$r['quantity'];$total+=$r['subtotal'];}return ['items'=>$rows,'total'=>$total];}
 public function add(int $userId,int $productId,int $quantity=1):bool{if($userId<1||$productId<1||$quantity<1)throw new InvalidArgumentException('Invalid cart item.');$cid=$this->id($userId);$s=$this->db->prepare('INSERT INTO cart_items(cart_id,product_id,quantity) SELECT ?,?,? FROM products WHERE id=? AND status="active" ON DUPLICATE KEY UPDATE quantity=quantity+VALUES(quantity)');$s->execute([$cid,$productId,$quantity,$productId]);return $s->rowCount()>0;}
 public function update(int $userId,int $productId,int $quantity):bool{$cid=$this->id($userId);if($quantity<1)return $this->remove($userId,$productId);$s=$this->db->prepare('UPDATE cart_items SET quantity=? WHERE cart_id=? AND product_id=?');$s->execute([$quantity,$cid,$productId]);return $s->rowCount()>0;}
 public function remove(int $userId,int $productId):bool{$s=$this->db->prepare('DELETE ci FROM cart_items ci JOIN carts c ON c.id=ci.cart_id WHERE c.user_id=? AND ci.product_id=?');$s->execute([$userId,$productId]);return $s->rowCount()>0;}
}