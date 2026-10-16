<article class="appointment-card" data-status="<?=e($a['status'])?>">
    <div class="card-top">
        <span class="crop-icon">♧</span>
        <div>
            <h3><?=e($a['crop'])?></h3>
            <small>REQUEST #<?=str_pad((string)$a['id'], 4, '0', STR_PAD_LEFT)?> · <?=date('d M Y', strtotime($a['created_at']))?></small>
        </div>
        <span class="badge <?=e($a['status'])?>"><?=ucfirst($a['status'])?></span>
    </div>

    <p class="problem"><?=nl2br(e($a['problem']))?></p>
    <?php if (isset($photosByAppointment[$a['id']]) && $photosByAppointment[$a['id']]['available']): ?>
        <figure class="problem-photo">
            <a href="photo.php?id=<?=e($photosByAppointment[$a['id']]['id'])?>" target="_blank" rel="noopener">
                <img src="photo.php?id=<?=e($photosByAppointment[$a['id']]['id'])?>" alt="Problem photo for <?=e($a['crop'])?>" loading="lazy">
            </a>
            <figcaption><?=e($photosByAppointment[$a['id']]['storage_name'])?> · Click to view full size</figcaption>
        </figure>
    <?php elseif (isset($photosByAppointment[$a['id']])): ?>
        <p>Photo <?=e($photosByAppointment[$a['id']]['storage_name'])?> is unavailable in storage.</p>
    <?php endif; ?>

    <div class="appointment-meta">
        <span>▦ <?=date('d M Y', strtotime($a['appointment_date']))?></span>
        <span>◷ <?=date('g:i A', strtotime($a['appointment_time']))?></span>
        <span>↗ <?=e($a['office'])?></span>
    </div>

    <?php if ($officer): ?>
        <div class="contact-box">
            <strong><?=e($a['farmer_name'])?></strong>
            <span><?=e($a['farmer_address'] ? $a['farmer_address'].' · '.$a['farmer_thana'].' · '.$a['farmer_district'] : $a['village'])?></span>
            <a href="tel:<?=e(preg_replace('/[^+0-9]/', '', $a['farmer_phone']))?>"><?=e($a['farmer_phone'])?></a>
            <a href="mailto:<?=e($a['farmer_email'])?>"><?=e($a['farmer_email'])?></a>
        </div>
    <?php else: ?>
        <div class="officer-line">Your officer <strong><?=e($a['officer_name'])?></strong></div>
        <?php if (in_array($a['status'], ['accepted','completed'], true)): ?>
            <div class="contact-box">
                <strong>✓ Your appointment is confirmed</strong>
                <span><?=e($a['specialization'])?> · <?=e($a['office'])?></span>
                <a href="tel:<?=e(preg_replace('/[^+0-9]/', '', $a['officer_phone']))?>">Call <?=e($a['officer_phone'])?></a>
                <a href="mailto:<?=e($a['officer_email'])?>"><?=e($a['officer_email'])?></a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($a['officer_note']): ?>
        <p class="officer-note"><strong>Officer’s note</strong><br><?=nl2br(e($a['officer_note']))?></p>
    <?php endif; ?>

    <?php if ($officer && in_array($a['status'], ['pending','accepted'], true)): ?>
        <form method="post" class="response-form">
            <?=csrf()?><input type="hidden" name="action" value="respond"><input type="hidden" name="id" value="<?=$a['id']?>">
            <label for="note-<?=$a['id']?>">Note to farmer (optional)</label>
            <textarea id="note-<?=$a['id']?>" name="officer_note" maxlength="1000" rows="2" placeholder="Share preparation tips or meeting details"><?=e($a['officer_note'] ?? '')?></textarea>
            <div class="actions">
                <?php if ($a['status'] === 'pending'): ?>
                    <button name="status" value="accepted">Accept appointment ✓</button>
                    <button class="secondary" name="status" value="declined">Decline</button>
                <?php else: ?>
                    <button name="status" value="completed">Mark completed ✓</button>
                <?php endif; ?>
            </div>
        </form>
    <?php elseif (!$officer && in_array($a['status'], ['pending','accepted'], true)): ?>
        <form method="post" data-confirm="Cancel this appointment?">
            <?=csrf()?><input type="hidden" name="action" value="cancel"><input type="hidden" name="id" value="<?=$a['id']?>">
            <button class="text-button cancel">Cancel appointment</button>
        </form>
    <?php endif; ?>

    <?php if (!$officer && in_array($a['status'], ['declined','cancelled'], true)): ?>
        <form method="post" data-confirm="Permanently delete this closed request, its photo and notifications from both dashboards? This cannot be undone.">
            <?=csrf()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$a['id']?>">
            <button class="text-button cancel">Delete closed request</button>
        </form>
    <?php endif; ?>
</article>
