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
$title = _title_backup;
include 'header.php';


if ($_SESSION['login'] === 1 and $db_rank === 1) {

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

                     $query = "SELECT id FROM backup_server ORDER by id";

                        if ($result = $mysqli->query($query)) {

                         /* fetch object array */
                        while ($row = $result->fetch_row()) {

                          if ($page == "backup?delete-".$row[0]) {
                            $error = false;

                            if ($error == false) {

                              $stmt = $mysqli->prepare("DELETE FROM backup_server WHERE id = ?");
                              $stmt->bind_param('i', $row[0]);
                              $stmt->execute();
                              $stmt->close();
                              msg_okay(_backup_message_removed);
                            } else {
                              msg_warning($msg);
                            }
                          } elseif ($page == "backup?edit-".$row[0]) {

                            if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                               if (isset($_POST['confirm'])) {

                                 $error = false;

                                 $name = htmlentities($_POST['name']);
                                 $type = htmlentities($_POST['type']);
                                 $ip = htmlentities($_POST['ip']);
                                 $port = htmlentities($_POST['port']);
                                 $user = htmlentities($_POST['user']);
                                 $password = htmlentities($_POST['password']);

                                 if(!preg_match("/^[a-zA-Z0-9._-]+$/",$name)){ $msg = _addons_message_error_name."<br>";  $error = true;}
                                 if ($type != "Rsync/Tar") { $error = true; $msg = _templates_invalid_type;}
                                 if (isValidIP($ip) == false) { $error = true; $msg = _dedicated_message_ip_invalid; }
                                 if(!preg_match("/^[0-9]+$/",$port)){ $msg = _dedicated_message_port_invalid."<br>";  $error = true;}
                                 if(!preg_match("/^[a-zA-Z0-9._-]+$/",$user)){ $msg = _gameserver_user_invalid."<br>";  $error = true;}

                                 if ($error == false) {

                                     $stmt = $mysqli->prepare("UPDATE backup_server SET name = ?,ip = ?,port = ?,user = ?,password = ?, type = ?  WHERE id = ?");
                                     $stmt->bind_param('ssisssi',$name,$ip,$port,$user,$password,$type,$row[0]);
                                     $stmt->execute();
                                     $stmt->close();

                                  msg_okay(_template_updated);
                                  $hide_msg = true;

                               } else {
                                 msg_error('Something went wrong, '.$msg);
                                 $hide_msg = true;
                               }
                              }
                          }

                          $stmt = $mysqli->prepare("SELECT name,type,ip,port,user,password FROM backup_server WHERE id = ?");
                          $stmt->bind_param('i', $row[0]);
                          $stmt->execute();
                          $stmt->bind_result($db_name,$db_type,$db_ip,$db_port,$db_user,$db_password);
                          $stmt->fetch();
                          $stmt->close();

                          echo '<form class="form-horizontal" action="index.php?page=backup?edit-'.$row[0].'" method="post">';
                          ?>
                          <div class="form-group">
                            <label class="control-label col-sm-2">Name:</label>
                            <div class="col-sm-6">
                              <input type="text" class="form-control input-sm" name="name" value="<?php echo htmlentities($db_name); ?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-sm-2">IP/Port:</label>
                            <div class="col-sm-3">
                              <input type="text" class="form-control input-sm" name="ip" value="<?php echo htmlentities($db_ip); ?>">
                            </div>
                            <div class="col-sm-3">
                              <input type="text" class="form-control input-sm" name="port" value="<?php echo htmlentities($db_port); ?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-sm-2">User/Passwort:</label>
                            <div class="col-sm-3">
                              <input type="text" class="form-control input-sm" name="user" value="<?php echo htmlentities($db_user); ?>">
                            </div>
                            <div class="col-sm-3">
                              <input type="text" class="form-control input-sm" name="password" value="<?php echo htmlentities($db_password); ?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-sm-2">Type:</label>
                            <div class="col-sm-3">
                              <select class="form-control input-sm" name="type">
                                <option>Rsync/Tar</option>
                              </select>
                            </div>
                          </div>
                          <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                              <button type="submit" name="confirm" class="btn btn-default btn-sm"><?php echo _button_save; ?></button>
                            </div>
                          </div>
                        </form>


                          <?php
                          }
                        }
                        /* free result set */
                        $result->close();
                        }

                  If ($page == "backup?add") {

                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {


                       if (isset($_POST['confirm'])) {

                         $error = false;

                         $name = htmlentities($_POST['name']);
                         $type = htmlentities($_POST['type']);
                         $ip = htmlentities($_POST['ip']);
                         $port = htmlentities($_POST['port']);
                         $user = htmlentities($_POST['user']);
                         $password = htmlentities($_POST['password']);

                         if(!preg_match("/^[a-zA-Z0-9._-]+$/",$name)){ $msg = _addons_message_error_name."<br>";  $error = true;}
                         if ($type != "Rsync/Tar") { $error = true; $msg = _templates_invalid_type;}
                         if (isValidIP($ip) == false) { $error = true; $msg = _dedicated_message_ip_invalid; }
                         if(!preg_match("/^[0-9]+$/",$port)){ $msg = _dedicated_message_port_invalid."<br>";  $error = true;}
                         if(!preg_match("/^[a-zA-Z0-9._-]+$/",$user)){ $msg = _gameserver_user_invalid."<br>";  $error = true;}
                         if (exists_entry("name","backup_server","name",$name) == true) { $error = true;  $msg = _template_exists;}

                         if ($error == false) {


                           $stmt = $mysqli->prepare("INSERT INTO backup_server(name,type,ip,port,user,password) VALUES (?,?,?,?,?,?)");
                           $stmt->bind_param('sssiss', $name,$type,$ip,$port,$user,$password);
                           $stmt->execute();
                           $stmt->close();

                          msg_okay(_backup_message_added);

                       } else {
                         msg_error('Something went wrong, '.$msg);
                       }
                      }
                  }
                  ?>

                  <form class="form-horizontal" action="index.php?page=backup?add" method="post">
                    <div class="form-group">
                      <label class="control-label col-sm-2">Name:</label>
                      <div class="col-sm-6">
                        <input type="text" class="form-control input-sm" name="name" placeholder="Chewbacca">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2">IP/Port:</label>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="ip" placeholder="127.0.0.1">
                      </div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="port" placeholder="22">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2">User/Passwort:</label>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="user" placeholder="user">
                      </div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="password" placeholder="password">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2">Type:</label>
                      <div class="col-sm-3">
                        <select class="form-control input-sm" name="type">
                          <option>Rsync/Tar</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" name="confirm" class="btn btn-default btn-sm"><?php echo _button_save; ?></button>
                      </div>
                    </div>
                  </form>


                  <?php
               } elseif ($page == "backup" or startsWith($page, "backup?delete") or startsWith($page, "backup?edit")) {
                    ?>
                    <form action="index.php?page=backup" method="post">
                    <a  style="margin-bottom:2px;" href="index.php?page=backup?add"  class="btn pull-right btn-success btn-xs"><i class="fa fa-plus"></i></a>
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th>Name</th>
                          <th>IP</th>
                          <th>Port</th>
                          <th>User</th>
                          <th>Type</th>
                          <th><?php echo _table_action; ?></th>
                        </tr>
                      </thead>
                      <tbody>
                     <?php

                     $query = "SELECT id,name,ip,port,user,type FROM backup_server ORDER by id ASC";

                      if ($stmt = $mysqli->prepare($query)) {
                          $stmt->execute();
                          $stmt->bind_result($db_id,$db_name, $db_ip,$db_port,$db_user,$db_type);

                          while ($stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>" . htmlentities($db_name) . "</td>";
                            echo "<td>" . htmlentities($db_ip) . "</td>";
                            echo "<td>" . htmlentities($db_port) . "</td>";
                            echo "<td>" . htmlentities($db_user) . "</td>";
                            echo "<td>" . htmlentities($db_type) . "</td>";
                            echo '<td> <a href="index.php?page=backup?edit-'.$db_id.'"  class="btn btn-primary btn-xs">'._button_edit.'</i></a>
                                      <a style="margin-left:2px" href="index.php?page=backup?delete-'.$db_id.'"  class="btn btn-danger btn-xs"><i class="fa fa-remove"></i></a>';
                            echo '</td>';
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

 } elseif ($_SESSION['login'] === 1 and $db_rank != 1) { header('Location: index.php?page=dashboard');
 } else {  header('Location: index.php');}


//Footer
include 'footer.html';
?>
