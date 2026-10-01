<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/_bootstrap.php';
apiMethod('POST'); apiCsrf(); $in=apiInput();
$first=trim((string)($in['first_name']??'')); $last=trim((string)($in['last_name']??'')); $email=strtolower(trim((string)($in['email']??''))); $phone=trim((string)($in['phone']??'')); $role=(string)($in['role']??'customer'); $pass=(string)($in['password']??'');
if($first===''||$last===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pass)<8||!in_array($role,['customer','supplier','manufacturer','business_buyer'],true)) apiError('Invalid registration data',422);
$db=apiDb(); try{$db->beginTransaction(); $s=$db->prepare('SELECT id FROM users WHERE email=? LIMIT 1');$s->execute([$email]);if($s->fetch())apiError('Email already registered',409);$s=$db->prepare('SELECT id FROM roles WHERE name=? LIMIT 1');$s->execute([$role]);$r=$s->fetch();if(!$r)apiError('Invalid role',422);$s=$db->prepare('INSERT INTO users(role_id,first_name,last_name,email,phone,password,status) VALUES(?,?,?,?,?,?,?)');$s->execute([$r['id'],$first,$last,$email,$phone,password_hash($pass,PASSWORD_BCRYPT,['cost'=>BCRYPT_COST]),in_array($role,['supplier','manufacturer'])?'pending':'active']);$id=(int)$db->lastInsertId();$db->commit();}catch(Throwable $e){if($db->inTransaction())$db->rollBack();apiException($e);}
apiResponse(['id'=>$id],201);