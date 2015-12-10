<?php

include 'pages/functions.php';

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
if (startsWith($page, "users")) {
 include 'pages/users.php';
}

//Rootserver
if (startsWith($page, "rootserver")) {
 include 'pages/rootserver.php';
}

//Templates
if (startsWith($page, "templates")) {
 include 'pages/templates.php';
}
//Addons
if (startsWith($page, "addons")) {
 include 'pages/addons.php';
}

//Events
if (startsWith($page, "events")) {
 include 'pages/events.php';
}

//Gameserver
if (startsWith($page, "gameserver")) {
 include 'pages/gameserver.php';
}

//Gameserver
if (startsWith($page, "bans")) {
 include 'pages/bans.php';
}

//Logout
if ($page=="logout") {
  session_start();
  session_unset();
  session_destroy();
  header('Location: index.php');
}































 ?>
