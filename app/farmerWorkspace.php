<?php
require __DIR__.'/dashboardData.php';
$section=in_array($page,['appointments','schemes'],true)?$page:'overview';
?>
<main class="dashboard farmer-workspace" data-live>
 <div class="dashboard-heading"><div><span class="eyebrow">YOUR FARMER WORKSPACE</span><h1><?= $section==='overview'?'Hello, '.e(explode(' ',$me['name'])[0]):($section==='schemes'?'Government schemes':'Krishi officer appointments') ?></h1><p class="muted"><?= $section==='overview'?'Your farm activity at a glance. Use the top navigation to manage each service.':($section==='schemes'?'Explore available support, apply with a land record, and follow your decisions.':'Request advice, track appointments, and read your officer’s updates.') ?></p></div><span class="date-chip"><?=date('d F Y')?></span></div>
 <div id="live-update" class="flash" hidden role="status">Your information has an update. <a href="index.php<?= $section==='overview'?'':'?page='.e($section) ?>">Refresh to view →</a></div>
 <?php if($section==='appointments'): ?>
  <?php require __DIR__.'/dashboardStats.php'; ?>
  <div class="dashboard-grid"><?php require __DIR__.'/appointments.php'; ?><aside><?php require __DIR__.'/bookingForm.php';require __DIR__.'/notifications.php';require __DIR__.'/addressForm.php';?></aside></div>
 <?php elseif($section==='schemes'): ?>
  <?php require __DIR__.'/schemeFarmerPanel.php'; ?>
 <?php else: ?>
  <?php require __DIR__.'/farmerOverview.php'; ?>
 <?php endif; ?>
</main>
