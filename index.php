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

//Settings
if ($page=="settings") {
 include 'pages/settings.php';
}

//Settings
if ($page=="usettings") {
 include 'pages/usettings.php';
}

//Users
if ($page=="users") {
 include 'pages/users.php';
}

//Rootserver
if ($page=="rootserver") {
 include 'pages/rootserver.php';
}

//Rootserver
if ($page=="templates") {
 include 'pages/templates.php';
}

//Rootserver
if ($page=="gameserver") {
 include 'pages/gameserver.php';
}

//Logout
if ($page=="logout") {
  session_start();
  session_destroy();
  header('Location: index.php');
}































 ?>
