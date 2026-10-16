<section class="appointments">
    <div class="section-heading">
        <div>
            <span class="eyebrow">KEEP YOUR SEASON ON TRACK</span>
            <h2><?= $officer ? 'Appointment requests' : 'Your appointments' ?></h2>
        </div>
        <select id="status-filter" aria-label="Filter appointments by status">
            <option value="all">All statuses</option>
            <?php foreach ($counts as $status => $count): ?>
                <option value="<?=e($status)?>"><?=ucfirst($status)?> (<?=$count?>)</option>
            <?php endforeach; ?>
        </select>
    </div>
    <p id="filter-empty" class="empty" hidden>No appointments match this status.</p>

    <?php if (!$appointments): ?>
        <div class="empty">
            <span>♧</span>
            <h3><?= $officer ? 'Your next connection starts here' : 'Let’s get something growing' ?></h3>
            <p><?= $officer ? 'When farmers request your guidance, their details will appear here.' : 'Send your first appointment request using the form alongside.' ?></p>
        </div>
    <?php endif; ?>

    <?php foreach ($appointments as $a) require __DIR__.'/appointmentCard.php'; ?>
</section>
