<?php

$mysqli = new mysqli("localhost", "Prometheus", "aTFGbJjEC9LtUSN4", "prometheus");

if ($mysqli->connect_error) {
   echo "Not connected, error: " . $mysqli_connection->connect_error;
   exit;
}

function user_exists($user,$id = 0) {
  global $mysqli;
  $query = "SELECT `id` FROM `users` WHERE name=?";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("s", $user);

          if($stmt->execute()){
              $stmt->store_result();

              $check= "";
              $stmt->bind_result($check);
              $stmt->fetch();

              if ($id == 0) {
                if ($stmt->num_rows == 1){
                return true;
                } else {
                return false;
                }
              } else {
                if ($id == $check) {
                  return false;
                } elseif ($check == "") {
                  return false;
                } else {
                  return true;
                }
              }
          } else {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
          }
      } else {
        die('prepare() failed: ' . htmlspecialchars($mysqli->error));
      }
}

function email_exists($email,$id = 0) {
  global $mysqli;
  $query = "SELECT `id` FROM `users` WHERE email=?";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("s", $email);

          if($stmt->execute()){
              $stmt->store_result();

              $check= "";
              $stmt->bind_result($check);
              $stmt->fetch();

              if ($id == 0) {
                if ($stmt->num_rows == 1){
                return true;
                } else {
                return false;
                }
              } else {
                if ($id == $check) {
                  return false;
                } elseif ($check == "") {
                  return false;
                } else {
                  return true;
                }
              }
          } else {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
          }
      } else {
        die('prepare() failed: ' . htmlspecialchars($mysqli->error));
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

          $rc = $stmt->bind_param("s", $was);
          if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}

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
          } else {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
          }
      } else {
        die('prepare() failed: ' . htmlspecialchars($mysqli->error));
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

function port_exists($ip,$port,$id=0) {
  global $mysqli;
  $query = "SELECT `user_id` FROM `gameservers` WHERE ip=? AND port=?";

  if ($stmt = $mysqli->prepare($query)){

          $rc = $stmt->bind_param("si", $ip,$port);
          if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}

          if($stmt->execute()){
              $stmt->store_result();

              $check= "";
              $stmt->bind_result($check);
              $stmt->fetch();

              if ($id == 0) {
                if ($stmt->num_rows == 1){
                return true;
                } else {
                return false;
                }
              } else {
                if ($id == $check) {
                  return false;
                } elseif ($check == "") {
                  return false;
                } else {
                  return true;
                }
              }
          } else {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
          }
      } else {
        die('prepare() failed: ' . htmlspecialchars($mysqli->error));
      }
}

function get_user_by_id($id) {
  global $mysqli;

  $name = "n/a";
  $stmt = $mysqli->prepare("SELECT name FROM users WHERE id = ?");
  if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
  $rc = $stmt->bind_param('i', $id);
  if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
  $rc = $stmt->execute();
  if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
  $stmt->bind_result($name);
  $stmt->fetch();
  $stmt->close();

  return $name;

}

function get_game_installed($dedi_id,$game) {
  global $mysqli;

  $result_id = 0;
  $type_t = "template";
  $stmt = $mysqli->prepare("SELECT id FROM jobs WHERE dedicated_id = ? AND type = ? AND type_id = ?");
  if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
  $rc = $stmt->bind_param('iss', $dedi_id,$type_t,$game);
  if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
  $rc = $stmt->execute();
  if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
  $stmt->bind_result($result_id);
  $stmt->fetch();
  $stmt->close();

  if ($result_id != 0) { $msg[1] = "Installation läuft noch!"; $msg[0] = 0; return $msg;}

  $stmt = $mysqli->prepare("SELECT id FROM templates WHERE name = ?");
  if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
  $rc = $stmt->bind_param('s',$game);
  if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
  $rc = $stmt->execute();
  if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
  $stmt->bind_result($template_id);
  $stmt->fetch();
  $stmt->close();

  $result_id = 0;
  $stmt = $mysqli->prepare("SELECT id FROM dedicated_games WHERE dedi_id = ? AND template_id = ?");
  if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
  $rc = $stmt->bind_param('ii',$dedi_id,$template_id);
  if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
  $rc = $stmt->execute();
  if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
  $stmt->bind_result($result_id);
  $stmt->fetch();
  $stmt->close();

  if ($result_id != 0) { $msg[1] = "Spiel ist installiert"; $msg[0] = 0; return $msg;}



  $msg[0] = 1;
  return $msg;
}

