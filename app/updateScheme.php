<?php
require_once __DIR__.'/schemeHelpers.php';
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) throw new RuntimeException('Choose a scheme.');
[$title,$benefit,$description,$limit,$district,$source,$status] = scheme_fields();
if (!query('SELECT id FROM schemes WHERE id=?', [$id])->fetch()) throw new RuntimeException('Scheme not found.');
query('UPDATE schemes SET title=?,benefit=?,description=?,max_land_katha=?,district_code=?,source_url=?,status=?,is_example=0 WHERE id=?',
    [$title,$benefit,$description,$limit,$district,$source,$status,$id]);
flash('Scheme updated.');
go('index.php#schemes');
