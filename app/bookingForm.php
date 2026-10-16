<?php
// District comes from the farmer's thana; only officers in that district appear.
$farmerDistrict = query('SELECT district_code FROM thanas WHERE code=?', [$me['thana_code'] ?? ''])->fetchColumn();
$officers = $farmerDistrict ? query(
    "SELECT o.id,o.name,o.office,o.specialization,
        (SELECT COUNT(*) FROM appointments p
         WHERE p.officer_id=o.id AND p.status='pending') AS pending_requests,
        NOT EXISTS (SELECT 1 FROM appointments h
                    WHERE h.officer_id=o.id AND h.farmer_id=?
                    AND h.status IN ('accepted','completed')) AS new_to_you
     FROM officers o JOIN thanas ot ON ot.code=o.thana_code
     WHERE o.status='active' AND ot.district_code=?
     ORDER BY pending_requests,o.name",
    [$me['id'],$farmerDistrict]
)->fetchAll() : [];
$districtName = $farmerDistrict ? query('SELECT name FROM districts WHERE code=?', [$farmerDistrict])->fetchColumn() : '';
?>
<section class="panel booking">
    <span class="eyebrow">LET’S TALK ABOUT YOUR FARM</span>
    <h2>Book an appointment</h2>
    <p class="muted"><?= $farmerDistrict ? 'Showing Krishi officers in '.e($districtName).' district.' : 'Complete your address above to find Krishi officers in your district.' ?></p>
    <?php if ($farmerDistrict && !$officers): ?><p class="muted">No active Krishi officers in your district have completed their address yet.</p><?php endif; ?>

    <?php if ($farmerDistrict && $officers): ?>
    <form method="post" class="stack" enctype="multipart/form-data">
        <?=csrf()?><input type="hidden" name="action" value="book">

        <label>Choose your Krishi officer
            <select name="officer_id" required>
                <option value="">Select an officer</option>
                <?php foreach ($officers as $o): ?>
                    <option value="<?=$o['id']?>" <?=($_POST['officer_id'] ?? '') == (string)$o['id'] ? 'selected' : ''?>>
                        <?=e($o['name'].' · '.$o['office'].' · '.$o['pending_requests'].' pending'.($o['new_to_you'] ? ' · New to you' : ''))?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <small class="muted">Officers with fewer pending requests appear first. “New to you” means no accepted or completed appointment with that officer.</small>

        <label>Crop / farming topic
            <input name="crop" placeholder="e.g. Rice, vegetables, soil health" maxlength="100" required value="<?=e($_POST['crop'] ?? '')?>">
        </label>
        <label>What’s happening on your farm?
            <textarea name="problem" rows="4" placeholder="Describe the problem, symptoms, and when they started…" maxlength="3000" required><?=e($_POST['problem'] ?? '')?></textarea>
        </label>

        <label class="photo-input" for="problem-photo">Problem photo (optional)
            <input id="problem-photo" type="file" name="problem_photo" accept="image/jpeg,image/png" aria-describedby="photo-help photo-error">
        </label>
        <small id="photo-help" class="photo-help">One JPG or PNG, up to 5 MB. Only you and your assigned officer can view it. If a request fails, choose the photo again.</small>
        <p id="photo-error" class="photo-validation" role="alert"></p>
        <figure id="photo-preview" class="photo-preview" hidden>
            <img id="photo-preview-image" alt="Selected problem photo preview">
            <figcaption id="photo-preview-caption"></figcaption>
            <button type="button" id="remove-photo" class="text-button">Remove selected photo</button>
        </figure>

        <div class="two">
            <label>Preferred date
                <input type="date" name="appointment_date" min="<?=date('Y-m-d')?>" required value="<?=e($_POST['appointment_date'] ?? '')?>">
            </label>
            <label>Preferred time
                <input type="time" name="appointment_time" min="09:00" max="17:00" required value="<?=e($_POST['appointment_time'] ?? '')?>">
            </label>
        </div>
        <small class="muted">Office hours: 9:00 AM–5:00 PM. Your officer will confirm availability. All times are Bangladesh time.</small>
        <button>Send appointment request ↗</button>
        <small class="form-foot">Your name, address, email and phone will be shared with your selected officer.</small>
    </form>
    <?php endif; ?>
</section>
