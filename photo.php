<?php
require __DIR__.'/app/lib.php';
require __DIR__.'/app/photo_helpers.php';
try {
    $me = user();
    if (!$me || $_SESSION['role'] === 'admin') { http_response_code(401); exit('Please sign in as the farmer or assigned officer to view this photo.'); }
    $id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    if (!$id) { http_response_code(404); exit('Photo not found.'); }
    $ownerColumn = $_SESSION['role'] === 'officer' ? 'officer_id' : 'farmer_id';
    $photo = query('SELECT p.storage_name,p.mime_type FROM appointment_photos p JOIN appointments a ON a.id=p.appointment_id WHERE p.id=? AND a.'.$ownerColumn.'=?', [$id,$me['id']])->fetch();
    if (!$photo || !valid_photo_storage_name($photo['storage_name'], $photo['mime_type'])) {
        http_response_code(404); exit('Photo not found.');
    }
    $path = __DIR__.'/uploads/'.$photo['storage_name'];
    if (!is_file($path)) { http_response_code(404); exit('Photo not found.'); }
    session_write_close();
    header('Content-Type: '.$photo['mime_type']);
    header('Content-Length: '.filesize($path));
    header('Content-Disposition: inline; filename="'.$photo['storage_name'].'"');
    header("Content-Security-Policy: default-src 'none'; sandbox");
    readfile($path);
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(503);
    exit('Photo temporarily unavailable.');
}
