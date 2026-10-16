<?php
require_once __DIR__.'/accountAdmin.php';
[$role, $table, $id] = admin_account_target();
$changed = query("UPDATE $table SET status='inactive' WHERE id=? AND status IN ('pending','active')", [$id])->rowCount();
if (!$changed) throw new RuntimeException('This account is already inactive or has been deleted.');
flash(ucfirst($role).' account set to inactive.');
go('index.php');
