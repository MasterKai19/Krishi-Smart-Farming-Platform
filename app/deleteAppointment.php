<?php
// Farmers may delete only their own declined or cancelled requests.
db()->beginTransaction();
$appointment = query(
    'SELECT id,status FROM appointments WHERE id=? AND farmer_id=? FOR UPDATE',
    [(int)($_POST['id'] ?? 0), $me['id']]
)->fetch();
if (!$appointment || !in_array($appointment['status'], ['declined','cancelled'], true)) {
    throw new RuntimeException('Only your cancelled or declined requests can be deleted.');
}

$id = $appointment['id'];
$photo = query('SELECT storage_name FROM appointment_photos WHERE appointment_id=?', [$id])->fetchColumn();
query('DELETE FROM notifications WHERE appointment_id=?', [$id]);
query('DELETE FROM appointment_photos WHERE appointment_id=?', [$id]);
query('DELETE FROM appointments WHERE id=? AND farmer_id=?', [$id, $me['id']]);
db()->commit();

if ($photo && preg_match('/^(?:photo[1-9][0-9]*\.(?:jpg|png)|[a-f0-9]{48}\.bin)$/D', $photo)) {
    $path = dirname(__DIR__).'/uploads/'.$photo;
    if (is_file($path) && !@unlink($path)) error_log('Krishi: orphan photo cleanup required: '.$photo);
}
flash('Closed request and its notifications/photo have been deleted.');
go('index.php?page=appointments');
