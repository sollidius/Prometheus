<?php
//header
$title = "Einstellungen";
include 'header.html';
include 'functions.php';

session_start();

if ($_SESSION['login'] == 1) {





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
                       <li><a href="index.php?page=usettings"><i class="fa fa-gear fa-fw"></i> Settings</a>
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
                   <h1 class="page-header"><?php echo $title; ?></h1>
               </div>
               <!-- /.col-lg-12 -->
           </div>
           <div class="row">
               <div class="col-lg-8">




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


<?php

 } else { header('Location: index.php');}


//Footer
include 'footer.html';
?>
