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
$title = _title_dasboard;
include 'header.php';

if ($_SESSION['login'] === 1 AND ($db_rank === 1 OR $db_rank === 2)) {





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
             <?php if ($db_rank == 1) { ?>
               <div class="col-lg-8">
<!--
                 <div class="row">
              <div class="col-lg-3 col-md-6">
                  <div class="panel panel-primary">
                      <div class="panel-heading">
                          <div class="row">
                              <div class="col-xs-3">
                                  <i class="fa fa-comments fa-5x"></i>
                              </div>
                              <div class="col-xs-9 text-right">
                                  <div class="huge">??</div>
                                  <div>???????????????</div>
                              </div>
                          </div>
                      </div>
                      <a href="#">
                          <div class="panel-footer">
                              <span class="pull-left">View Details</span>
                              <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                              <div class="clearfix"></div>
                          </div>
                      </a>
                  </div>
              </div>
              <div class="col-lg-3 col-md-6">
                  <div class="panel panel-success">
                      <div class="panel-heading">
                          <div class="row">
                              <div class="col-xs-3">
                                  <i class="fa fa-tasks fa-5x"></i>
                              </div>
                              <div class="col-xs-9 text-right">
                                  <div class="huge">??</div>
                                  <div>???????????????</div>
                              </div>
                          </div>
                      </div>
                      <a href="#">
                          <div class="panel-footer">
                              <span class="pull-left">View Details</span>
                              <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                              <div class="clearfix"></div>
                          </div>
                      </a>
                  </div>
              </div>
              <div class="col-lg-3 col-md-6">
                  <div class="panel panel-warning">
                      <div class="panel-heading">
                          <div class="row">
                              <div class="col-xs-3">
                                  <i class="fa fa-shopping-cart fa-5x"></i>
                              </div>
                              <div class="col-xs-9 text-right">
                                  <div class="huge">??</div>
                                  <div>???????????????</div>
                              </div>
                          </div>
                      </div>
                      <a href="#">
                          <div class="panel-footer">
                              <span class="pull-left">View Details</span>
                              <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                              <div class="clearfix"></div>
                          </div>
                      </a>
                  </div>
              </div>
              <div class="col-lg-3 col-md-6">
                  <div class="panel panel-danger">
                      <div class="panel-heading">
                          <div class="row">
                              <div class="col-xs-3">
                                  <i class="fa fa-support fa-5x"></i>
                              </div>
                              <div class="col-xs-9 text-right">
                                  <div class="huge">??</div>
                                  <div>???????????????</div>
                              </div>
                          </div>
                      </div>
                      <a href="#">
                          <div class="panel-footer">
                              <span class="pull-left">View Details</span>
                              <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                              <div class="clearfix"></div>
                          </div>
                      </a>
                  </div>
              </div>
          </div>
          <!-- /.row -->


               </div>
               <!-- /.col-lg-8 -->
               <div class="col-lg-4">
                   <div class="panel panel-default">
                       <div class="panel-heading">
                           <i class="fa fa-bell fa-fw"></i> Events
                       </div>
                       <!-- /.panel-heading -->
                       <div class="panel-body">
                           <div class="list-group">
                             <tbody>
                           <?php
                           $query = "SELECT type,message,timestamp FROM events ORDER by id DESC LIMIT 8";

                           if ($result = $mysqli->query($query)) {

                             /* fetch object array */
                             while ($row = $result->fetch_assoc()) {
                               $type = $row['type'];
                               echo '<a href="#" class="list-group-item">';
                               echo '<i class="fa fa-'.event_id_to_ico($type).' fa-fw"></i>';
                               if ($db_language == "de") {
                                 if ($type == 1) {
                                  echo "Der Gameserver ".$row['message']." wurde gestartet.";
                                } elseif ($type == 2) {
                                   echo "Der Gameserver ".$row['message']." wurde angehalten.";
                                } elseif ($type == 3) {
                                   echo "Der Gameserver ".$row['message']." wurde gelöscht.";
                                 } elseif ($type == 4) {
                                   echo "Der Gameserver ".$row['message']." wird aktualisiert.";
                                 } elseif ($type == 5) {
                                   echo "Der Gameserver ".$row['message']." wird neuinstalliert.";
                                 } elseif ($type == 6) {
                                   echo "Der Gameserver ".$row['message']." wurde hinzugefügt.";
                                 } elseif ($type == 7) {
                                   $tmp = explode(":",$row['message']);
                                   echo "Das Template ".$tmp[0]. " auf dem Rootserver ".$tmp[1]. " wird aktualisiert.";
                                 } elseif ($type == 8) {
                                   echo "Der Gameserver ".$row['message']." wurde aktualisiert.";
                                 } elseif ($type == 9) {
                                   echo "Der Gameserver ".$row['message']." wurde neugestartet.";
                                 } elseif ($type == 10) {
                                   echo "Der Gameserver ".$row['message']." ist abgestürtzt und wurde neu gestartet.";
                                 } elseif ($type == 11) {
                                   echo "Der Gameserver ".$row['message']." wurde wegen hoher CPU Last neugestartet.";
                                 } elseif ($type == 12) {
                                   echo "Das FTP Passwort wurde für den Gameserver ".$row['message']." geändert.</td>";
                                 } elseif ($type == 13) {
                                   $tmp = explode(":",$row['message']);
                                   echo "Das Template ".$tmp[0]. " auf dem Rootserver ".$tmp[1]. " wurde aktualisiert.</td>";
                                 } else {
                                    echo $row['message'];
                                 }
                               } elseif ($db_language == "en") {
                                 if ($type == 1) {
                                   echo "The Gameserver ".$row['message']." was started.";
                                 } elseif ($type == 2) {
                                    echo "The Gameserver ".$row['message']." was stopped.";
                                 } elseif ($type == 3) {
                                     echo "The Gameserver ".$row['message']." was deleted.";
                                 } elseif ($type == 4) {
                                   echo "The Gameserver ".$row['message']." gets updated.";
                                 } elseif ($type == 5) {
                                   echo "The Gameserver ".$row['message']." gets reinstalled.";
                                 } elseif ($type == 6) {
                                   echo "The Gameserver ".$row['message']." was added.";
                                 } elseif ($type == 7) {
                                   $tmp = explode(":",$row['message']);
                                   echo "The Template ".$tmp[0]. " on the Dedi ".$tmp[1]. " gets updated.";
                                 } elseif ($type == 8) {
                                   echo "The Gameserver ".$row['message']." was updated.";
                                 } elseif ($type == 9) {
                                   echo "The Gameserver ".$row['message']." has been restarted.";
                                 } elseif ($type == 10) {
                                   echo "The Gameserver ".$row['message']." crashed and was restarted.";
                                 } elseif ($type == 11) {
                                   echo "The Gameserver ".$row['message']." was restarted due to high CPU load.";
                                 } elseif ($type == 12) {
                                   echo "The FTP Password was changed for the Gameserver ".$row['message'].".</td>";
                                 } elseif ($type == 13) {
                                   $tmp = explode(":",$row['message']);
                                   echo "The Template ".$tmp[0]. " on the Dedi ".$tmp[1]. " was updated.</td>";
                                 } else {
                                    echo $row['message'];
                                 }
                               }
                               echo ' <span class="pull-right text-muted small"><em>'.date('d-m-Y H:i:s', $row['timestamp']).'</em></span>';

                               echo '</a>';
                              }
                              /* free result set */
                            $result->close();
                          }  ?>
                           </div>
                           <!-- /.list-group -->
                           <a href="index.php?page=events" class="btn btn-default btn-block"><?php echo _dashboard_events; ?></a>
                       </div>
                       <!-- /.panel-body -->
                   </div>
               </div>
               <!-- /.col-lg-4 -->
               <?php } ?>
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

 } else { header('Location: index.php');}


//Footer
include 'footer.html';
?>
