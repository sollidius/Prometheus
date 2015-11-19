<?php
//header
$title = "Gameserver";
include 'header.php';
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
               <div class="col-lg-12">
                 <?php
            //      if ($_SERVER['REQUEST_METHOD'] == 'POST') {


                    $query = "SELECT id,status,user_id FROM gameservers ORDER by id";

                    if ($result = $mysqli->query($query)) {

                        /* fetch object array */
                        while ($row = $result->fetch_row()) {
                          if ($page == "gameserver?reinstall-".$row[0] AND $row[1] == 0 AND $row[2] == $_SESSION['user_id'] or $page == "gameserver?reinstall-".$row[0] AND $row[1] == 0 AND $db_rank == 1) {

                            $gs_select = $row[0];

                            $stmt = $mysqli->prepare("SELECT ip,game,gs_login,slots,map,port,parameter,dedi_id FROM gameservers WHERE id = ?");
                            $stmt->bind_param('i', $gs_select);
                            $stmt->execute();
                            $stmt->bind_result($ip,$game,$gs_login,$slots,$map,$port,$parameter,$dedi_id);
                            $stmt->fetch();
                            $stmt->close();

                            $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
                            $stmt->bind_param('i', $dedi_id);
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
                             } else {

                               $ssh->exec('sudo pkill -u '.$gs_login);
                               $ssh->exec('sudo rm -r /home/'.$gs_login.'/*');
                               $copy = "screen -amds cp".$gs_login." bash -c 'sudo cp -R /home/".$dedi_login."/templates/".$game."/* /home/".$gs_login.";sudo cp -R /home/".$dedi_login."/templates/".$game."/linux32/libstdc++.so.6 /home/".$gs_login."/game/bin;sudo chown -R ".$gs_login.":".$gs_login." /home/".$gs_login.";chmod a-w /home/".$gs_login."'";
                               $ssh->exec($copy);

                               $status = 1;
                               $stmt = $mysqli->prepare("UPDATE gameservers SET status = ?  WHERE id = ?");
                               $stmt->bind_param('ii',$status,$gs_select);
                               $stmt->execute();
                               $stmt->close();

                               msg_okay("Der Gameserver wird neuinstalliert.");

                             }
                          }
                          if ($page == "gameserver?update-".$row[0] AND $row[1] == 0 AND $row[2] == $_SESSION['user_id'] or $page == "gameserver?update-".$row[0] AND $row[1] == 0 AND $db_rank == 1) {

                            $gs_select = $row[0];

                             $stmt = $mysqli->prepare("SELECT ip,game,gs_login,slots,map,port,parameter,dedi_id FROM gameservers WHERE id = ?");
                             $stmt->bind_param('i', $gs_select);
                             $stmt->execute();
                             $stmt->bind_result($ip,$game,$gs_login,$slots,$map,$port,$parameter,$dedi_id);
                             $stmt->fetch();
                             $stmt->close();

                             $stmt = $mysqli->prepare("SELECT name_internal FROM templates WHERE name = ?");
                             $stmt->bind_param('s', $game);
                             $stmt->execute();
                             $stmt->bind_result($name_internal);
                             $stmt->fetch();
                             $stmt->close();

                             $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
                             $stmt->bind_param('i', $dedi_id);
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
                              } else {

                                $stmt = $mysqli->prepare("SELECT type_name FROM templates WHERE name = ?");
                                $stmt->bind_param('s',$game);
                                $stmt->execute();
                                $stmt->bind_result($type_name);
                                $stmt->fetch();
                                $stmt->close();

                                $status = 1;
                                $stmt = $mysqli->prepare("UPDATE gameservers SET status = ?,status_update = ?  WHERE id = ?");
                                $stmt->bind_param('iii',$status,$status,$gs_select);
                                $stmt->execute();
                                $stmt->close();

                                $ssh->exec('sudo pkill -u '.$gs_login);
                                $ssh->exec('sudo rm /home/'.$gs_login.'/game/steam.log');
                                $ssh->exec('sudo touch /home/'.$gs_login.'/game/steam.log');
                                $ssh->exec('sudo chmod 777 /home/'.$gs_login.'/game/steam.log');
                                $ssh->exec('sudo -u '.$gs_login.' /home/'.$gs_login.'/steamcmd.sh +force_install_dir /home/'.$gs_login.'/game  +login anonymous +app_update '.$type_name.' validate +quit >> /home/'.$gs_login.'/game/steam.log &');
                                msg_okay("Der Gameserver wird aktualisiert.");
                              }
                          }
                          if ($page == "gameserver?start-".$row[0] AND $row[1] == 0 AND $row[2] == $_SESSION['user_id'] or $page == "gameserver?start-".$row[0] AND $row[1] == 0 AND $db_rank == 1) {
                            $gs_select = $row[0];

                             $stmt = $mysqli->prepare("SELECT ip,game,gs_login,slots,map,port,parameter,dedi_id FROM gameservers WHERE id = ?");
                             $stmt->bind_param('i', $gs_select);
                             $stmt->execute();
                             $stmt->bind_result($ip,$game,$gs_login,$slots,$map,$port,$parameter,$dedi_id);
                             $stmt->fetch();
                             $stmt->close();

                             $stmt = $mysqli->prepare("SELECT name_internal FROM templates WHERE name = ?");
                             $stmt->bind_param('s', $game);
                             $stmt->execute();
                             $stmt->bind_result($name_internal);
                             $stmt->fetch();
                             $stmt->close();

                             $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
                             $stmt->bind_param('i', $dedi_id);
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
                              } else {
                                $ssh->exec('sudo pkill -u '.$gs_login);
                                $ssh->exec('sudo -u '.$gs_login.' screen -adms game /home/'.$gs_login.'/game/srcds_run -game '.$name_internal.' -port '.$port.' +map '.$map.' -maxplayers '.$slots .' ' .$parameter);
                                msg_okay("Der Gamesever wurde gestartet.");
                              }
                              break;
                          }
                          if ($page == "gameserver?stop-".$row[0]  AND $row[1] == 0 AND $row[2] == $_SESSION['user_id'] or $page == "gameserver?stop-".$row[0] AND $row[1] == 0 AND $db_rank == 1) {
                            $gs_select = $row[0];

                            $stmt = $mysqli->prepare("SELECT ip,game,gs_login,slots,map,port,parameter,dedi_id FROM gameservers WHERE id = ?");
                            $stmt->bind_param('i', $gs_select);
                            $stmt->execute();
                            $stmt->bind_result($ip,$game,$gs_login,$slots,$map,$port,$parameter,$dedi_id);
                            $stmt->fetch();
                            $stmt->close();

                            $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
                            $stmt->bind_param('i', $dedi_id);
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
                             } else {
                               $ssh->exec('sudo pkill -u '.$gs_login);
                               msg_okay("Der Gameserver wurde angehalten.");
                             }
                             break;
                          }
                          if ($page == "gameserver?delete-".$row[0] AND $row[1] == 0 AND $db_rank == 1) {

                              $gs_select = $row[0];

                              $stmt = $mysqli->prepare("SELECT ip,game,gs_login,slots,map,port,parameter,dedi_id FROM gameservers WHERE id = ?");
                              $stmt->bind_param('i', $gs_select);
                              $stmt->execute();
                              $stmt->bind_result($ip,$game,$gs_login,$slots,$map,$port,$parameter,$dedi_id);
                              $stmt->fetch();
                              $stmt->close();

                              $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
                              $stmt->bind_param('i', $dedi_id);
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
                               } else {
                                 $ssh->exec('sudo pkill -u '.$gs_login);
                                 $ssh->exec('sudo userdel -r '.$gs_login);

                                 $stmt = $mysqli->prepare("DELETE FROM gameservers WHERE id = ?");
                                 $stmt->bind_param('i', $gs_select);
                                 $stmt->execute();
                                 $stmt->close();
                                 msg_okay("Der Gameserver wurde gelöscht.");
                               }
                               break;
                            }
                          if ($page == "gameserver?settings-".$row[0] AND $row[2] == $_SESSION['user_id'] or $page == "gameserver?settings-".$row[0] AND $db_rank == 1) {

                            if ($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['confirm-settings'])) {

                            $map = htmlentities($_POST['map']);
                            $parameter = htmlentities($_POST['parameter']);

                            $stmt = $mysqli->prepare("UPDATE gameservers SET map = ?,parameter = ?  WHERE id = ?");
                            $stmt->bind_param('ssi',$map,$parameter,$row[0]);
                            $stmt->execute();
                            $stmt->close();

                            }

                            $stmt = $mysqli->prepare("SELECT map,parameter FROM gameservers WHERE id = ?");
                            $stmt->bind_param('i', $row[0]);
                            $stmt->execute();
                            $stmt->bind_result($db_map,$db_parameter);
                            $stmt->fetch();
                            $stmt->close();
                            ?>
                            <form class="form-horizontal" action="<?php echo "index.php?page=gameserver?settings-".$row[0]; ?>" method="post">
                              <div class="form-group">
                                <label class="control-label col-sm-2">Map:</label>
                                <div class="col-sm-4">
                                  <input type="text" class="form-control input-sm" name="map" value="<?php echo $db_map;?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-sm-2">Paramter:</label>
                                <div class="col-sm-8">
                                  <input type="text" class="form-control input-sm" name="parameter" value="<?php echo $db_parameter;?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                  <button type="submit" name="confirm-settings" class="btn btn-default btn-sm">Abschicken</button>
                                </div>
                              </div>
                            </form>

                            <?php
                          }
                        }
                        /* free result set */
                        $result->close();
                    }

                    if ($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['confirm'])) {

                       $error = false; $msg = "";
                       $port = htmlentities($_POST['port']); $slots = htmlentities($_POST['slots']);
                       $dedicated = htmlentities($_POST['dedicated']); $type = htmlentities($_POST['type']);
                       $map = htmlentities($_POST['map']);
                       $user_gs = htmlentities($_POST['users']);

                       if(!preg_match("/^[0-9]+$/",$slots)){ $msg = "Der Slots enth&auml;lt ung&uuml;ltige Zeichen (0-9 sind Erlaubt)<br>";  $error = true;}
                       if(!preg_match("/^[0-9]+$/",$port)){ $msg = "Der Port enth&auml;lt ung&uuml;ltige Zeichen (0-9 sind Erlaubt)<br>";  $error = true;}
                       if(!preg_match("/^[0-9]+$/",$dedicated)){ $msg = "Dedicated enth&auml;lt ung&uuml;ltige Zeichen (0-9 sind Erlaubt)<br>";  $error = true;}
                       if(!preg_match("/^[0-9]+$/",$user_gs)){ $msg = "User enth&auml;lt ung&uuml;ltige Zeichen (0-9 sind Erlaubt)<br>";  $error = true;}
                       if(!preg_match("/^[a-zA-Z0-9]+$/",$type)){ $msg = "Das Spiel enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)<br>";  $error = true;}


                       $stmt = $mysqli->prepare("SELECT ip,port,user,password,id,language FROM dedicated WHERE id = ?");
                       $stmt->bind_param('i', $dedicated);
                       $stmt->execute();
                       $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password,$dedi_id,$dedi_language);
                       $stmt->fetch();
                       $stmt->close();

                       if (port_exists($dedi_ip,$port)) { $msg = "Port belegt"; $error = true;}
                       if (check_dedi_id($dedicated)) {$msg = "Ungültige Dedicated ID"; $error = true;}
                       if (check_template($type)) { $msg = "Ungültiges Template"; $error = true;}
                       if (check_user_id($user_gs)) { $msg = "Ungültiger User"; $error = true;}

                       $installed = check_game_installed($dedicated,$type);

                       if ($installed[0] != 1) { $error = true;$msg = $installed[1];}

                        if ($error == false) {

                         $stmt = $mysqli->prepare("SELECT name,u_count FROM users WHERE id = ?");
                         $stmt->bind_param('i', $user_gs);
                         $stmt->execute();
                         $stmt->bind_result($user_name,$user_u_count);
                         $stmt->fetch();
                         $stmt->close();

                         $gs_login = $user_name . "-" . $user_u_count;
                         $gs_password = generatePassword();

                         $ssh = new Net_SSH2($dedi_ip,$dedi_port);
                          if (!$ssh->login($dedi_login, $dedi_password)) {
                            msg_error("Login failed");
                            exit;
                          } else {

                            $ssh->exec('sudo useradd -m -d /home/'.$gs_login.' -s /bin/bash '.$gs_login);
                            $ssh->enablePTY();
                            $ssh->exec('sudo passwd '.$gs_login);
                            if ($dedi_language == "Deutsch") {
                             $ssh->read('Geben Sie ein neues UNIX-Passwort ein:');
                             $ssh->write($gs_password . "\n");
                             $ssh->read('Geben Sie das neue UNIX-Passwort erneut ein:');
                             $ssh->write($gs_password . "\n");
                             $ssh->read('passwd: Passwort erfolgreich geändert');
                            } elseif ($dedi_language == "Englisch") {
                              $ssh->read('Enter new UNIX password:');
                              $ssh->write($gs_password . "\n");
                              $ssh->read('Retype new UNIX password:');
                              $ssh->write($gs_password . "\n");
                              $ssh->read('passwd: password updated successfully');
                            }
                            $ssh->disablePTY();
                            $ssh->read('[prompt]');
                            $copy = "screen -amds cp".$gs_login." bash -c 'sudo cp -R /home/".$dedi_login."/templates/".$type."/* /home/".$gs_login.";sudo cp -R /home/".$dedi_login."/templates/".$type."/linux32/libstdc++.so.6 /home/".$gs_login."/game/bin;sudo chown -R ".$gs_login.":".$gs_login." /home/".$gs_login.";chmod a-w /home/".$gs_login."'";
                            $ssh->exec($copy);

                            $stmt = $mysqli->prepare("INSERT INTO gameservers(user_id,game,slots,ip,port,gs_login,gs_password,map,dedi_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)");
                            $stmt->bind_param('isisisssi', $user_gs,$type,$slots,$dedi_ip,$port,$gs_login,$gs_password,$map,$dedi_id);
                            $stmt->execute();
                            $stmt->close();

                            $user_u_count = $user_u_count +1;

                            $stmt = $mysqli->prepare("UPDATE users SET u_count = ? WHERE id = ?");
                            $stmt->bind_param('ii', $user_u_count, $user_gs);
                            $stmt->execute();
                            $stmt->close();

                            msg_okay("Der Gameserver wird installiert, das kann etwas dauern.");

                          }

                     } else {
                       msg_error('Something went wrong, '.$msg);
                     }
                }
                if ($page == "gameserver?add" and $db_rank == 1) {

                  ?>
                  <form class="form-horizontal" action="index.php?page=gameserver" method="post">
                    <div class="form-group">
                      <label class="control-label col-sm-2">Type/Root:</label>
                      <div class="col-sm-4">
                        <select class="form-control input-sm" name="type">
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
                        <select class="form-control input-sm" name="dedicated">
                        <?php
                        $query = "SELECT id,name FROM dedicated ORDER by id";

                         if ($stmt = $mysqli->prepare($query)) {
                             $stmt->execute();
                             $stmt->bind_result($db_id,$db_name);

                             while ($stmt->fetch()) {
                               echo '<option value="'. $db_id .'">'. $db_name .'</option>';
                             }
                             $stmt->close();
                         }  ?>
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2">Map/Benutzer:</label>
                      <div class="col-sm-4">
                        <input type="text" class="form-control input-sm" name="map" placeholder="gm_flatgrass">
                      </div>
                      <div class="col-sm-4">
                        <select class="form-control input-sm" name="users">
                        <?php
                        $query = "SELECT id,name FROM users ORDER by id";

                         if ($stmt = $mysqli->prepare($query)) {
                             $stmt->execute();
                             $stmt->bind_result($db_id,$db_name);

                             while ($stmt->fetch()) {
                               echo '<option value="'. $db_id .'">'. $db_name .'</option>';
                             }
                             $stmt->close();
                         }  ?>
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2">Port/Slots:</label>
                      <div class="col-sm-4">
                        <input type="text" class="form-control input-sm" name="port" placeholder="27015">
                      </div>
                      <div class="col-sm-4">
                        <input type="text" class="form-control input-sm" name="slots" placeholder="14">
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" name="confirm" class="btn btn-default btn-sm">Abschicken</button>
                      </div>
                    </div>
                  </form>
           <?php } elseif (startsWith($page, "gameserver")) {
                    ?>
                    <form action="index.php?page=gameserver" method="post">
                  <?php if ($db_rank == 1) { echo '<a  style="margin-bottom:2px;" href="index.php?page=gameserver?add"  class="btn pull-right btn-success btn-xs">+</a>';}  ?>
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th>Benutzer</th>
                          <th>Game</th>
                          <th>IP</th>
                          <th>Port</th>
                          <th>Slots</th>
                          <th>Map</th>
                          <th>Login</th>
                          <th>Passwort</th>
                          <th>Start/Stop</th>
                          <th>Aktion</th>
                        </tr>
                      </thead>
                      <tbody>
                     <?php

                     $query = "SELECT user_id, game, ip, port,slots, gs_login, gs_password, id, map,status FROM gameservers ORDER by id";

                     if ($result = $mysqli->query($query)) {

                       /* fetch object array */
                       while ($row = $result->fetch_assoc()) {
                            if ($db_rank == 1) {
                              $db_user_name = get_user_by_id($row["user_id"]);
                              echo "<tr>";
                              echo "<td>" . $db_user_name . "</td>";
                              echo "<td>" . $row["game"] . "</td>";
                              echo "<td>" . $row["ip"] . "</td>";
                              echo "<td>" . $row["port"] . "</td>";
                              echo "<td>" . $row["slots"] . "</td>";
                              echo "<td>" . $row["map"] . "</td>";
                              echo "<td>" . $row["gs_login"] . "</td>";
                              echo "<td>" . $row["gs_password"] . "</td>";
                              if ($row["status"] == 0) {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$row["id"].'"  class="btn btn-success btn-xs">(Re)Start</a> <a href="index.php?page=gameserver?stop-'.$row["id"].'"  class="btn btn-danger btn-xs">Stop</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$row["id"].'"  class="btn btn-warning btn-xs">Reinstall</a> <a href="index.php?page=gameserver?update-'.$row["id"].'"  class="btn btn-primary btn-xs">Update</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'"  class="btn btn-primary btn-xs">Einstellungen</a>  <a href="index.php?page=gameserver?delete-'.$row["id"].'"  class="btn btn-danger btn-xs">X</a>  </td>';
                              } else {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$row["id"].'"  class="btn btn-success btn-xs" disabled>(Re)Start</a> <a href="index.php?page=gameserver?stop-'.$row["id"].'"  class="btn btn-danger btn-xs" disabled >Stop</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$row["id"].'"  class="btn btn-warning btn-xs" disabled>Reinstall</a> <a href="index.php?page=gameserver?update-'.$row["id"].'"  class="btn btn-primary btn-xs" disabled>Update</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'"  class="btn btn-primary btn-xs" disabled>Einstellungen</a>  <a href="index.php?page=gameserver?delete-'.$row["id"].'"  class="btn btn-danger btn-xs" disabled>X</a>  </td>';
                              }
                              echo "</tr>";
                            } elseif ($db_rank == 2 AND $row["user_id"] == $_SESSION['user_id']) {
                              $db_user_name = get_user_by_id($row["user_id"]);
                              echo "<tr>";
                              echo "<td>" . $db_user_name . "</td>";
                              echo "<td>" . $row["game"] . "</td>";
                              echo "<td>" . $row["ip"] . "</td>";
                              echo "<td>" . $row["port"] . "</td>";
                              echo "<td>" . $row["slots"] . "</td>";
                              echo "<td>" . $row["map"] . "</td>";
                              echo "<td>" . $row["gs_login"] . "</td>";
                              echo "<td>" . $row["gs_password"] . "</td>";
                              if ($row["status"] == 0) {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$row["id"].'"  class="btn btn-success btn-xs">(Re)Start</a> <a href="index.php?page=gameserver?stop-'.$row["id"].'"  class="btn btn-danger btn-xs">Stop</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$row["id"].'"  class="btn btn-warning btn-xs">Reinstall</a> <a href="index.php?page=gameserver?update-'.$row["id"].'"  class="btn btn-primary btn-xs">Update</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'"  class="btn btn-primary btn-xs">Einstellungen</a>  <a href="index.php?page=gameserver?delete-'.$row["id"].'"  class="btn btn-danger btn-xs" disabled>X</a>  </td>';
                              } else {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$row["id"].'"  class="btn btn-success btn-xs" disabled>(Re)Start</a> <a href="index.php?page=gameserver?stop-'.$row["id"].'"  class="btn btn-danger btn-xs" disabled >Stop</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$row["id"].'"  class="btn btn-warning btn-xs" disabled>Reinstall</a> <a href="index.php?page=gameserver?update-'.$row["id"].'"  class="btn btn-primary btn-xs" disabled>Update</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'"  class="btn btn-primary btn-xs" disabled>Einstellungen</a>  <a href="index.php?page=gameserver?delete-'.$row["id"].'"  class="btn btn-danger btn-xs" disabled>X</a>  </td>';
                              }
                              echo "</tr>";
                            }
                          }
                          /* free result set */
                        $result->close();
                      }  ?>
                      </tbody>
                    </table>
                  </form>
                  <?php }
                 ?>
               </div>
               <!-- /.col-lg-12 -->
           </div>
           <!-- /.row -->
       </div>
       <!-- /#page-wrapper -->
   </div>
   <!-- /#wrapper -->
 </div>
</div>
</div>


<?php

 } else { header('Location: index.php');}


//Footer
include 'footer.html';
?>
