<?php
//header
$title = "Dashboard";
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

if ($_SESSION['login'] == 1) {





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
                               echo '<i class="fa fa-'.event_id_to_ico($type).' fa-fw"></i>'.$row['message'];
                               echo ' <span class="pull-right text-muted small"><em>'.date('d-m-Y H:i:s', $row['timestamp']).'</em></span>';

                               echo '</a>';
                              }
                              /* free result set */
                            $result->close();
                          }  ?>
                           </div>
                           <!-- /.list-group -->
                           <a href="index.php?page=events" class="btn btn-default btn-block">Alle Events</a>
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
