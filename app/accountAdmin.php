<?php
// Shared validation for admin actions. Only the fixed account tables are accepted.
function admin_account_target(): array {
    $role = $_POST['role'] ?? '';
    $tables = ['farmer'=>'farmers', 'officer'=>'officers'];
    if (!isset($tables[$role])) throw new RuntimeException('Select a farmer or Krishi officer account.');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    if (!$id) throw new RuntimeException('Select a valid account.');
    return [$role, $tables[$role], $id];
}
