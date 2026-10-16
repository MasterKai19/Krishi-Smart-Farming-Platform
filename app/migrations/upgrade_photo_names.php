<?php
// Run from the command line once after backing up MySQL and uploads.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Command line only'); }
$config = require dirname(__DIR__).'/config.php';
$pdo = new PDO('mysql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['database'].';charset=utf8mb4',
    $config['username'], $config['password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$directory = dirname(__DIR__, 2).'/uploads';
$renamed = [];
$missing = [];
try {
    $pdo->beginTransaction();
    $rows = $pdo->query('SELECT id,storage_name,mime_type,byte_size FROM appointment_photos ORDER BY id FOR UPDATE')->fetchAll();
    $update = $pdo->prepare('UPDATE appointment_photos SET storage_name=? WHERE id=?');
    foreach ($rows as $row) {
        if (!in_array($row['mime_type'], ['image/jpeg','image/png'], true)) throw new RuntimeException('Unexpected MIME type for photo '.$row['id']);
        $new = 'photo'.$row['id'].'.'.($row['mime_type'] === 'image/png' ? 'png' : 'jpg');
        $old = $row['storage_name'];
        if ($old === $new) {
            if (!is_file($directory.'/'.$new)) $missing[] = $new;
            continue;
        }
        if (!preg_match('/^[a-f0-9]{48}\.bin$/D', $old)) throw new RuntimeException('Unexpected old filename for photo '.$row['id']);
        $oldPath = $directory.'/'.$old;
        $newPath = $directory.'/'.$new;
        if (is_file($oldPath)) {
            if (file_exists($newPath)) throw new RuntimeException('Target filename already exists: '.$new);
            if (!rename($oldPath, $newPath)) throw new RuntimeException('Could not rename '.$old);
            $renamed[] = [$newPath,$oldPath];
        } elseif (is_file($newPath) && filesize($newPath) === (int)$row['byte_size']) {
            // A previous interrupted run may have renamed the file before updating MySQL.
        } else {
            $missing[] = $new;
        }
        $update->execute([$new,$row['id']]);
    }
    $pdo->commit();
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    foreach (array_reverse($renamed) as [$newPath,$oldPath]) if (is_file($newPath)) rename($newPath,$oldPath);
    throw $error;
}
echo count($renamed).' files renamed. '.count($rows).' database names checked.' . PHP_EOL;
if ($missing) echo 'Already missing files: '.implode(', ', $missing).PHP_EOL;
