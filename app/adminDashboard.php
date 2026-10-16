<?php require __DIR__.'/adminData.php'; ?>
<main class="dashboard">
    <div class="dashboard-heading">
        <div>
            <span class="eyebrow">ADMIN WORKSPACE</span>
            <h1>Hello, <?=e(explode(' ', $me['name'])[0])?><span class="wave"> ✳</span></h1>
            <p class="muted">Review new applications and manage account access.</p>
        </div>
        <span class="date-chip"><?=date('l, d F Y')?></span>
    </div>

    <section class="stats">
        <div><span>Pending applications</span><strong><?=$pendingFarmers+$pendingOfficers?></strong><small>Accounts awaiting your review</small></div>
        <div><span>Farmer applications</span><strong><?=$pendingFarmers?></strong><small>New farmer requests</small></div>
        <div><span>Officer applications</span><strong><?=$pendingOfficers?></strong><small>New Krishi officer requests</small></div>
        <div><span>Total accounts</span><strong><?=count($farmers)+count($officers)?></strong><small>Farmers and officers</small></div>
    </section>

    <div class="admin-grid">
        <?php foreach (['farmer'=>$farmers, 'officer'=>$officers] as $accountRole => $accounts): ?>
            <section class="panel">
                <div class="section-heading">
                    <h2><?= $accountRole === 'farmer' ? 'Farmer accounts' : 'Krishi officer accounts' ?></h2>
                    <span><?=count($accounts)?></span>
                </div>
                <?php if (!$accounts): ?><p class="muted">No accounts yet.</p><?php endif; ?>
                <?php foreach ($accounts as $account) require __DIR__.'/adminAccountCard.php'; ?>
            </section>
        <?php endforeach; ?>
    </div>
    <?php require __DIR__.'/adminPasswordForm.php'; ?>
    <?php require __DIR__.'/adminSchemePanel.php'; ?>
</main>
