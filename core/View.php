<?php
function view(string $path, array $data = []): void {
    extract($data, EXTR_SKIP);
    require __DIR__ . '/../views/' . $path . '.php';
}

function redirect_to(string $url): void {
    header('Location: ' . $url);
    exit;
}

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
