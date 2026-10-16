CREATE OR REPLACE SQL SECURITY INVOKER VIEW v_appointment_details AS
SELECT a.*, f.name AS farmer_name,f.phone AS farmer_phone,
 f.email AS farmer_email,f.village,f.detailed_address AS farmer_address,
 ft.name AS farmer_thana,fd.name AS farmer_district,o.name AS officer_name,
 o.phone AS officer_phone,o.email AS officer_email,o.office,o.specialization
FROM appointments a JOIN farmers f ON f.id=a.farmer_id
LEFT JOIN thanas ft ON ft.code=f.thana_code LEFT JOIN districts fd ON fd.code=ft.district_code
JOIN officers o ON o.id=a.officer_id;

CREATE OR REPLACE SQL SECURITY INVOKER VIEW v_farmer_notifications AS
SELECT n.id,n.appointment_id,n.message,n.is_read,n.created_at,a.farmer_id
FROM notifications n JOIN appointments a ON a.id=n.appointment_id;

CREATE OR REPLACE SQL SECURITY INVOKER VIEW v_officer_workload AS
SELECT o.id AS officer_id,COUNT(a.id) AS total,
 COALESCE(SUM(a.status='pending'),0) AS pending,
 COALESCE(SUM(a.status='accepted'),0) AS accepted,
 COALESCE(SUM(a.status='completed'),0) AS completed,
 COALESCE(SUM(a.status='declined'),0) AS declined,
 COALESCE(SUM(a.status='cancelled'),0) AS cancelled
FROM officers o LEFT JOIN appointments a ON a.officer_id=o.id
GROUP BY o.id;
