<?php



if (isset($_GET["page"])) {
$page = $_GET["page"];
}
if(!isset($page)) {
$page="login";
}

//Login Page
if ($page=="login") {
 include 'pages/login.php';
}

//Dashboard
if ($page=="dashboard") {
 include 'pages/dashboard.php';
}

//Logout
if ($page=="logout") {
  header('Location: index.php');
}































 ?>
