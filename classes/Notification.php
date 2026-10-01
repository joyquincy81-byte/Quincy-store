<?php
declare(strict_types=1);
final class Notification
{
 private PDO $db;
 public function __construct(){ $this->db=Database::getInstance()->getConnection(); }
 public function forUser(int $userId,int $limit=100):array{$limit=max(1,min(100,$limit));$s=$this->db->prepare('SELECT id,title,message,type,is_read,created_at FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT '.$limit);$s->execute([$userId]);return $s->fetchAll();}
 public function create(int $userId,string $title,string $message,string $type='info'):int{if($userId<1||trim($title)===''||trim($message)==='')throw new InvalidArgumentException('Notification content is required.');$s=$this->db->prepare('INSERT INTO notifications(user_id,title,message,type,is_read) VALUES(?,?,?,?,0)');$s->execute([$userId,trim($title),trim($message),$type]);return (int)$this->db->lastInsertId();}
 public function markRead(int $userId,int $id=0):bool{$q='UPDATE notifications SET is_read=1 WHERE user_id=?';$p=[$userId];if($id>0){$q.=' AND id=?';$p[]=$id;}$s=$this->db->prepare($q);$s->execute($p);return $s->rowCount()>0;}
}