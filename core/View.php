<?php

function view(string $path, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require __DIR__ . '/../views/' . $path . '.php';
}

function redirect_to(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function base_path(): string
{
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($script === '/' || $script === '.') {
        return '';
    }
    return rtrim($script, '/');
}

function app_url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

function is_logged_in_student(): bool
{
    return !empty($_SESSION['student_id']);
}

function student_session(): ?array
{
    return $_SESSION['student'] ?? null;
}
