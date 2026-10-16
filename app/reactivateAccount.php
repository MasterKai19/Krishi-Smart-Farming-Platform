<?php
require_once __DIR__.'/accountAdmin.php';
[$role, $table, $id] = admin_account_target();
$changed = query("UPDATE $table SET status='active' WHERE id=? AND status='inactive'", [$id])->rowCount();
if (!$changed) throw new RuntimeException('This account is no longer inactive. Refresh the dashboard.');
flash(ucfirst($role).' account reactivated.');
go('index.php');
