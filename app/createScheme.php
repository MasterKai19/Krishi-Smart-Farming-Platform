<?php
require_once __DIR__.'/schemeHelpers.php';
[$title,$benefit,$description,$limit,$district,$source,$status] = scheme_fields();
query('INSERT INTO schemes (title,benefit,description,max_land_katha,district_code,source_url,status,created_by) VALUES (?,?,?,?,?,?,?,?)',
    [$title,$benefit,$description,$limit,$district,$source,$status,$me['id']]);
flash('Scheme created.');
go('index.php#schemes');
