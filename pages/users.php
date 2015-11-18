<?php
//header
$title = "Benutzer";
include 'header.php';

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

                            msg_okay("Benutzer gelöscht.");

                          } elseif ($_SESSION['user_id'] == $row[0]) {

                           msg_warning("Du kannst dich nicht selber löschen.");

                          } else {

                           msg_warning("Der Benutzer besitzt noch Gameserver.");

                          }
                         }
                     }
                     /* free result set */
                     $result->close();
                 }

                  if ($_SERVER['REQUEST_METHOD'] == 'POST') {


                     if (isset($_POST['confirm'])) {

                       $error = false; $msg ="";
                       $rank = 2;
                       $name = $_POST['name']; $email = $_POST['email']; $password = $_POST['pwd1'];

                       if (isset($_POST['administrator'])) { $rank = 1;}

                       if ($_POST['pwd1'] != $_POST['pwd2']) {$error = true;$msg="Passwort ungleich";}
                       if (user_exists($name) == true) { $error = true;$msg="User exestiert";}
                       if (email_exists($email) == true) { $error = true;$msg="E-Mail exestiert";}
                       if (strlen($name) <= 2) {$error = true; $msg="Name zu Kurz";}
                       if(!preg_match("/^[a-zA-Z0-9]+$/",$name)){ $msg = "Der Username enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)<br>";  $error = true;}
                       if(!preg_match("/([0-9a-zA-Z])@(\w+)\.(\w+)/",$email)){ $msg = "Die E-Mail ist nicht g&uuml;ltig<br>";  $error = true;}
                       if (strlen($email) <= 5) {$error = true; $msg="E-Mail zu kurz";}
                       if (strlen($password) <= 8) {$error = true; $msg="Passwort zu Kurz";}

                       if ($error == false) {

                         $hash = password_hash($password, PASSWORD_DEFAULT);

                         $stmt = $mysqli->prepare("INSERT INTO users(name,email,password,rank) VALUES (?, ?, ?, ?)");
                         $stmt->bind_param('sssi', $name, $email,$hash,$rank);
                         $stmt->execute();
                         $stmt->close();

                         msg_okay("Der Benutzer wurde erstellt.");

                     } else {
                      msg_error($msg);
                     }

                    } else {

                  ?>

                  <form class="form-horizontal" action="index.php?page=users" method="post">
                    <div class="form-group">
                      <label class="control-label col-sm-2">Name:</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control input-sm" name="name" placeholder="Enter Name">
                      </div>
                      <div class="checkbox col-sm-2">
                        <label><input type="checkbox"  name ="administrator" value="1">Administrator</label>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2" for="email">E-Mail:</label>
                      <div class="col-sm-10">
                        <input type="email" class="form-control input-sm" name="email" placeholder="Enter email">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2" for="pwd">Passwort:</label>
                      <div class="col-sm-10">
                        <input type="password" class="form-control input-sm" name="pwd1" placeholder="Enter password">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2" for="pwd">Passwort Nochmal:</label>
                      <div class="col-sm-10">
                        <input type="password" class="form-control input-sm" name="pwd2" placeholder="Enter password">
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
                    <form action="index.php?page=users" method="post">
                    <button style="margin-bottom:2px;" type="submit" name="add" class="btn pull-right btn-success btn-xs">+</button>
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th>Name</th>
                          <th>E-Mail</th>
                          <th>Rank</th>
                          <th>Aktion</th>
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
                            echo "<td>" . $db_name . "</td>";
                            echo "<td>" . $db_email . "</td>";
                            if ($db_rank == 1) {
                              echo "<td>Administrator</td>";
                            } elseif ($db_rank == 2) {
                              echo "<td>User</td>";
                            }
                            echo '<td> <a href="index.php?page=users?delete-'.$db_id.'"  class="btn btn-danger btn-xs">X</a></td>';
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
