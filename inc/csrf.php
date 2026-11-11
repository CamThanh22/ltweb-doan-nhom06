<?php
declare(strict_types=1);
function csrfToken(): string {
    if (empty($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrfField(): string { return '<input type="hidden" name="csrf" value="'.htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8').'">'; }
function csrfHopLe(): bool {
    $ma = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf'] ?? null);
    return is_string($ma) && hash_equals(csrfToken(), $ma);
}
