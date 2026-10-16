<?php
require_once __DIR__.'/accountAdmin.php';
[$role, $table, $id] = admin_account_target();
$changed = query("UPDATE $table SET status='active' WHERE id=? AND status='pending'", [$id])->rowCount();
if (!$changed) throw new RuntimeException('This application is no longer pending. Refresh the dashboard.');
flash(ucfirst($role).' account approved.');
go('index.php');
