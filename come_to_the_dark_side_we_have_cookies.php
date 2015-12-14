<?php

include 'pages/functions.php';
include('components/GameQ-2/GameQ.php');
set_include_path('components/phpseclib');
include('Net/SSH2.php');

function ask_steam_for_cookies($appid) {

  $cookies = file_get_contents('https://api.steampowered.com/ISteamApps/UpToDateCheck/v1?appid='.$appid.'&version=1&format=json');
  $cookies = json_decode($cookies,true);

  foreach ($cookies as $value) {
    if (is_array($value)) {
     if ($value['success'] == 1) {
       return $value['required_version'];
     }
    }
  }
}

$query = "SELECT id,type,appid,name,name_internal,app_set_config,type_name FROM templates ORDER by id ASC";

if ($result = $mysqli->query($query)) {

   /* fetch object array */
   while ($row = $result->fetch_assoc()) {

     //Check if Game is in Use, before asking steampowered
     if (check_game_in_use_id($row["id"]) AND $row["type"] == "steamcmd") {
       if ($row['appid'] != 0) {
         //So, we gonna ask Steam for some information about the cookies
         sleep(1);
         $answerz = ask_steam_for_cookies($row["appid"]);

         //Check if we have something outdated
         $quary = "SELECT version,dedi_id,template_id,id FROM dedicated_games WHERE template_id = ".$row["id"]." ORDER by id ASC";
         if ($result2 = $mysqli->query($quary)) {
            /* fetch object array */
            while ($row2 = $result2->fetch_assoc()) {
              if ($answerz > $row2['version']) {
                echo "Outdated<br>";

                //Get Dedicated Info
                $stmt = $mysqli->prepare("SELECT ip,port,user,password,name FROM dedicated WHERE id = ?");
                $stmt->bind_param('i', $row2['dedi_id']);
                $stmt->execute();
                $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password,$dedi_name);
                $stmt->fetch();
                $stmt->close();

                $ssh = new Net_SSH2($dedi_ip,$dedi_port);
                 if (!$ssh->login($dedi_login, $dedi_password)) {
                   msg_error('Login failed');
                   //exit;
                 } else {

                   $db_app_set_config = $row["app_set_config"];
                   if ($db_app_set_config == "") {
                       $ssh->exec('cd /home/'.$dedi_login.'/templates/'.$row["name"] . ';rm steam.log;/home/'.$dedi_login.'/templates/'.$row["name"].'/steamcmd.sh +force_install_dir /home/'.$dedi_login.'/templates/'.$row["name"].'/game  +login anonymous +app_update '.$row["type_name"].' validate +quit >> /home/'.$dedi_login.'/templates/'.$row["name"].'/steam.log &');
                  } elseif ($db_app_set_config == "needed") {
                        $ssh->exec('cd /home/'.$dedi_login.'/templates/'.$db_game_name . ';rm steam.log;/home/'.$user.'/templates/'.$db_game_name.'/steamcmd.sh +force_install_dir /home/'.$user.'/templates/'.$db_game_name.'/game  +login anonymous +app_update '.$db_type_name.' validate +quit >> /home/'.$user.'/templates/'.$db_game_name.'/steam.log &');
                   } elseif ($db_app_set_config != "") {
                       $ssh->exec('cd /home/'.$dedi_login.'/templates/'.$db_game_name . ';rm steam.log;/home/'.$dedi_login.'/templates/'.$row["name"].'/steamcmd.sh +force_install_dir /home/'.$dedi_login.'/templates/'.$row["name"].'/game  +login anonymous +app_set_config '.$row["type_name"].' mod '.$db_app_set_config.' +app_update '.$row["type_name"].' validate +quit >> /home/'.$dedi_login.'/templates/'.$row_2["name"].'/steam.log &');
                   }

                   $template = "template_update"; $zero = 0;
                   $stmt = $mysqli->prepare("INSERT INTO jobs(template_id,dedicated_id,type,type_id) VALUES (?, ?, ?, ?)");
                   $stmt->bind_param('iisi', $row["id"], $row2['dedi_id'],$template,$zero);
                   $stmt->execute();
                   $stmt->close();

                   $stmt = $mysqli->prepare("UPDATE dedicated_games SET version = ? WHERE id = ?");
                   $stmt->bind_param('ii', $answerz,$row2['id']);
                   $stmt->execute();
                   $stmt->close();

                   event_add(4,"Das Template ".$row['name']. " auf dem Rootserver ".$dedi_name. " wird aktualisiert");


                 }
              }
            }
            /* free result set */
            $result2->close();
         }
       }
     }
   }
   /* free result set */
   $result->close();
 }
?>
