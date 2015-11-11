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
               <div class="col-lg-9">
                 <?php
            //      if ($_SERVER['REQUEST_METHOD'] == 'POST') {


                    $query = "SELECT id,status,user_id FROM gameservers ORDER by id";

                    if ($result = $mysqli->query($query)) {

                        /* fetch object array */
                        while ($row = $result->fetch_row()) {
                          if ($page == "gameserver?start-".$row[0] AND $row[1] == 0 AND $row[2] == $_SESSION['user_id'] or $page == "gameserver?start-".$row[0] AND $row[1] == 0 AND $db_rank == 1) {
                            $gs_select = $row[0];

                             $stmt = $mysqli->prepare("SELECT ip,game,gs_login,slots,map,port,parameter FROM gameservers WHERE id = ?");
                             $stmt->bind_param('i', $gs_select);
                             $stmt->execute();
                             $stmt->bind_result($ip,$game,$gs_login,$slots,$map,$port,$parameter);
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
                                $ssh->exec('sudo -u '.$gs_login.' screen -adms game /home/'.$gs_login.'/game/srcds_run -game '.$name_internal.' -port '.$port.' +map '.$map.' -maxplayers '.$slots .' ' .$parameter);
                                echo '<meta http-equiv="refresh" content="2; URL=index.php?page=gameserver">';
                                echo '
                                <div class="alert alert-success" role="alert">
                                  <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                  <span class="sr-only">Success:</span>
                                  Done
                                </div>';
                              }
                              break;
                          }
                          if ($page == "gameserver?stop-".$row[0]  AND $row[1] == 0 AND $row[2] == $_SESSION['user_id'] or $page == "gameserver?stop-".$row[0] AND $row[1] == 0 AND $db_rank == 1) {
                            $gs_select = $row[0];

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
                               echo '
                               <div class="alert alert-success" role="alert">
                                 <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                 <span class="sr-only">Success:</span>
                                 Done
                               </div>';
                             }
                             break;
                          }
                          if ($page == "gameserver?delete-".$row[0] AND $row[1] == 0 AND $db_rank == 1) {

                              $gs_select = $row[0];

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
                                 $ssh->exec('sudo userdel -r '.$gs_login);

                                 $stmt = $mysqli->prepare("DELETE FROM gameservers WHERE id = ?");
                                 $stmt->bind_param('i', $gs_select);
                                 $stmt->execute();
                                 $stmt->close();

                                 echo '
                                 <div class="alert alert-success" role="alert">
                                   <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                   <span class="sr-only">Success:</span>
                                   Done
                                 </div>';
                               }
                               break;
                            }
                          if ($page == "gameserver?settings-".$row[0]  AND $row[1] == 0 AND $row[2] == $_SESSION['user_id'] or $page == "gameserver?settings-".$row[0] AND $row[1] == 0 AND $db_rank == 1) {

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
                                  <input type="text" class="form-control" name="map" value="<?php echo $db_map;?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-sm-2">Paramter:</label>
                                <div class="col-sm-8">
                                  <input type="text" class="form-control" name="parameter" value="<?php echo $db_parameter;?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                  <button type="submit" name="confirm-settings" class="btn btn-default">Submit</button>
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

                       $result_id = 0;
                       $type_t = "template";
                       $stmt = $mysqli->prepare("SELECT id FROM jobs WHERE dedicated_id = ? AND type = ? AND type_id = ?");
                       $stmt->bind_param('iss', $dedi_id,$type_t,$type);
                       $stmt->execute();
                       $stmt->bind_result($result_id);
                       $stmt->fetch();
                       $stmt->close();

                       if ($result_id != 0) { $error = true;$msg = "Installation läuft noch";}


                       if ($error == false) {

                         $ssh = new Net_SSH2($dedi_ip,$dedi_port);
                          if (!$ssh->login($dedi_login, $dedi_password)) {
                            echo '
                            <div class="alert alert-danger" role="alert">
                              <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                              <span class="sr-only">Success:</span>
                              Login failed
                            </div>';
                            exit;
                          } else {

                            $output =  $ssh->exec('if ! test -d /home/'.$dedi_login.'/templates/'.$type.'; then echo "1"; fi');
                            if ($output == 1) {
                                $error = true;
                                $msg = "Template ist nicht installiert";
                            }
                          }

                          if ($error == false) {

                         $ssh = new Net_SSH2($dedi_ip,$dedi_port);
                          if (!$ssh->login($dedi_login, $dedi_password)) {
                            echo '
                            <div class="alert alert-danger" role="alert">
                              <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                              <span class="sr-only">Success:</span>
                              Login failed
                            </div>';
                            exit;
                          } else {

                            $output =  $ssh->exec('if ! test -d /home/'.$dedi_login.'/templates/'.$type.'; then echo "1"; fi');
                            if ($output == 1) {
                                $error = true;
                                $msg = "Template ist nicht installiert";
                            }
                          }

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

                            $stmt = $mysqli->prepare("INSERT INTO gameservers(user_id,user_name,game,slots,ip,port,gs_login,gs_password,map,dedi_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
                            $stmt->bind_param('issisisssi', $user_gs,$user_name,$type,$slots,$dedi_ip,$port,$gs_login,$gs_password,$map,$dedi_id);
                            $stmt->execute();
                            $stmt->close();

                            $user_u_count = $user_u_count +1;

                            $stmt = $mysqli->prepare("UPDATE users SET u_count = ? WHERE id = ?");
                            $stmt->bind_param('ii', $user_u_count, $user_gs);
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
                         Something went wrong, '.$msg.'
                       </div>';

                     }
                    } else {

                      echo '
                      <div class="alert alert-danger" role="alert">
                        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                        <span class="sr-only">Error:</span>
                        Something went wrong, '.$msg.'
                      </div>';

                    }

                }
                if ($page == "gameserver?add" and $db_rank == 1) {

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
                        <input type="text" class="form-control" name="map" placeholder="gm_flatgrass">
                      </div>
                      <div class="col-sm-4">
                        <select class="form-control" name="users">
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
                        <input type="text" class="form-control" name="port" placeholder="27015">
                      </div>
                      <div class="col-sm-4">
                        <input type="text" class="form-control" name="slots" placeholder="14">
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" name="confirm" class="btn btn-default">Submit</button>
                      </div>
                    </div>
                  </form>
           <?php } elseif ($page == "gameserver") {
                    ?>
                    <form action="index.php?page=gameserver" method="post">
                  <?php if ($db_rank == 1) { echo '<a  style="margin-bottom:2px;" href="index.php?page=gameserver?add"  class="btn pull-right btn-success">+</a>';}  ?>
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

                     $query = "SELECT user_id, user_name, game, ip, port,slots, gs_login, gs_password, id, map,status FROM gameservers ORDER by id";

                      if ($stmt = $mysqli->prepare($query)) {
                          $stmt->execute();
                          $stmt->bind_result($db_user_id, $db_user_name,$db_game,$db_ip,$db_port,$db_slots,$db_gs_login,$db_gs_password,$db_gs_id,$db_map,$db_status);

                          while ($stmt->fetch()) {
                            if ($db_rank == 1) {
                              echo "<tr>";
                              echo "<td>" . $db_user_name . "</td>";
                              echo "<td>" . $db_game . "</td>";
                              echo "<td>" . $db_ip . "</td>";
                              echo "<td>" . $db_port . "</td>";
                              echo "<td>" . $db_slots . "</td>";
                              echo "<td>" . $db_map . "</td>";
                              echo "<td>" . $db_gs_login . "</td>";
                              echo "<td>" . $db_gs_password . "</td>";
                              if ($db_status == 0) {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$db_gs_id.'"  class="btn btn-success btn-sm">(Re)Start</a> <a href="index.php?page=gameserver?stop-'.$db_gs_id.'"  class="btn btn-danger btn-sm">Stop</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$db_gs_id.'"  class="btn btn-warning btn-sm">Reinstall</a> <a href="index.php?page=gameserver?update-'.$db_gs_id.'"  class="btn btn-primary btn-sm">Update</a> <a href="index.php?page=gameserver?settings-'.$db_gs_id.'"  class="btn btn-primary btn-sm">Einstellungen</a>  <a href="index.php?page=gameserver?delete-'.$db_gs_id.'"  class="btn btn-danger btn-sm">X</a>  </td>';
                              } else {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$db_gs_id.'"  class="btn btn-success btn-sm" disabled>(Re)Start</a> <a href="index.php?page=gameserver?stop-'.$db_gs_id.'"  class="btn btn-danger btn-sm" disabled >Stop</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$db_gs_id.'"  class="btn btn-warning btn-sm" disabled>Reinstall</a> <a href="index.php?page=gameserver?update-'.$db_gs_id.'"  class="btn btn-primary btn-sm" disabled>Update</a> <a href="index.php?page=gameserver?settings-'.$db_gs_id.'"  class="btn btn-primary btn-sm" disabled>Einstellungen</a>  <a href="index.php?page=gameserver?delete-'.$db_gs_id.'"  class="btn btn-danger btn-sm" disabled>X</a>  </td>';
                              }
                              echo "</tr>";
                            } elseif ($db_rank == 2 AND $db_user_id == $_SESSION['user_id']) {
                              echo "<tr>";
                              echo "<td>" . $db_user_name . "</td>";
                              echo "<td>" . $db_game . "</td>";
                              echo "<td>" . $db_ip . "</td>";
                              echo "<td>" . $db_port . "</td>";
                              echo "<td>" . $db_slots . "</td>";
                              echo "<td>" . $db_map . "</td>";
                              echo "<td>" . $db_gs_login . "</td>";
                              echo "<td>" . $db_gs_password . "</td>";
                              if ($db_status == 0) {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$db_gs_id.'"  class="btn btn-success btn-sm">(Re)Start</a> <a href="index.php?page=gameserver?stop-'.$db_gs_id.'"  class="btn btn-danger btn-sm">Stop</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$db_gs_id.'"  class="btn btn-warning btn-sm">Reinstall</a> <a href="index.php?page=gameserver?update-'.$db_gs_id.'"  class="btn btn-primary btn-sm">Update</a> <a href="index.php?page=gameserver?settings-'.$db_gs_id.'"  class="btn btn-primary btn-sm">Einstellungen</a>  <a href="index.php?page=gameserver?delete-'.$db_gs_id.'"  class="btn btn-danger btn-sm">X</a>  </td>';
                              } else {
                                echo '<td> <a href="index.php?page=gameserver?start-'.$db_gs_id.'"  class="btn btn-success btn-sm" disabled>(Re)Start</a> <a href="index.php?page=gameserver?stop-'.$db_gs_id.'"  class="btn btn-danger btn-sm" disabled >Stop</a>  </td>';
                                echo '<td> <a href="index.php?page=gameserver?reinstall-'.$db_gs_id.'"  class="btn btn-warning btn-sm" disabled>Reinstall</a> <a href="index.php?page=gameserver?update-'.$db_gs_id.'"  class="btn btn-primary btn-sm" disabled>Update</a> <a href="index.php?page=gameserver?settings-'.$db_gs_id.'"  class="btn btn-primary btn-sm" disabled>Einstellungen</a>  <a href="index.php?page=gameserver?delete-'.$db_gs_id.'"  class="btn btn-danger btn-sm" disabled>X</a>  </td>';
                              }
                              echo "</tr>";
                            }
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
