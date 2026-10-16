<?php require __DIR__.'/dashboardData.php'; ?>
<main class="dashboard" data-live>
    <div class="dashboard-heading">
        <div>
            <span class="eyebrow"><?= $officer ? 'OFFICER WORKSPACE' : 'YOUR FARM, SUPPORTED' ?></span>
            <h1>Hello, <?=e(explode(' ', $me['name'])[0])?><span class="wave"> ✳</span></h1>
            <p class="muted"><?= $officer ? 'A little of your expertise can make a big difference.' : 'Good things are growing. Let’s take care of your next step.' ?></p>
        </div>
        <span class="date-chip"><?=date('l, d F Y')?></span>
    </div>

    <div id="live-update" class="flash" hidden role="status">
        Your dashboard has an update. <a href="index.php">Refresh to view →</a>
    </div>
    <?php require __DIR__.'/dashboardStats.php'; ?>
    <section class="land-entry-banner"><div><span class="eyebrow">LAND & CULTIVATION</span><h2><?= $officer ? 'Review lands and cultivation costs' : 'Every plot, every crop, every expense' ?></h2><p><?= $officer ? 'View district land records and review scheme applications with saved financial details.' : 'Register your lands, track crop seasons and expenses, then select a land when applying for support.' ?></p></div><a class="button" href="land.php">Open land management →</a></section>

    <div class="dashboard-grid">
        <?php require __DIR__.'/appointments.php'; ?>
        <aside>
            <?php require __DIR__.'/addressForm.php'; ?>
            <?php if ($officer): ?>
                <?php require __DIR__.'/officerProfile.php'; ?>
                <div class="help-card">
                    <span>✳</span><h3>Small advice. Lasting impact.</h3>
                    <p>Review the farmer’s concern and preferred time. Add a helpful note so they know what to prepare.</p>
                </div>
            <?php else: ?>
                <?php require __DIR__.'/bookingForm.php'; ?>
                <?php require __DIR__.'/notifications.php'; ?>
            <?php endif; ?>
        </aside>
    </div>
    <?php require __DIR__.($officer ? '/schemeOfficerPanel.php' : '/schemeFarmerPanel.php'); ?>
</main>
