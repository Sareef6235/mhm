<?php
require_once __DIR__ . '/../config/db.php';
unset($_SESSION['student']);
header('Location: login.php');
exit;
