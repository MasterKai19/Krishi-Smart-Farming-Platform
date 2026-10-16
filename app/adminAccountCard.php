<?php // $account and $accountRole are set in adminDashboard.php. ?>
<article class="admin-account">
    <div class="card-top">
        <div>
            <h3><?=e($account['name'])?></h3>
            <small>#<?=e($account['id'])?> · <?=e($account['email'])?></small>
        </div>
        <span class="badge <?=e($account['status'])?>"><?=ucfirst($account['status'])?></span>
    </div>
    <p><strong>Phone:</strong> <?=e($account['phone'])?></p>
    <?php if ($accountRole === 'farmer'): ?>
        <p><strong>Village:</strong> <?=e($account['village'])?></p>
        <p><strong>NID:</strong> <?=e($account['nid_number'] ?: 'Not provided (older account)')?></p>
        <p><strong>Land:</strong> <?= $account['land_katha'] ? e($account['land_katha']).' katha' : 'Not provided' ?></p>
    <?php else: ?>
        <p><strong>Office:</strong> <?=e($account['office'])?> · <strong>Specialization:</strong> <?=e($account['specialization'])?></p>
    <?php endif; ?>
    <p><strong>Address:</strong> <?= $account['thana_name'] ? e($account['detailed_address'].' · '.$account['thana_name'].' · '.$account['district_name'].' · '.$account['division_name']) : 'Not provided (older account)' ?></p>
    <p><strong>Appointments:</strong> <?=e($account['appointment_count'])?></p>

    <div class="admin-actions">
        <?php
        $available = [
            'pending' => ['approve_account'=>'Approve', 'deactivate_account'=>'Set inactive'],
            'active' => ['deactivate_account'=>'Set inactive'],
            'inactive' => ['reactivate_account'=>'Reactivate'],
        ][$account['status']];
        foreach ($available as $action => $label):
        ?>
            <form method="post">
                <?=csrf()?><input type="hidden" name="action" value="<?=e($action)?>">
                <input type="hidden" name="role" value="<?=e($accountRole)?>">
                <input type="hidden" name="id" value="<?=e($account['id'])?>">
                <button class="<?= $action === 'deactivate_account' ? 'secondary' : '' ?>"><?=e($label)?></button>
            </form>
        <?php endforeach; ?>
        <form method="post" data-confirm="Permanently delete this account, its appointments, scheme applications, notifications and photos? This cannot be undone.">
            <?=csrf()?><input type="hidden" name="action" value="delete_account">
            <input type="hidden" name="role" value="<?=e($accountRole)?>">
            <input type="hidden" name="id" value="<?=e($account['id'])?>">
            <button class="text-button cancel">Delete account</button>
        </form>
    </div>
</article>
