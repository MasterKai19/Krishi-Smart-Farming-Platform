<?php
require __DIR__.'/app/lib.php';
$error = '';
$technical = '';
$locked = file_exists(__DIR__.'/installed.lock');
if (!$locked && $_SERVER['REQUEST_METHOD'] === 'POST') {
 try {
  verify_csrf();
  $name = field('name',100);
  $email = field('email',190);
  $phone = field('phone',30);
  $password = $_POST['password'] ?? '';
  if (!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter a valid email address.');
  if (strlen($password)<8 || strlen($password)>72) throw new RuntimeException('Use a password of 8–72 characters.');
  if (!preg_match('/^[a-zA-Z0-9_]+$/',$config['database'])) throw new RuntimeException('Invalid database name in app/config.php.');
  $pdo = new PDO('mysql:host='.$config['host'].';port='.$config['port'].';charset=utf8mb4',$config['username'],$config['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
  $pdo->exec('CREATE DATABASE IF NOT EXISTS `'.$config['database'].'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
  foreach (explode(';',file_get_contents(__DIR__.'/app/database/schema.sql')) as $sql) if(trim($sql)) db()->exec($sql);
  foreach (explode(';',file_get_contents(__DIR__.'/app/database/locations.sql')) as $sql) if(trim($sql)) db()->exec($sql);
  foreach (explode(';',file_get_contents(__DIR__.'/app/database/views.sql')) as $sql) if(trim($sql)) db()->exec($sql);
  db()->exec(file_get_contents(__DIR__.'/app/database/land_schema.sql'));
  foreach (explode(';',file_get_contents(__DIR__.'/app/database/scheme_examples.sql')) as $sql) if(trim($sql)) db()->exec($sql);
  foreach (['admins','officers','farmers'] as $table) {
   if ((int)query("SELECT COUNT(*) FROM $table")->fetchColumn() > 0) throw new RuntimeException('Existing accounts found. Use the admin migration for an existing Krishi site.');
  }
  query('INSERT INTO admins (name,email,password,phone) VALUES (?,?,?,?)',[$name,$email,password_hash($password,PASSWORD_DEFAULT),$phone]);
  file_put_contents(__DIR__.'/installed.lock',date(DATE_ATOM));
  flash('Krishi is ready. Sign in as admin to review new accounts.');
  go('index.php?page=login&role=admin');
 } catch (PDOException $ex) { error_log($ex->getMessage()); $error = database_error($ex); $technical = $ex->getMessage(); }
 catch (RuntimeException $ex) { $error = $ex->getMessage(); }
}
page_start('Local setup'); ?>
<main class="auth-wrap"><div class="auth-copy"><span class="eyebrow">WELCOME TO KRISHI</span><h1>Plant the seeds.<br>Set up your workspace.</h1><p>Start Apache and MySQL in XAMPP, then create the first admin account. Farmers and officers can apply after setup.</p></div><section class="panel"><span class="eyebrow">ONE-TIME SETUP</span><h2><?= $locked ? 'Already set up' : 'Create your admin' ?></h2>
<?php if ($locked): ?><p>Krishi is installed and ready to use.</p><a class="button" href="index.php?page=login&role=admin">Go to admin sign in →</a>
<?php else: ?><?php if($error): ?><p class="error" role="alert"><?=e($error)?></p><?php endif; ?><?php if($technical): ?><details><summary>Technical error</summary><pre><?=e($technical)?></pre></details><?php endif; ?>
<form method="post" class="stack"><?=csrf()?><label>Full name<input name="name" maxlength="100" required></label><label>Email<input name="email" type="email" maxlength="190" required></label><label>Password<input name="password" type="password" minlength="8" maxlength="72" required autocomplete="new-password"></label><label>Phone<input name="phone" maxlength="30" required></label><button>Set up Krishi →</button></form>
<?php endif; ?></section></main><?php page_end(); ?>
