<?php
//header
$title = "Gameserver Vorlagen";
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
               <div class="col-lg-12">
                 <?php

                     $query = "SELECT id FROM templates ORDER by id";

                        if ($result = $mysqli->query($query)) {

                         /* fetch object array */
                        while ($row = $result->fetch_row()) {

                          if ($page == "templates?delete-".$row[0]) {
                            $error = false;
                            if (check_template_exist_in_games($row[0])) { $msg = "Das Template ist noch auf Rootservern installiert.";$error = true;}
                            if (check_template_job_exists_id_only($row[0])) { $msg ="Installation des Templates läuft noch."; $error = true;}

                            if ($error == false) {

                              $stmt = $mysqli->prepare("DELETE FROM templates WHERE id = ?");
                              $stmt->bind_param('i', $row[0]);
                              $stmt->execute();
                              $stmt->close();
                              msg_okay("Das Template wurde gelöscht.");
                            } else {
                              msg_warning($msg);
                            }
                          } elseif ($page == "templates?edit-".$row[0]) {

                            $limited = false; $hide_msg = false;
                            if (check_template_exist_in_games($row[0])) {$limited = true;}
                            if (check_template_job_exists_id_only($row[0])) {  $limited = true;}

                            if ($_SERVER['REQUEST_METHOD'] == 'POST') {


                               if (isset($_POST['confirm'])) {

                                 $error = false;

                                 $name = htmlentities($_POST['name']);
                                 $type = htmlentities($_POST['type']);
                                 $type_name = htmlentities($_POST['type_name']);
                                 $internal = htmlentities($_POST['internal']);
                                 $path = htmlentities($_POST['path']);
                                 if(!preg_match("/^[a-zA-Z0-9]+$/",$name)){ $msg = "Der Username enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)<br>";  $error = true;}
                                 if(!preg_match("/^[a-zA-Z0-9]+$/",$internal)){ $msg = "Internal enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)<br>";  $error = true;}
                                 if(!preg_match("/^[a-zA-Z0-9]+$/",$type)){ $msg = "Type enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)<br>";  $error = true;}
                                 if(!preg_match("/^[a-zA-Z0-9]+$/",$type_name)){ $msg = "Type enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)<br>";  $error = true;}

                                 if (check_template($name,$row[0])) { $error = true; $msg = "Template exestiert bereits";}

                                 if ($error == false) {

                                   if ($limited == true) {

                                     $stmt = $mysqli->prepare("UPDATE templates SET name_internal = ?,type_name = ?, map_path = ? WHERE id = ?");
                                     $stmt->bind_param('sssi',$internal,$type_name,$path,$row[0]);
                                     $stmt->execute();
                                     $stmt->close();

                                   } else {

                                     $stmt = $mysqli->prepare("UPDATE templates SET name_internal = ?,type_name = ?,type = ?,name = ?,map_path = ?  WHERE id = ?");
                                     $stmt->bind_param('sssssi',$internal,$type_name,$type,$name,$path,$row[0]);
                                     $stmt->execute();
                                     $stmt->close();

                                   }

                                  msg_okay("Das Template wurde aktualisiert.");
                                  $hide_msg = true;

                               } else {
                                 msg_error('Something went wrong, '.$msg);
                                 $hide_msg = true;
                               }
                              }
                          }

                          $stmt = $mysqli->prepare("SELECT name,name_internal,type,type_name,map_path FROM templates WHERE id = ?");
                          $stmt->bind_param('i', $row[0]);
                          $stmt->execute();
                          $stmt->bind_result($db_name,$db_internal,$db_type,$db_type_name,$db_path);
                          $stmt->fetch();
                          $stmt->close();

                          echo '<form class="form-horizontal" action="index.php?page=templates?edit-'.$row[0].'" method="post">';
                          ?>
                            <div class="form-group">
                              <?php if ($limited == true AND $hide_msg == false) { msg_warning("Nur teilweise editierbar, da bereits installiert."); } ?>
                              <label class="control-label col-sm-2">Name/Internal:</label>
                              <div class="col-sm-3">
                                <?php if ($limited == true) {
                                  echo '<input type="text" class="form-control input-sm" name="name" value="'.$db_name.'" readonly="readonly">';
                                } else {
                                  echo '<input type="text" class="form-control input-sm" name="name" value="'.$db_name.'">';
                                }
                                ?>
                              </div>
                              <div class="col-sm-3">
                                <input type="text" class="form-control input-sm" name="internal" value="<?php echo $db_internal;?>">
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="control-label col-sm-2">Type:</label>
                              <div class="col-sm-3">
                                <?php if ($limited == true AND $hide_msg == false) {
                                  echo '<input type="text" class="form-control input-sm" name="type" value="'.$db_type.'" readonly="readonly">';
                                } else {
                                  echo '<input type="text" class="form-control input-sm" name="type" value="'.$db_type.'">';
                                }
                                ?>
                              </div>
                              <div class="col-sm-3">
                                <input type="text" class="form-control input-sm" name="type_name" value="<?php echo $db_type_name;?>">
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="control-label col-sm-2">Map Pfad:</label>
                              <div class="col-sm-3">
                                <input type="text" class="form-control input-sm" name="path" value="<?php echo $db_path;?>">
                              </div>
                            </div>
                            <div class="form-group">
                              <div class="col-sm-offset-2 col-sm-10">
                                <button type="submit" name="confirm" class="btn btn-default btn-sm">Abschicken</button>
                              </div>
                            </div>
                          </form>


                          <?php
                          }
                        }
                        /* free result set */
                        $result->close();
                        }

                  If ($page == "templates?add") {

                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {


                       if (isset($_POST['confirm'])) {

                         $error = false;

                         $name = htmlentities($_POST['name']);
                         $type = htmlentities($_POST['type']);
                         $type_name = htmlentities($_POST['type_name']);
                         $internal = htmlentities($_POST['internal']);
                         $map_path = htmlentities($_POST['path']);
                         if(!preg_match("/^[a-zA-Z0-9]+$/",$name)){ $msg = "Der Username enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)<br>";  $error = true;}
                         if(!preg_match("/^[a-zA-Z0-9]+$/",$internal)){ $msg = "Internal enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)<br>";  $error = true;}
                         if(!preg_match("/^[a-zA-Z0-9]+$/",$type)){ $msg = "Type enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)<br>";  $error = true;}
                         if(!preg_match("/^[a-zA-Z0-9]+$/",$type_name)){ $msg = "Type enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)<br>";  $error = true;}


                         if (exists_entry("name","templates","name",$name) == true) { $error = true;}

                         if ($error == false) {


                           $stmt = $mysqli->prepare("INSERT INTO templates(name,type,type_name,name_internal,map_path) VALUES (?, ?, ?, ? ,?)");
                           $stmt->bind_param('sssss', $name, $type,$type_name,$internal,$map_path);
                           $stmt->execute();
                           $stmt->close();

                          msg_okay("Das Template wurde angelegt.");

                       } else {
                         msg_error('Something went wrong, '.$msg);
                       }
                      }
                  }
                  ?>

                  <form class="form-horizontal" action="index.php?page=templates?add" method="post">
                    <div class="form-group">
                      <label class="control-label col-sm-2">Name/Internal:</label>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="name" placeholder="Garrysmod">
                      </div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="internal" placeholder="garrysmod">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2">Type:</label>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="type" placeholder="steamcmd">
                      </div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="type_name" placeholder="4020">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2">Map Pfad:</label>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="path" placeholder="csgo">
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" name="confirm" class="btn btn-default btn-sm">Abschicken</button>
                      </div>
                    </div>
                  </form>


                  <?php
               } elseif ($page == "templates" or startsWith($page, "templates?delete") or startsWith($page, "templates?edit")) {
                    ?>
                    <form action="index.php?page=templates" method="post">
                    <a  style="margin-bottom:2px;" href="index.php?page=templates?add"  class="btn pull-right btn-success btn-xs"><i class="fa fa-plus"></i></a>
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th>Name</th>
                          <th>Internal</th>
                          <th>Type</th>
                          <th>Type Name</th>
                          <th>Pfad</th>
                          <th>Aktion</th>
                        </tr>
                      </thead>
                      <tbody>
                     <?php

                     $query = "SELECT name, type,type_name,name_internal,id,map_path FROM templates ORDER by id";

                      if ($stmt = $mysqli->prepare($query)) {
                          $stmt->execute();
                          $stmt->bind_result($db_name, $db_type,$db_type_name,$db_name_internal,$db_id,$db_path);

                          while ($stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>" . $db_name . "</td>";
                            echo "<td>" . $db_name_internal . "</td>";
                            echo "<td>" . $db_type . "</td>";
                            echo "<td>" . $db_type_name . "</td>";
                            echo "<td>" . $db_path . "</td>";
                            echo '<td> <a href="index.php?page=templates?edit-'.$db_id.'"  class="btn btn-primary btn-xs">Editieren</i></a>
                                      <a style="margin-left:2px" href="index.php?page=templates?delete-'.$db_id.'"  class="btn btn-danger btn-xs"><i class="fa fa-remove"></i></a>';
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

 } elseif ($_SESSION['login'] == 1 and $db_rank != 1) { header('Location: index.php?page=dashboard');
 } else {  header('Location: index.php');}


//Footer
include 'footer.html';
?>
