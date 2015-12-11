<?php

include 'pages/functions.php';
include('components/GameQ-2/GameQ.php');
set_include_path('components/phpseclib');
include('Net/SSH2.php');

function check_gs($servers) {

  $fp = fsockopen("www.google.com", 80, $errno, $errstr, 5);
  if ($fp) {
    $gq = new GameQ(); // or $gq = GameQ::factory();
    $gq->setOption('timeout', 5); // Seconds
    $gq->setOption('debug', TRUE);
    $gq->setFilter('normalise');
    $gq->addServers($servers);

    $results = $gq->requestData();
    if ($results['serv']["gq_online"] == 1) {
          return $results;
    } else {
      $gq = new GameQ(); // or $gq = GameQ::factory();
      $gq->setOption('timeout', 5); // Seconds
      $gq->setOption('debug', TRUE);
      $gq->setFilter('normalise');
      $gq->addServers($servers);

      $results = $gq->requestData();
      return $results;
    }
  }
}

$wi_id = 1;
$stmt = $mysqli->prepare("SELECT log_gs_cleanup,gs_check_crash,gs_check_cpu,gs_check_cpu_msg FROM wi_settings WHERE id = ?");
$stmt->bind_param('i', $wi_id);
$stmt->execute();
$stmt->bind_result($db_log_gs_cleanup,$gs_check_crash,$gs_check_cpu,$gs_check_cpu_msg);
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
         //exit;
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

           $status = $ssh->exec("ps -ef | grep -i image".$row[1]." | grep -v grep; echo $?");
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
         } elseif ($row[4] == "addon") {

           $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
           $stmt->bind_param('i', $row[0]);
           $stmt->execute();
           $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password);
           $stmt->fetch();
           $stmt->close();

           $stmt = $mysqli->prepare("SELECT ip,game,gs_login,slots,map,port,parameter,dedi_id FROM gameservers WHERE id = ?");
           $stmt->bind_param('i', $row[3]);
           $stmt->execute();
           $stmt->bind_result($ip,$game,$gs_login,$slots,$map,$port,$parameter,$dedi_id);
           $stmt->fetch();
           $stmt->close();

           $ssh = new Net_SSH2($dedi_ip,$dedi_port);
            if (!$ssh->login($dedi_login, $dedi_password)) {
              //exit;
            } else {
              $status = $ssh->exec("ps -ef | grep -i addon".$gs_login." | grep -v grep; echo $?");
              if ($status == 1) {

                $stmt = $mysqli->prepare("DELETE FROM jobs WHERE id = ?");
                $stmt->bind_param('i', $row[2]);
                $stmt->execute();
                $stmt->close();

                $status = 1; $status_text = "Installed";
                $stmt = $mysqli->prepare("INSERT INTO addons_installed(dedi_id,addons_id,gs_id,status,status_text) VALUES (?, ?, ?, ?, ?)");
                if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
                $rc = $stmt->bind_param('iiiis', $row[0],$row[1],$row[3],$status,$status_text);
                  if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
                $rc = $stmt->execute();
                  if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
                $stmt->close();

              }
            }
         }
       }
    }

    /* free result set */
    $result->close();
}

$query = "SELECT gs_login,dedi_id,status,id,status_update,game,ip,port,running,is_running,deadline,restart,restart_time FROM gameservers ORDER by id";

