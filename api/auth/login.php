<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/_bootstrap.php';
apiMethod('POST'); apiCsrf();
$in=apiInput(); $email=strtolower(trim((string)($in['email']??''))); $password=(string)($in['password']??'');
if (!filter_var($email,FILTER_VALIDATE_EMAIL)||$password==='') apiError('Invalid credentials',422);
$s=apiDb()->prepare('SELECT u.*,r.name role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.email=? LIMIT 1'); $s->execute([$email]); $u=$s->fetch();
if (!$u||!password_verify($password,(string)$u['password'])) apiError('Invalid credentials',401);
if (($u['status']??'')!=='active') apiError('Account is not active',403);
setUserSession($u); apiDb()->prepare('UPDATE users SET last_login=NOW() WHERE id=?')->execute([$u['id']]);
apiResponse(['user'=>$_SESSION['user']]);