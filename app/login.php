<?php
$role = $_POST['role'] ?? 'farmer';
$tables = ['farmer'=>'farmers', 'officer'=>'officers', 'admin'=>'admins'];
if (!isset($tables[$role])) throw new RuntimeException('Select a valid account type.');

$account = query(
    'SELECT * FROM '.$tables[$role].' WHERE email=?',
    [trim($_POST['email'] ?? '')]
)->fetch();
if (!$account || !password_verify($_POST['password'] ?? '', $account['password'])) {
    throw new RuntimeException('Email or password is incorrect for this account type.');
}
if ($role !== 'admin' && $account['status'] !== 'active') {
    $message = $account['status'] === 'pending'
        ? 'Your account is awaiting admin approval.'
        : 'Your account is inactive. Contact the admin.';
    throw new RuntimeException($message);
}

session_regenerate_id(true);
$_SESSION['uid'] = $account['id'];
$_SESSION['role'] = $role;
go('index.php');
