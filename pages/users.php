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
$title = _title_users;
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

                 $query = "SELECT id FROM users ORDER by id";

                 if ($result = $mysqli->query($query)) {

                     /* fetch object array */
                     while ($row = $result->fetch_row()) {

                         if ($page == "users?delete-".$row[0]) {

                           $id_result = 0;
                           $stmtz = $mysqli->prepare("SELECT id FROM gameservers WHERE user_id = ?");
                           $stmtz->bind_param('i', $row[0]);
                           $stmtz->execute();
                           $stmtz->bind_result($id_result);
                           $stmtz->fetch();
                           $stmtz->close();

                          if ($id_result == 0 AND $_SESSION['user_id'] != $row[0]) {

                            $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
                            $stmt->bind_param('i', $row[0]);
                            $stmt->execute();
                            $stmt->close();

                            msg_okay(_users_message_deleted);

                          } elseif ($_SESSION['user_id'] == $row[0]) {

                           msg_warning(_users_message_yourself);

                          } else {

                           msg_warning(_users_message_gameserver);

                          }
                        }  elseif ($page == "users?edit-".$row[0]) {

                          if ($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['confirm'])) {

                              $error = false; $msg ="";
                              $rank = 2;
                              $name = htmlentities($_POST['name']); $email = htmlentities($_POST['email']); $password = htmlentities($_POST['pwd1']);

                              if (isset($_POST['administrator'])) { $rank = 1;}

                              if (!$_POST['pwd1'] == "") {
                                if ($_POST['pwd1'] != $_POST['pwd2']) {$error = true;$msg=_users_password_notequal;}
                                if (strlen($password) < 8) {$error = true; $msg=_users_password_toshort;}
                              }
                              if (user_exists($name,$row[0]) == true) { $error = true;$msg=_users_exists;}
                              if (email_exists($email,$row[0]) == true) { $error = true;$msg=_users_email_exists;}
                              if (strlen($name) <= 2) {$error = true; $msg=_users_name_toshort;}
                              if(!preg_match("/^[a-zA-Z0-9]+$/",$name)){ $msg = _users_name_invalid_letters."<br>";  $error = true;}
                              //if(!preg_match("/([0-9a-zA-Z])@(\w+)\.(\w+)/",$email)){ $msg = _users_email_invalid."<br>";  $error = true;}
                              if (strlen($email) < 6) {$error = true; $msg=_users_email_toshort;}
                              if (isValidEmail($email) == false) { $msg =_users_email_invalid; $error = true;}

                              if ($error == false) {

                                  if ($_POST['pwd1'] == "") {

                                    $stmt = $mysqli->prepare("UPDATE users SET name = ?, email = ?, rank= ?  WHERE id = ?");
                                    $stmt->bind_param('ssii',$name,$email,$rank,$row[0]);
                                    $stmt->execute();
                                    $stmt->close();

                                  } else {

                                    $hash = password_hash($password, PASSWORD_DEFAULT);

                                    $stmt = $mysqli->prepare("UPDATE users SET name = ?, email = ?, rank= ?, password = ?  WHERE id = ?");
                                    $stmt->bind_param('ssisi',$name,$email,$rank,$hash,$row[0]);
                                    $stmt->execute();
                                    $stmt->close();
                                  }
                                msg_okay(_users_user_updated);

                            } else {
                             msg_error($msg);
                            }
                          }

                          $stmt = $mysqli->prepare("SELECT name,email,password,rank FROM users WHERE id = ?");
                          $stmt->bind_param('i', $row[0]);
                          $stmt->execute();
                          $stmt->bind_result($db_name,$db_email,$db_password,$db_rank);
                          $stmt->fetch();
                          $stmt->close();
                          echo '<form class="form-horizontal" action="index.php?page=users?edit-'.$row[0].'" method="post">';
                          ?>
                            <div class="form-group">
                              <label class="control-label col-sm-2"><?php echo _users_name; ?>:</label>
                              <div class="col-sm-8">
                                <input type="text" class="form-control input-sm" name="name" value="<?php echo htmlentities($db_name);?>">
                              </div>
                              <div class="col-sm-2">
                                  <input data-size="small" data-off="User" id="toggle-user" data-on="Administrator" data-height="20" type="checkbox" name="administrator" data-toggle="toggle">
                                  <?php
                                 if ($db_rank == 1) {
                                  ?>
                                  <script> function toggleOncleanup() { $('#toggle-user').bootstrapToggle('on'); } window.onload=toggleOncleanup; </script>
                                  <?php
                                } elseif ($db_rank == 0) { ?>
                                  <script> function toggleOffcleanup() { $('#toggle-user').bootstrapToggle('off'); } window.onload=toggleOffcleanup; </script>
                                  <?php
                                }
                                ?>
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="control-label col-sm-2" for="email"><?php echo _users_email; ?>:</label>
                              <div class="col-sm-10">
                                <input type="email" class="form-control input-sm" name="email" value="<?php echo htmlentities($db_email);?>">
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="control-label col-sm-2" for="pwd"><?php echo _usettings_password; ?>:</label>
                              <div class="col-sm-10">
                                <input type="password" class="form-control input-sm" name="pwd1" placeholder="Leer lassen, wenn keine änderung">
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="control-label col-sm-2" for="pwd"><?php echo _usettings_repeatpwd; ?></label>
                              <div class="col-sm-10">
                                <input type="password" class="form-control input-sm" name="pwd2" placeholder="Leer lassen, wenn keine änderung">
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


                  if ($page == "users?add") {

                  if ($_SERVER['REQUEST_METHOD'] == 'POST') {


                     if (isset($_POST['confirm'])) {

                       $error = false; $msg ="";
                       $rank = 2;
                       $name = htmlentities($_POST['name']); $email = htmlentities($_POST['email']); $password = htmlentities($_POST['pwd1']);

                       if (isset($_POST['administrator'])) { $rank = 1;}

                       if ($_POST['pwd1'] != $_POST['pwd2']) {$error = true;$msg=_users_password_notequal;}
                       if (user_exists($name) == true) { $error = true;$msg=_users_exists;}
                       if (email_exists($email) == true) { $error = true;$msg=_users_email_exists;}
                       if (strlen($name) <= 2) {$error = true; $msg=_users_name_toshort;}
                       if(!preg_match("/^[a-zA-Z0-9]+$/",$name)){ $msg = _users_name_invalid_letters."<br>";  $error = true;}
                       //if(!preg_match("/([0-9a-zA-Z])@(\w+)\.(\w+)/",$email)){ $msg = "Die E-Mail ist nicht g&uuml;ltig<br>";  $error = true;}
                       if (strlen($email) <= 5) {$error = true; $msg=_users_email_toshort;}
                       if (isValidEmail($email) == false) { $msg =_users_email_invalid; $error = true;}
                       if (strlen($password) <= 8) {$error = true; $msg=_users_password_toshort;}

                       if ($error == false) {

                         $hash = password_hash($password, PASSWORD_DEFAULT);

                         $stmt = $mysqli->prepare("INSERT INTO users(name,email,password,rank) VALUES (?, ?, ?, ?)");
                         $stmt->bind_param('sssi', $name, $email,$hash,$rank);
                         $stmt->execute();
                         $stmt->close();

                         msg_okay(_users_user_created);

                     } else {
                      msg_error($msg);
                     }

                    }

                  }

                  ?>

                  <form class="form-horizontal" action="index.php?page=users?add" method="post">
                    <div class="form-group">
                      <label class="control-label col-sm-2"><?php echo _users_name; ?>:</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control input-sm" name="name" placeholder="Enter Name">
                      </div>
                        <div class="col-sm-2">
                          <input data-size="small" data-off="User" data-on="Administrator" data-height="20" type="checkbox" name="administrator" data-toggle="toggle">
                        </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2" for="email"><?php echo _users_email; ?>:</label>
                      <div class="col-sm-10">
                        <input type="email" class="form-control input-sm" name="email" placeholder="Enter email">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2" for="pwd"><?php echo _usettings_password; ?>:</label>
                      <div class="col-sm-10">
                        <input type="password" class="form-control input-sm" name="pwd1" placeholder="Enter password">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2" for="pwd"><?php echo _usettings_repeatpwd; ?>:</label>
                      <div class="col-sm-10">
                        <input type="password" class="form-control input-sm" name="pwd2" placeholder="Enter password">
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" name="confirm" class="btn btn-default btn-sm"><?php echo _button_save; ?></button>
                      </div>
                    </div>
                  </form>



                  <?php
                } elseif ($page == "users" or startsWith($page, "users?delete")) {
                    ?>
                    <form action="index.php?page=users" method="post">
                    <a  style="margin-bottom:2px;" href="index.php?page=users?add"  class="btn pull-right btn-success btn-xs"><i class="fa fa-user-plus"></i></a>
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th><?php echo _users_name; ?></th>
                          <th><?php echo _users_email; ?></th>
                          <th><?php echo _users_rank; ?></th>
                          <th><?php echo _table_action; ?></th>
                        </tr>
                      </thead>
                      <tbody>
                     <?php

                     $query = "SELECT name, email, rank,id FROM users ORDER by id";

                      if ($stmt = $mysqli->prepare($query)) {
                          $stmt->execute();
                          $stmt->bind_result($db_name, $db_email,$db_rank,$db_id);

                          while ($stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>" . htmlentities($db_name) . "</td>";
                            echo "<td>" . htmlentities($db_email) . "</td>";
                            //echo "<td>***********</td>";
                            if ($db_rank == 1) {
                              echo "<td>Administrator</td>";
                            } elseif ($db_rank == 2) {
                              echo "<td>User</td>";
                            }
                            echo '<td><a href="index.php?page=users?edit-'.$db_id.'"  class="btn btn-primary btn-xs">'._button_edit.'</a>
                                  <a href="index.php?page=users?delete-'.$db_id.'"  class="btn btn-danger btn-xs"><i class="fa fa-remove"></i></a>';
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
