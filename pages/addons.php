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
$title = _title_addons;
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

                     $query = "SELECT id FROM addons ORDER by id";

                        if ($result = $mysqli->query($query)) {

                         /* fetch object array */
                        while ($row = $result->fetch_row()) {

                          if ($page == "addons?delete-".$row[0]) {
                            $error = false;


                            if ($error == false) {

                              $stmt = $mysqli->prepare("DELETE FROM addons WHERE id = ?");
                              $stmt->bind_param('i', $row[0]);
                              $stmt->execute();
                              $stmt->close();
                              msg_okay(_addons_message_deleted);
                            } else {
                              msg_warning($msg);
                            }
                          } elseif ($page == "addons?edit-".$row[0]) {

                            $limited = false; $hide_msg = false;
                            if (check_template_exist_in_games($row[0])) {$limited = true;}
                            if (check_template_job_exists_id_only($row[0])) {  $limited = true;}

                            if ($_SERVER['REQUEST_METHOD'] == 'POST') {


                               if (isset($_POST['confirm'])) {

                                 $error = false;

                                 $name = htmlentities($_POST['name']);
                                 $game = htmlentities($_POST['game']);
                                 $url = htmlentities($_POST['url']);
                                 $path = htmlentities($_POST['path']);
                                 $folder = htmlentities($_POST['folder']);
                                 if(!preg_match("/^[a-zA-Z0-9._-]+$/",$name)){ $msg = _addons_message_error_name ."<br>";  $error = true;}
                                 if(!preg_match("/^[a-zA-Z0-9._-]+$/",$game)){ $msg = _addons_message_error_game ."<br>";  $error = true;
                                 } else {

                                  $stmt = $mysqli->prepare("SELECT id FROM templates WHERE name = ?");
                                  $stmt->bind_param('s', $game);
                                  $stmt->execute();
                                  $stmt->bind_result($template_id);
                                  $stmt->fetch();
                                  $stmt->close();

                                  if (check_template($template_id)) { $msg = _message_addon_error; $error = true;}

                                }
                                if (isValidURL($url) == false) { $msg = _addons_message_error_url; $error = true;}


                                 if ($error == false) {

                                     $stmt = $mysqli->prepare("SELECT id FROM templates WHERE name = ?");
                                     $stmt->bind_param('s', $game);
                                     $stmt->execute();
                                     $stmt->bind_result($template_id);
                                     $stmt->fetch();
                                     $stmt->close();

                                     $stmt = $mysqli->prepare("UPDATE addons SET name = ?,url = ?,path = ?,game_id = ?, folder = ?  WHERE id = ?");
                                     $stmt->bind_param('sssisi',$name,$url,$path,$template_id,$folder,$row[0]);
                                     $stmt->execute();
                                     $stmt->close();

                                  msg_okay(_addons_message_updated);
                                  $hide_msg = true;

                               } else {
                                 msg_error('Something went wrong, '.$msg);
                                 $hide_msg = true;
                               }
                              }
                          }

                          $stmt = $mysqli->prepare("SELECT name,game_id,url,path,folder FROM addons WHERE id = ?");
                          $stmt->bind_param('i', $row[0]);
                          $stmt->execute();
                          $stmt->bind_result($db_name,$db_game_id,$db_url,$db_path,$db_folder);
                          $stmt->fetch();
                          $stmt->close();

                          $stmt = $mysqli->prepare("SELECT name FROM templates WHERE id = ?");
                          $stmt->bind_param('i',$db_game_id);
                          $stmt->execute();
                          $stmt->bind_result($db_game_name);
                          $stmt->fetch();
                          $stmt->close();

                          echo'<form class="form-horizontal" action="index.php?page=addons?edit-'.$row[0].'" method="post">';?>
                            <div class="form-group">
                              <label class="control-label col-sm-2">Name/Game:</label>
                              <div class="col-sm-3">
                                <input type="text" class="form-control input-sm" name="name" value="<?php echo htmlentities($db_name);?>">
                              </div>
                              <div class="col-sm-3">
                                <select class="form-control input-sm" name="game">
                                <?php
                                $query = "SELECT name FROM templates ORDER by name ASC";

                                 if ($stmt = $mysqli->prepare($query)) {
                                     $stmt->execute();
                                     $stmt->bind_result($db_name);

                                     while ($stmt->fetch()) {
                                       if ($db_name == $db_game_name) {
                                         echo '<option selected="selected">'.$db_name.'</option>';
                                       } else {
                                         echo "<option>".$db_name."</option>";
                                       }
                                     }
                                     $stmt->close();
                                 }  ?>
                                </select>
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="control-label col-sm-2">URL/<?php echo _addons_path; ?>:</label>
                              <div class="col-sm-3">
                                <input type="text" class="form-control input-sm" name="url" value="<?php echo htmlentities($db_url);?>">
                              </div>
                              <div class="col-sm-3">
                                <input type="text" class="form-control input-sm" name="path"value="<?php echo htmlentities($db_path);?>">
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="control-label col-sm-2"><?php echo _addons_folder; ?>:</label>
                              <div class="col-sm-3">
                                <input type="text" class="form-control input-sm" name="folder" value="<?php echo htmlentities($db_folder);?>">
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

                  If ($page == "addons?add") {

                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {


                       if (isset($_POST['confirm'])) {

                         $error = false;

                         $name = htmlentities($_POST['name']);
                         $game = htmlentities($_POST['game']);
                         $url = htmlentities($_POST['url']);
                         $path = htmlentities($_POST['path']);
                         $folder = htmlentities($_POST['folder']);
                         if(!preg_match("/^[a-zA-Z0-9._-]+$/",$name)){ $msg = _addons_message_error_name ."<br>";  $error = true;}
                         if(!preg_match("/^[a-zA-Z0-9._-]+$/",$game)){ $msg = _addons_message_error_game ."<br>";  $error = true;
                         } else {

                           $stmt = $mysqli->prepare("SELECT id FROM templates WHERE name = ?");
                           $stmt->bind_param('i', $game);
                           $stmt->execute();
                           $stmt->bind_result($template_id);
                           $stmt->fetch();
                           $stmt->close();

                           if (check_template($template_id)) { $msg = _message_addon_error; $error = true;}

                         }
                         if (isValidURL($url) == false) { $msg = _addons_message_error_url; $error = true;}

                         if ($error == false) {

                           $stmt = $mysqli->prepare("SELECT id FROM templates WHERE name = ?");
                           $stmt->bind_param('i', $game);
                           $stmt->execute();
                           $stmt->bind_result($template_id);
                           $stmt->fetch();
                           $stmt->close();

                           $stmt = $mysqli->prepare("INSERT INTO addons(game_id,name,url,path,folder) VALUES (?, ?, ?, ?, ?)");
                           $stmt->bind_param('issss', $template_id, $name,$url,$path,$folder);
                           $stmt->execute();
                           $stmt->close();

                          msg_okay(_addon_message_added);

                       } else {
                         msg_error('Something went wrong, '.$msg);
                       }
                      }
                  }
                  ?>

                  <form class="form-horizontal" action="index.php?page=addons?add" method="post">
                    <div class="form-group">
                      <label class="control-label col-sm-2">Name/Game:</label>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="name" placeholder="Sourcemod">
                      </div>
                      <div class="col-sm-3">
                        <select class="form-control input-sm" name="game">
                        <?php
                        $query = "SELECT name FROM templates ORDER by name ASC";

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
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2">URL/<?php echo _addons_path; ?>:</label>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="url" placeholder="csgo">
                      </div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="path" placeholder="csgo">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2"><?php echo _addons_folder; ?>:</label>
                      <div class="col-sm-3">
                        <input type="text" class="form-control input-sm" name="folder" placeholder="ulib">
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" name="confirm" class="btn btn-default btn-sm"><?php echo _button_save; ?></button>
                      </div>
                    </div>
                  </form>


                  <?php
               } elseif ($page == "addons" or startsWith($page, "addons?delete") or startsWith($page, "addons?edit")) {
                    ?>
                    <a  style="margin-bottom:2px;" href="index.php?page=addons?add"  class="btn pull-right btn-success btn-xs"><i class="fa fa-plus"></i></a>
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th><?php echo _addons_name; ?></th>
                          <th><?php echo _addons_game; ?></th>
                          <th><?php echo _addons_url; ?></th>
                          <th><?php echo _addons_path; ?></th>
                          <th><?php echo _table_action; ?></th>
                        </tr>
                      </thead>
                      <tbody>
                     <?php

                     $query = "SELECT id, game_id, name, url, path FROM addons ORDER by id";

                     if ($result = $mysqli->query($query)) {

                       /* fetch object array */
                       while ($row = $result->fetch_assoc()) {
                            $stmt = $mysqli->prepare("SELECT name FROM templates WHERE id = ?");
                            $stmt->bind_param('i', $row['game_id']);
                            $stmt->execute();
                            $stmt->bind_result($game);
                            $stmt->fetch();
                            $stmt->close();

                            $path = $row['path'];
                            $url = $row['url'];
                            $id = $row['id'];
                            echo "<tr>";
                            echo "<td>" . htmlentities($row['name']) . "</td>";
                            echo "<td>" . htmlentities($game) . "</td>";
                            if ( strlen($url) > 15) {
                              echo "<td>" . htmlentities(substr($url, 0, 15)) . "...</td>";
                            } else {
                              echo "<td>" . htmlentities($url) . "</td>";
                            }
                            if ( strlen($path) > 15) {
                              echo "<td>" . htmlentities(substr($path, 0, 15)) . "...</td>";
                            } else {
                              echo "<td>" . htmlentities($path) . "</td>";
                            }
                            echo '<td> <a href="index.php?page=addons?edit-'.$id.'"  class="btn btn-primary btn-xs">'._button_edit.'</i></a>
                                      <a style="margin-left:2px" href="index.php?page=addons?delete-'.$id.'"  class="btn btn-danger btn-xs"><i class="fa fa-remove"></i></a>';
                            echo '</td>';
                            echo "</tr>";
                          }
                          /* free result set */
                        $result->close();
                      } ?>
                      </tbody>
                    </table>
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
