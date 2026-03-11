<?php
session_start();
require_once __DIR__ . '/controllers/AppController.php';

$page = $_GET['page'] ?? 'home';

switch ($page) {
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
