<section class="panel notifications">
    <div class="section-heading">
        <h2>Notifications <span class="notification-count"><?=$unread?></span></h2>
        <?php if ($unread): ?>
            <form method="post">
                <?=csrf()?><input type="hidden" name="action" value="read">
                <button class="text-button">Mark all read</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (!$notifications): ?>
        <p class="muted">You’re all caught up. Appointment updates will appear here.</p>
    <?php endif; ?>
    <?php foreach ($notifications as $n): ?>
        <div class="notification <?=$n['is_read'] ? '' : 'unread'?>">
            <p><?=e($n['message'])?></p>
            <small><?=date('d M, g:i A', strtotime($n['created_at']))?></small>
        </div>
    <?php endforeach; ?>
    <small class="muted">Showing the latest 20 updates. Checks for changes every 15 seconds.</small>
</section>
