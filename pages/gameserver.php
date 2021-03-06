<?php

session_start();

$db_rank = 2;
//Load user Data from DB
$stmt = $mysqli->prepare("SELECT rank,id,language FROM users WHERE id = ? LIMIT 1");
if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
$rc = $stmt->bind_param('i', $_SESSION['user_id']);
if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
$rc = $stmt->execute();
if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
$stmt->bind_result($db_rank,$db_id,$db_language);
$stmt->fetch();
$stmt->close();

if ($db_language == "de") {
    require_once('lang/de.lang.php');
  } elseif ($db_language == "en") {
    require_once('lang/en.lang.php');
  } else {
    echo "Invalid Language";
    exit;
  }

//header
$title = _title_gameserver;
include 'header.php';
set_include_path('components/phpseclib');
include('Net/SSH2.php');


if ($_SESSION['login'] === 1 AND ($db_rank === 1 OR $db_rank === 2)) {

?>
<div id="wrapper">

      <script>
        function addLoadEvent(func) {
        var oldonload = window.onload;
        if (typeof window.onload != 'function') {
          window.onload = func;
        } else {
          window.onload = function() {
            if (oldonload) {
              oldonload();
            }
            func();
          }
        }
      }
      </script>

      <?php include 'navbar.php'; ?>

       <div id="page-wrapper">
           <div class="row">
               <div class="col-lg-12">
                   <h1 class="page-header"><?php echo $title; ?></h1>
               <!-- /.col-lg-12 -->
                 <?php
            //      if ($_SERVER['REQUEST_METHOD'] == 'POST') {


                    $query = "SELECT id,status,user_id,ip,game,dedi_id,gs_login,port FROM gameservers ORDER by id";

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

                               $stmt = $mysqli->prepare("SELECT name FROM templates WHERE id = ?");
                               $stmt->bind_param('i', $game);
                               $stmt->execute();
                               $stmt->bind_result($game_name);
                               $stmt->fetch();
                               $stmt->close();

                               $ssh->exec('sudo pkill -u '.$gs_login);
                               $ssh->exec('sudo rm -r /home/'.$gs_login.'/*');
                               $copy = "screen -amds cp".$gs_login." bash -c 'sudo cp -R /home/".$dedi_login."/templates/".$game_name."/* /home/".$gs_login.";sudo cp -R /home/".$dedi_login."/templates/".$game_name."/linux32/libstdc++.so.6 /home/".$gs_login."/game/bin;sudo chown -R ".$gs_login.":".$gs_login." /home/".$gs_login.";'";
                               $ssh->exec($copy);

                               $status = 1; $running = 0;
                               $stmt = $mysqli->prepare("UPDATE gameservers SET status = ?, is_running = ?, running = ?  WHERE id = ?");
                               $stmt->bind_param('iiii',$status,$running,$running,$gs_select);
                               $stmt->execute();
                               $stmt->close();

                               msg_okay(_gameserver_reinstalled);

                               //event_add(5,"Gameserver ".$ip.":".$port." wird neuinstalliert.");
                               event_add(5,$ip.":".$port);

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

                             $stmt = $mysqli->prepare("SELECT type,type_name,name,app_set_config FROM templates WHERE id = ?");
                             $stmt->bind_param('i', $game);
                             $stmt->execute();
                             $stmt->bind_result($type,$type_name,$game_name,$app_set_config);
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

                                  $status = 1; $running = 0;
                                  $stmt = $mysqli->prepare("UPDATE gameservers SET status = ?,status_update = ?,is_running = ?,running = ?  WHERE id = ?");
                                  $stmt->bind_param('iiiii',$status,$status,$running,$running,$gs_select);
                                  $stmt->execute();
                                  $stmt->close();

                                  $ssh->exec('sudo -u '.$gs_login.' screen -S game'.$gs_login.' -p 0 -X quit');
                                  $ssh->exec('sudo rm /home/'.$gs_login.'/game/steam.log');
                                  $ssh->exec('sudo touch /home/'.$gs_login.'/game/steam.log');
                                  $ssh->exec('sudo chmod 777 /home/'.$gs_login.'/game/steam.log');
                                  $ssh->exec('cd /home/'.$gs_login.'/; sudo cp /home/'.$dedi_login.'/templates/'.$game_name.'/steamcmd_linux.tar.gz /home/'.$gs_login.'/; sudo tar xvf steamcmd_linux.tar.gz; sudo rm steamcmd_linux.tar.gz;sudo chown -R '.$gs_login.':'.$gs_login.' /home/'.$gs_login.'');
                                  if ($app_set_config == "") {
                                    $ssh->exec('sudo -u '.$gs_login.' /home/'.$gs_login.'/steamcmd.sh +force_install_dir /home/'.$gs_login.'/game  +login anonymous +app_update '.$type_name.' validate +quit >> /home/'.$gs_login.'/game/steam.log &');
                                  } elseif ($app_set_config == "needed") {
                                    $ssh->exec('sudo -u '.$gs_login.' /home/'.$gs_login.'/steamcmd.sh +force_install_dir /home/'.$gs_login.'/game  +login anonymous +app_update '.$type_name.' validate +quit >> /home/'.$gs_login.'/game/steam.log &');
                                  } elseif ($app_set_config != "") {
                                    $ssh->exec('sudo -u '.$gs_login.' /home/'.$gs_login.'/steamcmd.sh +force_install_dir /home/'.$gs_login.'/game  +login anonymous +app_set_config '.$type_name.' mod '.$app_set_config.' +app_update '.$type_name.' validate +quit >> /home/'.$gs_login.'/game/steam.log &');
                                  }
                                  msg_okay(_gameserver_updated);

                                  //event_add(4,"Der Gameserver ".$ip.":".$port." wird aktualisiert.");
                                  event_add(4,$ip.":".$port);
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

                             $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
                             $stmt->bind_param('i', $dedi_id);
                             $stmt->execute();
                             $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password);
                             $stmt->fetch();
                             $stmt->close();

                             $stmt = $mysqli->prepare("SELECT gameq,app_set_config,name_internal,type FROM templates WHERE id = ?");
                             $stmt->bind_param('i', $game);
                             $stmt->execute();
                             $stmt->bind_result($db_gameq,$db_app_set_config,$db_name_internal,$type);
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
                                 gameserver_restart($type,$ssh,$gs_login,$db_name_internal,$port,$ip,$map,$slots,$parameter,$db_gameq,$gs_select,$db_app_set_config);
                                 //event_add(1,"Der Gameserver ".$ip.":".$port." wurde gestartet.");
                                 event_add(1,$ip.":".$port);
                                 msg_okay(_gameserver_started);
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
                               $ssh->exec('sudo -u '.$gs_login.' screen -S game'.$gs_login.' -p 0 -X quit');
                               msg_okay(_gameserver_stopped);

                               //event_add(2,"Der Gameserver ".$ip.":".$port." wurde angehalten.");
                               event_add(2,$ip.":".$port);

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
                                 $ssh->exec('sudo -u '.$gs_login.' screen -S game'.$gs_login.' -p 0 -X quit');
                                 $ssh->exec('sudo pkill -u '.$gs_login.';sleep 2;sudo userdel -r '.$gs_login);

                                 $stmt = $mysqli->prepare("DELETE FROM gameservers WHERE id = ?");
                                 $stmt->bind_param('i', $gs_select);
                                 $stmt->execute();
                                 $stmt->close();

                                 //event_add(3,"Der Gameserver ".$ip.":".$port." wurde gelöscht.");
                                 event_add(3,$ip.":".$port);

                                 msg_okay(_gameserver_deleted);
                               }
                               break;
                            }
                          if (($page == "gameserver?settings-".$row[0] or $page == "gameserver?settings-".$row[0]."-addons") AND $row[2] == $_SESSION['user_id'] or $page == "gameserver?settings-".$row[0] AND $db_rank == 1) {

                            $gs_select = $row[0];

                            $stmt = $mysqli->prepare("SELECT ip,game,gs_login,slots,map,port,parameter,dedi_id,parameters_active,gameswitch,game FROM gameservers WHERE id = ?");
                            $stmt->bind_param('i', $gs_select);
                            $stmt->execute();
                            $stmt->bind_result($ip,$game,$gs_login,$slots,$map,$port,$db_parameter,$dedi_id,$parameter_active,$gameswitch,$db_game);
                            $stmt->fetch();
                            $stmt->close();

                            $stmt = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
                            $stmt->bind_param('i', $dedi_id);
                            $stmt->execute();
                            $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password);
                            $stmt->fetch();
                            $stmt->close();

                            if ($page == "gameserver?settings-".$row[0]) {

                              if ($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['confirm-settings'])) {

                                if ($db_rank == 2) {

                                  $error = false;
                                  $map = htmlentities($_POST['map']);
                                  $parameter = htmlentities($_POST['parameter']);
                                  $time = htmlentities($_POST['time']);
                                  $clock = "o'clock";
                                  $time = str_replace(" Uhr", "",$time);
                                  $time = str_replace(" ".$clock, "",$time);
                                  $restart_active = 0; $updates_active = 0;
                                  $game = htmlentities($_POST['game']);
                                  if(!preg_match("/^[a-zA-Z0-9._-]+$/",$map)){ $msg = _gameserver_map_invalid."<br>";  $error = true;}
                                  if ($parameter != "") {
                                    if(!preg_match("/^[a-zA-Z0-9._ -]+$/",$parameter)){ $msg = _gameserver_map_invalid."<br>";  $error = true;}
                                  }

                                  $stmt = $mysqli->prepare("SELECT id FROM templates WHERE name= ?");
                                  $stmt->bind_param('s', $game);
                                  $stmt->execute();
                                  $stmt->bind_result($game);
                                  $stmt->fetch();
                                  $stmt->close();

                                  if (isset($_POST['restart_active'])) { $restart_active = 1;}
                                  if (isset($_POST['updates_active'])) { $updates_active = 1;}

                                  if ($parameter_active == 0) {
                                    $parameter = $db_parameter;
                                  }

                                  $installed = check_game_installed($row[5],$game);

                                  if ($gameswitch == 0 OR $installed[0] == 0) {
                                    $game = $db_game;
                                  }

                                  if ($db_game != $game) {
                                    msg_info(_gameserver_game_change);
                                  }
                                    if ($error == false) {

                                      $stmt = $mysqli->prepare("UPDATE gameservers SET map = ?,parameter = ?,restart = ?,restart_time = ?, autoupdate = ?, game = ?  WHERE id = ?");
                                      $stmt->bind_param('ssiiiii',$map,$parameter,$restart_active,$time,$updates_active,$game,$row[0]);
                                      $stmt->execute();
                                      $stmt->close();

                                    }

                                } elseif ($db_rank == 1) {

                                  $error = false; $parameter_active = 0; $restart_active = 0; $updates_active = 0;
                                  $map = htmlentities($_POST['map']);
                                  $parameter = htmlentities($_POST['parameter']);
                                  $slots = htmlentities($_POST['slots']);
                                  $port = htmlentities($_POST['port']);
                                  $time = htmlentities($_POST['time']);
                                  $clock = "o'clock";
                                  $time = str_replace(" Uhr", "",$time);
                                  $time = str_replace(" ".$clock, "",$time);
                                  if (isset($_POST['parameter_active'])) { $parameter_active = 1;}
                                  if (isset($_POST['restart_active'])) { $restart_active = 1;}
                                  if (isset($_POST['updates_active'])) { $updates_active = 1;}
                                  $game = htmlentities($_POST['game']);

                                  $stmt = $mysqli->prepare("SELECT id FROM templates WHERE name= ?");
                                  $stmt->bind_param('s', $game);
                                  $stmt->execute();
                                  $stmt->bind_result($game);
                                  $stmt->fetch();
                                  $stmt->close();

                                  $installed = check_game_installed($row[5],$game);

                                  if ($gameswitch == 0 OR $installed[0] == 0) {
                                    $game = $db_game;
                                  }

                                  if(!preg_match("/^[0-9]+$/",$slots)){ $msg = _gameserver_slots_invalid."<br>";  $error = true;}
                                  if(!preg_match("/^[0-9]+$/",$port)){ $msg = _gameserver_port_invalid."<br>";  $error = true;}
                                  if(!preg_match("/^[a-zA-Z0-9._-]+$/",$map)){ $msg = _gameserver_map_invalid."<br>";  $error = true;}
                                  if ($parameter != "") {
                                    if(!preg_match("/^[a-zA-Z0-9._ -]+$/",$parameter)){ $msg = _gameserver_map_invalid."<br>";  $error = true;}
                                  }
                                  if (port_exists($row[3],$port,$row[2])) { $msg = _gameserver_port_in_use; $error = true;}

                                  if ($error == false) {

                                    $stmt = $mysqli->prepare("UPDATE gameservers SET map = ?,parameter = ?, slots = ?, port = ?, parameters_active = ?, restart = ?,restart_time = ?,autoupdate = ?, game = ?  WHERE id = ?");
                                    $stmt->bind_param('ssiiiiiiii',$map,$parameter,$slots,$port,$parameter_active,$restart_active,$time,$updates_active,$game,$row[0]);
                                    $stmt->execute();
                                    $stmt->close();

                                    if ($db_game != $game) {
                                      msg_info(_gameserver_game_change);
                                    }

                                  } else {
                                    msg_error($msg);
                                  }
                                }
                              }

                              $stmt = $mysqli->prepare("SELECT map,parameter,slots,port,parameters_active,restart,restart_time,autoupdate FROM gameservers WHERE id = ?");
                              $stmt->bind_param('i', $row[0]);
                              $stmt->execute();
                              $stmt->bind_result($db_map,$db_parameter,$db_slots,$db_port,$db_parameter_active,$db_restart,$restart_time,$autoupdate);
                              $stmt->fetch();
                              $stmt->close();

                              $stmt = $mysqli->prepare("SELECT map_path FROM templates WHERE id = ?");
                              if ( false===$stmt ) { die('prepare() failed: ' . htmlspecialchars($mysqli->error));}
                              $rc = $stmt->bind_param('i', $game);
                              if ( false===$rc ) { die('bind_param() failed: ' . htmlspecialchars($stmt->error));}
                              $rc = $stmt->execute();
                              if ( false===$rc ) { die('execute() failed: ' . htmlspecialchars($stmt->error)); }
                              $stmt->bind_result($db_path);
                              $stmt->fetch();
                              $stmt->close();

                              ?>
                              <form class="form-horizontal" action="<?php echo "index.php?page=gameserver?settings-".$row[0]; ?>" method="post">
                                <div class="form-group">
                                  <label class="control-label col-sm-2"><?php echo _gameserver_map; ?>/<?php echo _gameserver_restart; ?>:</label>
                                  <div class="col-sm-2">
                                    <input class="typeahead form-control input-sm" type="text" name="map" value="<?php echo htmlentities($db_map); ?>">
                                    <?php
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
                                        $maps=array();
                                        foreach ($lines as &$element) {
                                          if (endsWith($element, ".bsp")) {
                                            //echo  "<option>".$element."</option>";
                                            //echo "<br>";
                                            $element = str_replace(".bsp","",$element);
                                            array_push($maps,$element);
                                          }
                                        }
                                     }
                                     ?>
                                  </div>
                                  <div class="col-sm-2">
                                    <select class="form-control input-sm" name="time">
                                      <?php
                                       $clock = "o'clock";
                                      for ($i = 0; $i <= 23; $i++) {
                                        if ($i == $restart_time) {
                                          if ($db_language == "de") {
                                            echo '<option selected="selected">'.$i.' Uhr</option>';
                                          } elseif ($db_language == "en") {
                                            echo '<option selected="selected">'.$i.' '.$clock.'</option>';
                                          }
                                        } else {
                                          if ($db_language == "de") {
                                            echo '<option>'.$i.' Uhr</option>';
                                          } elseif ($db_language == "en") {
                                            echo '<option>'.$i.' '.$clock.'</option>';
                                          }
                                        }
                                      }
                                      ?>
                                    </select>
                                  </div>
                                  <?php
                                  if ($db_restart == 1) {
                                   ?>  <script> function toggleOnrestart() { $('#toggle-restart').bootstrapToggle('on'); }  addLoadEvent(toggleOnrestart); </script> <?php
                                 } elseif ($db_restart == 0) {
                                   ?>  <script> function toggleOffrestart() { $('#toggle-restart').bootstrapToggle('off'); }  addLoadEvent(toggleOffrestart); </script> <?php
                                 }
                                   ?>
                                  <div class="col-sm-2">
                                      <input data-size="small" id="toggle-restart" data-height="20" type="checkbox" name="restart_active" data-toggle="toggle">
                                  </div>
                                  <script type="text/javascript">

                                    var substringMatcher = function(strs) {
                                    return function findMatches(q, cb) {
                                    var matches, substringRegex;

                                    // an array that will be populated with substring matches
                                    matches = [];

                                    // regex used to determine if a string contains the substring `q`
                                    substrRegex = new RegExp(q, 'i');

                                    // iterate through the pool of strings and for any string that
                                    // contains the substring `q`, add it to the `matches` array
                                    $.each(strs, function(i, str) {
                                      if (substrRegex.test(str)) {
                                        matches.push(str);
                                      }
                                    });

                                    cb(matches);
                                    };
                                    };

                                    var maps =  <?php echo json_encode($maps) ?>;

                                    $('.typeahead').typeahead({
                                    hint: true,
                                    highlight: true,
                                    minLength: 1
                                    },
                                    {
                                    name: 'maps',
                                    source: substringMatcher(maps)
                                    });
                                    </script>
                                </div>
                                <div class="form-group">
                                  <label class="control-label col-sm-2"><?php echo  _gameserver_parameter; ?>:</label>
                                  <div class="col-sm-4">
                                    <?php if ($db_rank == 1) { ?>
                                  <input type="text" class="form-control input-sm" name="parameter" value="<?php echo htmlentities($db_parameter);?>">
                                  <?php } elseif ($db_rank == 2 AND $db_parameter_active == 1) { ?>
                                    <input type="text" class="form-control input-sm" name="parameter" value="<?php echo htmlentities($db_parameter);?>"> <?php
                                  } elseif ($db_rank == 2 AND $db_parameter_active == 0) { ?>
                                    <input type="text" class="form-control input-sm" name="parameter" value="<?php echo htmlentities($db_parameter);?>" readonly="readonly"> <?php
                                  } ?>
                                  </div>
                                  <div class="col-sm-2">
                                    <?php if ($db_rank == 1) {
                                      echo '<input data-size="small" id="toggle-parameter" data-height="20" type="checkbox" name="parameter_active" data-toggle="toggle">';
                                    }
                                     if ($db_parameter_active == 1) {
                                      ?>  <script> function toggleOnparam() { $('#toggle-parameter').bootstrapToggle('on'); }  addLoadEvent(toggleOnparam); </script> <?php
                                    } elseif ($db_parameter_active == 0) {
                                      ?>  <script> function toggleOffparam() { $('#toggle-parameter').bootstrapToggle('off'); }  addLoadEvent(toggleOffparam); </script> <?php
                                    }
                                    ?>
                                  </div>
                                </div>
                                <?php
                                 if ($db_rank == 1) {
                                  ?>
                                  <div class="form-group">
                                    <label class="control-label col-sm-2">Slots/Port:</label>
                                    <div class="col-sm-2">
                                      <input type="text" class="form-control input-sm" name="slots" value="<?php echo htmlentities($db_slots);?>">
                                    </div>
                                    <div class="col-sm-2">
                                      <input type="text" class="form-control input-sm" name="port" value="<?php echo htmlentities($db_port);?>">
                                    </div>
                                  </div>
                              <?php  } ?>
                              <div class="form-group">
                                <label class="control-label col-sm-2"><?php echo _gameserver_game; ?>/Updates:</label>
                                <div class="col-sm-2">
                                  <select class="form-control input-sm form-control" name="game">
                                  <?php
                                  $query_2 = "SELECT name,id FROM templates ORDER by name ASC";

                                  if ($result_3 = $mysqli->query($query_2)) {

                                      /* fetch object array */
                                      while ($row_3 = $result_3->fetch_row()) {
                                        $installed = check_game_installed($row[5],$row_3[1]);
                                        if ($installed[0] == 1 AND $row_3[1] == $row[4]) {
                                          echo '<option selected="selected">'.$row_3[0].'</option>';
                                        } elseif  ($installed[0] == 1 ) {
                                          echo "<option>".$row_3[0]."</option>";
                                         } else {
                                          echo "<option disabled>".$row_3[0]."</option>";
                                         }
                                       }
                                       /* free result set */
                                       $result_3->close();
                                   }
                                   ?>
                                  </select>
                                </div>
                                <div class="col-sm-2">
                                    <input data-size="small" id="toggle-updates" data-height="20" type="checkbox" name="updates_active" data-toggle="toggle">
                                <?php   if ($autoupdate == 1) {
                                    ?>  <script> function toggleOnupdates() { $('#toggle-updates').bootstrapToggle('on'); }  addLoadEvent(toggleOnupdates); </script> <?php
                                  } elseif ($autoupdate == 0) {
                                    ?>  <script> function toggleOffupdates() { $('#toggle-updates').bootstrapToggle('off'); }  addLoadEvent(toggleOffupdates); </script> <?php
                                  }
                                  ?>
                                </div>
                              </div>
                                <div class="form-group">
                                  <div class="col-sm-offset-2 col-sm-10">
                                    <button type="submit" name="confirm-settings" class="btn btn-default btn-sm"><?php echo _button_save; ?></button>
                                  </div>
                                </div>
                              </form>

                              <?php
                            } elseif ($page == "gameserver?settings-".$row[0]."-addons") {

                              if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                                $query = "SELECT id, game_id, name,url ,path FROM addons ORDER by id";
                                  if ($result_2 = $mysqli->query($query)) {

                                   /* fetch object array */
                                   while ($row_2 = $result_2->fetch_assoc()) {
                                    $installed = get_addon_installed($dedi_id,$row_2["id"],$row[0]);
                                    if (isset($_POST['install_'.$row_2["id"]]) AND $installed[0] == 1) {
                                      $stmt = $mysqli->prepare("SELECT url,path FROM addons WHERE id = ?");
                                      $stmt->bind_param('i', $row_2["id"]);
                                      $stmt->execute();
                                      $stmt->bind_result($db_url,$db_path);
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
                                         $file = basename($row_2["url"]);
                                         if (endsWith($file,".zip")) {
                                          $ssh->exec("sudo -u ".$gs_login." screen -amds addon".$gs_login." bash -c 'cd /home/".$gs_login."/".$db_path.";wget ".$db_url.";unzip ".$file.";rm ".$file.";'");
                                         } elseif (endsWith($file,".tar")) {
                                          $ssh->exec("sudo -u ".$gs_login." screen -amds addon".$gs_login." bash -c 'cd /home/".$gs_login."/".$db_path.";wget ".$db_url.";tar xvf ".$file.";rm ".$file.";'");
                                         }
                                         $template = "addon";
                                         $stmt = $mysqli->prepare("INSERT INTO jobs(template_id,dedicated_id,type,type_id) VALUES (?, ?, ?, ?)");
                                         $stmt->bind_param('iiss',$row[0],$dedi_id,$template,$row_2["id"]);
                                         $stmt->execute();
                                         $stmt->close();
                                         msg_okay("Das Addon wird installiert, das kann etwas dauern :)");
                                       }
                                    } elseif (isset($_POST['remove_'.$row_2["id"]]) AND $installed[1] == "Addon ist installiert") {
                                      $stmt = $mysqli->prepare("SELECT url,path,folder FROM addons WHERE id = ?");
                                      $stmt->bind_param('i', $row_2["id"]);
                                      $stmt->execute();
                                      $stmt->bind_result($db_url,$db_path,$db_folder);
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
                                          $ssh->exec("sudo -u ".$gs_login." bash -c 'rm -r /home/".$gs_login."/".$db_path."/".$db_folder.";'");
                                          msg_okay("Das Addon wurde deinstalliert :)");

                                          $stmt = $mysqli->prepare("DELETE FROM addons_installed WHERE dedi_id = ? AND gs_id = ? AND addons_id = ?");
                                          $stmt->bind_param('iii',$dedi_id,$row[0],$row_2["id"]);
                                          $stmt->execute();
                                          $stmt->close();
                                       }
                                    }
                                   }
                                  /* free result set */
                                  $result_2->close();
                                  }

                              }
                                echo '<form action="index.php?page=gameserver?settings-'.$row[0].'-addons" method="post">'; ?>
                             <div class="col-sm-4">
                             <table class="table table-bordered">
                               <thead>
                                 <tr>
                                   <th colspan="1">Name</th>
                                   <th colspan="1"><?php echo _table_action; ?></th>
                                 </tr>
                               </thead>
                               <tbody>
                              <?php

                              $query = "SELECT id, game_id, name,url ,path FROM addons WHERE game_id = ".$row[4]." ORDER by id";

                                if ($result_2 = $mysqli->query($query)) {

                                 /* fetch object array */
                                 while ($row_2 = $result_2->fetch_assoc()) {

                                     $installed = get_addon_installed($dedi_id,$row_2["id"],$row[0]);
                                     echo "<tr>";
                                     echo "<td>" . $row_2["name"] . "</td>";
                                     if ($installed[0] == 0) {
                                        echo '<td><button type="submit" name="install_'.$row_2["id"].'" class="btn btn-xs btn-success" disabled>'.$installed[1].'</button>';
                                        echo '<button style="margin-left:2px;" type="submit" name="remove_'.$row_2["id"].'" class="btn btn-xs btn-danger">'._dedicated_remove.'</button></td>';
                                     } else {
                                       echo '<td><button type="submit" name="install_'.$row_2["id"].'" class="btn btn-xs btn-success">'._dedicated_install.'</button> <button style="margin-left:2px;" type="submit" name="install_'.$row_2["id"].'" class="btn btn-xs btn-danger" disabled>'._dedicated_remove.'</button> </td>';
                                     }
                                     echo "</tr>";
                                   }
                                   /* free result set */
                                         $result_2->close();
                                     } ?>
                               </tbody>
                             </table>
                             </div>
                           </form>
                             <?php
                              }
                            }
                          if ($page == "gameserver?console-".$row[0] AND $row[2] == $_SESSION['user_id'] or $page == "gameserver?console-".$row[0] AND $db_rank == 1) {

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


                            if ($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['console_submit'])) {


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

                            $stmt = $mysqli->prepare("SELECT name_internal,type FROM templates WHERE id = ?");
                            $stmt->bind_param('i', $game);
                            $stmt->execute();
                            $stmt->bind_result($name_internal,$type);
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
                               if ($type == "steamcmd" AND $row[1] == 0) {
                                 $text = $ssh->exec('cd /home/'.$gs_login.'/game;sudo -u '.$gs_login.' cat -A screenlog.0');
                               } elseif ($type == "steamcmd" AND $row[1] == 1) {
                                $text = $ssh->exec('cd /home/'.$gs_login.'/game;sudo -u '.$gs_login.' cat -A steam.log');
                               } elseif ($type == "image") {
                                 $text = $ssh->exec('cd /home/'.$gs_login.'/;sudo -u '.$gs_login.' cat -A screenlog.0');
                               }
                               $lines = explode("^M$",$text);
                               echo '<form class="form-horizontal" action="index.php?page=gameserver?console-'.$gs_select.'" method="post">';
                               echo '<div class="form-group"><div class="col-sm-10">';
                               echo '<textarea id="console" class="form-control input-sm"" rows="16" readonly="readonly">';
                               foreach ($lines as &$element) {
                                 $element = str_replace("$","",$element);
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
                                      <button class="btn btn-primary btn-sm" name="console_submit" type="submit"><?php echo _button_save; ?></button>
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
                          if ($page == "gameserver?pw-".$row[0] AND $row[2] == $_SESSION['user_id'] or $page == "gameserver?pw-".$row[0] AND $db_rank == 1) {

                            $stmt = $mysqli->prepare("SELECT ip,port,user,password,language FROM dedicated WHERE id = ?");
                            $stmt->bind_param('i', $row[5]);
                            $stmt->execute();
                            $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password,$language);
                            $stmt->fetch();
                            $stmt->close();

                            $user = $row[6];
                            $password = generatePassword();

                            $ssh = new Net_SSH2($dedi_ip,$dedi_port);
                             if (!$ssh->login($dedi_login, $dedi_password)) {
                               echo '
                               <div class="alert alert-danger" role="alert">
                                 <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                 <span class="sr-only">Error:</span>
                                 Login failed
                               </div>';
                             } else {

                               $ssh->enablePTY();
                               $ssh->exec('sudo passwd '.$user);
                               if ($language == "English") {
                               $ssh->read('Enter new UNIX password:');
                               $ssh->write($password . "\n");
                               $ssh->read('Retype new UNIX password:');
                               $ssh->write($password . "\n");
                               $ssh->read('passwd: password updated successfully');
                               } elseif ($language == "Deutsch") {
                                 $ssh->read('Geben Sie ein neues UNIX-Passwort ein:');
                                 $ssh->write($password . "\n");
                                 $ssh->read('Geben Sie das neue UNIX-Passwort erneut ein:');
                                 $ssh->write($password . "\n");
                                 $ssh->read('passwd: Passwort erfolgreich geändert');
                               }
                               $ssh->disablePTY();
                               $ssh->read('[prompt]');

                               $status = 1;
                               $stmt = $mysqli->prepare("UPDATE gameservers SET gs_password = ?  WHERE id = ?");
                               $stmt->bind_param('si',$password,$row[0]);
                               $stmt->execute();
                               $stmt->close();

                               msg_okay(_gameserver_pw_changed);

                               //event_add(12,$row[3].":".$row[7]); Lots of Spam meh

                             }
                          }
                        }
                        /* free result set */
                        $result->close();
                    }

                if ($page == "gameserver?add" and $db_rank == 1) {

                  if ($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['confirm'])) {

                     $error = false; $msg = ""; $mass = 0;
                     $port = htmlentities($_POST['port']); $slots = htmlentities($_POST['slots']);
                     $dedicated = htmlentities($_POST['dedicated']); $type = htmlentities($_POST['type']);
                     $map = htmlentities($_POST['map']);
                     $user_gs = htmlentities($_POST['users']);
                     if (isset($_POST['mass'])) {  $mass = 1;}
                     if (isset($_POST['mass_ammount'])) { $mass_ammount = $_POST['mass_ammount'];}

                     if ($mass == 1) {
                        if(!preg_match("/^[0-9]+$/",$mass_ammount)){ $msg = _gameserver_mass_error."<br>";  $error = true;}
                        if ($mass_ammount > 14) {
                          $mass_ammount = 14;
                        }
                        if ($mass_ammount < 0) {
                          $mass_ammount = 2;
                        }
                     }
                     if(!preg_match("/^[0-9]+$/",$slots)){ $msg = _gameserver_slots_invalid."<br>";  $error = true;}
                     if(!preg_match("/^[0-9]+$/",$port)){ $msg = _gameserver_port_invalid."<br>";  $error = true;}
                     if(!preg_match("/^[0-9]+$/",$dedicated)){ $msg = _gameserver_dedicated_invalid."<br>";  $error = true;}
                     if(!preg_match("/^[0-9]+$/",$user_gs)){ $msg = _gameserver_user_invalid."<br>";  $error = true;}
                     if(!preg_match("/^[a-zA-Z0-9._-]+$/",$type)){ $msg = _gameserver_game_invalid."<br>";  $error = true;}


                     $stmt = $mysqli->prepare("SELECT ip,port,user,password,id,language FROM dedicated WHERE id = ?");
                     $stmt->bind_param('i', $dedicated);
                     $stmt->execute();
                     $stmt->bind_result($dedi_ip,$dedi_port,$dedi_login,$dedi_password,$dedi_id,$dedi_language);
                     $stmt->fetch();
                     $stmt->close();

                     $stmt = $mysqli->prepare("SELECT id,appid FROM templates WHERE name = ?");
                     $stmt->bind_param('s', $type);
                     $stmt->execute();
                     $stmt->bind_result($game,$db_appid);
                     $stmt->fetch();
                     $stmt->close();

                     if ($game == "") { exit; }

                     if (port_exists($dedi_ip,$port)) { $msg = _gameserver_port_in_use; $error = true;}
                     if (check_dedi_id($dedicated)) {$msg = _gameserver_dedi_id_invalid; $error = true;}
                     if (check_template($game)) { $msg = _message_template_error; $error = true;}
                     if (check_user_id($user_gs)) { $msg = _gameserver_user_invalid; $error = true;}

                     $installed = check_game_installed($dedicated,$game);

                     if ($installed[0] != 1) { $error = true;$msg = $installed[1];}

                      if ($error == false) {

                        $i = 1;
                        if ($mass == 0) { $mass_ammount =1;}
                        while ($i <= $mass_ammount) {

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
                             $copy = "screen -amds cp".$gs_login." bash -c 'sudo cp -R /home/".$dedi_login."/templates/".$type."/* /home/".$gs_login.";sudo cp -R /home/".$dedi_login."/templates/".$type."/linux32/libstdc++.so.6 /home/".$gs_login."/game/bin;sudo chown -R ".$gs_login.":".$gs_login." /home/".$gs_login.";chmod a-w /home/".$gs_login.";sudo rm /home/".$gs_login."/screenlog.0;'";
                             $ssh->exec($copy);

                             $version = 0;
                             if ($db_appid != "") {
                               $version = ask_steam_for_cookies($db_appid);
                             }


                             $stmt = $mysqli->prepare("INSERT INTO gameservers(user_id,game,slots,ip,port,gs_login,gs_password,map,dedi_id,version) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                             $stmt->bind_param('isisisssii', $user_gs,$game,$slots,$dedi_ip,$port,$gs_login,$gs_password,$map,$dedi_id,$version);
                             $stmt->execute();
                             $stmt->close();

                             $user_u_count = $user_u_count +1;

                             $stmt = $mysqli->prepare("UPDATE users SET u_count = ? WHERE id = ?");
                             $stmt->bind_param('ii', $user_u_count, $user_gs);
                             $stmt->execute();
                             $stmt->close();

                             msg_okay(_gameserver_installed);

                            // event_add(6,"Der Gameserver ".$dedi_ip.":".$port." wurde hinzugefügt.");
                            event_add(6,$dedi_ip.":".$port);

                           }
                           $i++;
                           while (1 != 2 ) {
                            $port =$port +4;
                              if (port_exists($dedi_ip,$port) == false) { break;}
                            }
                        }

                   } else {
                     msg_error('Something went wrong, '.$msg);
                   }
                }
                  ?>
                  <form class="form-horizontal" action="index.php?page=gameserver?add" method="post">
                    <div class="form-group">
                      <label class="control-label col-sm-2"><?php echo _gameserver_game; ?>/Root:</label>
                      <div class="col-sm-4">
                        <select class="form-control input-sm" name="type">
                        <?php
                        $query = "SELECT name FROM templates ORDER by name ASC";

                         if ($stmt = $mysqli->prepare($query)) {
                             $stmt->execute();
                             $stmt->bind_result($db_name);

                             while ($stmt->fetch()) {
                               echo "<option>" . htmlentities($db_name) . "</option>";
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
                               echo '<option value="'. htmlentities($db_id) .'">'. htmlentities($db_name) .'</option>';
                             }
                             $stmt->close();
                         }  ?>
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2">Map/<?php echo _dedicated_user; ?>:</label>
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
                               echo '<option value="'. htmlentities($db_id) .'">'. htmlentities($db_name) .'</option>';
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
                      <label class="control-label col-sm-2"><?php echo _gameserver_mass; ?>:</label>
                      <input data-size="small" data-width="15" type="checkbox" name="mass" data-toggle="toggle">
                      <div class="col-sm-2">
                      <select name="mass_ammount" class="form-control input-sm">
                        <option>2</option>
                        <option>4</option>
                        <option>6</option>
                        <option>8</option>
                        <option>10</option>
                        <option>12</option>
                        <option>14</option>
                      </select>
                    </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" name="confirm" class="btn btn-default btn-sm"><?php echo _button_save; ?></button>
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
                          <th><?php echo _gameserver_user; ?></th>
                          <th><?php echo _gameserver_game; ?></th>
                          <th>IP+Port</th>
                          <th><?php echo _gameserver_slots; ?></th>
                          <th><?php echo _gameserver_map; ?></th>
                          <th><?php echo _gameserver_ftp_login; ?></th>
                          <th><?php echo _gameserver_ftp_password; ?></th>
                          <th>Start/Stop</th>
                          <th><?php echo _table_action; ?></th>
                        </tr>
                      </thead>
                      <tbody>
                     <?php

                     $query = "SELECT user_id, game, ip, port,slots, gs_login, gs_password, id, map,status,is_running,player_online FROM gameservers ORDER by id";

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
                              echo "<td>" . htmlentities($db_user_name) . "</td>";
                              echo "<td>" . htmlentities(get_template_by_id($row["game"])) . "</td>";
                              echo "<td>" . htmlentities($row["ip"] .":".$row["port"])."</td>";
                              echo "<td>" . htmlentities($row['player_online']."/". $row["slots"]) . "</td>";
                              echo "<td>" . htmlentities($row["map"]) . "</td>";
                              echo "<td>" . htmlentities($row["gs_login"]) . "</td>";
                              echo '<td>' . htmlentities($row["gs_password"]) .' <a href="index.php?page=gameserver?pw-'.$row["id"].'"  class="btn btn-primary btn-xs pull-right"><i class="fa fa-refresh"></i></a></td>';
                              if ($row["status"] == 0) {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$row["id"].'"  class="btn btn-success btn-xs">'._gameserver_button_restart.'</a> <a href="index.php?page=gameserver?stop-'.$row["id"].'"  class="btn btn-danger btn-xs">'._gameserver_button_stop.'</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$row["id"].'"  class="btn btn-warning btn-xs">'._gameserver_button_reinstall.'</a> <a href="index.php?page=gameserver?update-'.$row["id"].'"  class="btn btn-primary btn-xs">'._gameserver_button_update.'</a> <a href="index.php?page=gameserver?console-'.$row["id"].'" class="btn btn-primary btn-xs">'._gameserver_button_console.'</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'-addons"  class="btn btn-primary btn-xs">'._gameserver_button_addons.'</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'"  class="btn btn-primary btn-xs">'._gameserver_button_settings.'</a>  <a href="index.php?page=gameserver?delete-'.$row["id"].'"  class="btn btn-danger btn-xs"><i class="fa fa-remove"></i></a>  </td>';
                              } else {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$row["id"].'"  class="btn btn-success btn-xs" disabled>'._gameserver_button_restart.'</a> <a href="index.php?page=gameserver?stop-'.$row["id"].'"  class="btn btn-danger btn-xs" disabled >'._gameserver_button_stop.'</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$row["id"].'"  class="btn btn-warning btn-xs" disabled>'._gameserver_button_reinstall.'</a> <a href="index.php?page=gameserver?update-'.$row["id"].'"  class="btn btn-primary btn-xs" disabled>'._gameserver_button_update.'</a> <a href="index.php?page=gameserver?console-'.$row["id"].'"  class="btn btn-primary btn-xs">'._gameserver_button_console.'</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'-addons"  class="btn btn-primary btn-xs">'._gameserver_button_addons.'</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'"  class="btn btn-primary btn-xs">'._gameserver_button_settings.'</a>  <a href="index.php?page=gameserver?delete-'.$row["id"].'"  class="btn btn-danger btn-xs" disabled><i class="fa fa-remove"></i></a>  </td>';
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
                              echo "<td>" . htmlentities($db_user_name) . "</td>";
                              echo "<td>" . htmlentities(get_template_by_id($row["game"])) . "</td>";
                              echo "<td>" . htmlentities($row["ip"] .":".$row["port"]) ."</td>";
                              echo "<td>" . htmlentities($row["slots"]) . "</td>";
                              echo "<td>" . htmlentities($row["map"]) . "</td>";
                              echo "<td>" . htmlentities($row["gs_login"]) . "</td>";
                              echo '<td>' . htmlentities($row["gs_password"]) .' <a href="index.php?page=gameserver?pw-'.$row["id"].'"  class="btn btn-primary btn-xs pull-right"><i class="fa fa-refresh"></i></a></td>';
                              if ($row["status"] == 0) {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$row["id"].'"  class="btn btn-success btn-xs">'._gameserver_button_restart.'</a> <a href="index.php?page=gameserver?stop-'.$row["id"].'"  class="btn btn-danger btn-xs">'._gameserver_button_stop.'</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$row["id"].'"  class="btn btn-warning btn-xs">'._gameserver_button_reinstall.'</a> <a href="index.php?page=gameserver?update-'.$row["id"].'"  class="btn btn-primary btn-xs">'._gameserver_button_update.'</a> <a href="index.php?page=gameserver?console-'.$row["id"].'"  class="btn btn-primary btn-xs">'._gameserver_button_console.'</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'-addons"  class="btn btn-primary btn-xs">'._gameserver_button_addons.'</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'"  class="btn btn-primary btn-xs">'._gameserver_button_settings.'</a>  <a href="index.php?page=gameserver?delete-'.$row["id"].'"  class="btn btn-danger btn-xs" disabled><i class="fa fa-remove"></i></a>  </td>';
                              } else {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$row["id"].'"  class="btn btn-success btn-xs" disabled>'._gameserver_button_restart.'</a> <a href="index.php?page=gameserver?stop-'.$row["id"].'"  class="btn btn-danger btn-xs" disabled >'._gameserver_button_stop.'</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$row["id"].'"  class="btn btn-warning btn-xs" disabled>'._gameserver_button_reinstall.'</a> <a href="index.php?page=gameserver?update-'.$row["id"].'"  class="btn btn-primary btn-xs" disabled>'._gameserver_button_update.'</a> <a href="index.php?page=gameserver?console-'.$row["id"].'"  class="btn btn-primary btn-xs">'._gameserver_button_console.'</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'-addons"  class="btn btn-primary btn-xs">'._gameserver_button_addons.'</a> <a href="index.php?page=gameserver?settings-'.$row["id"].'"  class="btn btn-primary btn-xs">'._gameserver_button_settings.'</a>  <a href="index.php?page=gameserver?delete-'.$row["id"].'"  class="btn btn-danger btn-xs" disabled><i class="fa fa-remove"></i></a>  </td>';
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
