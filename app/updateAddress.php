<?php
if (!$me || !in_array($_SESSION['role'], ['farmer','officer'], true)) {
    throw new RuntimeException('Sign in as a farmer or officer to update your address.');
}
[$thana, $address] = submitted_location();
$table = $_SESSION['role'] === 'farmer' ? 'farmers' : 'officers';
if ($table === 'farmers') {
    require_once __DIR__.'/schemeHelpers.php';
    $land = submitted_land('land_katha');
}
if ($table === 'farmers' && empty($me['nid_number']) && trim((string)($_POST['nid_number'] ?? '')) !== '') {
    $nid = submitted_nid();
    query('UPDATE farmers SET thana_code=?,detailed_address=?,nid_number=?,land_katha=? WHERE id=?', [$thana,$address,$nid,$land,$me['id']]);
} elseif ($table === 'farmers') {
    query('UPDATE farmers SET thana_code=?,detailed_address=?,land_katha=? WHERE id=?', [$thana,$address,$land,$me['id']]);
} else {
    query("UPDATE $table SET thana_code=?,detailed_address=? WHERE id=?", [$thana,$address,$me['id']]);
}
flash('Your address has been updated.');
go($_SESSION['role']==='farmer' ? 'index.php?page=appointments' : 'index.php');
