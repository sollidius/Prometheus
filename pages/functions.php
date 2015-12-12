<?php

date_default_timezone_set('Europe/Amsterdam');
$mysqli = new mysqli("localhost", "prometheus", "GZLUeYPKMDR69H6Z", "prometheus");

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

function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
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

function msg_info($msg) {
  echo'
  <div class="alert alert-dismissible alert-info">
    <button type="button" class="close" data-dismiss="alert">x</button>
    <h4>Info!</h4>
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
  $type_i = "image";
  $stmt = $mysqli->prepare("SELECT id FROM jobs WHERE dedicated_id = ? AND (type = ? OR type = ?) AND type_id = ?");
  if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
  $rc = $stmt->bind_param('isss', $dedi_id,$type_t,$type_i,$game);
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

function get_addon_installed($dedi_id,$addon_id,$gs_id) {
  global $mysqli;

  $result_id = 0;
  $type = "addon";
  $stmt = $mysqli->prepare("SELECT id FROM jobs WHERE dedicated_id = ? AND type = ? AND type_id = ? and template_id = ?");
  if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
  $rc = $stmt->bind_param('isii', $dedi_id,$type,$addon_id,$gs_id);
  if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
  $rc = $stmt->execute();
  if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
  $stmt->bind_result($result_id);
  $stmt->fetch();
  $stmt->close();

  if ($result_id != 0) { $msg[1] = "Installation läuft noch!"; $msg[0] = 0; return $msg;}

  $result_id = 0;
  $stmt = $mysqli->prepare("SELECT id FROM addons_installed WHERE dedi_id = ? AND addons_id = ? AND gs_id = ?");
  if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
  $rc = $stmt->bind_param('iii',$dedi_id,$addon_id,$gs_id);
  if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
  $rc = $stmt->execute();
  if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
  $stmt->bind_result($result_id);
  $stmt->fetch();
  $stmt->close();

  if ($result_id != 0) { $msg[1] = "Addon ist installiert"; $msg[0] = 0; return $msg;}

  $msg[0] = 1;
  return $msg;
}

function check_game_installed($dedi_id,$game) {
  global $mysqli;

  $result_id = 0;
  $type_t = "template";
  $stmt = $mysqli->prepare("SELECT id FROM jobs WHERE dedicated_id = ? AND type = ? AND type_id = ?");
  if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
  $rc = $stmt->bind_param('isi', $dedi_id,$type_t,$game);
  if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
  $rc = $stmt->execute();
  if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
  $stmt->bind_result($result_id);
  $stmt->fetch();
  $stmt->close();

  if ($result_id != 0) { $msg[1] = "Installation läuft noch!"; $msg[0] = 0; return $msg;}

  $result_id = 0;
  $stmt = $mysqli->prepare("SELECT id FROM dedicated_games WHERE dedi_id = ? AND template_id = ?");
  if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
  $rc = $stmt->bind_param('ii',$dedi_id,$game);
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
  $query = "SELECT `id` FROM `templates` WHERE id=?";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("i", $template);

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

              if ($stmt->num_rows >= 1){
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

function event_add($type,$msg) {

  global $mysqli;

  $time = time();
  $stmt = $mysqli->prepare("INSERT INTO events(type,message,timestamp) VALUES (?, ?, ?)");
  if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
  $rc = $stmt->bind_param('isi', $type,$msg,$time);
  if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
  $rc = $stmt->execute();
  if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
  $stmt->close();

}

function check_template_exist_in_games_dedi_id($id) {
  global $mysqli;
  $query = "SELECT `id` FROM `dedicated_games` WHERE dedi_id=?";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("i",$id);

          if($stmt->execute()){
              $stmt->store_result();

              $check= "";
              $stmt->bind_result($check);
              $stmt->fetch();

              if ($stmt->num_rows >= 1){
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

function event_id_to_ico($id) {

  if ($id == 1) {
    return "rocket";
  } elseif ($id == 2) {
    return "stop";
  } elseif ($id == 3) {
    return "remove";
  } elseif ($id == 4) {
    return "download";
  } elseif ($id == 5) {
    return "refresh";
  } elseif ($id == 6) {
    return "plus";
  } elseif ($id == 7) {
    return "exclamation-triangle";
  } elseif ($id == 8) {
    return "exclamation";
  }

}

function gameserver_restart($type,$ssh,$gs_login,$name_internal,$port,$ip,$map,$slots,$parameter,$gameq,$gs_select,$app_set_config) {
  global $mysqli;
  $ssh->exec('sudo -u '.$gs_login.' screen -S game'.$gs_login.' -p 0 -X quit');
  if ($type == "steamcmd" AND $app_set_config == "") {
      $ssh->exec('cd /home/'.$gs_login.'/game;sudo -u '.$gs_login.' rm screenlog.0');
      $ssh->exec('cd /home/'.$gs_login.'/game;sudo -u '.$gs_login.' screen -A -m -d -L -S game'.$gs_login.' /home/'.$gs_login.'/game/srcds_run -game '.$name_internal.' +port '.$port.' +ip '.$ip.' +map '.$map.' -maxplayers '.$slots .' ' .$parameter);
  } elseif ($type == "steamcmd" AND  $app_set_config != "") {
      $ssh->exec('cd /home/'.$gs_login.'/game;sudo -u '.$gs_login.' rm screenlog.0');
      $ssh->exec('cd /home/'.$gs_login.'/game;sudo -u '.$gs_login.' screen -A -m -d -L -S game'.$gs_login.' /home/'.$gs_login.'/game/hlds_run -game '.$name_internal.' +port '.$port.' +ip '.$ip.' +map '.$map.' -maxplayers '.$slots .' ' .$parameter);
  } elseif ($type == "image") {
    if ($gameq == "minecraft") {
      $server_port = str_replace("server-port=","",$ssh->exec('cat /home/'.$gs_login.'/server.properties | grep "server-port="'));
      $server_port = preg_replace("/\s+/", "", $server_port);
      $query_port = str_replace("query.port=","",$ssh->exec('cat /home/'.$gs_login.'/server.properties | grep "query.port="'));
      $query_port = preg_replace("/\s+/", "", $query_port);
      $max_players = str_replace("max-players=","",$ssh->exec('cat /home/'.$gs_login.'/server.properties | grep "max-players="'));
      $max_players = preg_replace("/\s+/", "", $max_players);
      $query_enable = str_replace("enable-query=","",$ssh->exec('cat /home/'.$gs_login.'/server.properties | grep "enable-query="'));
      $query_enable = preg_replace("/\s+/", "", $query_enable);
      $ssh->exec("sudo -u ".$gs_login." find /home/".$gs_login."/server.properties -type f -exec sed -i 's/server-port=".$server_port."/server-port=".$port."/g' {} \;");
      $ssh->exec("sudo -u ".$gs_login." find /home/".$gs_login."/server.properties -type f -exec sed -i 's/query.port=".$query_port."/query.port=".$port."/g' {} \;");
      $ssh->exec("sudo -u ".$gs_login." find /home/".$gs_login."/server.properties -type f -exec sed -i 's/max-players=".$max_players."/max-players=".$slots."/g' {} \;");
      $ssh->exec("sudo -u ".$gs_login." find /home/".$gs_login."/server.properties -type f -exec sed -i 's/enable-query=".$query_enable."/enable-query=true/g' {} \;");
    }
     $ssh->exec('cd /home/'.$gs_login.'/;sudo -u '.$gs_login.' rm screenlog.0');
     $ssh->exec('cd /home/'.$gs_login.'/;sudo -u '.$gs_login.' screen -A -m -d -L -S game'.$gs_login.' '.$name_internal.' ' .$parameter.'');
  }

  $deadline = strtotime('+4 minutes', time());
  $is_running = 2; $running = 1;
  $stmt = $mysqli->prepare("UPDATE gameservers SET is_running = ?,running = ?,deadline = ?  WHERE id = ?");
  if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
  $rc = $stmt->bind_param('iiii',$is_running,$running,$deadline,$gs_select);
  if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
  $rc = $stmt->execute();
  if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
  $stmt->close();

}

function isSecure() {
  return
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || $_SERVER['SERVER_PORT'] == 443;
}

function check_blocked_ip($ip_forward,$ip_remote) {
  global $mysqli;
  $time = time();
  $query = "SELECT `id` FROM `blacklist` WHERE (ip_forward=? OR ip_remote = ?) AND timestamp_expires > ? ";

  if ($stmt = $mysqli->prepare($query)){

          $stmt->bind_param("ssi", $ip_forward,$ip_remote,$time);

          if($stmt->execute()){
              $stmt->store_result();

              $check= "";
              $stmt->bind_result($check);
              $stmt->fetch();

              if ($stmt->num_rows >= 3){
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


 ?>
