<?php
// Run once from the command line after backing up the database: php upgrade_schemes.php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Command line only'); }
$config = require dirname(__DIR__).'/config.php';
$pdo = new PDO('mysql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['database'].';charset=utf8mb4', $config['username'], $config['password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$column = $pdo->query("SHOW COLUMNS FROM farmers LIKE 'land_katha'")->fetch();
if (!$column) $pdo->exec('ALTER TABLE farmers ADD land_katha DECIMAL(9,2) NULL AFTER detailed_address');
foreach (explode(';', file_get_contents(dirname(__DIR__).'/database/schema.sql')) as $sql) {
    if (str_starts_with(trim($sql), 'CREATE TABLE IF NOT EXISTS schemes') || str_starts_with(trim($sql), 'CREATE TABLE IF NOT EXISTS scheme_applications')) $pdo->exec($sql);
}
foreach (explode(';', file_get_contents(dirname(__DIR__).'/database/scheme_examples.sql')) as $sql) if (trim($sql)) $pdo->exec($sql);
echo "Scheme tables and examples ready.\n";
