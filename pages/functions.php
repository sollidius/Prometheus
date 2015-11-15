<?php

$mysqli = new mysqli("localhost", "Prometheus", "aTFGbJjEC9LtUSN4", "prometheus");

if ($mysqli->connect_error) {
   echo "Not connected, error: " . $mysqli_connection->connect_error;
   exit;
}

function user_exists($user) {
  global $mysqli;
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

function email_exists($email) {
  global $mysqli;
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

function exists_entry($spalte,$tabelle,$wo,$was) {

  $query = "SELECT ".$spalte." FROM ".$tabelle." WHERE ".$wo."=?";
  global $mysqli;
  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("s", $was);

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

function generatePassword($pwlen=12) {
	mt_srand();
	$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  $pw = "";
	for($i=0;$i<$pwlen;$i++)
	{
		$pw .= $salt[mt_rand(0, strlen($salt)-1)];
	}

	return $pw;
}

function msg_okay($msg) {

$tmp = '
<div class="alert alert-success" role="alert">
 <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
 <span class="sr-only">Success:</span>
 '.$msg.'
 </div>';

echo $tmp;
}




 ?>
