<?php
require_once('classes/tools.class.php');

$db = Tools::GetDB();

$email = "EMAIL / USERNAME HERE";
$password = "SECURE PASSWORD HERE";
$hash = password_hash($password, PASSWORD_DEFAULT);
$first_name = "FIRSTNAME HERE";
$last_name = "LAST NAME HERE";
$user_type = 0;

$sql = "INSERT INTO users (email, hash, last_name, first_name, user_type) VALUES (?, ?, ?, ?, ?)";
$stmt = $db->prepare($sql);
$stmt->bind_param('ssssi', $email, $hash, $last_name, $first_name, $user_type);
$stmt->execute();
