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
  }

//header
$title = _title_rootserver;
include 'header.php';
set_include_path('components/phpseclib');
include('Net/SSH2.php');


if ($_SESSION['login'] === 1 and $db_rank === 1) {


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

                              $query = "SELECT id,name,type,type_name,app_set_config FROM templates ORDER by id";

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

                                         $installed = get_game_installed($id,$row_2["id"]);
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
                                            $db_app_set_config = $row_2["app_set_config"];
                                            if ($db_app_set_config == "") {
                                                $ssh->exec('cd /home/'.$user.'/templates/'.$row_2["name"] . ';/home/'.$user.'/templates/'.$row_2["name"].'/steamcmd.sh +force_install_dir /home/'.$user.'/templates/'.$row_2["name"].'/game  +login anonymous +app_update '.$row_2["type_name"].' validate +quit >> /home/'.$user.'/templates/'.$row_2["name"].'/steam.log &');
                                            } elseif ($db_app_set_config == "needed") {
                                                 $ssh->exec('cd /home/'.$user.'/templates/'.$row_2["name"] . ';rm steam.log;/home/'.$user.'/templates/'.$row_2["name"].'/steamcmd.sh +force_install_dir /home/'.$user.'/templates/'.$row_2["name"].'/game  +login anonymous +app_update '.$row_2["type_name"].' validate +quit >> /home/'.$user.'/templates/'.$row_2["name"].'/steam.log &');
                                            } elseif ($db_app_set_config != "") {
                                                $ssh->exec('cd /home/'.$user.'/templates/'.$row_2["name"] . ';/home/'.$user.'/templates/'.$row_2["name"].'/steamcmd.sh +force_install_dir /home/'.$user.'/templates/'.$row_2["name"].'/game  +login anonymous +app_set_config '.$row_2["type_name"].' mod '.$db_app_set_config.' +app_update '.$row_2["type_name"].' validate +quit >> /home/'.$user.'/templates/'.$row_2["name"].'/steam.log &');
                                            }

                                            $template = "template"; $zero = 0;
                                            $stmt = $mysqli->prepare("INSERT INTO jobs(template_id,dedicated_id,type,type_id) VALUES (?, ?, ?, ?)");
                                            $stmt->bind_param('iisi', $row_2["id"], $id,$template,$zero);
                                            $stmt->execute();
                                            $stmt->close();

                                            msg_okay(_dedicated_template_created);
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
                                              msg_error(_dedicated_file_error);
                                              exit;
                                            }
                                            $template = "image"; $zero = 0;
                                            $stmt = $mysqli->prepare("INSERT INTO jobs(template_id,dedicated_id,type,type_id) VALUES (?, ?, ?, ?)");
                                            $stmt->bind_param('iisi', $row_2["id"], $id,$template,$zero);
                                            $stmt->execute();
                                            $stmt->close();
                                            msg_okay(_dedicated_image_created);
                                          }
                                        } else {
                                          msg_error($installed[1]);
                                        }
                                       }
                                       break;
                                    }   elseif (isset($_POST['remove_'.$row_2["id"]])) {
                                      $error = false;

                                      if (check_game_in_use($row_2["id"],$row["ip"])) { $msg =_dedicated_message_gameserver_exists; $error = true;}
                                      if (check_template_job_exists($row["id"],$row_2["id"])) { $msg =_dedicated_message_installation_running; $error = true;}

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
                                           msg_okay(_dedicated_message_template_deleted);

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
                        msg_info(_dedicated_message_info);
                        echo '<form class="form-horizontal" action="index.php?page=rootserver?manage='.$row["id"].'" method="post">'; ?>
                         <div class="col-sm-5">
                         <table class="table table-bordered">
                           <thead>
                             <tr>
                               <th colspan="1"><?php echo _users_name; ?></th>
                               <th colspan="1"><?php echo _table_action; ?></th>
                             </tr>
                           </thead>
                           <tbody>
                          <?php

                          $query = "SELECT name, type,type_name,id FROM templates ORDER by name ASC";

                            if ($result_2 = $mysqli->query($query)) {

                             /* fetch object array */
                             while ($row_2 = $result_2->fetch_assoc()) {
                                 $installed = get_game_installed($row['id'],$row_2["id"]);
                                 echo "<tr>";
                                 echo "<td>" . $row_2["name"] . "</td>";
                                 if ($installed[0] == 0) {
                                    echo '<td><button type="submit" name="game_'.$row_2["id"].'" class="btn btn-xs btn-success" disabled>'.$installed[1].'</button>';
                                    echo '<button style="margin-left:2px;" type="submit" name="remove_'.$row_2["id"].'" class="btn btn-xs btn-danger">'._dedicated_remove.'</button></td>';
                                 } else {
                                   echo '<td><button type="submit" name="game_'.$row_2["id"].'" class="btn btn-xs btn-success">'._dedicated_install.'</button> <button style="margin-left:2px;" type="submit" name="remove_'.$row_2["id"].'" class="btn btn-xs btn-danger" disabled>'._dedicated_remove.'</button> </td>';
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

                            if (ip_exists($ip,$row["id"])) { $msg = _dedicated_message_ip_exists; $error = true;}
                            if(!preg_match("/^[0-9]+$/",$port)){ $msg = _dedicated_message_port_exists."<br>";  $error = true;}

                            if ($error == false) {

                              $stmtz = $mysqli->prepare("SELECT ip FROM dedicated WHERE id = ?");
                              $stmtz->bind_param('i', $row["id"]);
                              $stmtz->execute();
                              $stmtz->bind_result($ip_before);
                              $stmtz->fetch();
                              $stmtz->close();

                              $stmt = $mysqli->prepare("UPDATE dedicated SET ip = ?,port = ? WHERE id = ?");
                              $stmt->bind_param('sii', $ip, $port,$row["id"]);
                              $stmt->execute();
                              $stmt->close();

                              $stmt = $mysqli->prepare("UPDATE gameservers SET ip = ? WHERE ip = ?");
                              $stmt->bind_param('ss', $ip, $ip_before);
                              $stmt->execute();
                              $stmt->close();


                               msg_okay(_dedicated_message_updated);

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
                               <button type="submit" name="confirm" class="btn btn-default btn-sm"><?php echo _button_save; ?></button>
                             </div>
                           </div>
                         </form>

                         <?php

                      } elseif ($page == "rootserver?remove=".$row["id"]) {

                      $error = false;

                      if (check_template_exist_in_games_dedi_id($row["id"])) { $error = true; $msg = _dedicated_message_template_installed;}

                      if ($error == false) {

                        $stmt = $mysqli->prepare("DELETE FROM dedicated WHERE id = ?");
                        $stmt->bind_param('i', $row["id"]);
                        $stmt->execute();
                        $stmt->close();

                        $stmt = $mysqli->prepare("DELETE FROM jobs WHERE dedicated_id = ?");
                        $stmt->bind_param('i', $row["id"]);
                        $stmt->execute();
                        $stmt->close();

                        msg_okay(_dedicated_deleted);

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


                   if (exists_entry("name","dedicated","name",$name) == true) { $error = true; $msg = _dedicated_message_exists;}
                   if (exists_entry("ip","dedicated","ip",$ip) == true) { $error = true; $msg = _dedicated_message_exists;}
                   if(!preg_match("/^[a-zA-Z0-9._-]+$/",$name)){ $msg = _dedicated_message_name_invalid."<br>";  $error = true;}
                   if(!preg_match("/^[a-zA-Z0-9]+$/",$user)){ $msg = _dedicated_message_username_invalid."<br>";  $error = true;}
                   if(!preg_match("/^[a-zA-Z0-9]+$/",$root)){ $msg = _dedicated_message_root_invalid."<br>";  $error = true;}
                   if(!preg_match("/^[0-9]+$/",$port)){ $msg = _dedicated_message_port_invalid."<br>";  $error = true;}
                   if (isValidIP($ip) == false) { $msg = _dedicated_message_ip_invalid."<br>";  $error = true;}

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
                        //SSL
                        $vsftpd.= "rsa_cert_file=/etc/ssl/private/vsftpd.pem\n";
                        $vsftpd.= "rsa_private_key_file=/etc/ssl/private/vsftpd.pem\n";
                        $vsftpd.= "ssl_enable=YES\n";
                        $vsftpd.= "allow_anon_ssl=NO\n";
                        $vsftpd.= "ssl_tlsv1=YES\n";
                        $vsftpd.= "ssl_sslv2=NO\n";
                        $vsftpd.= "ssl_sslv3=NO\n";
                        $vsftpd.= "require_ssl_reuse=NO\n";
                        $vsftpd.= "ssl_ciphers=HIGH\n";
                        $vsftpd.= "################\n";


                        if ($os == "Debian 8 32bit") {

                          $ssh->setTimeout(45);
                          $ssh->exec('apt-get update');
                          $ssh->exec('apt-get -y install sudo');
                          $ssh->exec('apt-get -y install screen');
                          $ssh->exec('apt-get -y install libtinfo5 libncurses5');
                          $ssh->exec('apt-get -y install lib32stdc++6');
                          $ssh->exec('apt-get -y install vsftpd');
                          $ssh->exec('apt-get -y install unzip');
                          $ssh->exec('apt-get -y install gawk');
                          $ssh->exec("apt-get -y install openssl");
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
                          $ssh->exec("apt-get -y install openssl");
                          $os_version = "Debian 8"; $os_bit = "64";

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
                            $ssh->exec("apt-get -y install openssl");
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
                            $ssh->exec("apt-get -y install openssl");
                            $os_version = "Ubuntu 14.04"; $os_bit = "64";

                          } elseif ($os == "Ubuntu 15.04 32bit") {

                            $ssh->setTimeout(45);
                            $ssh->exec('apt-get update');
                            $ssh->exec('apt-get -y install sudo');
                            $ssh->exec('apt-get -y install screen');
                            $ssh->exec('apt-get -y install libtinfo5 libncurses5');
                            $ssh->exec('apt-get -y install lib32stdc++6');
                            $ssh->exec('apt-get -y install vsftpd');
                            $ssh->exec('apt-get -y install unzip');
                            $ssh->exec('apt-get -y install gawk');
                            $ssh->exec("apt-get -y install openssl");
                            $os_version = "Ubuntu 15.04"; $os_bit = "32";

                          } elseif ($os == "Ubuntu 15.04 64bit") {

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
                            $ssh->exec("apt-get -y install openssl");
                            $os_version = "Ubuntu 15.04"; $os_bit = "64";


                        } else {
                                msg_error('Something went wrong, Invalid OS');
                                exit;
                        }


                        $ssh->exec('sudo useradd -m -d /home/'.$user.' -s /bin/bash '.$user);
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
                        $ssh->exec("usermod -a -G sudo ".$user);
                        $ssh->exec('echo "%sudo ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers');
                        $ssh->exec('echo "'.$vsftpd.'" >> /etc/vsftpd.conf');
                        $ssh->exec('sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/vsftpd.pem -out /etc/ssl/private/vsftpd.pem -subj "/C=AU/ST=AU/L=AU/O=Internet Widgits Pty Ltd/OU=IT/CN='.$root.'"');
                        $ssh->exec('service vsftpd restart');

                        $stmt = $mysqli->prepare("INSERT INTO dedicated(name,os,ip,port,user,password,status,language,os_bit) VALUES (?, ?, ?, ? ,? ,? ,?, ? ,?)");
                        $stmt->bind_param('sssissisi', $name,$os_version,$ip,$port,$user,$password,$status,$language,$os_bit);
                        $stmt->execute();
                        $stmt->close();

                        unset($root_password);
                        unset($root);

                      }
                      msg_okay(_dedicated_message_added);

                  } else {
                    msg_error('Something went wrong, '.$msg);
                 }

                 }
               }

                msg_info(_dedicated_message_info_abort);
                  ?>
                <form class="form-horizontal" action="index.php?page=rootserver?add" method="post">
                  <div class="form-group">
                    <label class="control-label col-sm-2">Name:</label>
                    <div class="col-sm-6">
                      <input type="text" class="form-control input-sm" name="name" placeholder="Chewbacca">
                    </div>
                    <div class="col-sm-2">
                      <select class="form-control input-sm" name="os">
                        <option disabled selected>Debian</option>
                        <option>Debian 8 32bit</option>
                        <option>Debian 8 64bit</option>
                        <option disabled selected>Ubuntu</option>
                        <option>Ubuntu 14.04 32bit</option>
                        <option>Ubuntu 14.04 64bit</option>
                        <option>Ubuntu 15.04 32bit</option>
                        <option>Ubuntu 15.04 64bit</option>
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
                        <option>English</option>
                        <option>Deutsch</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-sm-2" for="pwd"><?php echo _dedicated_user; ?>/<?php echo _usettings_password; ?>:</label>
                    <div class="col-sm-4">
                      <input type="text" class="form-control input-sm" name="user" placeholder="prometheus">
                    </div>
                    <?php $pw = generatePassword(20); ?>
                    <div class="col-sm-2">
                      <input type="password" class="form-control input-sm" name="password" value="<?php echo $pw; ?>">
                    </div>
                    <div class="col-sm-2">
                      <input type="text" class="form-control input-sm" value="<?php echo $pw; ?>" readonly="readonly">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-sm-2" for="pwd">Root/<?php echo _usettings_password; ?>:</label>
                    <div class="col-sm-4">
                      <input type="text" class="form-control input-sm" name="root" placeholder="root">
                    </div>
                    <div class="col-sm-4">
                      <input type="password" class="form-control input-sm" name="root_password" placeholder="123456">
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                      <button type="submit" name="confirm" class="btn btn-default btn-sm"><?php echo _button_save; ?></button>
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
                        <th><?php echo _users_name; ?></th>
                        <th>IP</th>
                        <th>Port</th>
                        <th><?php echo _gameserver_user; ?></th>
                        <th><?php echo _usettings_password; ?></th>
                        <th>Status</th>
                        <th><?php echo _table_action; ?></th>
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
                          if ($row["status"] == 1) { echo '<td>'._dedicated_installed.': ';

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
                          echo '<a href="index.php?page=rootserver?edit='.$row["id"].'" class="btn pull-left btn-primary btn-xs">'._button_edit.'</a>
                                  <a style="margin-left:2px;" href="index.php?page=rootserver?manage='.$row["id"].'" class="btn pull-left btn-primary btn-xs">'._button_manage.'</a>
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
