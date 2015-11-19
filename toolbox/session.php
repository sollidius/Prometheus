<?php

session_start();
$_SESSION['login'] = 1;
$_SESSION['user_id'] = "18";
header('Location: index.php?page=dashboard');


 ?>
