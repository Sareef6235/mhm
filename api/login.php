<?php require_once __DIR__.'/../includes/functions.php'; secure_session(); verify_csrf();
$stmt=db()->prepare('SELECT * FROM users WHERE email=? AND status="active" LIMIT 1'); $stmt->execute([$_POST['email']??'']); $user=$stmt->fetch();
if($user && password_verify($_POST['password']??'', $user['password_hash'])){ session_regenerate_id(true); $_SESSION['user']=['id'=>$user['id'],'name'=>$user['name'],'role'=>$user['role'],'permissions'=>explode(',', $user['permissions'] ?? '')]; audit('login','users',(int)$user['id']); header('Location: /admin/dashboard.php'); exit; }
header('Location: /login.php?error=1');
