<?php
// Read only the signed-in user's appointments and totals for dashboard.php.
$officer = $_SESSION['role'] === 'officer';
$ownerColumn = $officer ? 'officer_id' : 'farmer_id';

$appointments = query(
    "SELECT * FROM v_appointment_details WHERE $ownerColumn=? ORDER BY created_at DESC,id DESC",
    [$me['id']]
)->fetchAll();

$photoRows = query(
    "SELECT p.id,p.appointment_id,p.storage_name,p.mime_type FROM appointment_photos p
     JOIN appointments a ON a.id=p.appointment_id WHERE a.$ownerColumn=?",
    [$me['id']]
)->fetchAll();
$photosByAppointment = [];
foreach ($photoRows as $photoRow) {
    $photoRow['available'] = valid_photo_storage_name($photoRow['storage_name'], $photoRow['mime_type'])
        && is_file(dirname(__DIR__).'/uploads/'.$photoRow['storage_name']);
    $photosByAppointment[$photoRow['appointment_id']] = $photoRow;
}

$counts = array_fill_keys(['pending','accepted','completed','declined','cancelled'], 0);
if ($officer) {
    $workload = query('SELECT * FROM v_officer_workload WHERE officer_id=?', [$me['id']])->fetch();
    foreach ($counts as $status => $unused) $counts[$status] = (int)($workload[$status] ?? 0);
} else {
    $totals = query(
        'SELECT status,COUNT(*) AS total FROM appointments WHERE farmer_id=? GROUP BY status',
        [$me['id']]
    )->fetchAll();
    foreach ($totals as $row) $counts[$row['status']] = (int)$row['total'];
}
$totalAppointments = array_sum($counts);

$notifications = $officer ? [] : query(
    'SELECT * FROM v_farmer_notifications WHERE farmer_id=? ORDER BY created_at DESC,id DESC LIMIT 20',
    [$me['id']]
)->fetchAll();
$unread = $officer ? 0 : (int)query(
    'SELECT COUNT(*) FROM v_farmer_notifications WHERE farmer_id=? AND is_read=0',
    [$me['id']]
)->fetchColumn();

// The browser compares this value with dashboardUpdates.php every 15 seconds.
$snapshot = query(
    "SELECT id,status,updated_at FROM appointments WHERE $ownerColumn=? ORDER BY id",
    [$me['id']]
)->fetchAll();
if ($officer) {
    $schemeSnapshot = query("SELECT a.id,a.status,a.reviewed_at FROM scheme_applications a JOIN farmers f ON f.id=a.farmer_id LEFT JOIN thanas ft ON ft.code=f.thana_code LEFT JOIN scheme_application_land sl ON sl.application_id=a.id JOIN thanas ot ON ot.district_code=COALESCE(sl.district_code,ft.district_code) JOIN officers o ON o.thana_code=ot.code WHERE o.id=? ORDER BY a.id", [$me['id']])->fetchAll();
} else {
    $schemeSnapshot = query('SELECT id,status,reviewed_at FROM scheme_applications WHERE farmer_id=? ORDER BY id', [$me['id']])->fetchAll();
}
$version = hash('sha256', json_encode([$snapshot,$schemeSnapshot])).':'.$unread;
echo '<script type="application/json" id="initial-version">'.json_encode($version).'</script>';
