<?php
require_once __DIR__ . '/auth.php';
unset($_SESSION['admin_id'], $_SESSION['admin_username']);
header('Location: login.php');
exit;
