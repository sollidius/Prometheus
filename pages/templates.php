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
$title = _title_templates;
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

                     $query = "SELECT id FROM templates ORDER by id";

                        if ($result = $mysqli->query($query)) {

                         /* fetch object array */
                        while ($row = $result->fetch_row()) {

                          if ($page == "templates?delete-".$row[0]) {
                            $error = false;
                            if (check_template_exist_in_games($row[0])) { $msg = _templates_rootserver_installed;$error = true;}
                            if (check_template_job_exists_id_only($row[0])) { $msg =_template_rootserver_still_running; $error = true;}

                            if ($error == false) {

                              $stmt = $mysqli->prepare("DELETE FROM templates WHERE id = ?");
                              $stmt->bind_param('i', $row[0]);
                              $stmt->execute();
                              $stmt->close();
                              msg_okay(_template_deleted);
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
                                 $gameq = htmlentities($_POST['gameq']);
                                 $appid = htmlentities($_POST['appid']);
                                 $app_set_config = htmlentities($_POST['app_set_config']);
                                 if(!preg_match("/^[a-zA-Z0-9._-]+$/",$name)){ $msg = _addons_message_error_name."<br>";  $error = true;}
                                 if ($type == "steamcmd") {
                                   if(!preg_match("/^[a-zA-Z0-9]+$/",$internal)){ $msg = _templates_internal_error."<br>";  $error = true;}
                                 }
                                 if(!preg_match("/^[a-zA-Z0-9]+$/",$type)){ $msg = _templates_type_error."<br>";  $error = true;}
                                 if ($type == "steamcmd") {
                                    if(!preg_match("/^[a-zA-Z0-9]+$/",$type_name)){ $msg = _templates_typename_error."<br>";  $error = true;}
                                 }
                                 if ($gameq != "") {
                                    if(!preg_match("/^[a-zA-Z0-9\s]+$/",$gameq)){ $msg = _templates_gameq_error." <br>";  $error = true;}
                                 }
                                 if ($type == "steamcmd") {

                                 } elseif ($type == "image") {

                                 } else {
                                    $error = true; $msg = _templates_invalid_type;
                                 }
                                 if (check_template($name,$row[0])) { $error = true; $msg = _template_exists;}

                                 if ($error == false) {

                                   if ($limited == true) {

                                     $stmt = $mysqli->prepare("UPDATE templates SET name_internal = ?,type_name = ?, map_path = ?, gameq = ?, app_set_config = ?, appid = ? WHERE id = ?");
                                     $stmt->bind_param('sssssii',$internal,$type_name,$path,$gameq,$app_set_config,$appid,$row[0]);
                                     $stmt->execute();
                                     $stmt->close();

                                   } else {

                                     $stmt = $mysqli->prepare("UPDATE templates SET name_internal = ?,type_name = ?,type = ?,name = ?,map_path = ?, gameq = ?, app_set_config = ?, appid = ?  WHERE id = ?");
                                     $stmt->bind_param('sssssssii',$internal,$type_name,$type,$name,$path,$gameq,$app_set_config,$appid,$row[0]);
                                     $stmt->execute();
                                     $stmt->close();

                                   }

                                  msg_okay(_template_updated);
                                  $hide_msg = true;

                               } else {
                                 msg_error('Something went wrong, '.$msg);
                                 $hide_msg = true;
                               }
                              }
                          }

                          $stmt = $mysqli->prepare("SELECT name,name_internal,type,type_name,map_path,gameq,app_set_config,appid FROM templates WHERE id = ?");
                          $stmt->bind_param('i', $row[0]);
                          $stmt->execute();
                          $stmt->bind_result($db_name,$db_internal,$db_type,$db_type_name,$db_path,$db_gameq,$db_app_set_config,$db_appid);
                          $stmt->fetch();
                          $stmt->close();

                          echo '<form class="form-horizontal" action="index.php?page=templates?edit-'.$row[0].'" method="post">';
                          ?>
                            <div class="form-group">
                              <?php if ($limited == true AND $hide_msg == false) { msg_warning(_template_limited); } ?>
                              <label class="control-label col-sm-2"><?php echo _template_name; ?>/<?php echo _template_internal; ?>:</label>
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
                              <label class="control-label col-sm-2"><?php echo _template_type; ?>:</label>
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
                              <label class="control-label col-sm-2"><?php echo _template_map_path; ?>/GameQ:</label>
                              <div class="col-sm-3">
                                <input type="text" class="form-control input-sm" name="path" value="<?php echo $db_path;?>">
                              </div>
                              <div class="col-sm-3">
                                <input type="text" class="form-control input-sm" name="gameq" value="<?php echo $db_gameq;?>">
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="control-label col-sm-2">App_set_config/Appid:</label>
                              <div class="col-sm-3">
                                <input type="text" class="form-control input-sm" name="app_set_config"  value="<?php echo $db_app_set_config;?>">
                              </div>
                              <div class="col-sm-3">
                                <input type="text" class="form-control input-sm" name="appid"  value="<?php echo $db_appid;?>">
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

                  If ($page == "templates?add") {

                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {


                       if (isset($_POST['confirm'])) {

                         $error = false;

                         $name = htmlentities($_POST['name']);
                         $type = htmlentities($_POST['type']);
                         $type_name = htmlentities($_POST['type_name']);
                         $internal = htmlentities($_POST['internal']);
                         $map_path = htmlentities($_POST['path']);
                         $gameq = htmlentities($_POST['gameq']);
                         $appid = htmlentities($_POST['appid']);
                         $app_set_config = htmlentities($_POST['app_set_config']);
                         if(!preg_match("/^[a-zA-Z0-9._-]+$/",$name)){ $msg = _addons_message_error_name."<br>";  $error = true;}
                         if ($type == "steamcmd") {
                           if(!preg_match("/^[a-zA-Z0-9]+$/",$internal)){ $msg = _templates_internal_error."<br>";  $error = true;}
                         }
                         if(!preg_match("/^[a-zA-Z0-9]+$/",$type)){ $msg = _templates_type_error."<br>";  $error = true;}
                         if ($type == "steamcmd") {
                            if(!preg_match("/^[a-zA-Z0-9]+$/",$type_name)){ $msg = _templates_typename_error."<br>";  $error = true;}
                         }
                         if ($gameq != "") {
                            if(!preg_match("/^[a-zA-Z0-9\s]+$/",$gameq)){ $msg = _templates_gameq_error."<br>";  $error = true;}
                         }
                         if ($type == "steamcmd") {

                         } elseif ($type == "image") {

                         } else {
                            $error = true; $msg = _templates_invalid_type;
                         }
                         if (exists_entry("name","templates","name",$name) == true) { $error = true;  $msg = _template_exists;}

                         if ($error == false) {


                           $stmt = $mysqli->prepare("INSERT INTO templates(name,type,type_name,name_internal,map_path,gameq,app_set_config,appid) VALUES (?, ?, ?, ? ,? ,?, ? ,?)");
                           $stmt->bind_param('sssssssi', $name, $type,$type_name,$internal,$map_path,$gameq,$app_set_config,$appid);
                           $stmt->execute();
                           $stmt->close();

                          msg_okay(_template_added);

                       } else {
                         msg_error('Something went wrong, '.$msg);
                       }
                      }
                  }
                  ?>

                  <form class="form-horizontal" action="index.php?page=templates?add" method="post">
                    <div class="form-group">
                      <label class="control-label col-sm-2"><?php echo _template_name; ?>/<?php echo _template_internal; ?>:</label>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="name" placeholder="Garrysmod">
                      </div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="internal" placeholder="garrysmod">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2"><?php echo _template_type; ?>:</label>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="type" placeholder="steamcmd oder image">
                      </div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="type_name" placeholder="4020">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2"><?php echo _template_map_path; ?>/GameQ:</label>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="path" placeholder="csgo">
                      </div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="gameq" placeholder="csgo">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2">App_set_config/Appid:</label>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="app_set_config" placeholder="ricochet">
                      </div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="appid" placeholder="4000">
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" name="confirm" class="btn btn-default btn-sm"><?php echo _button_save; ?></button>
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
                          <th><?php echo _template_name; ?></th>
                          <th>Internal</th>
                          <th><?php echo _template_type; ?></th>
                          <th><?php echo _template_type_name; ?></th>
                          <th>AppID</th>
                          <th>App_Set Config</th>
                          <th><?php echo _template_map_path; ?></th>
                          <th>GameQ</th>
                          <th><?php echo _table_action; ?></th>
                        </tr>
                      </thead>
                      <tbody>
                     <?php

                     $query = "SELECT name, type,type_name,name_internal,id,map_path,gameq,app_set_config,appid FROM templates ORDER by name ASC";

                      if ($stmt = $mysqli->prepare($query)) {
                          $stmt->execute();
                          $stmt->bind_result($db_name, $db_type,$db_type_name,$db_name_internal,$db_id,$db_path,$db_gameq,$db_app_set_config,$db_appid);

                          while ($stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>" . $db_name . "</td>";
                            if ( strlen($db_name_internal) > 15) {
                              echo "<td>" . substr($db_name_internal, 0, 15) . "...</td>";
                            } else {
                              echo "<td>" . $db_name_internal . "</td>";
                            }
                            if ( strlen($db_type) > 15) {
                              echo "<td>" . substr($db_type, 0, 15) . "...</td>";
                            } else {
                              echo "<td>" . $db_type . "</td>";
                            }
                            if ( strlen($db_type_name) > 15) {
                              echo "<td>" . substr($db_type_name, 0, 15) . "...</td>";
                            } else {
                              echo "<td>" . $db_type_name . "</td>";
                            }
                            echo "<td>" . $db_appid . "</td>";
                            echo "<td>" . $db_app_set_config . "</td>";
                            echo "<td>" . $db_path . "</td>";
                            if ($db_gameq == "") {
                              echo '<td><p style="color: #777;margin:0;padding=0;">gmod</p></td>';
                            } else {
                              echo "<td>" . $db_gameq . "</td>";
                            }
                            echo '<td> <a href="index.php?page=templates?edit-'.$db_id.'"  class="btn btn-primary btn-xs">'._button_edit.'</i></a>
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
