<?php
$current = $_POST['current_password'] ?? '';
$next = $_POST['new_password'] ?? '';
if (!password_verify($current, $me['password'])) throw new RuntimeException('Current password is incorrect.');
if (strlen($next)<8 || strlen($next)>72) throw new RuntimeException('Use a new password of 8–72 characters.');
query('UPDATE admins SET password=? WHERE id=?', [password_hash($next,PASSWORD_DEFAULT),$me['id']]);
session_regenerate_id(true);
flash('Admin password changed.');
go('index.php');
