<?php
declare(strict_types=1);

session_name('KRISHISESSID');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

$config = require __DIR__.'/config.php';
date_default_timezone_set($config['timezone']);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function database_error(PDOException $error): string {
    global $config;
    $code = (int)($error->errorInfo[1] ?? 0);
    $target = $config['host'].':'.$config['port'];

    if (str_contains($error->getMessage(), 'could not find driver')) {
        return 'PHP is missing the PDO MySQL driver. Enable extension=pdo_mysql in XAMPP php/php.ini, then restart Apache.';
    }
    if (in_array($code, [2002,2003,2006], true)) {
        return 'Cannot connect to MySQL at '.$target.'. Start MySQL in XAMPP and confirm its MySQL port is '.$config['port'].'.';
    }
    if (in_array($code, [1045,1698], true)) {
        return 'MySQL rejected the username or password. Update app/config.php to match your MySQL account.';
    }
    if (in_array($code, [1044,1142], true)) {
        return 'The MySQL account does not have permission to create or use the Krishi database.';
    }
    if ($code === 1049) return 'The Krishi database does not exist yet. Run install.php to create it.';
    if (in_array($code, [1054,1146,1005,1215], true)) {
        return 'The database tables do not match Krishi’s schema. Preserve the existing data and review the setup.';
    }
    return 'MySQL setup failed (SQLSTATE '.$error->getCode().', driver code '.$code.').';
}

function db(): PDO {
    global $config;
    static $connection;
    if (!$connection) {
        $connection = new PDO(
            'mysql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['database'].';charset=utf8mb4',
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    return $connection;
}

function query(string $sql, array $params = []): PDOStatement {
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement;
}

function csrf(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">';
}

function verify_csrf(): void {
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        throw new RuntimeException('Your form expired. Refresh the page and try again.');
    }
}

function go(string $url): void {
    header('Location: '.$url);
    exit;
}

function flash(string $message): void { $_SESSION['flash'] = $message; }

function user(): ?array {
    if (empty($_SESSION['uid']) || !in_array($_SESSION['role'] ?? '', ['farmer','officer','admin'], true)) return null;
    $table = ['farmer'=>'farmers', 'officer'=>'officers', 'admin'=>'admins'][$_SESSION['role']];
    $account = query("SELECT * FROM $table WHERE id=?", [$_SESSION['uid']])->fetch() ?: null;
    if (!$account || ($_SESSION['role'] !== 'admin' && $account['status'] !== 'active')) {
        unset($_SESSION['uid'], $_SESSION['role']);
        return null;
    }
    return $account;
}

function field(string $key, int $max = 150): string {
    $value = trim($_POST[$key] ?? '');
    if ($value === '' || strlen($value) > $max) {
        throw new RuntimeException('Please provide a valid '.str_replace('_', ' ', $key).' (maximum '.$max.' characters).');
    }
    return $value;
}

function page_start(string $title, ?array $me = null): void { ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#215c3c">
    <title><?=e($title)?> · Krishi</title>
    <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/photos.css">
    <link rel="stylesheet" href="assets/land.css">
    <script defer src="assets/land.js"></script>
    <script defer src="assets/app.js"></script>
    <script defer src="assets/photos.js"></script>
    <script defer src="assets/locations.js"></script>
</head>
<body>
<header>
    <a class="brand" href="index.php"><span class="brand-icon">✳</span> Krishi<span class="brand-sub">GROW BETTER, TOGETHER</span></a>
    <nav>
        <?php if ($me): ?>
            <?php $section=basename($_SERVER['SCRIPT_NAME'])==='land.php'?'land':($_GET['page']??'home'); ?>
            <a class="workspace-nav" <?=!in_array($section,['land','schemes','appointments'])?'aria-current="page"':''?> href="index.php">Dashboard</a>
            <a class="workspace-nav" <?=$section==='land'?'aria-current="page"':''?> href="land.php">Land & costs</a>
            <?php if ($_SESSION['role']==='farmer'): ?>
            <a class="workspace-nav" <?=$section==='schemes'?'aria-current="page"':''?> href="index.php?page=schemes">Government schemes</a>
            <a class="workspace-nav" <?=$section==='appointments'?'aria-current="page"':''?> href="index.php?page=appointments">Krishi officer appointments</a>
            <?php endif; ?>
            <span class="identity"><?=e($me['name'])?> <small><?=e(ucfirst($_SESSION['role']))?></small></span>
            <form action="index.php" method="post">
                <?=csrf()?><input type="hidden" name="action" value="logout">
                <button class="text-button">Sign out ↗</button>
            </form>
        <?php else: ?>
            <span class="nav-note">A little guidance. A better harvest.</span>
            <?php if (($_GET['page'] ?? 'home') !== 'home'): ?>
                <a href="index.php?page=login">Sign in ↗</a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>
</header>
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="flash" role="status"><?=e($_SESSION['flash'])?></div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
<?php }

function page_end(): void { ?>
<footer>
    <a class="brand" href="index.php">✳ Krishi</a>
    <span>Connecting farmers with agricultural expertise.</span>
    <small>Cultivating a better tomorrow.</small>
</footer>
</body>
</html>
<?php }
