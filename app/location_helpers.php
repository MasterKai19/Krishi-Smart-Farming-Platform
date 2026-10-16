<?php
// The selected thana determines its district and division through foreign keys.
function submitted_location(): array {
    $division = trim((string)($_POST['division_code'] ?? ''));
    $district = trim((string)($_POST['district_code'] ?? ''));
    $thana = trim((string)($_POST['thana_code'] ?? ''));
    $address = trim((string)($_POST['detailed_address'] ?? ''));
    if ($address === '' || strlen($address) > 750) {
        throw new RuntimeException('Enter your specific address (road, holding, village or area; maximum 255 characters).');
    }
    $valid = query(
        'SELECT t.code FROM thanas t JOIN districts d ON d.code=t.district_code
         WHERE t.code=? AND d.code=? AND d.division_code=?',
        [$thana, $district, $division]
    )->fetch();
    if (!$valid) throw new RuntimeException('Choose a valid division, district and thana/upazila.');
    return [$thana, $address];
}

function submitted_nid(): string {
    $nid = trim((string)($_POST['nid_number'] ?? ''));
    if (!preg_match('/^(?:[0-9]{10}|[0-9]{13}|[0-9]{17})$/', $nid)) {
        throw new RuntimeException('Enter a valid 10, 13 or 17 digit NID number.');
    }
    return $nid;
}
