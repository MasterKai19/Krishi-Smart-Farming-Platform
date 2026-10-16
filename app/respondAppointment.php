<?php
$status = $_POST['status'] ?? '';
$note = trim($_POST['officer_note'] ?? '');
if (!in_array($status, ['accepted','declined','completed'], true)) {
    throw new RuntimeException('Invalid appointment action.');
}
if (strlen($note) > 1000) throw new RuntimeException('Please keep your note under 1,000 characters.');

db()->beginTransaction();
// Lock this officer's decisions so the same time slot cannot be accepted twice.
query('SELECT id FROM officers WHERE id=? FOR UPDATE', [$me['id']]);
$appointment = query(
    'SELECT * FROM appointments WHERE id=? AND officer_id=? FOR UPDATE',
    [(int)($_POST['id'] ?? 0), $me['id']]
)->fetch();
if (!$appointment || ($status === 'completed'
    ? $appointment['status'] !== 'accepted'
    : $appointment['status'] !== 'pending')) {
    throw new RuntimeException('This appointment has already changed. Refresh your dashboard.');
}

if ($status === 'accepted') {
    if (strtotime($appointment['appointment_date'].' '.$appointment['appointment_time']) <= time()) {
        throw new RuntimeException('This appointment time has passed. Decline it and ask the farmer to choose a new time.');
    }
    $conflict = query(
        "SELECT id FROM appointments WHERE officer_id=? AND appointment_date=?
         AND appointment_time=? AND status='accepted'",
        [$me['id'], $appointment['appointment_date'], $appointment['appointment_time']]
    )->fetch();
    if ($conflict) throw new RuntimeException('You already have an accepted appointment at this time.');
}

query('UPDATE appointments SET status=?,officer_note=? WHERE id=?', [$status, $note, $appointment['id']]);
$message = $me['name'].' '.$status.' your '.$appointment['crop'].' appointment for '
    .date('d M Y', strtotime($appointment['appointment_date'])).' at '
    .date('g:i A', strtotime($appointment['appointment_time'])).'.';
query('INSERT INTO notifications (appointment_id,message) VALUES (?,?)', [$appointment['id'], $message]);
db()->commit();
flash('Appointment updated and the farmer has been notified.');
go('index.php');
