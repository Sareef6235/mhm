<?php
session_start();
require_once __DIR__ . '/controllers/AppController.php';

$page = $_GET['page'] ?? (is_logged_in_student() ? 'home' : 'login');

switch ($page) {
    case 'login':
        AppController::login();
        break;
    case 'logout':
        AppController::logout();
        break;
    case 'tracker':
        AppController::tracker();
        break;
    case 'record':
        AppController::record();
        break;
    case 'history':
        AppController::history();
        break;
    case 'profile':
        AppController::profile();
        break;
    case 'home':
    default:
        AppController::home();
        break;
}
