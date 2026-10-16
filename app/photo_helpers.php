<?php
declare(strict_types=1);

const PHOTO_MAX_BYTES = 5 * 1024 * 1024;

function photo_storage_name(int $id, string $mime): string {
    return 'photo'.$id.'.'.($mime === 'image/png' ? 'png' : 'jpg');
}

function valid_photo_storage_name(string $name, string $mime): bool {
    if (!in_array($mime, ['image/jpeg','image/png'], true)) return false;
    if (preg_match('/^photo([1-9][0-9]*)\.(?:jpg|png)$/D', $name, $match)) {
        return $name === photo_storage_name((int)$match[1], $mime);
    }
    return preg_match('/^[a-f0-9]{48}\.bin$/D', $name) === 1;
}

function validate_problem_photo(?array $file): ?array {
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (!is_int($file['error'] ?? null) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The photo could not be uploaded. Choose a JPG or PNG up to 5 MB and try again.');
    }
    $tmp = $file['tmp_name'] ?? '';
    if (!is_string($tmp) || !is_uploaded_file($tmp)) throw new RuntimeException('Please select a valid photo.');
    $size = filesize($tmp);
    if ($size === false || $size < 1 || $size > PHOTO_MAX_BYTES) throw new RuntimeException('Your photo must be no larger than 5 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $dimensions = @getimagesize($tmp);
    if (!in_array($mime, ['image/jpeg', 'image/png'], true) || !$dimensions || ($dimensions['mime'] ?? '') !== $mime) {
        throw new RuntimeException('Only genuine JPG and PNG photos are allowed.');
    }
    if ($dimensions[0] > 12000 || $dimensions[1] > 12000 || $dimensions[0] * $dimensions[1] > 40000000) {
        throw new RuntimeException('This photo is too large in dimensions. Resize it to under 40 megapixels and try again.');
    }
    return ['tmp' => $tmp, 'mime' => $mime, 'size' => $size];
}

function book_with_photo(array $appointment, ?array $file): void {
    $photo = validate_problem_photo($file);
    $savedPath = null;
    try {
        db()->beginTransaction();
        query('INSERT INTO appointments (farmer_id,officer_id,crop,problem,appointment_date,appointment_time) VALUES (?,?,?,?,?,?)', $appointment);
        $appointmentId = db()->lastInsertId();
        if ($photo) {
            $directory = dirname(__DIR__).'/uploads';
            if (!is_dir($directory) || !is_writable($directory)) throw new RuntimeException('Photo storage is unavailable. Please try again later or submit without a photo.');
            // Reserve the photo's database ID first; that ID becomes its readable filename.
            query('INSERT INTO appointment_photos (appointment_id,storage_name,mime_type,byte_size) VALUES (?,?,?,?)',
                [$appointmentId,'pending-appointment-'.$appointmentId,$photo['mime'],$photo['size']]);
            $photoId = (int)db()->lastInsertId();
            $storageName = photo_storage_name($photoId, $photo['mime']);
            $savedPath = $directory.'/'.$storageName;
            if (is_file($savedPath)) throw new RuntimeException('That photo name is already in use. Please try again.');
            if (!move_uploaded_file($photo['tmp'], $savedPath)) throw new RuntimeException('The photo could not be saved. Please try again.');
            query('UPDATE appointment_photos SET storage_name=? WHERE id=?', [$storageName,$photoId]);
        }
        db()->commit();
    } catch (Throwable $error) {
        if (db()->inTransaction()) db()->rollBack();
        if ($savedPath && is_file($savedPath)) unlink($savedPath);
        throw $error;
    }
}
