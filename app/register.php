<?php
$name = field('name', 100);
$email = field('email', 190);
$phone = field('phone', 30);
$village = field('village');
$nid = submitted_nid();
require_once __DIR__.'/schemeHelpers.php';
$land = submitted_land('land_katha');
[$thana, $address] = submitted_location();
$password = $_POST['password'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter a valid email address.');
if (strlen($password) < 8 || strlen($password) > 72) throw new RuntimeException('Use a password of 8–72 characters.');

query(
    'INSERT INTO farmers (name,email,password,phone,village,nid_number,thana_code,detailed_address,land_katha,status) VALUES (?,?,?,?,?,?,?,?,?,?)',
    [$name, $email, password_hash($password, PASSWORD_DEFAULT), $phone, $village, $nid, $thana, $address, $land, 'pending']
);
flash('Application received. An admin must approve your farmer account before you can sign in.');
go('index.php?page=login');
