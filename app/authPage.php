<?php
$register = $page === 'register';
$officerApplication = $register && ($_GET['role'] ?? '') === 'officer';
$formAction = $register ? ($officerApplication ? 'apply_officer' : 'register') : 'login';
?>
<main class="auth-wrap">
 <div class="auth-copy">
  <span class="eyebrow">YOUR PARTNER IN THE FIELD</span>
  <h1>Good advice.<br>Better harvests.</h1>
  <p>Connect with your Krishi officer, share what’s happening on your farm, and get the guidance you need to grow.</p>
  <div class="quote">“Every thriving farm starts with the right support.”<small>THE KRISHI PROMISE</small></div>
 </div>
 <section class="panel">
  <span class="eyebrow"><?= $register ? 'JOIN THE COMMUNITY' : 'WELCOME BACK' ?></span>
  <h2><?= $register ? ($officerApplication ? 'Apply as a Krishi officer' : 'Create a farmer account') : 'Sign in to Krishi' ?></h2>
  <p class="muted"><?= $register ? 'An admin will review your application before you can sign in.' : 'Your farm support, all in one place.' ?></p>
  <form method="post" class="stack">
   <?=csrf()?><input type="hidden" name="action" value="<?= $formAction ?>">
   <?php if ($register): ?>
    <label>Full name<input name="name" autocomplete="name" required maxlength="100" value="<?=e($_POST['name'] ?? '')?>"></label>
   <?php else: ?>
    <label>Account type<select name="role">
     <option value="farmer" <?=($_GET['role'] ?? '')==='farmer'?'selected':''?>>Farmer</option>
     <option value="officer" <?=($_GET['role'] ?? '')==='officer'?'selected':''?>>Krishi officer</option>
     <option value="admin" <?=($_GET['role'] ?? '')==='admin'?'selected':''?>>Admin</option>
    </select></label>
   <?php endif; ?>
   <label>Email address<input type="email" name="email" autocomplete="email" required maxlength="190" value="<?=e($_POST['email'] ?? '')?>"></label>
   <label>Password<input type="password" name="password" autocomplete="<?= $register ? 'new-password':'current-password' ?>" required <?= $register ? 'minlength="8" maxlength="72"':'' ?>></label>
   <?php if ($register): ?>
    <label>Phone number<input name="phone" autocomplete="tel" required maxlength="30" value="<?=e($_POST['phone'] ?? '')?>"></label>
    <?php if ($officerApplication): ?>
     <label>Office<input name="office" required maxlength="150" value="<?=e($_POST['office'] ?? '')?>"></label>
     <label>Specialization<input name="specialization" required maxlength="150" value="<?=e($_POST['specialization'] ?? '')?>"></label>
    <?php else: ?>
     <label>Village / location<input name="village" required maxlength="150" value="<?=e($_POST['village'] ?? '')?>"></label>
     <label>NID number<input name="nid_number" inputmode="numeric" pattern="[0-9]{10}|[0-9]{13}|[0-9]{17}" required maxlength="17" value="<?=e($_POST['nid_number'] ?? '')?>"></label>
     <label>Land area (katha)<input name="land_katha" type="number" min="0.01" step="0.01" required value="<?=e($_POST['land_katha'] ?? '')?>"></label>
    <?php endif; ?>
    <?php $locationSelection = $_POST; require __DIR__.'/locationFields.php'; ?>
   <?php endif; ?>
   <button><?= $register ? 'Send application' : 'Sign in' ?> →</button>
  </form>
  <p class="auth-switch">
   <?php if ($register): ?>Already have an account? <a href="index.php?page=login">Sign in</a>
   <?php else: ?>New to Krishi? <a href="index.php?page=register">Apply as a farmer</a><?php endif; ?>
   · <a href="index.php?page=register&role=officer">Apply as a Krishi officer</a>
  </p>
 </section>
</main>
