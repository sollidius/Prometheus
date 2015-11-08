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
               <div class="col-lg-8">




               </div>
               <!-- /.col-lg-8 -->
               <div class="col-lg-4">
                   <div class="panel panel-default">
                       <div class="panel-heading">
                           <i class="fa fa-bell fa-fw"></i> Notifications Panel
                       </div>
                       <!-- /.panel-heading -->
                       <div class="panel-body">
                           <div class="list-group">
                               <a href="#" class="list-group-item">
                                   <i class="fa fa-comment fa-fw"></i> New Comment
                                   <span class="pull-right text-muted small"><em>4 minutes ago</em>
                                   </span>
                               </a>
                               <a href="#" class="list-group-item">
                                   <i class="fa fa-twitter fa-fw"></i> 3 New Followers
                                   <span class="pull-right text-muted small"><em>12 minutes ago</em>
                                   </span>
                               </a>
                               <a href="#" class="list-group-item">
                                   <i class="fa fa-envelope fa-fw"></i> Message Sent
                                   <span class="pull-right text-muted small"><em>27 minutes ago</em>
                                   </span>
                               </a>
                               <a href="#" class="list-group-item">
                                   <i class="fa fa-tasks fa-fw"></i> New Task
                                   <span class="pull-right text-muted small"><em>43 minutes ago</em>
                                   </span>
                               </a>
                               <a href="#" class="list-group-item">
                                   <i class="fa fa-upload fa-fw"></i> Server Rebooted
                                   <span class="pull-right text-muted small"><em>11:32 AM</em>
                                   </span>
                               </a>
                               <a href="#" class="list-group-item">
                                   <i class="fa fa-bolt fa-fw"></i> Server Crashed!
                                   <span class="pull-right text-muted small"><em>11:13 AM</em>
                                   </span>
                               </a>
                               <a href="#" class="list-group-item">
                                   <i class="fa fa-warning fa-fw"></i> Server Not Responding
                                   <span class="pull-right text-muted small"><em>10:57 AM</em>
                                   </span>
                               </a>
                               <a href="#" class="list-group-item">
                                   <i class="fa fa-shopping-cart fa-fw"></i> New Order Placed
                                   <span class="pull-right text-muted small"><em>9:49 AM</em>
                                   </span>
                               </a>
                               <a href="#" class="list-group-item">
                                   <i class="fa fa-money fa-fw"></i> Payment Received
                                   <span class="pull-right text-muted small"><em>Yesterday</em>
                                   </span>
                               </a>
                           </div>
                           <!-- /.list-group -->
                           <a href="#" class="btn btn-default btn-block">View All Alerts</a>
                       </div>
                       <!-- /.panel-body -->
                   </div>
               </div>
               <!-- /.col-lg-4 -->
           </div>
           <!-- /.row -->
       </div>
       <!-- /#page-wrapper -->

   </div>
   <!-- /#wrapper -->


<?php

 } else { header('Location: index.php');}


//Footer
include 'footer.html';
?>
