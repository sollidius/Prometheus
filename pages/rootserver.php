<?php

session_start();

$db_rank = 2;
//Load user Data from DB
$stmt = $mysqli->prepare("SELECT rank,id,language FROM users WHERE id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($db_rank,$db_id,$db_language);
$stmt->fetch();
$stmt->close();

if ($db_language == "de") {
    require_once('lang/de.lang.php');
  } elseif ($db_language == "en") {
    require_once('lang/en.lang.php');
  }

//header
$title = _title_rootserver;
include 'header.php';
set_include_path('components/phpseclib');
include('Net/SSH2.php');


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
             <div class="col-lg-12">
               <?php
                    $query = "SELECT id,ip,port,user,password FROM dedicated ORDER by id";

                    if ($result = $mysqli->query($query)) {

                      /* fetch object array */
                      while ($row = $result->fetch_assoc()) {
                        if ($page == "rootserver?manage=".$row["id"]) {

                          if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                            //Install Games

                              $query = "SELECT id,name,type,type_name FROM templates ORDER by id";

                              if ($result_2 = $mysqli->query($query)) {

                                  /* fetch object array */
                                  while ($row_2 = $result_2->fetch_assoc()) {
                                    if (isset($_POST['game_'.$row_2["id"]])) {

                                      $id = $row["id"];

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

                                         $installed = get_game_installed($id,$row_2["name"]);
                                        $output =  $ssh->exec('if ! test -d /home/'.$user.'/templates; then echo "1"; fi');
                                        if ($output == 1) { $ssh->exec('mkdir /home/'.$user.'/templates'); }
                                        $output =  $ssh->exec('if ! test -d /home/'.$user.'/templates/'.$row_2["name"].'; then echo "1"; fi');
                                        if ($output == 1) { $ssh->exec('mkdir /home/'.$user.'/templates/'.$row_2["name"]); }
                                        if ($installed[0] == 1) {
                                          //Steamcmd
                                          if ($row_2["type"] == "steamcmd") {

                                            //$ssh->exec('cd /home/'.$user.'/templates/'.$row[1] . ';wget --no-check-certificate https://steamcdn-a.akamaihd.net/client/installer/steamcmd_linux.tar.gz');
                                            $ssh->exec('cd /home/'.$user.'/templates/'.$row_2["name"] . ';wget https://steamcdn-a.akamaihd.net/client/installer/steamcmd_linux.tar.gz');
                                            $ssh->exec('cd /home/'.$user.'/templates/'.$row_2["name"] . ';tar xvf steamcmd_linux.tar.gz');
                                            $ssh->exec('cd /home/'.$user.'/templates/'.$row_2["name"] . ';/home/'.$user.'/templates/'.$row_2["name"].'/steamcmd.sh +force_install_dir /home/'.$user.'/templates/'.$row_2["name"].'/game  +login anonymous +app_update '.$row_2["type_name"].' validate +quit >> /home/'.$user.'/templates/'.$row_2["name"].'/steam.log &');

                                            $template = "template";
                                            $stmt = $mysqli->prepare("INSERT INTO jobs(template_id,dedicated_id,type,type_id) VALUES (?, ?, ?, ?)");
                                            $stmt->bind_param('iiss', $row_2["id"], $id,$template,$row_2["name"]);
                                            $stmt->execute();
                                            $stmt->close();

                                            msg_okay("Das Template wird erstellt, das kann etwas dauern :)");
                                          } elseif ($row_2["type"] == "image") {

                                            $file = basename($row_2["type_name"]);
                                            if (endsWith($file,".zip")) {
                                              $name = $row_2["name"]; $type_name = $row_2["type_name"];
                                              $cmd = 'cd /home/'.$user.'/templates/'.$name.';screen -A -m -d -L -S image'.$name.' bash -c "cd /home/'.$user.'/templates/'.$name.';wget '.$type_name.';unzip '.$file.';rm '.$file.'"';
                                              $ssh->exec($cmd);
                                            } elseif (endsWith($file,".tar")) {
                                              $name = $row_2["name"]; $type_name = $row_2["type_name"];
                                              $cmd = 'cd /home/'.$user.'/templates/'.$name.';screen -A -m -d -L -S image'.$name.' bash -c "cd /home/'.$user.'/templates/'.$name.';wget '.$type_name.';tar xvf '.$file.';rm '.$file.'"';
                                              $ssh->exec($cmd);
                                            } else {
                                              //Die Hard 4.0
                                              msg_error("Nur .tar oder .zip");
                                              exit;
                                            }
                                            $template = "image";
                                            $stmt = $mysqli->prepare("INSERT INTO jobs(template_id,dedicated_id,type,type_id) VALUES (?, ?, ?, ?)");
                                            $stmt->bind_param('iiss', $row_2["id"], $id,$template,$row_2["name"]);
                                            $stmt->execute();
                                            $stmt->close();
                                            msg_okay("Das Image wird erstellt, das kann etwas dauern :)");
                                          }
                                        } else {
                                          msg_error($installed[1]);
                                        }
                                       }
                                       break;
                                    }   elseif (isset($_POST['remove_'.$row_2["id"]])) {
                                      $error = false;

                                      if (check_game_in_use($row_2["name"],$row["ip"])) { $msg ="Es exestieren noch Installierte Gameserver mit diesen Spiel."; $error = true;}
                                      if (check_template_job_exists($row["id"],$row_2["id"])) { $msg ="Installation des Templates läuft noch."; $error = true;}

                                      if ($error == false) {

                                        $ssh = new Net_SSH2($row["ip"],$row["port"]);
                                         if (!$ssh->login($row["user"], $row["password"])) {
                                           msg_error("Login failed");
                                           exit;
                                         } else {
                                           $ssh->exec('rm -r /home/'.$row["user"].'/templates/'.$row_2["name"]);

                                           $status_game = 1;
                                           $stmt = $mysqli->prepare("DELETE FROM dedicated_games WHERE dedi_id = ? AND template_id = ? AND status = ?");
                                           $stmt->bind_param('iii', $row["id"],$row_2["id"],$status_game);
                                           $stmt->execute();
                                           $stmt->close();
                                           msg_okay("Das Template wurde auf dem Rootserver gelöscht.");

                                         }
                                      } else {
                                      msg_warning($msg);
                                      }
                                    }
                                  }
                                  /* free result set */
                                  $result_2->close();
                              }
                            }
                        echo '<form class="form-horizontal" action="index.php?page=rootserver?manage='.$row["id"].'" method="post">'; ?>
                         <div class="col-sm-4">
                         <table class="table table-bordered">
                           <thead>
                             <tr>
                               <th colspan="1">Name</th>
                               <th colspan="1">Aktion</th>
                             </tr>
                           </thead>
                           <tbody>
                          <?php

                          $query = "SELECT name, type,type_name,id FROM templates ORDER by id";

                            if ($result_2 = $mysqli->query($query)) {

                             /* fetch object array */
                             while ($row_2 = $result_2->fetch_assoc()) {
                                 $installed = get_game_installed($row['id'],$row_2["name"]);
                                 echo "<tr>";
                                 echo "<td>" . $row_2["name"] . "</td>";
                                 if ($installed[0] == 0) {
                                    echo '<td><button type="submit" name="game_'.$row_2["id"].'" class="btn btn-xs btn-success" disabled>'.$installed[1].'</button>';
                                    echo '<button style="margin-left:2px;" type="submit" name="remove_'.$row_2["id"].'" class="btn btn-xs btn-danger">Deinstallieren</button></td>';
                                 } else {
                                   echo '<td><button type="submit" name="game_'.$row_2["id"].'" class="btn btn-xs btn-success">Installieren</button> <button style="margin-left:2px;" type="submit" name="remove_'.$row_2["id"].'" class="btn btn-xs btn-danger" disabled>Deinstallieren</button> </td>';
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
                       } elseif ($page == "rootserver?edit=".$row["id"]) {


                         if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                          if (isset($_POST['confirm'])) {

                            $error = false;

                            $ip = htmlentities($_POST['ip']); $port = htmlentities($_POST['port']);

                            if (ip_exists($ip,$row["id"])) { $msg = "Die IP exestiert bereits."; $error = true;}
                            if(!preg_match("/^[0-9]+$/",$port)){ $msg = "Der Port enth&auml;lt ung&uuml;ltige Zeichen (0-9 sind Erlaubt)<br>";  $error = true;}

                            if ($error == false) {

                              $stmt = $mysqli->prepare("UPDATE dedicated SET ip = ?,port = ? WHERE id = ?");
                              $stmt->bind_param('sii', $ip, $port,$row["id"]);
                              $stmt->execute();
                              $stmt->close();


                               msg_okay("Der Rootserver wurde aktualisiert.");

                           } else {
                             msg_error('Something went wrong, '.$msg);
                          }

                          }
                        }


                         echo '<form class="form-horizontal" action="index.php?page=rootserver?edit='.$row["id"].'" method="post">';
                         ?>
                           <div class="form-group">
                             <label class="control-label col-sm-2">IP/Port</label>
                             <div class="col-sm-4">
                               <input type="text" class="form-control input-sm" name="ip" value="<?php echo $row["ip"] ?>" >
                             </div>
                             <div class="col-sm-2">
                               <input type="text" class="form-control input-sm" name="port" value="<?php echo $row["port"] ?>">
                             </div>
                           </div>
                           <div class="form-group">
                             <div class="col-sm-offset-2 col-sm-10">
                               <button type="submit" name="confirm" class="btn btn-default btn-sm">Abschicken</button>
                             </div>
                           </div>
                         </form>

                         <?php

                      } elseif ($page == "rootserver?remove=".$row["id"]) {

                      $error = false;

                      if (check_template_exist_in_games_dedi_id($row["id"])) { $error = true; $msg = "Templates noch installiert.";}

                      if ($error == false) {

                        $stmt = $mysqli->prepare("DELETE FROM dedicated WHERE id = ?");
                        $stmt->bind_param('i', $row["id"]);
                        $stmt->execute();
                        $stmt->close();

                        $stmt = $mysqli->prepare("DELETE FROM jobs WHERE dedicated_id = ?");
                        $stmt->bind_param('i', $row["id"]);
                        $stmt->execute();
                        $stmt->close();

                        msg_okay("Rootserver gelöscht.");

                      } else {

                        msg_warning($msg);

                      }

                      } elseif ($page == "rootserver?delete=".$row["id"]) {
                        //soonTM

                        msg_warning("soonTM");

                      }
                      }
                    /* free result set */
                    $result->close();
                  }

               if ($page == "rootserver?add") {

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                 if (isset($_POST['confirm'])) {

                   $error = false;
                   $status = 1;

                   $name = htmlentities($_POST['name']); $ip = htmlentities($_POST['ip']); $port = htmlentities($_POST['port']);
                   $user = htmlentities($_POST['user']); $password = htmlentities($_POST['password']); $root = htmlentities($_POST['root']); $root_password = htmlentities($_POST['root_password']);
                   $os = htmlentities($_POST['os']);
                   $language = htmlentities($_POST['language']);
                   $os_bit = "64";
                   $os_version = "";


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

                        $vsftpd = "\n###Prometheus###\n";
                        $vsftpd.= "anonymous_enable=NO\n";
                        $vsftpd.= "write_enable=YES\n";
                        $vsftpd.= "chroot_local_user=YES\n";
                        $vsftpd.= "write_enable=YES\n";
                        $vsftpd.= "local_enable=YES\n";
                        $vsftpd.= "allow_writeable_chroot=YES\n";
                        $vsftpd.= "################\n";


                        if ($os == "Debian 7 32bit") {

                          $ssh->setTimeout(45);
                          $ssh->exec('apt-get update');
                          $ssh->exec('apt-get -y install sudo');
                          $ssh->exec('apt-get -y install screen');
                          $ssh->exec('apt-get -y install libtinfo5 libncurses5');
                          $ssh->exec('apt-get -y install lib32stdc++6');
                          $ssh->exec('apt-get -y install vsftpd');
                          $ssh->exec('apt-get -y install unzip');
                          $ssh->exec('apt-get -y install gawk');
                          $os_version = "Debian 7"; $os_bit = "32";

                        } elseif ($os == "Debian 7 64bit") {

                          $ssh->exec('dpkg --add-architecture i386');
                          $ssh->setTimeout(45);
                          $ssh->exec('apt-get update');
                          $ssh->exec('apt-get -y install sudo');
                          $ssh->exec('apt-get -y install screen');
                          $ssh->exec('apt-get -y install ia32-libs');
                          $ssh->exec('apt-get -y install libtinfo5:i386 libncurses5:i386');
                          $ssh->exec('apt-get -y install lib32stdc++6');
                          $ssh->exec('apt-get -y install lib32gcc1');
                          $ssh->exec('apt-get -y install vsftpd');
                          $ssh->exec('apt-get -y install unzip');
                          $ssh->exec('apt-get -y install gawk');
                          $os_version = "Debian 7"; $os_bit = "64";

                        } elseif ($os == "Debian 8 32bit") {

                          $ssh->setTimeout(45);
                          $ssh->exec('apt-get update');
                          $ssh->exec('apt-get -y install sudo');
                          $ssh->exec('apt-get -y install screen');
                          $ssh->exec('apt-get -y install libtinfo5 libncurses5');
                          $ssh->exec('apt-get -y install lib32stdc++6');
                          $ssh->exec('apt-get -y install vsftpd');
                          $ssh->exec('apt-get -y install unzip');
                          $ssh->exec('apt-get -y install gawk');
                          $os_version = "Debian 8"; $os_bit = "32";

                        } elseif ($os == "Debian 8 64bit") {

                          $ssh->exec('dpkg --add-architecture i386');
                          $ssh->setTimeout(45);
                          $ssh->exec('apt-get update');
                          $ssh->exec('apt-get -y install sudo');
                          $ssh->exec('apt-get -y install screen');
                          $ssh->exec('apt-get -y install ia32-libs');
                          $ssh->exec('apt-get -y install libtinfo5:i386 libncurses5:i386');
                          $ssh->exec('apt-get -y install lib32stdc++6');
                          $ssh->exec('apt-get -y install lib32gcc1');
                          $ssh->exec('apt-get -y install vsftpd');
                          $ssh->exec('apt-get -y install unzip');
                          $ssh->exec('apt-get -y install gawk');
                          $os_version = "Debian 8"; $os_bit = "64";

                        } elseif ($os == "Ubuntu 12.04 32bit") {

                            $ssh->setTimeout(45);
                            $ssh->exec('apt-get update');
                            $ssh->exec('apt-get -y install sudo');
                            $ssh->exec('apt-get -y install screen');
                            $ssh->exec('apt-get -y install libtinfo5 libncurses5');
                            $ssh->exec('apt-get -y install lib32stdc++6');
                            $ssh->exec('apt-get -y install vsftpd');
                            $ssh->exec('apt-get -y install unzip');
                            $ssh->exec('apt-get -y install gawk');
                            $os_version = "Ubuntu 12.04"; $os_bit = "32";

                          } elseif ($os == "Ubuntu 12.04 64bit") {

                            $ssh->exec('dpkg --add-architecture i386');
                            $ssh->setTimeout(45);
                            $ssh->exec('apt-get update');
                            $ssh->exec('apt-get -y install sudo');
                            $ssh->exec('apt-get -y install screen');
                            $ssh->exec('apt-get -y install ia32-libs');
                            $ssh->exec('apt-get -y install libtinfo5:i386 libncurses5:i386');
                            $ssh->exec('apt-get -y install lib32stdc++6');
                            $ssh->exec('apt-get -y install lib32gcc1');
                            $ssh->exec('apt-get -y install vsftpd');
                            $ssh->exec('apt-get -y install unzip');
                            $ssh->exec('apt-get -y install gawk');
                            $os_version = "Ubuntu 12.04"; $os_bit = "64";

                          } elseif ($os == "Ubuntu 14.04 32bit") {

                            $ssh->setTimeout(45);
                            $ssh->exec('apt-get update');
                            $ssh->exec('apt-get -y install sudo');
                            $ssh->exec('apt-get -y install screen');
                            $ssh->exec('apt-get -y install libtinfo5 libncurses5');
                            $ssh->exec('apt-get -y install lib32stdc++6');
                            $ssh->exec('apt-get -y install vsftpd');
                            $ssh->exec('apt-get -y install unzip');
                            $ssh->exec('apt-get -y install gawk');
                            $os_version = "Ubuntu 14.04"; $os_bit = "32";

                          } elseif ($os == "Ubuntu 14.04 64bit") {

                            $ssh->exec('dpkg --add-architecture i386');
                            $ssh->setTimeout(45);
                            $ssh->exec('apt-get update');
                            $ssh->exec('apt-get -y install sudo');
                            $ssh->exec('apt-get -y install screen');
                            $ssh->exec('apt-get -y install ia32-libs');
                            $ssh->exec('apt-get -y install libtinfo5:i386 libncurses5:i386');
                            $ssh->exec('apt-get -y install lib32stdc++6');
                            $ssh->exec('apt-get -y install lib32gcc1');
                            $ssh->exec('apt-get -y install vsftpd');
                            $ssh->exec('apt-get -y install unzip');
                            $ssh->exec('apt-get -y install gawk');
                            $os_version = "Ubuntu 14.04"; $os_bit = "64";


                        } else {
                                msg_error('Something went wrong, Invalid OS');
                                exit;
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
                        $ssh->exec('echo "'.$vsftpd.'" >> /etc/vsftpd.conf');
                        $ssh->exec('service vsftpd restart');

                        $stmt = $mysqli->prepare("INSERT INTO dedicated(name,os,ip,port,user,password,status,language,os_bit) VALUES (?, ?, ?, ? ,? ,? ,?, ? ,?)");
                        $stmt->bind_param('sssissisi', $name,$os_version,$ip,$port,$user,$password,$status,$language,$os_bit);
                        $stmt->execute();
                        $stmt->close();

                        unset($root_password);
                        unset($root);

                      }

                      msg_okay("Der Rootserver wurde angelegt.");

                  } else {
                    msg_error('Something went wrong, '.$msg);
                 }

                 }
               }

                ?>

                <form class="form-horizontal" action="index.php?page=rootserver?add" method="post">
                  <div class="form-group">
                    <label class="control-label col-sm-2">Name:</label>
                    <div class="col-sm-6">
                      <input type="text" class="form-control input-sm" name="name" placeholder="Chewbacca">
                    </div>
                    <div class="col-sm-2">
                      <select class="form-control input-sm" name="os">
                        <option>Debian 7 32bit</option>
                        <option>Debian 7 64bit</option>
                        <option>Debian 8 32bit</option>
                        <option>Debian 8 64bit</option>
                        <option>Ubuntu 12.04 32bit</option>
                        <option>Ubuntu 12.04 64bit</option>
                        <option>Ubuntu 14.04 32bit</option>
                        <option>Ubuntu 14.04 64bit</option>
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

                <?php
              } elseif ($page == "rootserver" or startsWith($page, "rootserver?remove=") or startsWith($page, "rootserver?delete=")) {
                  ?>
                  <form action="index.php?page=rootserver" method="post">
                  <a  style="margin-bottom:2px;" href="index.php?page=rootserver?add"  class="btn pull-right btn-success btn-xs"><i class="fa fa-plus"></i></a>
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>IP</th>
                        <th>Port</th>
                        <th>Benutzer</th>
                        <th>Passwort</th>
                        <th>Status</th>
                        <th>Aktion</th>
                      </tr>
                    </thead>
                    <tbody>
                   <?php

                   $query = "SELECT name,ip,port,user,status,id FROM dedicated ORDER by id";

                   if ($result = $mysqli->query($query)) {

                     /* fetch object array */
                     while ($row = $result->fetch_assoc()) {
                          echo "<tr>";
                          echo "<td>" . $row["name"] . "</td>";
                          echo "<td>" . $row["ip"] . "</td>";
                          echo "<td>" . $row["port"] . "</td>";
                          echo "<td>" . $row["user"] . "</td>";
                          echo "<td> ******** </td>";
                          if ($row["status"] == 0) { echo "<td>Unbekannt</td>"; }
                          if ($row["status"] == 1) { echo '<td>Installiert: ';

                            $query = 'SELECT template_id FROM dedicated_games WHERE dedi_id = '.$row["id"].' ORDER by id';
                              $count = 0;
                              if ($result_2 = $mysqli->query($query)) {

                                  /* fetch object array */
                                  while ($row_2 = $result_2->fetch_assoc()) {
                                    if ($count >= 1) {
                                    echo ', '.get_template_by_id($row_2["template_id"]);
                                  } else {
                                    echo get_template_by_id($row_2["template_id"]);
                                  }
                                    $count++;
                                  }
                                $result_2->close();
                              }
                          echo '</td>';
                          echo '<td>';
                          echo '<a href="index.php?page=rootserver?edit='.$row["id"].'" class="btn pull-left btn-primary btn-xs">Editieren</a>
                                  <a style="margin-left:2px;" href="index.php?page=rootserver?manage='.$row["id"].'" class="btn pull-left btn-primary btn-xs">Verwalten</a>
                                  <a style="margin-left:2px;" href="index.php?page=rootserver?remove='.$row["id"].'" class="btn pull-left btn-danger btn-xs"><i class="fa fa-eraser"></i></a>
                                  <a style="margin-left:2px;" href="index.php?page=rootserver?delete='.$row["id"].'" class="btn pull-left btn-danger btn-xs"><i class="fa fa-remove"></i></a>';
                          echo '</td>'; }
                          echo "</tr>";
                        }
                          $result->close();
                      } ?>
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

} elseif ($_SESSION['login'] == 1 and $db_rank != 1) { header('Location: index.php?page=dashboard');
} else {  header('Location: index.php');}


//Footer
include 'footer.html';
?>
