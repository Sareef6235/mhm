<?php
session_start();
require_once __DIR__ . '/../models/AdminModel.php';

function admin_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
