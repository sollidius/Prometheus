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
        $status = $ssh->exec("ps -ef | grep -i install".$row[1]." | grep -v grep; echo $?");
        if ($status == 1) {

          $stmt = $mysqli->prepare("DELETE FROM jobs WHERE id = ?");
          $stmt->bind_param('i', $row[2]);
          $stmt->execute();
          $stmt->close();

        }
       }
    }

    /* free result set */
    $result->close();
}


 ?>
