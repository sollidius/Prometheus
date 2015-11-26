<?php

include 'pages/functions.php';
include('components/GameQ-2/GameQ.php');
set_include_path('components/phpseclib');
include('Net/SSH2.php');

function check_gs($servers) {

  $gq = new GameQ(); // or $gq = GameQ::factory();
  $gq->setOption('timeout', 5); // Seconds
  $gq->setOption('debug', TRUE);
  $gq->setFilter('normalise');
  $gq->addServers($servers);

  $results = $gq->requestData();
  return $results;

}

$wi_id = 1;
$stmt = $mysqli->prepare("SELECT log_gs_cleanup,gs_check_crash FROM wi_settings WHERE id = ?");
$stmt->bind_param('i', $wi_id);
$stmt->execute();
$stmt->bind_result($db_log_gs_cleanup,$gs_check_crash);
$stmt->fetch();
$stmt->close();


$query = "SELECT dedicated_id,type_id,id,template_id,type FROM jobs ORDER by id";

if ($result = $mysqli->query($query)) {

    /* fetch object array */
    while ($row = $result->fetch_row()) {

      $stmtz = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
      $stmtz->bind_param('i', $row[0]);
      $stmtz->execute();
      $stmtz->bind_result($ip,$port,$user,$password);
      $stmtz->fetch();
      $stmtz->close();

      $ssh = new Net_SSH2($ip,$port);
       if (!$ssh->login($user, $password)) {
         exit;
       } else {
         if ($row[4] == "template") {
           $status = $ssh->exec('cat /home/'.$user.'/templates/'.$row[1].'/steam.log  | grep "state is 0x[0-9][0-9][0-9] after update job" ; echo $?');
           if ($status == 1) {
               $status = $ssh->exec('cat /home/'.$user.'/templates/'.$row[1].'/steam.log  | grep "Success!" ; echo $?');
               if ($status != 1) {

                 $stmt = $mysqli->prepare("DELETE FROM jobs WHERE id = ?");
                 $stmt->bind_param('i', $row[2]);
                 $stmt->execute();
                 $stmt->close();

                 $status = 1; $status_text = "Installed";
                 $stmt = $mysqli->prepare("INSERT INTO dedicated_games(dedi_id,template_id,status,status_text) VALUES (?, ?, ?, ?)");
                 $stmt->bind_param('iiis', $row[0],$row[3],$status,$status_text);
                 $stmt->execute();
                 $stmt->close();

               }
           } elseif ($status != 1) {

             $stmt = $mysqli->prepare("SELECT type,type_name FROM templates WHERE name = ?");
             $stmt->bind_param('s', $row[1]);
             $stmt->execute();
             $stmt->bind_result($db_type,$db_type_name);
             $stmt->fetch();
             $stmt->close();

             $ssh->exec('cd /home/'.$user.'/templates/'.$row[1] . ';rm steam.log;/home/'.$user.'/templates/'.$row[1].'/steamcmd.sh +force_install_dir /home/'.$user.'/templates/'.$row[1].'/game  +login anonymous +app_update '.$db_type_name.' validate +quit >> /home/'.$user.'/templates/'.$row[1].'/steam.log &');

           }
         } elseif ($row[4] == "image") {

          echo  $status = $ssh->exec("ps -ef | grep -i image".$row[1]." | grep -v grep; echo $?");
           if ($status == 1) {

             $stmt = $mysqli->prepare("DELETE FROM jobs WHERE id = ?");
             $stmt->bind_param('i', $row[2]);
             $stmt->execute();
             $stmt->close();

             $status = 1; $status_text = "Installed";
             $stmt = $mysqli->prepare("INSERT INTO dedicated_games(dedi_id,template_id,status,status_text) VALUES (?, ?, ?, ?)");
             $stmt->bind_param('iiis', $row[0],$row[3],$status,$status_text);
             $stmt->execute();
             $stmt->close();

           }
         }
       }
    }
    
    /* free result set */
    $result->close();
}

$query = "SELECT gs_login,dedi_id,status,id,status_update,game,ip,port,running,is_running,deadline FROM gameservers ORDER by id";

