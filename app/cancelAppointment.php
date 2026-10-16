<?php
$changed = query(
    "UPDATE appointments SET status='cancelled'
     WHERE id=? AND farmer_id=? AND status IN ('pending','accepted')",
    [(int)($_POST['id'] ?? 0), $me['id']]
)->rowCount();
flash($changed ? 'Appointment cancelled.' : 'This appointment can no longer be cancelled.');
go('index.php?page=appointments');
