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


                    $query = "SELECT id,status,user_id,ip FROM gameservers ORDER by id";

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
                               $copy = "screen -amds cp".$gs_login." bash -c 'sudo cp -R /home/".$dedi_login."/templates/".$game."/* /home/".$gs_login.";sudo cp -R /home/".$dedi_login."/templates/".$game."/linux32/libstdc++.so.6 /home/".$gs_login."/game/bin;sudo chown -R ".$gs_login.":".$gs_login." /home/".$gs_login.";'";
                               $ssh->exec($copy);

                               $status = 1;
                               $stmt = $mysqli->prepare("UPDATE gameservers SET status = ?  WHERE id = ?");
                               $stmt->bind_param('ii',$status,$gs_select);
                               $stmt->execute();
                               $stmt->close();

                               msg_okay("Der Gameserver wird neuinstalliert.");

                               event_add(5,"Gameserver ".$ip.":".$port." wird neuinstalliert.");

                             }
                          }
                          if ($page == "gameserver?update-".$row[0] AND $row[1] == 0 AND $row[2] == $_SESSION['user_id'] or $page == "gameserver?update-".$row[0] AND $row[1] == 0 AND $db_rank == 1) {

                            $gs_select = $row[0];

                              $error = false; $msg = "";

                             $stmt = $mysqli->prepare("SELECT ip,game,gs_login,slots,map,port,parameter,dedi_id FROM gameservers WHERE id = ?");
                             $stmt->bind_param('i', $gs_select);
                             $stmt->execute();
                             $stmt->bind_result($ip,$game,$gs_login,$slots,$map,$port,$parameter,$dedi_id);
                             $stmt->fetch();
                             $stmt->close();

                             $stmt = $mysqli->prepare("SELECT name_internal,type FROM templates WHERE name = ?");
                             $stmt->bind_param('s', $game);
                             $stmt->execute();
                             $stmt->bind_result($name_internal,$type);
                             $stmt->fetch();
                             $stmt->close();

                             if ($type == "image") { $error = true; $msg = "Deaktiviert für Images";}

                             $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
                             $stmt->bind_param('i', $dedi_id);
                             $stmt->execute();
                             $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password);
                             $stmt->fetch();
                             $stmt->close();

                             if ($error == false) {

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

                                  event_add(4,"Der Gameserver ".$ip.":".$port." wird aktualisiert.");
                                }
                             } else {
                               msg_warning($msg);
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

                             $stmt = $mysqli->prepare("SELECT name_internal,type FROM templates WHERE name = ?");
                             $stmt->bind_param('s', $game);
                             $stmt->execute();
                             $stmt->bind_result($name_internal,$type);
                             $stmt->fetch();
                             $stmt->close();

                             $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
                             $stmt->bind_param('i', $dedi_id);
                             $stmt->execute();
                             $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password);
                             $stmt->fetch();
                             $stmt->close();

                             $stmt = $mysqli->prepare("SELECT type,type_name,gameq FROM templates WHERE name = ?");
                             $stmt->bind_param('s', $game);
                             $stmt->execute();
                             $stmt->bind_result($db_type,$db_type_name,$gameq);
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
                                $stmt->bind_param('iiii',$is_running,$running,$deadline,$gs_select);
                                $stmt->execute();
                                $stmt->close();

                                event_add(1,"Der Gameserver ".$ip.":".$port." wurde gestartet.");

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

                               event_add(2,"Der Gameserver ".$ip.":".$port." wurde angehalten.");

                               $is_running = 0; $running = 0;
                               $stmt = $mysqli->prepare("UPDATE gameservers SET is_running = ?,running = ?  WHERE id = ?");
                               $stmt->bind_param('iii',$is_running,$running,$gs_select);
                               $stmt->execute();
                               $stmt->close();
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

                                 event_add(3,"Der Gameserver ".$ip.":".$port." wurde gelöscht.");

                                 msg_okay("Der Gameserver wurde gelöscht.");
                               }
                               break;
                            }
                          if ($page == "gameserver?settings-".$row[0] AND $row[2] == $_SESSION['user_id'] or $page == "gameserver?settings-".$row[0] AND $db_rank == 1) {

                            $gs_select = $row[0];

                            $stmt = $mysqli->prepare("SELECT ip,game,gs_login,slots,map,port,parameter,dedi_id,parameters_active FROM gameservers WHERE id = ?");
                            $stmt->bind_param('i', $gs_select);
                            $stmt->execute();
                            $stmt->bind_result($ip,$game,$gs_login,$slots,$map,$port,$parameter,$dedi_id,$parameter_active);
                            $stmt->fetch();
                            $stmt->close();

                            $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
                            $stmt->bind_param('i', $dedi_id);
                            $stmt->execute();
                            $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password);
                            $stmt->fetch();
                            $stmt->close();


                            if ($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['confirm-settings'])) {

                              if ($db_rank == 2) {

                                $map = htmlentities($_POST['map']);
                                $parameter = htmlentities($_POST['parameter']);

                                if ($parameter_active == 1) {

                                  $stmt = $mysqli->prepare("UPDATE gameservers SET map = ?,parameter = ?  WHERE id = ?");
                                  $stmt->bind_param('ssi',$map,$parameter,$row[0]);
                                  $stmt->execute();
                                  $stmt->close();

                                } else {

                                  $stmt = $mysqli->prepare("UPDATE gameservers SET map = ?  WHERE id = ?");
                                  $stmt->bind_param('si',$map,$row[0]);
                                  $stmt->execute();
                                  $stmt->close();

                                }

                              } elseif ($db_rank == 1) {

                                $error = false; $parameter_active = 0;
                                $map = htmlentities($_POST['map']);
                                $parameter = htmlentities($_POST['parameter']);
                                $slots = htmlentities($_POST['slots']);
                                $port = htmlentities($_POST['port']);
                                if (isset($_POST['parameter_active'])) { $parameter_active = 1;}

                                if(!preg_match("/^[0-9]+$/",$slots)){ $msg = "Der Slots enth&auml;lt ung&uuml;ltige Zeichen (0-9 sind Erlaubt)<br>";  $error = true;}
                                if(!preg_match("/^[0-9]+$/",$port)){ $msg = "Der Port enth&auml;lt ung&uuml;ltige Zeichen (0-9 sind Erlaubt)<br>";  $error = true;}
                                if (port_exists($row[3],$port,$row[2])) { $msg = "Port belegt"; $error = true;}

                                if ($error == false) {

                                  $stmt = $mysqli->prepare("UPDATE gameservers SET map = ?,parameter = ?, slots = ?, port = ?, parameters_active = ?  WHERE id = ?");
                                  $stmt->bind_param('ssiiii',$map,$parameter,$slots,$port,$parameter_active,$row[0]);
                                  $stmt->execute();
                                  $stmt->close();

                                } else {
                                  msg_error($msg);
                                }
                              }
                            }

                            $stmt = $mysqli->prepare("SELECT map,parameter,slots,port,parameters_active FROM gameservers WHERE id = ?");
                            $stmt->bind_param('i', $row[0]);
                            $stmt->execute();
                            $stmt->bind_result($db_map,$db_parameter,$db_slots,$db_port,$db_parameter_active);
                            $stmt->fetch();
                            $stmt->close();

                            $stmt = $mysqli->prepare("SELECT map_path FROM templates WHERE name = ?");
                            if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
                            $rc = $stmt->bind_param('s', $game);
                            if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
                            $rc = $stmt->execute();
                            if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
                            $stmt->bind_result($db_path);
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
                                $msg =  $ssh->exec('cd /home/'.$gs_login.'/game/'.$db_path.'/maps/;ls');
                                $lines = preg_split('/\s+/', $msg);
                                foreach ($lines as &$element) {
                                  if (endsWith($element, ".bsp")) {
                                    //echo $element;
                                    //echo "<br>";
                                  }
                                }
                             }
                            ?>
                            <form class="form-horizontal" action="<?php echo "index.php?page=gameserver?settings-".$row[0]; ?>" method="post">
                              <div class="form-group">
                                <label class="control-label col-sm-2">Map:</label>
                                <div class="col-sm-4">
                                  <input type="text" class="form-control input-sm typeahead" autocomplete="off" name="map" value="<?php echo $db_map;?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-sm-2">Paramter:</label>
                                <div class="col-sm-4">
                                  <?php if ($db_rank == 1) { ?>
                                <input type="text" class="form-control input-sm" name="parameter" value="<?php echo $db_parameter;?>">
                                <?php } elseif ($db_rank == 2 AND $db_parameter_active == 1) { ?>
                                  <input type="text" class="form-control input-sm" name="parameter" value="<?php echo $db_parameter;?>"> <?php
                                } elseif ($db_rank == 2 AND $db_parameter_active == 0) { ?>
                                  <input type="text" class="form-control input-sm" name="parameter" value="<?php echo $db_parameter;?>" readonly="readonly"> <?php
                                } ?>
                                </div>
                                <div class="col-sm-2">
                                  <?php if ($db_rank == 1) {
                                    echo '<input data-size="small" id="toggle-parameter" data-height="20" type="checkbox" name="parameter_active" data-toggle="toggle">';
                                  }
                                   if ($db_parameter_active == 1) {
                                    ?>
                                    <script>
                                      function toggleOn() {
                                        $('#toggle-parameter').bootstrapToggle('on');
                                      }
                                      window.onload=toggleOn;
                                    </script>
                                    <?php
                                  } elseif ($db_parameter_active == 0) { ?>
                                    <script>
                                      function toggleOff() {
                                        $('#toggle-parameter').bootstrapToggle('off');
                                      }
                                      window.onload=toggleOff;
                                    </script>
                                    <?php
                                  }
                                  ?>
                                </div>
                              </div>
                              <?php if ($db_rank == 1) {
                                ?>
                                <div class="form-group">
                                  <label class="control-label col-sm-2">Slots/Port:</label>
                                  <div class="col-sm-2">
                                    <input type="text" class="form-control input-sm" name="slots" value="<?php echo $db_slots;?>">
                                  </div>
                                  <div class="col-sm-2">
                                    <input type="text" class="form-control input-sm" name="port" value="<?php echo $db_port;?>">
                                  </div>
                                </div>
                            <?php  } ?>
                              <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                  <button type="submit" name="confirm-settings" class="btn btn-default btn-sm">Abschicken</button>
                                </div>
                              </div>
                            </form>

                            <?php
                          }
                          if ($page == "gameserver?console-".$row[0]  AND $row[1] == 0 AND $row[2] == $_SESSION['user_id'] or $page == "gameserver?console-".$row[0] AND $row[1] == 0 AND $db_rank == 1) {

                            $gs_select = $row[0];


                            if ($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['console_submit'])) {


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

                              $cmd = htmlentities($_POST['cmd']);

                              $ssh = new Net_SSH2($dedi_ip,$dedi_port);
                               if (!$ssh->login($dedi_login, $dedi_password)) {
                                 echo '
                                 <div class="alert alert-danger" role="alert">
                                   <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                   <span class="sr-only">Error:</span>
                                   Login failed
                                 </div>';
                               } else {
                                 $ssh->exec('sudo -u '.$gs_login.' screen -S "game'.$gs_login.'" -X stuff "'.$cmd.'\n"');
                                 sleep(2);
                               }
                            }

                            $stmt = $mysqli->prepare("SELECT ip,game,gs_login,slots,map,port,parameter,dedi_id FROM gameservers WHERE id = ?");
                            $stmt->bind_param('i', $gs_select);
                            $stmt->execute();
                            $stmt->bind_result($ip,$game,$gs_login,$slots,$map,$port,$parameter,$dedi_id);
                            $stmt->fetch();
                            $stmt->close();

                            $stmt = $mysqli->prepare("SELECT name_internal,type FROM templates WHERE name = ?");
                            $stmt->bind_param('s', $game);
                            $stmt->execute();
                            $stmt->bind_result($name_internal,$type);
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
                               if ($type == "steamcmd") {
                                 $text = $ssh->exec('cd /home/'.$gs_login.'/game;sudo -u '.$gs_login.' cat -A screenlog.0');
                               } elseif ($type == "image") {
                                 $text = $ssh->exec('cd /home/'.$gs_login.'/;sudo -u '.$gs_login.' cat -A screenlog.0');
                               }
                               $lines = explode("^M$",$text);
                               echo '<form class="form-horizontal" action="index.php?page=gameserver?console-'.$gs_select.'" method="post">';
                               echo '<div class="form-group"><div class="col-sm-10">';
                               echo '<textarea id="console" class="form-control input-sm"" rows="16" readonly="readonly">';
                               foreach ($lines as &$element) {
                                 echo $element;
                               }
                               echo '</textarea></div>';
                               ?>
                                 <div style="margin-top:5px;" class="col-sm-10">
                                   <div class="input-group">
                                   <input type="text" class="form-control input-sm" name="cmd" placeholder="changelevel de_dust2">
                                   <span class="input-group-btn">
                                     <?php
                                    echo '<a href="index.php?page=gameserver?console-'.$gs_select.'"  class="btn btn-success btn-sm">Update</a>';
                                     ?>
                                      <button class="btn btn-primary btn-sm" name="console_submit" type="submit">Abschicken</button>
                                   </span>
                                 </div>
                               </div>
                             </div>
                           </form>
                               <script>
                               var textarea = document.getElementById('console');
                               textarea.scrollTop = textarea.scrollHeight;
                              </script>
                               <?php
                             }
                             break;
                          }
                        }
                        /* free result set */
                        $result->close();
                    }

                if ($page == "gameserver?add" and $db_rank == 1) {

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
                          $copy = "screen -amds cp".$gs_login." bash -c 'sudo cp -R /home/".$dedi_login."/templates/".$type."/* /home/".$gs_login.";sudo cp -R /home/".$dedi_login."/templates/".$type."/linux32/libstdc++.so.6 /home/".$gs_login."/game/bin;sudo chown -R ".$gs_login.":".$gs_login." /home/".$gs_login.";chmod a-w /home/".$gs_login.";rm screenlog.0;'";
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

                          event_add(6,"Der Gameserver ".$dedi_ip.":".$port." wurde hinzugefügt.");

                        }

                   } else {
                     msg_error('Something went wrong, '.$msg);
                   }
              }

                  ?>
                  <form class="form-horizontal" action="index.php?page=gameserver?add" method="post">
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
                  <?php if ($db_rank == 1 AND startsWith($page, "gameserver?console") == false ) { echo '<a  style="margin-bottom:2px;margin-top:2px;" href="index.php?page=gameserver?add"  class="btn pull-right btn-success btn-xs"><i class="fa fa-plus"></i></a>';}
                  elseif ($db_rank == 1 AND startsWith($page, "gameserver?console") == true ) { echo '<a  style="margin-bottom:2px;margin-top:28px;" href="index.php?page=gameserver?add"  class="btn pull-right btn-success btn-xs"><i class="fa fa-plus"></i></a>';} ?>
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th>Benutzer</th>
                          <th>Game</th>
                          <th>IP+Port</th>
                          <th>Slots</th>
                          <th>Map</th>
                          <th>FTP Login</th>
                          <th>FTP Passwort</th>
                          <th>Start/Stop</th>
                          <th>Aktion</th>
                        </tr>
                      </thead>
                      <tbody>
                     <?php

                     $query = "SELECT user_id, game, ip, port,slots, gs_login, gs_password, id, map,status,is_running FROM gameservers ORDER by id";

                     if ($result = $mysqli->query($query)) {

                       /* fetch object array */
                       while ($row = $result->fetch_assoc()) {
                            if ($db_rank == 1) {
                              $db_user_name = get_user_by_id($row["user_id"]);
                              if ($row["is_running"] == 1) {
                                  echo '<tr class="success">';
                              } elseif ($row["is_running"] == 2)  {
                                  echo '<tr class="warning">';
                              } elseif ($row["is_running"] == 0)  {
                                  echo '<tr class="danger">';
                              }
                              echo "<td>" . $db_user_name . "</td>";
                              echo "<td>" . $row["game"] . "</td>";
                              echo "<td>" . $row["ip"] .":".$row["port"]."</td>";
                              echo "<td>" . $row["slots"] . "</td>";
                              echo "<td>" . $row["map"] . "</td>";
                              echo "<td>" . $row["gs_login"] . "</td>";
                              echo "<td>" . $row["gs_password"] . "</td>";
                              if ($row["status"] == 0) {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$row["id"].'"  class="btn btn-success btn-xs">(Re)Start</a> <a href="index.php?page=gameserver?stop-'.$row["id"].'"  class="btn btn-danger btn-xs">Stop</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$row["id"].'"  class="btn btn-warning btn-xs">Reinstall</a> <a href="index.php?page=gameserver?update-'.$row["id"].'"  class="btn btn-primary btn-xs">Update</a> <a href="index.php?page=gameserver?console-'.$row["id"].'"  class="btn btn-primary btn-xs">Console</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'"  class="btn btn-primary btn-xs">Einstellungen</a>  <a href="index.php?page=gameserver?delete-'.$row["id"].'"  class="btn btn-danger btn-xs"><i class="fa fa-remove"></i></a>  </td>';
                              } else {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$row["id"].'"  class="btn btn-success btn-xs" disabled>(Re)Start</a> <a href="index.php?page=gameserver?stop-'.$row["id"].'"  class="btn btn-danger btn-xs" disabled >Stop</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$row["id"].'"  class="btn btn-warning btn-xs" disabled>Reinstall</a> <a href="index.php?page=gameserver?update-'.$row["id"].'"  class="btn btn-primary btn-xs" disabled>Update</a> <a href="index.php?page=gameserver?console-'.$row["id"].'"  class="btn btn-primary btn-xs" disabled>Console</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'"  class="btn btn-primary btn-xs" disabled>Einstellungen</a>  <a href="index.php?page=gameserver?delete-'.$row["id"].'"  class="btn btn-danger btn-xs" disabled><i class="fa fa-remove"></i></a>  </td>';
                              }
                              echo "</tr>";
                            } elseif ($db_rank == 2 AND $row["user_id"] == $_SESSION['user_id']) {
                              $db_user_name = get_user_by_id($row["user_id"]);
                              if ($row["is_running"] == 1) {
                                  echo '<tr class="success">';
                              } elseif ($row["is_running"] == 2)  {
                                  echo '<tr class="warning">';
                              } elseif ($row["is_running"] == 0)  {
                                  echo '<tr class="danger">';
                              }
                              echo "<td>" . $db_user_name . "</td>";
                              echo "<td>" . $row["game"] . "</td>";
                              echo "<td>" . $row["ip"] .":".$row["port"]."</td>";
                              echo "<td>" . $row["slots"] . "</td>";
                              echo "<td>" . $row["map"] . "</td>";
                              echo "<td>" . $row["gs_login"] . "</td>";
                              echo "<td>" . $row["gs_password"] . "</td>";
                              if ($row["status"] == 0) {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$row["id"].'"  class="btn btn-success btn-xs">(Re)Start</a> <a href="index.php?page=gameserver?stop-'.$row["id"].'"  class="btn btn-danger btn-xs">Stop</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$row["id"].'"  class="btn btn-warning btn-xs">Reinstall</a> <a href="index.php?page=gameserver?update-'.$row["id"].'"  class="btn btn-primary btn-xs">Update</a> <a href="index.php?page=gameserver?console-'.$row["id"].'"  class="btn btn-primary btn-xs">Console</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'"  class="btn btn-primary btn-xs">Einstellungen</a>  <a href="index.php?page=gameserver?delete-'.$row["id"].'"  class="btn btn-danger btn-xs" disabled><i class="fa fa-remove"></i></a>  </td>';
                              } else {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$row["id"].'"  class="btn btn-success btn-xs" disabled>(Re)Start</a> <a href="index.php?page=gameserver?stop-'.$row["id"].'"  class="btn btn-danger btn-xs" disabled >Stop</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$row["id"].'"  class="btn btn-warning btn-xs" disabled>Reinstall</a> <a href="index.php?page=gameserver?update-'.$row["id"].'"  class="btn btn-primary btn-xs" disabled>Update</a> <a href="index.php?page=gameserver?console-'.$row["id"].'"  class="btn btn-primary btn-xs" disabled>Console</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'"  class="btn btn-primary btn-xs" disabled>Einstellungen</a>  <a href="index.php?page=gameserver?delete-'.$row["id"].'"  class="btn btn-danger btn-xs" disabled><i class="fa fa-remove"></i></a>  </td>';
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
