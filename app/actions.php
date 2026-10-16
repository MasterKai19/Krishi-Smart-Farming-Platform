<?php
// One form action per file, like the small CRUD example.
// The second value is the role required to run that action.
$actions = [
    'logout'                => ['logout.php', null],
    'login'                 => ['login.php', null],
    'register'              => ['register.php', null],
    'apply_officer'         => ['applyOfficer.php', null],
    'update_address'        => ['updateAddress.php', null],
    'book'                  => ['bookAppointment.php', 'farmer'],
    'cancel'                => ['cancelAppointment.php', 'farmer'],
    'delete'                => ['deleteAppointment.php', 'farmer'],
    'read'                  => ['readNotifications.php', 'farmer'],
    'respond'               => ['respondAppointment.php', 'officer'],
    'approve_account'       => ['approveAccount.php', 'admin'],
    'deactivate_account'    => ['deactivateAccount.php', 'admin'],
    'reactivate_account'    => ['reactivateAccount.php', 'admin'],
    'delete_account'        => ['deleteAccount.php', 'admin'],
    'change_admin_password' => ['changeAdminPassword.php', 'admin'],
    'create_scheme'         => ['createScheme.php', 'admin'],
    'update_scheme'         => ['updateScheme.php', 'admin'],
    'apply_scheme'          => ['applyScheme.php', 'farmer'],
    'review_scheme'         => ['reviewScheme.php', 'officer'],
];

try {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if (!isset($actions[$action])) throw new RuntimeException('That action is unavailable for this account.');

    [$file, $requiredRole] = $actions[$action];
    if ($requiredRole && (!$me || $_SESSION['role'] !== $requiredRole)) {
        throw new RuntimeException('That action is unavailable for this account.');
    }
    require __DIR__.'/'.$file;
} catch (PDOException $error) {
    if (db()->inTransaction()) db()->rollBack();
    error_log($error->getMessage());
    $message = $error->getCode() === '23000' && ($_POST['action'] ?? '') === 'apply_scheme'
        ? 'You have already applied for this scheme.'
        : ($error->getCode() === '23000'
        ? 'That email is already registered. Try signing in.'
        : 'We could not save your changes. Please try again.');
    $error = $message;
} catch (RuntimeException $exception) {
    if (db()->inTransaction()) db()->rollBack();
    $error = $exception->getMessage();
}
