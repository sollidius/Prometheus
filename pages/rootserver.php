<?php
//header
$title = "Rootserver";
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

if ($_SESSION['login'] == 1 and $db_rank == 1) {



?>
<div class="container-fluid">
  <div class="row">
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

                  //Install Games

                    $query = "SELECT id,name,type,type_name FROM templates ORDER by id";

                    if ($result = $mysqli->query($query)) {

                        /* fetch object array */
                        while ($row = $result->fetch_assoc()) {
                          if (isset($_POST['game_'.$row["id"]])) {

                            $id = htmlentities($_POST['send_root_id']);

                            $stmtz = $mysqli->prepare("SELECT ip,port,user,password FROM dedicated WHERE id = ?");
                            $stmtz->bind_param('i', $id);
                            $stmtz->execute();
                            $stmtz->bind_result($ip,$port,$user,$password);
                            $stmtz->fetch();
                            $stmtz->close();

                            $ssh = new Net_SSH2($ip,$port);
                             if (!$ssh->login($user, $password)) {
                               msg_error('Login failed');
                               exit;
                             } else {

                              $output =  $ssh->exec('if ! test -d /home/'.$user.'/templates; then echo "1"; fi');
                              if ($output == 1) { $ssh->exec('mkdir /home/'.$user.'/templates'); }
                              $output =  $ssh->exec('if ! test -d /home/'.$user.'/templates/'.$row["name"].'; then echo "1"; fi');
                              if ($output == 1) { $ssh->exec('mkdir /home/'.$user.'/templates/'.$row["name"]);

                                //Steamcmd
                                if ($row["type"] == "steamcmd") {

                                  //$ssh->exec('cd /home/'.$user.'/templates/'.$row[1] . ';wget --no-check-certificate https://steamcdn-a.akamaihd.net/client/installer/steamcmd_linux.tar.gz');
                                  $ssh->exec('cd /home/'.$user.'/templates/'.$row["name"] . ';wget https://steamcdn-a.akamaihd.net/client/installer/steamcmd_linux.tar.gz');
                                  $ssh->exec('cd /home/'.$user.'/templates/'.$row["name"] . ';tar xvf steamcmd_linux.tar.gz');
                                  $ssh->exec('cd /home/'.$user.'/templates/'.$row["name"] . ';/home/'.$user.'/templates/'.$row["name"].'/steamcmd.sh +force_install_dir /home/'.$user.'/templates/'.$row["name"].'/game  +login anonymous +app_update '.$row["type_name"].' validate +quit >> /home/'.$user.'/templates/'.$row["name"].'/steam.log &');

                                  $template = "template";
                                  $stmt = $mysqli->prepare("INSERT INTO jobs(dedicated_id,type,type_id) VALUES (?, ?, ?)");
                                  $stmt->bind_param('iss', $id,$template,$row["name"]);
                                  $stmt->execute();
                                  $stmt->close();

                                  msg_okay("Das Template wird erstellt, das kann etwas dauern :)");
                                  $_POST['add_games_'.$id] = 1;
                                }
                              } else {
                                $_POST['add_games_'.$id] = 1;
                                msg_error('Spiel ist bereits installiert');
                              }
                             }
                          }
                        }

                        /* free result set */
                        $result->close();
                    }

                    //Check Root
                    $send_root_id = 0;
                    $query = "SELECT id FROM dedicated ORDER by id";

                     if ($stmt = $mysqli->prepare($query)) {
                         $stmt->execute();
                         $stmt->bind_result($db_id);

                         while ($stmt->fetch()) {
                             if (isset($_POST['add_games_'.$db_id])) {
                                    $send_root_id = $db_id;
                                    $_POST['add_games'] = 1;
                             }
                         }
                         $stmt->close();
                     }


                   if (isset($_POST['confirm'])) {

                     $error = false;
                     $status = 1;

                     $name = $_POST['name']; $ip = $_POST['ip']; $port = $_POST['port'];
                     $user = $_POST['user']; $password = $_POST['password']; $root = $_POST['root']; $root_password = $_POST['root_password'];
                     $os = $_POST['os'];
                     $language = $_POST['language'];


                     if (exists_entry("name","dedicated","name",$name) == true) { $error = true; $msg = "Exestiert bereits";}
                     if (exists_entry("ip","dedicated","ip",$ip) == true) { $error = true; $msg = "Exestiert bereits";}
                     if(!preg_match("/^[a-zA-Z0-9]+$/",$name)){ $msg = "Der Name enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)<br>";  $error = true;}
                     if(!preg_match("/^[a-zA-Z0-9]+$/",$user)){ $msg = "Der Username enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)<br>";  $error = true;}
                     if(!preg_match("/^[a-zA-Z0-9]+$/",$root)){ $msg = "Der Root Benutzer enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)<br>";  $error = true;}
                     if(!preg_match("/^[0-9]+$/",$port)){ $msg = "Der Port enth&auml;lt ung&uuml;ltige Zeichen (0-9 sind Erlaubt)<br>";  $error = true;}

                     if ($error == false) {

                       $ssh = new Net_SSH2($ip,$port);
                        if (!$ssh->login($root, $root_password)) {
                          msg_error('Login failed');
                          exit;
                        } else {
                          if ($os == "Debian 7") {

                            $ssh->exec('dpkg --add-architecture i386');
                            $ssh->setTimeout(45);
                            $ssh->exec('apt-get update');
                            $ssh->exec('apt-get -y install sudo');
                            $ssh->exec('apt-get -y install screen');
                            $ssh->exec('apt-get -y install ia32-libs');
                            $ssh->exec('apt-get -y install libtinfo5 libncurses5');
                            $ssh->exec('apt-get -y install lib32stdc++6');



                          } elseif ($os == "Debian 8") {

                            $ssh->exec('dpkg --add-architecture i386');
                            $ssh->setTimeout(45);
                            $ssh->exec('apt-get update');
                            $ssh->exec('apt-get -y install sudo');
                            $ssh->exec('apt-get -y install screen');
                            $ssh->exec('apt-get -y install libc6:i386');
                            $ssh->exec('apt-get -y install libtinfo5:i386 libncurses5:i386');
                            $ssh->exec('apt-get -y install lib32stdc++6');

                          }

                          $ssh->exec('sudo useradd -m -d /home/'.$user.' -s /bin/bash '.$user);
                          $ssh->enablePTY();
                          $ssh->exec('sudo passwd '.$user);
                          if ($language == "Englisch") {
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
                          $ssh->exec("usermod -a -G sudo ".$user);
                          $ssh->exec('echo "%sudo ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers');
                          $ssh->read('[prompt]');

                          $stmt = $mysqli->prepare("INSERT INTO dedicated(name,os,ip,port,user,password,status,language) VALUES (?, ?, ?, ? ,? ,? ,?, ?)");
                          $stmt->bind_param('sssissis', $name,$os,$ip,$port,$user,$password,$status,$language);
                          $stmt->execute();
                          $stmt->close();

                          unset($root_password);
                          unset($root);

                        }

                        msg_okay("Der Rootserver wurde angelegt.");

                   } else {
                     msg_error('Something went wrong, '.$msg);
                   }

                 } else if (isset($_POST['add_games'])) {

                  ?>
                  <form class="form-horizontal" action="index.php?page=rootserver" method="post">
                   <div class="col-sm-4">
                   <table class="table table-bordered">
                     <thead>
                       <tr>
                         <th colspan="2">Name</th>
                       </tr>
                     </thead>
                     <tbody>
                    <?php

                    $query = "SELECT name, type,type_name,id FROM templates ORDER by id";

                     if ($stmt = $mysqli->prepare($query)) {
                         $stmt->execute();
                         $stmt->bind_result($db_name, $db_type,$db_type_name,$db_id);

                         while ($stmt->fetch()) {
                           echo "<tr>";
                           echo "<td>" . $db_name . "</td>";
                           echo '<td><button style="margin-bottom:2px;" type="submit" name="game_'.$db_id.'" class="btn btn-xs center-block btn-success">Installieren</button></td>';
                           echo "</tr>";
                         }
                         $stmt->close();
                     }
                     $mysqli->close(); ?>
                     </tbody>
                   </table>
                   </div>
                  <input type="hidden" name="send_root_id" value="<?php echo $send_root_id; ?>">
                 </form>


                   <?php
                  } else {

                ?>

                <form class="form-horizontal" action="index.php?page=rootserver" method="post">
                  <div class="form-group">
                    <label class="control-label col-sm-2">Name:</label>
                    <div class="col-sm-6">
                      <input type="text" class="form-control input-sm" name="name" placeholder="Chewbacca">
                    </div>
                    <div class="col-sm-2">
                      <select class="form-control input-sm" name="os">
                        <option>Debian 7</option>
                        <option>Debian 8</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-sm-2">IP/Port:</label>
                    <div class="col-sm-4">
                      <input type="text" class="form-control input-sm" name="ip" placeholder="127.0.0.1">
                    </div>
                    <div class="col-sm-2">
                      <input type="text" class="form-control input-sm" name="port" placeholder="22">
                    </div>
                    <div class="col-sm-2">
                      <select class="form-control input-sm" name="language">
                        <option>Englisch</option>
                        <option>Deutsch</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-sm-2" for="pwd">Benutzer/Passwort:</label>
                    <div class="col-sm-4">
                      <input type="text" class="form-control input-sm" name="user" placeholder="prometheus">
                    </div>
                    <div class="col-sm-4">
                      <input type="password" class="form-control input-sm" name="password" placeholder="123456">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-sm-2" for="pwd">Root/Passwort:</label>
                    <div class="col-sm-4">
                      <input type="text" class="form-control input-sm" name="root" placeholder="root">
                    </div>
                    <div class="col-sm-4">
                      <input type="password" class="form-control input-sm" name="root_password" placeholder="123456">
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                      <button type="submit" name="confirm" class="btn btn-default btn-sm">Abschicken</button>
                    </div>
                  </div>
                </form>



                <?php }
                } else {
                  ?>
                  <form action="index.php?page=rootserver" method="post">
                  <button style="margin-bottom:2px;" type="submit" name="add" class="btn pull-right btn-success btn-xs">+</button>
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>IP</th>
                        <th>Port</th>
                        <th>Benutzer</th>
                        <th>Passwort</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                   <?php

                   $query = "SELECT name,ip,port,user,status,id FROM dedicated ORDER by id";

                    if ($stmt = $mysqli->prepare($query)) {
                        $stmt->execute();
                        $stmt->bind_result($db_name, $db_ip,$db_port,$db_user,$db_status,$db_id);

                        while ($stmt->fetch()) {
                          echo "<tr>";
                          echo "<td>" . $db_name . "</td>";
                          echo "<td>" . $db_ip . "</td>";
                          echo "<td>" . $db_port . "</td>";
                          echo "<td>" . $db_user . "</td>";
                          echo "<td> ******** </td>";
                          if ($db_status == 0) { echo "<td>Unbekannt</td>"; }
                          if ($db_status == 1) { echo '<td>Installiert <button style="margin-bottom:2px;" type="submit" name="add_games_'.$db_id.'" class="btn btn-xs pull-right btn-success">+</button></td>'; }
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
 </div>
</div>
</div>


<?php

} elseif ($_SESSION['login'] == 1 and $db_rank != 1) { header('Location: index.php?page=dashboard');
} else {  header('Location: index.php');}


//Footer
include 'footer.html';
?>
