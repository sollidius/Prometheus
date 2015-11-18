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

function msg_warning($msg) {
  echo'
  <div class="alert alert-dismissible alert-warning">
    <button type="button" class="close" data-dismiss="alert">x</button>
    <h4>Warnung!</h4>
    <p>'.$msg.'</p>
 </div>';
}

function msg_okay($msg) {
  echo'
  <div class="alert alert-dismissible alert-success">
    <button type="button" class="close" data-dismiss="alert">x</button>
    <h4>Okay!</h4>
    <p>'.$msg.'</p>
 </div>';
}

function msg_error($msg) {
  echo'
  <div class="alert alert-dismissible alert-danger">
    <button type="button" class="close" data-dismiss="alert">x</button>
    <h4>Error!</h4>
    <p>'.$msg.'</p>
 </div>';
}

function port_exists($ip,$port) {
  global $mysqli;
  $query = "SELECT `id` FROM `gameservers` WHERE ip=? AND port=?";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("si", $ip,$port);

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

function get_user_by_id($id) {
  global $mysqli;

  $name = "n/a";
  $stmt = $mysqli->prepare("SELECT name FROM users WHERE id = ?");
  if ( false===$stmt ) {
  die('prepare() failed: ' . htmlspecialchars($mysqli->error));
  }
  $rc = $stmt->bind_param('i', $id);
  if ( false===$rc ) {
    die('bind_param() failed: ' . htmlspecialchars($stmt->error));
  }
  $rc = $stmt->execute();
  if ( false===$rc ) {
  die('execute() failed: ' . htmlspecialchars($stmt->error));
  }
  $stmt->bind_result($name);
  $stmt->fetch();
  $stmt->close();

  return $name;

}

 ?>
