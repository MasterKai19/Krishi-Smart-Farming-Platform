<?php
$name = field('name', 100);
$email = field('email', 190);
$phone = field('phone', 30);
$office = field('office', 150);
$specialization = field('specialization', 150);
[$thana, $address] = submitted_location();
$password = $_POST['password'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter a valid email address.');
if (strlen($password) < 8 || strlen($password) > 72) throw new RuntimeException('Use a password of 8–72 characters.');

query(
    'INSERT INTO officers (name,email,password,phone,office,specialization,thana_code,detailed_address,status) VALUES (?,?,?,?,?,?,?,?,?)',
    [$name, $email, password_hash($password, PASSWORD_DEFAULT), $phone, $office, $specialization, $thana, $address, 'pending']
);
flash('Application received. An admin must approve your Krishi officer account before you can sign in.');
go('index.php?page=login&role=officer');
