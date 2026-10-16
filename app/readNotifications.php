<?php
query(
    'UPDATE notifications SET is_read=1 WHERE appointment_id IN
     (SELECT id FROM appointments WHERE farmer_id=?)',
    [$me['id']]
);
go('index.php?page=appointments');
