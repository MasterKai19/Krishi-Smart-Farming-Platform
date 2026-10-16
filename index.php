<?php
// Start here. All forms submit to this file; each action has its own file in app/.
require __DIR__.'/app/lib.php';
require __DIR__.'/app/photo_helpers.php';
require __DIR__.'/app/location_helpers.php';
require __DIR__.'/app/landHelpers.php';

try {
    $me = user();
    query('SELECT id FROM admins LIMIT 1');
} catch (PDOException $error) {
    page_start('Setup needed');
    ?>
    <main class="auth-wrap"><section class="panel">
        <h1>Welcome to Krishi.</h1>
        <p>Start Apache and MySQL in XAMPP, check app/config.php, then run setup.</p>
        <a class="button" href="install.php">Set up Krishi →</a>
    </section></main>
    <?php
    page_end();
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__.'/app/actions.php';
}
if (($_GET['page'] ?? '') === 'updates') {
    require __DIR__.'/app/dashboardUpdates.php';
}

$page = $_GET['page'] ?? 'home';
$titles=['appointments'=>'Krishi officer appointments','schemes'=>'Government schemes'];
page_start($me ? ($titles[$page] ?? 'Dashboard') : 'Grow with expert guidance', $me);
if ($error) echo '<div class="error global-error" role="alert">'.e($error).'</div>';

if (!$me && ($page === 'login' || $page === 'register')) {
    require __DIR__.'/app/authPage.php';
} elseif (!$me) {
    require __DIR__.'/app/homePage.php';
} elseif ($_SESSION['role'] === 'admin') {
    require __DIR__.'/app/adminDashboard.php';
} elseif ($_SESSION['role'] === 'farmer') {
    require __DIR__.'/app/farmerWorkspace.php';
} else {
    require __DIR__.'/app/dashboard.php';
}

page_end();
