<?php
declare(strict_types=1);
final class Business
{
    private PDO $db;
    public function __construct(){ $this->db=Database::getInstance()->getConnection(); }
    public function findByUserId(int $userId): ?array { $s=$this->db->prepare('SELECT * FROM businesses WHERE user_id=? LIMIT 1');$s->execute([$userId]);return $s->fetch()?:null; }
    public function find(int $id): ?array { $s=$this->db->prepare('SELECT * FROM businesses WHERE id=? LIMIT 1');$s->execute([$id]);return $s->fetch()?:null; }
    public function list(string $term='', int $limit=100): array { $limit=max(1,min(100,$limit));$like='%'.$term.'%';$s=$this->db->prepare('SELECT * FROM businesses WHERE status="active" AND (business_name LIKE ? OR email LIKE ?) ORDER BY business_name LIMIT '.$limit);$s->execute([$like,$like]);return $s->fetchAll(); }
    public function create(int $userId,array $data): int { if($userId<1||trim((string)($data['business_name']??''))==='')throw new InvalidArgumentException('Business name is required.');$s=$this->db->prepare('INSERT INTO businesses (user_id,business_name,email,phone,address,city,country,status) VALUES (?,?,?,?,?,?,?,?)');$s->execute([$userId,trim($data['business_name']),$data['email']??null,$data['phone']??null,$data['address']??null,$data['city']??null,$data['country']??null,$data['status']??'active']);return (int)$this->db->lastInsertId(); }
    public function update(int $userId,int $id,array $data): bool { $fields=[];$params=[];foreach(['business_name','email','phone','address','city','country'] as $f)if(array_key_exists($f,$data)){$fields[]="$f=?";$params[]=$data[$f];}if(!$fields)return false;$params[]=$id;$params[]=$userId;$s=$this->db->prepare('UPDATE businesses SET '.implode(',',$fields).' WHERE id=? AND user_id=?');$s->execute($params);return $s->rowCount()>0; }
}