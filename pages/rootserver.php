<?php
//header
$title = "Rootserver";
include 'header.php';
include 'functions.php';
set_include_path('components/phpseclib');
include('Net/SSH2.php');

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
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {


                   if (isset($_POST['confirm'])) {

                     $error = false;
                     $status = 0;

                     $name = $_POST['name']; $ip = $_POST['ip']; $port = $_POST['port'];
                     $user = $_POST['user']; $password = $_POST['password']; $root = $_POST['root']; $root_password = $_POST['root_password'];

                     if ($error == false) {

                       $stmt = $mysqli->prepare("INSERT INTO dedicated(name,ip,port,user,password,status) VALUES (?, ?, ?, ? ,? ,?)");
                       $stmt->bind_param('ssissi', $name,$ip,$port,$user,$password,$status);
                       $stmt->execute();
                       $stmt->close();

                       $ssh = new Net_SSH2($ip,$port);
                        if (!$ssh->login($root, $root_password)) {
                           exit('Login Failed');
                        }

                        echo $ssh->exec('pwd');
                        echo $ssh->exec('ls -la');

                       echo '
                       <div class="alert alert-success" role="alert">
                         <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                         <span class="sr-only">Error:</span>
                         Okay
                       </div>';

                   } else {

                     echo '
                     <div class="alert alert-danger" role="alert">
                       <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                       <span class="sr-only">Error:</span>
                       Something went wrong
                     </div>';

                   }






                  } else {

                ?>

                <form class="form-horizontal" action="index.php?page=rootserver" method="post">
                  <div class="form-group">
                    <label class="control-label col-sm-2">Name:</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" name="name" placeholder="Chewbacca">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-sm-2" for="email">IP/Port:</label>
                    <div class="col-sm-6">
                      <input type="text" class="form-control" name="ip" placeholder="127.0.0.1">
                    </div>
                    <div class="col-sm-2">
                      <input type="text" class="form-control" name="port" placeholder="22">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-sm-2" for="pwd">Benutzer/Passwort:</label>
                    <div class="col-sm-4">
                      <input type="text" class="form-control" name="user" placeholder="prometheus">
                    </div>
                    <div class="col-sm-4">
                      <input type="password" class="form-control" name="password" placeholder="123456">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-sm-2" for="pwd">Root/Passwort:</label>
                    <div class="col-sm-4">
                      <input type="text" class="form-control" name="root" placeholder="prometheus">
                    </div>
                    <div class="col-sm-4">
                      <input type="password" class="form-control" name="root_password" placeholder="123456">
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                      <button type="submit" name="confirm" class="btn btn-default">Submit</button>
                    </div>
                  </div>
                </form>



                <?php }
                } else {
                  ?>
                  <form action="index.php?page=rootserver" method="post">
                  <button style="margin-bottom:2px;" type="submit" name="add" class="btn pull-right btn-success">+</button>
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>IP</th>
                        <th>Port</th>
                        <th>Benutzer</th>
                        <th>Passwort</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                   <?php

                   $query = "SELECT name,ip,port,user,password,status FROM dedicated ORDER by id";

                    if ($stmt = $mysqli->prepare($query)) {
                        $stmt->execute();
                        $stmt->bind_result($db_name, $db_ip,$db_port,$db_user,$db_password,$db_status);

                        while ($stmt->fetch()) {
                          echo "<tr>";
                          echo "<td>" . $db_name . "</td>";
                          echo "<td>" . $db_ip . "</td>";
                          echo "<td>" . $db_port . "</td>";
                          echo "<td>" . $db_user . "</td>";
                          echo "<td>" . $db_password . "</td>";
                          if ($db_status == 0) { echo "<td>Unbekannt</td>"; }
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


<?php

} elseif ($_SESSION['login'] == 1 and $db_rank != 1) { header('Location: index.php?page=dashboard');
} else {  header('Location: index.php');}


//Footer
include 'footer.html';
?>