if ($result = $mysqli->query($query)) {

    /* fetch object array */
    while ($row = $result->fetch_row()) {

      $stmt = $mysqli->prepare("SELECT type,type_name,gameq FROM templates WHERE name = ?");
      $stmt->bind_param('s', $row[5]);
      $stmt->execute();
      $stmt->bind_result($db_type,$db_type_name,$gameq);
      $stmt->fetch();
      $stmt->close();

      $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
      $stmt->bind_param('i', $row[1]);
      $stmt->execute();
      $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password);
      $stmt->fetch();
      $stmt->close();

      //Status
      $servers[1]["type"] = "unknown";
      $servers[1]["type"] = $gameq;
      $servers[1]["host"] = $row[6] .':'.$row[7];
      $servers[1]["id"] = "serv";
      if ($servers[1]["type"] != "unknown") {
        $results = check_gs($servers);
        foreach ($results as &$element) {
            if ($element["gq_online"] == 1) {

              $running = 1;
              $stmt = $mysqli->prepare("UPDATE gameservers SET is_running = ?  WHERE id = ?");
              $stmt->bind_param('ii',$running,$row[3]);
              $stmt->execute();
              $stmt->close();

            } else {

              $running = 0;
              $stmt = $mysqli->prepare("UPDATE gameservers SET is_running = ?  WHERE id = ?");
              $stmt->bind_param('ii',$running,$row[3]);
              $stmt->execute();
              $stmt->close();

            }
        }
      }

      if ($row[2] == 1 AND $row[4] == 0) {

      $ssh = new Net_SSH2($dedi_ip,$dedi_port);
       if (!$ssh->login($dedi_login, $dedi_password)) {
         exit;
       } else {
         $status = $ssh->exec("ps -ef | grep -i cp".$row[0]." | grep -v grep; echo $?");
         if ($status == 1) {

           $status = 0;
           $stmt = $mysqli->prepare("UPDATE gameservers SET status = ?  WHERE id = ?");
           $stmt->bind_param('ii',$status,$row[3]);
           $stmt->execute();
           $stmt->close();

         }
      }
      } elseif ($row[2] == 1 AND $row[4] == 1) {

        $ssh = new Net_SSH2($dedi_ip,$dedi_port);
         if (!$ssh->login($dedi_login, $dedi_password)) {
           exit;
         } else {

           $status = $ssh->exec('cat /home/'.$row[0].'/game/steam.log  | grep "state is 0x402 after update job" ; echo $?');
           if ($status == 1) {
               $status = $ssh->exec('cat /home/'.$row[0].'/game/steam.log  | grep "Success!" ; echo $?');
               if ($status != 1) {

                 echo "Updated finished....";

                 $status = 0;
                 $stmt = $mysqli->prepare("UPDATE gameservers SET status = ?,status_update = ?  WHERE id = ?");
                 $stmt->bind_param('iii',$status,$status,$row[3]);
                 $stmt->execute();
                 $stmt->close();

               }
           } elseif ($status != 1) {

             $stmt = $mysqli->prepare("SELECT type,type_name FROM templates WHERE name = ?");
             $stmt->bind_param('s', $row[5]);
             $stmt->execute();
             $stmt->bind_result($db_type,$db_type_name);
             $stmt->fetch();
             $stmt->close();

             $ssh->exec('sudo rm /home/'.$row[0].'/game/steam.log');
             $ssh->exec('sudo touch /home/'.$row[0].'/game/steam.log');
             $ssh->exec('sudo chmod 777 /home/'.$row[0].'/game/steam.log');
             $ssh->exec('sudo -u '.$row[0].' /home/'.$row[0].'/steamcmd.sh +force_install_dir /home/'.$row[0].'/game  +login anonymous +app_update '.$db_type_name.' validate +quit >> /home/'.$row[0].'/game/steam.log &');

           }
        }
      } elseif ($row[8] == 1 AND $row[9] == 0 AND time() > $row[10] AND $gs_check_crash == 1) {
        //If running but is_running false = Restart

        $stmt = $mysqli->prepare("SELECT ip,game,gs_login,slots,map,port,parameter,dedi_id FROM gameservers WHERE id = ?");
        $stmt->bind_param('i', $row[3]);
        $stmt->execute();
        $stmt->bind_result($ip,$game,$gs_login,$slots,$map,$port,$parameter,$dedi_id);
        $stmt->fetch();
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT type,type_name,gameq FROM templates WHERE name = ?");
        $stmt->bind_param('s', $game);
        $stmt->execute();
        $stmt->bind_result($db_type,$db_type_name,$gameq);
        $stmt->fetch();
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT name_internal,type FROM templates WHERE name = ?");
        $stmt->bind_param('s', $game);
        $stmt->execute();
        $stmt->bind_result($name_internal,$type);
        $stmt->fetch();
        $stmt->close();

        $ssh = new Net_SSH2($dedi_ip,$dedi_port);
         if (!$ssh->login($dedi_login, $dedi_password)) {
           exit;
         } else {
           $ssh->exec('sudo pkill -u '.$gs_login);
           if ($type == "steamcmd") {
               $ssh->exec('cd /home/'.$gs_login.'/game;sudo -u '.$gs_login.' rm screenlog.0');
               $ssh->exec('cd /home/'.$gs_login.'/game;sudo -u '.$gs_login.' screen -A -m -d -L -S game'.$gs_login.' /home/'.$gs_login.'/game/srcds_run -game '.$name_internal.' -port '.$port.' +map '.$map.' -maxplayers '.$slots .' ' .$parameter);
           } elseif ($type == "image") {
             if ($gameq == "minecraft") {
               $server_port = str_replace("server-port=","",$ssh->exec('cat /home/'.$gs_login.'/server.properties | grep "server-port="'));
               $server_port = preg_replace("/\s+/", "", $server_port);
               $query_port = str_replace("query.port=","",$ssh->exec('cat /home/'.$gs_login.'/server.properties | grep "query.port="'));
               $query_port = preg_replace("/\s+/", "", $query_port);
               $max_players = str_replace("max-players=","",$ssh->exec('cat /home/'.$gs_login.'/server.properties | grep "max-players="'));
               $max_players = preg_replace("/\s+/", "", $max_players);
               echo $ssh->exec("sudo -u ".$gs_login." find /home/".$gs_login."/server.properties -type f -exec sed -i 's/server-port=".$server_port."/server-port=".$port."/g' {} \;");
               echo $ssh->exec("sudo -u ".$gs_login." find /home/".$gs_login."/server.properties -type f -exec sed -i 's/query.port=".$query_port."/query.port=".$port."/g' {} \;");
               echo $ssh->exec("sudo -u ".$gs_login." find /home/".$gs_login."/server.properties -type f -exec sed -i 's/max-players=".$max_players."/max-players=".$slots."/g' {} \;");
             }
              $ssh->exec('cd /home/'.$gs_login.'/;sudo -u '.$gs_login.' rm screenlog.0');
              $ssh->exec('cd /home/'.$gs_login.'/;sudo -u '.$gs_login.' screen -A -m -d -L -S game'.$gs_login.' '.$name_internal.' ' .$parameter.'');
           }

           $deadline = strtotime('+4 minutes', time());
           $is_running = 2; $running = 1;
           $stmt = $mysqli->prepare("UPDATE gameservers SET is_running = ?,running = ?,deadline = ?  WHERE id = ?");
           $stmt->bind_param('iiii',$is_running,$running,$deadline,$row[3]);
           $stmt->execute();
           $stmt->close();

           event_add(7,"Der Gameserver ".$ip.":".$port." ist abgestürtzt und wurde neu gestartet.");
         }
      }
      //Log Cleanup
      if ($db_log_gs_cleanup == 1) {
        $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");

        $ssh = new Net_SSH2($dedi_ip,$dedi_port);
         if (!$ssh->login($dedi_login, $dedi_password)) {
           exit;
         } else {
           if ($db_type == "steamcmd") {
             $ssh->exec("sudo -u ".$row[0]." bash -c 'tail -n 150 /home/".$row[0]."/game/screenlog.0 > /home/".$row[0]."/game/screenlog.tmp'");
             $ssh->exec("sudo -u ".$row[0]." bash -c 'cat /home/".$row[0]."/game/screenlog.tmp > /home/".$row[0]."/game/screenlog.0'");
             $ssh->exec("sudo -u ".$row[0]." bash -c 'rm /home/".$row[0]."/game/screenlog.tmp'");
           } elseif ($db_type == "image") {
             $ssh->exec("sudo -u ".$row[0]." bash -c 'tail -n 150 /home/".$row[0]."/screenlog.0 > /home/".$row[0]."/screenlog.tmp'");
             $ssh->exec("sudo -u ".$row[0]." bash -c 'cat /home/".$row[0]."/screenlog.tmp > /home/".$row[0]."/screenlog.0'");
             $ssh->exec("sudo -u ".$row[0]." bash -c 'rm /home/".$row[0]."/screenlog.tmp'");
           }
       }
     }
    }

    /* free result set */
    $result->close();
}
//Events Cleanup

$query = "SELECT id,type,message,timestamp FROM events ORDER by id";

if ($result = $mysqli->query($query)) {

    /* fetch object array */
    while ($row = $result->fetch_row()) {
      $time = time();
      $delete = strtotime('+1 day', $row[3]);
      if ($time > $delete) {
        $stmt = $mysqli->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param('i', $row[0]);
        $stmt->execute();
        $stmt->close();
      }
    }
    /* free result set */
    $result->close();
}

$time = time(); $id = 1;

$stmt = $mysqli->prepare("UPDATE wi_settings SET cronjob_lastrun = ?  WHERE id = ?");
$stmt->bind_param('ii',$time,$id);
$stmt->execute();
$stmt->close();

echo "ok";

 ?>
