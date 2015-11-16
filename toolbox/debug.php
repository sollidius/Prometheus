<?php
include '../pages/functions.php';

if (function_exists('mysqli_connect')) {
  //mysqli is installed
  echo "MySQLi installiert<br>";
} else {
  echo "MySQLi nicht installiert<br>";
}

if ($mysqli_connection->connect_error) {
   echo "Not connected, error: " . $mysqli_connection->connect_error;
}else {
   echo "MySQLi Verbunden<br>";
}

if (function_exists('ssh2_connect')) {
  //mysqli is installed
  echo "php5-ssh2 installiert<br>";
} else {
  echo "php5-ssh2 nicht installiert<br>";
}

























?>
