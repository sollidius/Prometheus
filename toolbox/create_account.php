<?php
include '../pages/functions.php';

$name = "Test"; $email = "123@123.de"; $password = "123456";

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare("INSERT INTO users(name,email,password) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $name, $email,$hash);
$stmt->execute();
$stmt->close();






 ?>
