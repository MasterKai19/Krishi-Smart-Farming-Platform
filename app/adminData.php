<?php
$farmers = query("SELECT f.*,t.name AS thana_name,d.name AS district_name,v.name AS division_name,
    (SELECT COUNT(*) FROM appointments a WHERE a.farmer_id=f.id) AS appointment_count
    FROM farmers f LEFT JOIN thanas t ON t.code=f.thana_code
    LEFT JOIN districts d ON d.code=t.district_code LEFT JOIN divisions v ON v.code=d.division_code
    ORDER BY FIELD(f.status,'pending','active','inactive'),f.created_at DESC,f.id DESC")->fetchAll();
$officers = query("SELECT o.*,t.name AS thana_name,d.name AS district_name,v.name AS division_name,
    (SELECT COUNT(*) FROM appointments a WHERE a.officer_id=o.id) AS appointment_count
    FROM officers o LEFT JOIN thanas t ON t.code=o.thana_code
    LEFT JOIN districts d ON d.code=t.district_code LEFT JOIN divisions v ON v.code=d.division_code
    ORDER BY FIELD(o.status,'pending','active','inactive'),o.created_at DESC,o.id DESC")->fetchAll();
$pendingFarmers = count(array_filter($farmers, fn($row) => $row['status'] === 'pending'));
$pendingOfficers = count(array_filter($officers, fn($row) => $row['status'] === 'pending'));
