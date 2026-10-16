<section class="stats">
    <div>
        <span>Total appointments</span>
        <strong><?=$totalAppointments?></strong>
        <small>Your connections with Krishi</small>
    </div>
    <div>
        <span>Awaiting response <i class="dot amber"></i></span>
        <strong><?=$counts['pending']?></strong>
        <small><?= $officer ? 'Requests ready for your review' : 'Your officer will respond here' ?></small>
    </div>
    <div>
        <span>Accepted <i class="dot green"></i></span>
        <strong><?=$counts['accepted']?></strong>
        <small>Ready for a conversation</small>
    </div>
    <div>
        <span><?= $officer ? 'Completed' : 'Unread notifications' ?></span>
        <strong><?= $officer ? $counts['completed'] : $unread ?></strong>
        <small><?= $officer ? 'Support delivered to farmers' : 'Stay in the know' ?></small>
    </div>
</section>
