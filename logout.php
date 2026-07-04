<?php require_once __DIR__.'/includes/functions.php'; secure_session(); audit('logout','users',$_SESSION['user']['id']??null); session_destroy(); header('Location: /login.php');
