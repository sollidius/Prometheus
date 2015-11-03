<?php
//header
$title = "Dashboard";
include 'header.html';

?>
<div id="wrapper">

       <!-- Navigation -->
       <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
           <div class="navbar-header">
               <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                   <span class="sr-only">Toggle navigation</span>
                   <span class="icon-bar"></span>
                   <span class="icon-bar"></span>
                   <span class="icon-bar"></span>
               </button>
               <a class="navbar-brand" href="index.php?page=dashboard">Prometheus</a>
           </div>
           <!-- /.navbar-header -->
           <ul class="nav navbar-top-links navbar-right">
               <li class="dropdown">
                   <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                       <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
                   </a>
                   <ul class="dropdown-menu dropdown-user">
                       <li><a href="#"><i class="fa fa-user fa-fw"></i> User Profile</a>
                       </li>
                       <li><a href="#"><i class="fa fa-gear fa-fw"></i> Settings</a>
                       </li>
                       <li class="divider"></li>
                       <li><a href="index.php?page=logout"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                       </li>
                   </ul>
                   <!-- /.dropdown-user -->
               </li>
               <!-- /.dropdown -->
           </ul>
           <!-- /.navbar-top-links -->

           <div class="navbar-default sidebar" role="navigation">
               <div class="sidebar-nav navbar-collapse">
                   <ul class="nav" id="side-menu">
                       <li>
                           <a href="index.php?page=dashboard"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                       </li>
                       <li>
                           <a href="index.php?page=settings"><i class="fa fa-table fa-fw"></i> Einstellungen</a>
                       </li>
                       <li>
                           <a href="index.php?page=users"><i class="fa fa-table fa-fw"></i> Benutzer</a>
                       </li>
                       <li>
                           <a href="index.php?page=rootserver"><i class="fa fa-edit fa-fw"></i> Rootserver</a>
                       </li>
                       <li>
                           <a href="index.php?page=gameserver"><i class="fa fa-edit fa-fw"></i> Gameserver</a>
                       </li>
                       <li>
                           <a href="#"><i class="fa fa-wrench fa-fw"></i> UI Elements<span class="fa arrow"></span></a>
                           <ul class="nav nav-second-level">
                               <li>
                                   <a href="panels-wells.html">Panels and Wells</a>
                               </li>
                               <li>
                                   <a href="buttons.html">Buttons</a>
                               </li>
                           </ul>
                           <!-- /.nav-second-level -->
                       </li>
                   </ul>
               </div>
               <!-- /.sidebar-collapse -->
           </div>
           <!-- /.navbar-static-side -->
       </nav>

       <div id="page-wrapper">
           <div class="row">
               <div class="col-lg-12">
                   <h1 class="page-header">Dashboard</h1>
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











//Footer
include 'footer.html';
?>