if ($result = $mysqli->query($query)) {

    /* fetch object array */
    while ($row = $result->fetch_row()) {

      $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
      $stmt->bind_param('i', $row[1]);
      $stmt->execute();
      $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password);
      $stmt->fetch();
      $stmt->close();

      $stmt = $mysqli->prepare("SELECT ip,game,gs_login,slots,map,port,parameter,dedi_id FROM gameservers WHERE id = ?");
      $stmt->bind_param('i', $row[3]);
      $stmt->execute();
      $stmt->bind_result($ip,$game,$gs_login,$slots,$map,$port,$parameter,$dedi_id);
      $stmt->fetch();
      $stmt->close();

      $stmt = $mysqli->prepare("SELECT type,type_name,gameq,name_internal,type FROM templates WHERE name = ?");
      $stmt->bind_param('s', $row[5]);
      $stmt->execute();
      $stmt->bind_result($db_type,$db_type_name,$gameq,$name_internal,$type);
      $stmt->fetch();
      $stmt->close();

      //Status
      $servers[1]["type"] = "unknown";
      $servers[1]["type"] = $gameq;
      $servers[1]["host"] = $row[6] .':'.$row[7];
      $servers[1]["id"] = "serv";
      $current_players = 1000; $current_status = "unknown"; $current_maxplayers = "0";
      if ($servers[1]["type"] != "unknown") {
        $results = check_gs($servers);
        //print_r($results);
        //print "<pre>";
        //print_r($results);
        //print "</pre>";
        //exit;
            $current_status = "known";
            $results['serv']["gq_online"] = (int)$results['serv']["gq_online"];
            if ($results['serv']["gq_online"] === 1) {

              $current_players = $results['serv']['gq_numplayers'];
              $current_maxplayers = $results['serv']['max_players'];
              $running = 1;
              echo $current_players ."/".$current_maxplayers;
              echo "<br>";
              $stmt = $mysqli->prepare("UPDATE gameservers SET is_running = ?,player_online = ?  WHERE id = ?");
              $stmt->bind_param('iii',$running,$results['serv']['gq_numplayers'],$row[3]);
              $rc = $stmt->execute();
              $stmt->close();

              if ($current_players === 0 AND $gs_check_crash == 1) {

                $ssh = new Net_SSH2($dedi_ip,$dedi_port);
                 if (!$ssh->login($dedi_login, $dedi_password)) {
                   //exit;
                 } else {

                    $load = $ssh->exec("sudo -u ".$gs_login." top -b -n 1 -u ".$gs_login." | awk 'NR>7 { sum += $9; } END { print sum; }'");
                    if ($load > 19) {
                      gameserver_restart($type,$ssh,$gs_login,$name_internal,$port,$ip,$map,$slots,$parameter,$gameq,$row[3]);
                      event_add(5,"Der Gameserver ".$ip.":".$port." wurde wegen hoher CPU Last neugestartet. (".$current_status."-".$current_players."/".$current_maxplayers.")");
                    }
                 }
              } elseif ($current_players > 0 AND $gs_check_cpu_msg == 1 AND $current_players != 1000) {

                $ssh = new Net_SSH2($dedi_ip,$dedi_port);
                 if (!$ssh->login($dedi_login, $dedi_password)) {
                   //exit;
                 } else {
                    $load = $ssh->exec("sudo -u ".$gs_login." top -b -n 1 -u ".$gs_login." | awk 'NR>7 { sum += $9; } END { print sum; }'");
                    if ($load > 90) {
                      $cmd = "say The CPU load is dam high! Please clean your stuff up.";
                      $ssh->exec('sudo -u '.$gs_login.' screen -S "game'.$gs_login.'" -X stuff "'.$cmd.'\n"');
                    }
                 }
              }

            } elseif ($results['serv']["gq_online"] === 0) {

              $current_players = 0;
              $current_maxplayers = 0;
              echo $current_players ."/".$current_maxplayers;
              echo "<br>";
              $running = 0;
              $stmt = $mysqli->prepare("UPDATE gameservers SET is_running = ?,player_online = ?  WHERE id = ?");
              $stmt->bind_param('iii',$running,$results['serv']['gq_numplayers'],$row[3]);
              $stmt->execute();
              $stmt->close();

            }
      }

      if ($row[2] == 1 AND $row[4] == 0) {

      $ssh = new Net_SSH2($dedi_ip,$dedi_port);
       if (!$ssh->login($dedi_login, $dedi_password)) {
         //exit;
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
           //exit;
         } else {

           $status = $ssh->exec('cat /home/'.$row[0].'/game/steam.log  | grep "state is 0x402 after update job" ; echo $?');
           if ($status == 1) {
               $status = $ssh->exec('cat /home/'.$row[0].'/game/steam.log  | grep "Success!" ; echo $?');
               if ($status != 1) {

                 //echo "Updated finished....";
                 event_add(4,"Der Gameserver ".$ip.":".$port." wurde aktualisiert.");

                 $status = 0;
                 $stmt = $mysqli->prepare("UPDATE gameservers SET status = ?,status_update = ?  WHERE id = ?");
                 $stmt->bind_param('iii',$status,$status,$row[3]);
                 $stmt->execute();
                 $stmt->close();

               }
           } elseif ($status != 1) {

             $ssh->exec('sudo rm /home/'.$row[0].'/game/steam.log');
             $ssh->exec('sudo touch /home/'.$row[0].'/game/steam.log');
             $ssh->exec('sudo chmod 777 /home/'.$row[0].'/game/steam.log');
             $ssh->exec('sudo -u '.$row[0].' /home/'.$row[0].'/steamcmd.sh +force_install_dir /home/'.$row[0].'/game  +login anonymous +app_update '.$db_type_name.' validate +quit >> /home/'.$row[0].'/game/steam.log &');

           }
        }
      } elseif ($row[8] == 1 AND $row[9] == 0 AND time() > $row[10] AND $gs_check_crash == 1) {
        //If running but is_running false = Restart

        $ssh = new Net_SSH2($dedi_ip,$dedi_port);
         if (!$ssh->login($dedi_login, $dedi_password)) {
           //exit;
         } else {
            gameserver_restart($type,$ssh,$gs_login,$name_internal,$port,$ip,$map,$slots,$parameter,$gameq,$row[3]);
            event_add(7,"Der Gameserver ".$ip.":".$port." ist abgestürtzt und wurde neu gestartet.");
         }
      }
      //Log Cleanup
      if ($db_log_gs_cleanup == 1) {
        $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");

        $ssh = new Net_SSH2($dedi_ip,$dedi_port);
         if (!$ssh->login($dedi_login, $dedi_password)) {
           //exit;
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
     //Daily Restart
     if ($row[11] == 1 AND date('H') == $row['12'] AND date('i') == 5) {
       $ssh = new Net_SSH2($dedi_ip,$dedi_port);
        if (!$ssh->login($dedi_login, $dedi_password)) {
          //exit;
        } else {
       gameserver_restart($type,$ssh,$gs_login,$name_internal,$port,$ip,$map,$slots,$parameter,$gameq,$row[3]);
       event_add(5,"Der Gameserver wurde neugestartet.");
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
//Bans Cleanup

$query = "SELECT id,timestamp_expires FROM blacklist ORDER by id";

if ($result = $mysqli->query($query)) {

    /* fetch object array */
    while ($row = $result->fetch_row()) {
      $time = time();
      $delete = strtotime('+1 day', $row[1]);
      if ($time > $delete) {
        $stmt = $mysqli->prepare("DELETE FROM blacklist WHERE id = ?");
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
