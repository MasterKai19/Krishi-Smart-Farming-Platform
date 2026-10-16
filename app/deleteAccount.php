<?php
require_once __DIR__.'/accountAdmin.php';
[$role, $table, $id] = admin_account_target();
$ownerColumn = $role === 'farmer' ? 'farmer_id' : 'officer_id';
$photoNames = [];
db()->beginTransaction();
$account = query("SELECT id FROM $table WHERE id=? FOR UPDATE", [$id])->fetch();
if (!$account) throw new RuntimeException('This account has already been deleted.');
if ($role === 'farmer' && query('SELECT id FROM lands WHERE farmer_id=? LIMIT 1', [$id])->fetch()) {
    throw new RuntimeException('This farmer has land and financial history. Deactivate the account to preserve these records.');
}
$appointments = query("SELECT id FROM appointments WHERE $ownerColumn=? FOR UPDATE", [$id])->fetchAll();
foreach ($appointments as $appointment) {
    $appointmentId = $appointment['id'];
    $photo = query('SELECT storage_name FROM appointment_photos WHERE appointment_id=?', [$appointmentId])->fetchColumn();
    if ($photo) $photoNames[] = $photo;
    query('DELETE FROM notifications WHERE appointment_id=?', [$appointmentId]);
    query('DELETE FROM appointment_photos WHERE appointment_id=?', [$appointmentId]);
    query('DELETE FROM appointments WHERE id=?', [$appointmentId]);
}
query("DELETE FROM $table WHERE id=?", [$id]);
db()->commit();
foreach ($photoNames as $photo) {
    if (preg_match('/^(?:photo[1-9][0-9]*\.(?:jpg|png)|[a-f0-9]{48}\.bin)$/D', $photo)) {
        $path = dirname(__DIR__).'/uploads/'.$photo;
        if (is_file($path) && !@unlink($path)) error_log('Krishi: orphan photo cleanup required: '.$photo);
    }
}
flash(ucfirst($role).' account and its appointments deleted.');
go('index.php');
