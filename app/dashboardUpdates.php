<?php
 header('Content-Type: application/json');
 if (!$me || $_SESSION['role'] === 'admin') { http_response_code(401); echo json_encode(['error'=>'Farmer or officer sign-in required']); exit; }
 $role=$_SESSION['role'];
 $rows=query('SELECT id,status,updated_at FROM appointments WHERE '.($role==='officer'?'officer_id':'farmer_id').'=? ORDER BY id',[$me['id']])->fetchAll();
 $schemeRows=$role==='officer'
    ? query('SELECT a.id,a.status,a.reviewed_at FROM scheme_applications a JOIN farmers f ON f.id=a.farmer_id LEFT JOIN thanas ft ON ft.code=f.thana_code LEFT JOIN scheme_application_land sl ON sl.application_id=a.id JOIN thanas ot ON ot.district_code=COALESCE(sl.district_code,ft.district_code) JOIN officers o ON o.thana_code=ot.code WHERE o.id=? ORDER BY a.id',[$me['id']])->fetchAll()
    : query('SELECT id,status,reviewed_at FROM scheme_applications WHERE farmer_id=? ORDER BY id',[$me['id']])->fetchAll();
 $unread=$role==='farmer' ? (int)query('SELECT COUNT(*) FROM v_farmer_notifications WHERE farmer_id=? AND is_read=0',[$me['id']])->fetchColumn() : 0;
 echo json_encode(['version'=>hash('sha256',json_encode([$rows,$schemeRows])), 'unread'=>$unread]); exit;
