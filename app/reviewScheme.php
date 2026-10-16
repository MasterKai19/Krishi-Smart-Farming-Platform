<?php
$id = filter_var($_POST['application_id'] ?? null, FILTER_VALIDATE_INT);
$decision = $_POST['decision'] ?? '';
$note = trim((string)($_POST['officer_note'] ?? ''));
if (!$id || !in_array($decision, ['approved','rejected'], true)) throw new RuntimeException('Choose an application and decision.');
if (strlen($note) > 500) throw new RuntimeException('Keep your response under 500 characters.');
if ($decision === 'rejected' && $note === '') throw new RuntimeException('Explain why the application was rejected.');
db()->beginTransaction();
$application = query("SELECT a.id,a.status,COALESCE(sl.district_code,ft.district_code) AS farmer_district,ot.district_code AS officer_district FROM scheme_applications a JOIN farmers f ON f.id=a.farmer_id AND f.status='active' LEFT JOIN thanas ft ON ft.code=f.thana_code LEFT JOIN scheme_application_land sl ON sl.application_id=a.id JOIN officers o ON o.id=? JOIN thanas ot ON ot.code=o.thana_code WHERE a.id=? FOR UPDATE", [$me['id'],$id])->fetch();
if (!$application || $application['farmer_district'] !== $application['officer_district']) {
    throw new RuntimeException('This application is outside your district.');
}
if ($application['status'] !== 'pending') throw new RuntimeException('This application has already been reviewed.');
query('UPDATE scheme_applications SET status=?,officer_id=?,officer_note=?,reviewed_at=NOW() WHERE id=?', [$decision,$me['id'],$note ?: null,$id]);
db()->commit();
flash('Scheme application '.$decision.'.');
go('index.php#schemes');
