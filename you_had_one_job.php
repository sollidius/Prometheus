<?php

include 'pages/functions.php';
set_include_path('components/phpseclib');
include('Net/SSH2.php');

$query = "SELECT dedicated_id,type_id,id FROM jobs ORDER by id";

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
        $status = $ssh->exec('cat /home/'.$user.'/templates/'.$row[1].'/steam.log  | grep "state is 0x402 after update job" ; echo $?');
        echo $status;
        if ($status == 1) {
            $status = $ssh->exec('cat /home/'.$user.'/templates/'.$row[1].'/steam.log  | grep "Success!" ; echo $?');
            if ($status != 1) {

              $stmt = $mysqli->prepare("DELETE FROM jobs WHERE id = ?");
              $stmt->bind_param('i', $row[2]);
              $stmt->execute();
              $stmt->close();

            }
        } elseif ($status != 1) {

          $stmt = $mysqli->prepare("SELECT type,type_name FROM templates WHERE name = ?");
          $stmt->bind_param('i', $row[1]);
          $stmt->execute();
          $stmt->bind_result($db_type,$db_type_name);
          $stmt->fetch();
          $stmt->close();

          $ssh->exec('cd /home/'.$user.'/templates/'.$row[1] . ';rm steam.log;/home/'.$user.'/templates/'.$row[1].'/steamcmd.sh +force_install_dir /home/'.$user.'/templates/'.$row[1].'/game  +login anonymous +app_update '.$db_type_name.' validate +quit >> /home/'.$user.'/templates/'.$row[1].'/steam.log &');
          
        }
       }
    }

    /* free result set */
    $result->close();
}

$query = "SELECT gs_login,dedi_id,status,id FROM gameservers ORDER by id";

if ($result = $mysqli->query($query)) {

    /* fetch object array */
    while ($row = $result->fetch_row()) {

      if ($row[2] == 1) {
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
    }
    }

    /* free result set */
    $result->close();
}
echo "ok";

 ?>
