<?php

$mysqli = new mysqli("localhost", "Prometheus", "aTFGbJjEC9LtUSN4", "prometheus");

if ($mysqli_connection->connect_error) {
   echo "Not connected, error: " . $mysqli_connection->connect_error;
   exit;
}

function user_exists($user,$mysqli) {

  $query = "SELECT `name` FROM `users` WHERE name=?";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("s", $user);

          if($stmt->execute()){
              $stmt->store_result();

              $check= "";
              $stmt->bind_result($check);
              $stmt->fetch();

              if ($stmt->num_rows == 1){
              return true;
            } else {
              return false;
            }
          }
      }
}

function email_exists($email,$mysqli) {

  $query = "SELECT `email` FROM `users` WHERE email=?";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("s", $email);

          if($stmt->execute()){
              $stmt->store_result();

              $check= "";
              $stmt->bind_result($check);
              $stmt->fetch();

              if ($stmt->num_rows == 1){
              return true;
            } else {
              return false;
            }
          }
      }
}

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}





 ?>
