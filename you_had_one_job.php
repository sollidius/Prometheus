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

$query = "SELECT gs_login,dedi_id,status,id,status_update,game,ip,port FROM gameservers ORDER by id";

if ($result = $mysqli->query($query)) {

    /* fetch object array */
    while ($row = $result->fetch_row()) {

      if ($row[2] == 1 AND $row[4] == 0) {
      $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
      $stmt->bind_param('i', $row[1]);
      $stmt->execute();
      $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password);
      $stmt->fetch();
      $stmt->close();

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

        $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
        $stmt->bind_param('i', $row[1]);
        $stmt->execute();
        $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password);
        $stmt->fetch();
        $stmt->close();


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
      }
      //Log Cleanup
      $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
      $stmt->bind_param('i', $row[1]);
      $stmt->execute();
      $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password);
      $stmt->fetch();
      $stmt->close();

      $ssh = new Net_SSH2($dedi_ip,$dedi_port);
       if (!$ssh->login($dedi_login, $dedi_password)) {
         exit;
       } else {
       $ssh->exec("sudo -u ".$row[0]." bash -c 'tail -n 150 /home/".$row[0]."/game/screenlog.0 > /home/".$row[0]."/game/screenlog.tmp'");
       $ssh->exec("sudo -u ".$row[0]." bash -c 'cat /home/".$row[0]."/game/screenlog.tmp > /home/".$row[0]."/game/screenlog.0'");
       $ssh->exec("sudo -u ".$row[0]." bash -c 'rm /home/".$row[0]."/game/screenlog.tmp'");
       //Status
       $servers[1]["type"] = "unknown";
       $stmt = $mysqli->prepare("SELECT gameq FROM templates WHERE name = ?");
       $stmt->bind_param('i', $row[5]);
       $stmt->execute();
       $stmt->bind_result($gameq);
       $stmt->fetch();
       $stmt->close();
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

echo "ok";

 ?>
