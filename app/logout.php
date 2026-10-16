<?php
$_SESSION = [];
session_regenerate_id(true);
go('index.php');
