<?php
// Run once from the command line after backing up the existing database.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Command line only.'); }
ini_set('session.save_path', sys_get_temp_dir());
require dirname(__DIR__).'/lib.php';

function has_column(string $table, string $column): bool {
    return (bool)query(
        'SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?',
        [$table, $column]
    )->fetchColumn();
}
function has_constraint(string $table, string $name): bool {
    return (bool)query(
        'SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND CONSTRAINT_NAME=?',
        [$table, $name]
    )->fetchColumn();
}

$schema = explode(';', file_get_contents(dirname(__DIR__).'/database/schema.sql'));
foreach (array_slice($schema, 0, 3) as $sql) if (trim($sql)) db()->exec($sql);
foreach (explode(';', file_get_contents(dirname(__DIR__).'/database/locations.sql')) as $sql) if (trim($sql)) db()->exec($sql);

$columns = [
    'farmers' => [
        'nid_number' => 'VARCHAR(17) NULL',
        'thana_code' => 'CHAR(8) NULL',
        'detailed_address' => 'VARCHAR(255) NULL',
    ],
    'officers' => [
        'thana_code' => 'CHAR(8) NULL',
        'detailed_address' => 'VARCHAR(255) NULL',
    ],
];
foreach ($columns as $table => $items) {
    foreach ($items as $column => $type) {
        if (!has_column($table, $column)) db()->exec("ALTER TABLE $table ADD COLUMN $column $type");
    }
}
if (!has_constraint('farmers', 'uq_farmer_nid')) {
    db()->exec('ALTER TABLE farmers ADD CONSTRAINT uq_farmer_nid UNIQUE (nid_number)');
}
foreach (['farmers'=>'fk_farmer_thana', 'officers'=>'fk_officer_thana'] as $table => $name) {
    if (!has_constraint($table, $name)) {
        db()->exec("ALTER TABLE $table ADD CONSTRAINT $name FOREIGN KEY (thana_code) REFERENCES thanas(code)");
    }
}
foreach (explode(';', file_get_contents(dirname(__DIR__).'/database/views.sql')) as $sql) if (trim($sql)) db()->exec($sql);
echo "Location migration complete. Existing accounts are preserved; they can add their address on the dashboard.\n";
