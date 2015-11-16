<?php
include '../pages/functions.php';

$name = "Test"; $email = "123@123.de"; $password = "123456789"; $rank = 2;

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare("INSERT INTO users(name,email,password,rank) VALUES (?, ?, ?, ?)");
$stmt->bind_param('sssi', $name, $email,$hash,$rank);
$stmt->execute();
$stmt->close();


echo "okay";



 ?>