function check_game_installed($dedi_id,$game) {
  global $mysqli;

  $result_id = 0;
  $type_t = "template";
  $stmt = $mysqli->prepare("SELECT id FROM jobs WHERE dedicated_id = ? AND type = ? AND type_id = ?");
  if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
  $rc = $stmt->bind_param('iss', $dedi_id,$type_t,$game);
  if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
  $rc = $stmt->execute();
  if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
  $stmt->bind_result($result_id);
  $stmt->fetch();
  $stmt->close();

  if ($result_id != 0) { $msg[1] = "Installation läuft noch!"; $msg[0] = 0; return $msg;}

  $stmt = $mysqli->prepare("SELECT id FROM templates WHERE name = ?");
  if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
  $rc = $stmt->bind_param('s',$game);
  if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
  $rc = $stmt->execute();
  if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
  $stmt->bind_result($template_id);
  $stmt->fetch();
  $stmt->close();

  $result_id = 0;
  $stmt = $mysqli->prepare("SELECT id FROM dedicated_games WHERE dedi_id = ? AND template_id = ?");
  if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
  $rc = $stmt->bind_param('ii',$dedi_id,$template_id);
  if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
  $rc = $stmt->execute();
  if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
  $stmt->bind_result($result_id);
  $stmt->fetch();
  $stmt->close();

  if ($result_id != 0) { $msg[1] = "Spiel ist installiert"; $msg[0] = 1; return $msg;}

  $msg[0] = 0;
  $msg[1] = "Spiel ist nicht Installiert.";
  return $msg;
}

function get_template_by_id($id) {
  global $mysqli;

  $stmt = $mysqli->prepare("SELECT name FROM templates WHERE id = ?");
  if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
  $rc = $stmt->bind_param('i',$id);
  if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
  $rc = $stmt->execute();
  if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
  $stmt->bind_result($name);
  $stmt->fetch();
  $stmt->close();

  return $name;

}

function check_template($template,$id = 0) {
  global $mysqli;
  $query = "SELECT `id` FROM `templates` WHERE name=?";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("s", $template);

          if($stmt->execute()){
              $stmt->store_result();

              $check= "";
              $stmt->bind_result($check);
              $stmt->fetch();

              if ($id == 0) {
              if ($stmt->num_rows == 1){
              return false;
            } else {
              return true;
            }
          } else {
            if ($id == $check) {
              return false;
            } elseif ($check == "") {
              return false;
            } else {
              return true;
            }
          }
          } else {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
          }
      } else {
        die('prepare() failed: ' . htmlspecialchars($mysqli->error));
      }
}

function check_user_id($id) {
  global $mysqli;
  $query = "SELECT `name` FROM `users` WHERE id=?";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("i", $id);

          if($stmt->execute()){
              $stmt->store_result();

              $check= "";
              $stmt->bind_result($check);
              $stmt->fetch();

              if ($stmt->num_rows == 1){
              return false;
            } else {
              return true;
            }
          } else {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
          }
      } else {
        die('prepare() failed: ' . htmlspecialchars($mysqli->error));
      }
}

function check_dedi_id($id) {
  global $mysqli;
  $query = "SELECT `name` FROM `dedicated` WHERE id=?";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("i", $id);

          if($stmt->execute()){
              $stmt->store_result();

              $check= "";
              $stmt->bind_result($check);
              $stmt->fetch();

              if ($stmt->num_rows == 1){
              return false;
            } else {
              return true;
            }
          } else {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
          }
      } else {
        die('prepare() failed: ' . htmlspecialchars($mysqli->error));
      }
}

function check_game_in_use($game,$ip) {
  global $mysqli;
  $query = "SELECT `id` FROM `gameservers` WHERE game=? AND ip = ?";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("ss", $game,$ip);

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
          } else {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
          }
      } else {
        die('prepare() failed: ' . htmlspecialchars($mysqli->error));
      }
}

function check_template_job_exists($dedi_id,$template_id) {
  global $mysqli;
  $query = "SELECT `id` FROM `jobs` WHERE dedicated_id=? AND template_id = ?";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("ii", $dedi_id,$template_id);

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
          } else {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
          }
      } else {
        die('prepare() failed: ' . htmlspecialchars($mysqli->error));
      }
}

function check_template_job_exists_id_only($template_id) {
  global $mysqli;
  $query = "SELECT `id` FROM `jobs` WHERE template_id = ?";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("i",$template_id);

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
          } else {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
          }
      } else {
        die('prepare() failed: ' . htmlspecialchars($mysqli->error));
      }
}

function check_template_exist_in_games($template_id) {
  global $mysqli;
  $query = "SELECT `id` FROM `dedicated_games` WHERE template_id=?";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("i",$template_id);

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
          } else {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
          }
      } else {
        die('prepare() failed: ' . htmlspecialchars($mysqli->error));
      }
}

function isValidEmail($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function ip_exists($ip,$id = 0) {
  global $mysqli;
  $query = "SELECT `id` FROM `dedicated` WHERE ip=?";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("s", $ip);

          if($stmt->execute()){
              $stmt->store_result();

              $check= "";
              $stmt->bind_result($check);
              $stmt->fetch();

              if ($id == 0) {
                if ($stmt->num_rows == 1){
                return true;
                } else {
                return false;
                }
              } else {
                if ($id == $check) {
                  return false;
                } elseif ($check == "") {
                  return false;
                } else {
                  return true;
                }
              }
          } else {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
          }
      } else {
        die('prepare() failed: ' . htmlspecialchars($mysqli->error));
      }
}

function console_send($cmd) {
//screen -S "gameUser-1" -X stuff "changelevel de_dust2\n"





}


 ?>
