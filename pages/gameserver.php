<?php
//header
$title = "Gameserver";
include 'header.php';
include 'functions.php';
set_include_path('components/phpseclib');
include('Net/SSH2.php');

session_start();

$db_rank = 2;
//Load user Data from DB
$stmt = $mysqli->prepare("SELECT rank,id FROM users WHERE id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($db_rank,$db_id);
$stmt->fetch();
$stmt->close();

if ($_SESSION['login'] == 1) {





?>
<div id="wrapper">

      <?php include 'navbar.php'; ?>

       <div id="page-wrapper">
           <div class="row">
               <div class="col-lg-12">
                   <h1 class="page-header"><?php echo $title; ?></h1>
               </div>
               <!-- /.col-lg-12 -->
           </div>
           <div class="row">
               <div class="col-lg-8">
                 <?php
                  if ($_SERVER['REQUEST_METHOD'] == 'POST') {


                    $query = "SELECT id FROM gameservers ORDER by id";

                    if ($result = $mysqli->query($query)) {

                        /* fetch object array */
                        while ($row = $result->fetch_row()) {
                          if (isset($_POST['start-'.$row[0]])) {
                            $gs_select = $row[0];
                            $_POST['gstart'] = 1;
                          }
                          if (isset($_POST['stop-'.$row[0]])) {
                            $gs_select = $row[0];
                            $_POST['gstop'] = 1;
                          }
                        }

                        /* free result set */
                        $result->close();
                    }


                     if (isset($_POST['confirm'])) {

                       $error = false;
                       $port = $_POST['port']; $slots = $_POST['slots'];
                       $dedicated = $_POST['dedicated']; $type = $_POST['type'];
                       $map = $_POST['map'];

                       if ($error == false) {

                         $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE name = ?");
                         $stmt->bind_param('i', $dedicated);
                         $stmt->execute();
                         $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password);
                         $stmt->fetch();
                         $stmt->close();

                         $stmt = $mysqli->prepare("SELECT name,u_count FROM users WHERE id = ?");
                         $stmt->bind_param('i', $_SESSION['user_id']);
                         $stmt->execute();
                         $stmt->bind_result($user_name,$user_u_count);
                         $stmt->fetch();
                         $stmt->close();

                         $gs_login = $user_name . "-" . $user_u_count;
                         $gs_password = "123456";


                         $ssh = new Net_SSH2($dedi_ip,$dedi_port);
                          if (!$ssh->login($dedi_login, $dedi_password)) {
                            echo '
                            <div class="alert alert-danger" role="alert">
                              <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                              <span class="sr-only">Error:</span>
                              Login failed
                            </div>';
                            exit;
                          } else {

                            $ssh->exec('sudo useradd -m -d /home/'.$gs_login.' -s /bin/bash '.$gs_login);
                            $ssh->enablePTY();
                            $ssh->exec('sudo passwd '.$gs_login);
                            $ssh->read('Enter new UNIX password:');
                            $ssh->write($gs_password . "\n");
                            $ssh->read('Retype new UNIX password:');
                            $ssh->write($gs_password . "\n");
                            $ssh->read('passwd: password updated successfully');
                            $ssh->disablePTY();
                            $ssh->read('[prompt]');
                            $copy = "screen -amds cp".$gs_login." bash -c 'sudo cp -R /home/".$dedi_login."/templates/".$type."/* /home/".$gs_login.";sudo cp -R /home/".$dedi_login."/templates/".$type."/linux32/libstdc++.so.6 /home/".$gs_login."/game/bin;sudo chown -R ".$gs_login.":".$gs_login." /home/".$gs_login.";chmod a-w /home/".$gs_login."'";
                            $ssh->exec($copy);

                            $stmt = $mysqli->prepare("INSERT INTO gameservers(user_id,user_name,game,slots,ip,port,gs_login,gs_password,map) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param('issisisss', $_SESSION['user_id'],$user_name,$type,$slots,$dedi_ip,$port,$gs_login,$gs_password,$map);
                            $stmt->execute();
                            $stmt->close();

                            $user_u_count = $user_u_count +1;

                            $stmt = $mysqli->prepare("UPDATE users SET u_count = ? WHERE id = ?");
                            $stmt->bind_param('ii', $user_u_count, $_SESSION['user_id']);
                            $stmt->execute();
                            $stmt->close();

                            echo '
                            <div class="alert alert-success" role="alert">
                              <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                              <span class="sr-only">Error:</span>
                              Der Gameserver wird installiert, das kann etwas dauern
                            </div>';

                          }

                     } else {

                       echo '
                       <div class="alert alert-danger" role="alert">
                         <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                         <span class="sr-only">Error:</span>
                         Something went wrong
                       </div>';

                     }

                   } elseif (isset($_POST['gstop'])) {

                     $stmt = $mysqli->prepare("SELECT ip,game,gs_login,slots FROM gameservers WHERE id = ?");
                     $stmt->bind_param('i', $gs_select);
                     $stmt->execute();
                     $stmt->bind_result($ip,$game,$gs_login,$slots);
                     $stmt->fetch();
                     $stmt->close();

                     $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE ip = ?");
                     $stmt->bind_param('s', $ip);
                     $stmt->execute();
                     $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password);
                     $stmt->fetch();
                     $stmt->close();

                     $ssh = new Net_SSH2($dedi_ip,$dedi_port);
                      if (!$ssh->login($dedi_login, $dedi_password)) {
                        echo '
                        <div class="alert alert-danger" role="alert">
                          <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                          <span class="sr-only">Error:</span>
                          Login failed
                        </div>';
                        exit;
                      } else {
                        $ssh->exec('sudo pkill -u '.$gs_login);
                        echo '<meta http-equiv="refresh" content="2; URL=index.php?page=gameserver">';
                        echo '
                        <div class="alert alert-success" role="alert">
                          <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                          <span class="sr-only">Success:</span>
                          Done
                        </div>';
                      }


                   } elseif (isset($_POST['gstart'])) {

                      $stmt = $mysqli->prepare("SELECT ip,game,gs_login,slots,map,port FROM gameservers WHERE id = ?");
                      $stmt->bind_param('i', $gs_select);
                      $stmt->execute();
                      $stmt->bind_result($ip,$game,$gs_login,$slots,$map,$port);
                      $stmt->fetch();
                      $stmt->close();

                      $stmt = $mysqli->prepare("SELECT name_internal FROM templates WHERE name = ?");
                      $stmt->bind_param('s', $game);
                      $stmt->execute();
                      $stmt->bind_result($name_internal);
                      $stmt->fetch();
                      $stmt->close();

                      $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE ip = ?");
                      $stmt->bind_param('s', $ip);
                      $stmt->execute();
                      $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password);
                      $stmt->fetch();
                      $stmt->close();

                      $ssh = new Net_SSH2($dedi_ip,$dedi_port);
                       if (!$ssh->login($dedi_login, $dedi_password)) {
                         echo '
                         <div class="alert alert-danger" role="alert">
                           <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                           <span class="sr-only">Error:</span>
                           Login failed
                         </div>';
                         exit;
                       } else {
                         $ssh->exec('sudo pkill -u '.$gs_login);
                         $ssh->exec('sudo -u '.$gs_login.' screen -adms game /home/'.$gs_login.'/game/srcds_run -game '.$name_internal.' -port '.$port.' +map '.$map.' -maxplayers '.$slots);
                         echo '<meta http-equiv="refresh" content="2; URL=index.php?page=gameserver">';
                         echo '
                         <div class="alert alert-success" role="alert">
                           <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                           <span class="sr-only">Success:</span>
                           Done
                         </div>';
                       }

                    } else {

                  ?>

                  <form class="form-horizontal" action="index.php?page=gameserver" method="post">
                    <div class="form-group">
                      <label class="control-label col-sm-2">Type/Root:</label>
                      <div class="col-sm-4">
                        <select class="form-control" name="type">
                        <?php
                        $query = "SELECT name FROM templates ORDER by id";

                         if ($stmt = $mysqli->prepare($query)) {
                             $stmt->execute();
                             $stmt->bind_result($db_name);

                             while ($stmt->fetch()) {
                               echo "<option>" . $db_name . "</option>";
                             }
                             $stmt->close();
                         }  ?>
                        </select>
                      </div>
                      <div class="col-sm-4">
                        <select class="form-control" name="dedicated">
                        <?php
                        $query = "SELECT name FROM dedicated ORDER by id";

                         if ($stmt = $mysqli->prepare($query)) {
                             $stmt->execute();
                             $stmt->bind_result($db_name);

                             while ($stmt->fetch()) {
                               echo "<option>" . $db_name . "</option>";
                             }
                             $stmt->close();
                         }  ?>
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2">Port/Slots:</label>
                      <div class="col-sm-4">
                        <input type="text" class="form-control" name="port" placeholder="27015">
                      </div>
                      <div class="col-sm-4">
                        <input type="text" class="form-control" name="slots" placeholder="14">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2">Map:</label>
                      <div class="col-sm-4">
                        <input type="text" class="form-control" name="map" placeholder="gm_flatgrass">
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" name="confirm" class="btn btn-default">Submit</button>
                      </div>
                    </div>
                  </form>



                  <?php }
                  } else {
                    ?>
                    <form action="index.php?page=gameserver" method="post">
                    <button style="margin-bottom:2px;" type="submit" name="add" class="btn pull-right btn-success">+</button>
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th>Benutzer</th>
                          <th>Game</th>
                          <th>Steuerung</th>
                          <th>IP</th>
                          <th>Port</th>
                          <th>Slots</th>
                          <th>Map</th>
                          <th>Login</th>
                          <th>Passwort</th>
                        </tr>
                      </thead>
                      <tbody>
                     <?php

                     $query = "SELECT user_id, user_name, game, ip, port,slots, gs_login, gs_password, id, map FROM gameservers ORDER by id";

                      if ($stmt = $mysqli->prepare($query)) {
                          $stmt->execute();
                          $stmt->bind_result($db_user_id, $db_user_name,$db_game,$db_ip,$db_port,$db_slots,$db_gs_login,$db_gs_password,$db_gs_id,$db_map);

                          while ($stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>" . $db_user_name . "</td>";
                            echo "<td>" . $db_game . "</td>";
                            echo '<td> <button type="submit" name="start-'.$db_gs_id.'" class="btn btn-success btn-sm">(Re)Start</button> <button type="submit" name="stop-'.$db_gs_id.'" class="btn btn-danger btn-sm">Stop</button>  </td>';
                            echo "<td>" . $db_ip . "</td>";
                            echo "<td>" . $db_port . "</td>";
                            echo "<td>" . $db_slots . "</td>";
                            echo "<td>" . $db_map . "</td>";
                            echo "<td>" . $db_gs_login . "</td>";
                            echo "<td>" . $db_gs_password . "</td>";
                            echo "</tr>";
                          }
                          $stmt->close();
                      }
                      $mysqli->close(); ?>
                      </tbody>
                    </table>
                  </form>
                  <?php }
                 ?>
               </div>
               <!-- /.col-lg-8 -->
               <div class="col-lg-4">





               </div>
               <!-- /.col-lg-4 -->
           </div>
           <!-- /.row -->
       </div>
       <!-- /#page-wrapper -->

   </div>
   <!-- /#wrapper -->


<?php

 } else { header('Location: index.php');}


//Footer
include 'footer.html';
?>
