<?php
declare(strict_types=1);
final class Manufacturer
{
 private PDO $db;
 public function __construct(){ $this->db=Database::getInstance()->getConnection(); }
 public function findByUserId(int $userId): ?array{$s=$this->db->prepare('SELECT * FROM manufacturers WHERE user_id=? LIMIT 1');$s->execute([$userId]);return $s->fetch()?:null;}
 public function find(int $id): ?array{$s=$this->db->prepare('SELECT * FROM manufacturers WHERE id=? LIMIT 1');$s->execute([$id]);return $s->fetch()?:null;}
 public function list(string $term='',int $limit=100):array{$limit=max(1,min(100,$limit));$like='%'.$term.'%';$s=$this->db->prepare('SELECT * FROM manufacturers WHERE status="active" AND (manufacturer_name LIKE ? OR email LIKE ?) ORDER BY manufacturer_name LIMIT '.$limit);$s->execute([$like,$like]);return $s->fetchAll();}
 public function create(int $userId,array $data):int{if($userId<1||trim((string)($data['manufacturer_name']??''))==='')throw new InvalidArgumentException('Manufacturer name is required.');$s=$this->db->prepare('INSERT INTO manufacturers (user_id,manufacturer_name,email,phone,address,city,country,status) VALUES (?,?,?,?,?,?,?,?)');$s->execute([$userId,trim($data['manufacturer_name']),$data['email']??null,$data['phone']??null,$data['address']??null,$data['city']??null,$data['country']??null,$data['status']??'active']);return (int)$this->db->lastInsertId();}
 public function update(int $userId,int $id,array $data):bool{$fields=[];$p=[];foreach(['manufacturer_name','email','phone','address','city','country'] as $f)if(array_key_exists($f,$data)){$fields[]="$f=?";$p[]=$data[$f];}if(!$fields)return false;$p[]=$id;$p[]=$userId;$s=$this->db->prepare('UPDATE manufacturers SET '.implode(',',$fields).' WHERE id=? AND user_id=?');$s->execute($p);return $s->rowCount()>0;}
}