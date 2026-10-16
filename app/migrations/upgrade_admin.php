<?php
// One-time CLI migration for an existing Krishi database. Stored in the protected app/migrations directory.
if (PHP_SAPI !== 'cli') exit(1);
$config = require dirname(__DIR__).'/config.php';
[$script, $name, $email, $phone] = array_pad($argv, 4, null);
if (!$name || !$email || !$phone || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php app/migrations/upgrade_admin.php <admin-name> <admin-email> <admin-phone>\n");
    exit(1);
}
$db = new PDO(
    'mysql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['database'].';charset=utf8mb4',
    $config['username'], $config['password'],
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
);
$db->exec("CREATE TABLE IF NOT EXISTS admins (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 phone VARCHAR(30) NOT NULL,
 admin_scope VARCHAR(100) NOT NULL DEFAULT 'account_review',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
foreach (['farmers','officers'] as $table) {
    $check = $db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?');
    $check->execute([$config['database'],$table,'status']);
    if (!$check->fetchColumn()) {
        // Existing accounts remain active; new accounts default to pending after the second ALTER.
        $db->exec("ALTER TABLE $table ADD COLUMN status ENUM('pending','active','inactive') NOT NULL DEFAULT 'active'");
        $db->exec("ALTER TABLE $table MODIFY COLUMN status ENUM('pending','active','inactive') NOT NULL DEFAULT 'pending'");
    }
}
$existing = (int)$db->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if ($existing) {
    echo "Schema ready; an admin already exists. No account changed.\n";
    exit;
}
$password = bin2hex(random_bytes(16));
$insert = $db->prepare('INSERT INTO admins (name,email,password,phone) VALUES (?,?,?,?)');
$insert->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),$phone]);
echo "Admin created for $email\n";
echo "One-time initial password: $password\n";
