<?php
$crop = field('crop', 100);
$problem = field('problem', 3000);
$date = field('appointment_date', 10);
$time = field('appointment_time', 5);
$officerId = (int)($_POST['officer_id'] ?? 0);

$when = DateTime::createFromFormat('!Y-m-d H:i', $date.' '.$time);
if (!$when || $when->format('Y-m-d H:i') !== $date.' '.$time || $when <= new DateTime()) {
    throw new RuntimeException('Choose a valid date and time in the future.');
}
if ($time < '09:00' || $time > '17:00') {
    throw new RuntimeException('Choose a time between 9:00 AM and 5:00 PM.');
}
if (!query(
    "SELECT o.id FROM farmers f JOIN thanas ft ON ft.code=f.thana_code
     JOIN officers o ON o.id=? AND o.status='active'
     JOIN thanas ot ON ot.code=o.thana_code AND ot.district_code=ft.district_code
     WHERE f.id=?",
    [$officerId,$me['id']]
)->fetch()) {
    throw new RuntimeException('Choose an active Krishi officer in your registered district. Complete your address if needed.');
}

book_with_photo(
    [$me['id'], $officerId, $crop, $problem, $date, $time],
    $_FILES['problem_photo'] ?? null
);
flash('Appointment requested. We will notify you here when your officer responds.');
go('index.php?page=appointments');
