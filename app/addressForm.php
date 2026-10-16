<?php
$current = query(
    'SELECT t.code AS thana_code,t.name AS thana_name,d.code AS district_code,d.name AS district_name,
     v.code AS division_code,v.name AS division_name FROM thanas t
     JOIN districts d ON d.code=t.district_code JOIN divisions v ON v.code=d.division_code
     WHERE t.code=?',
    [$me['thana_code'] ?? '']
)->fetch() ?: [];
$locationSelection = $current + ['detailed_address'=>$me['detailed_address'] ?? ''];
?>
<section class="panel address-panel">
    <span class="eyebrow">YOUR ADDRESS</span>
    <h2><?= $current ? 'Your registered region' : 'Complete your address' ?></h2>
    <?php if ($current): ?>
        <p class="muted"><?=e($current['division_name'].' · '.$current['district_name'].' · '.$current['thana_name'])?></p>
        <p><?=e($me['detailed_address'] ?? '')?></p>
    <?php else: ?>
        <p class="muted">Choose your region to <?= $officer ? 'appear for local farmers' : 'find officers in your district' ?>.</p>
    <?php endif; ?>
    <?php if (!$officer && empty($me['nid_number'])): ?>
        <p class="muted">Your account was created before NID was collected. You can add it here.</p>
    <?php endif; ?>
    <?php if (!$officer): ?><p><strong>Original profile area:</strong> <?= $me['land_katha'] ? e($me['land_katha']).' katha' : 'Not yet provided' ?></p><?php endif; ?>
    <?php if (!$officer): ?><p class="muted">Scheme eligibility now uses your registered plots in <a href="land.php">Land &amp; costs</a>, not this old profile estimate.</p><?php endif; ?>
    <details <?= $current ? '' : 'open' ?>>
        <summary><?= $current ? 'Update address' : 'Add address' ?></summary>
        <form method="post" class="stack">
            <?=csrf()?><input type="hidden" name="action" value="update_address">
            <?php require __DIR__.'/locationFields.php'; ?>
            <?php if (!$officer): ?><label>Original profile estimate (katha)<input name="land_katha" type="number" min="0.01" step="0.01" required value="<?=e($me['land_katha'] ?? '')?>"></label><?php endif; ?>
            <?php if (!$officer && empty($me['nid_number'])): ?>
                <label>NID number (optional for existing accounts)
                    <input name="nid_number" inputmode="numeric" pattern="[0-9]{10}|[0-9]{13}|[0-9]{17}" maxlength="17">
                </label>
            <?php endif; ?>
            <button>Save address</button>
        </form>
    </details>
</section>
