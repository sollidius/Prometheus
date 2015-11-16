<?php
//header
$title = "Benutzer Einstellungen";
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

  $msg = "";
  $success = false;


 if ($_SERVER['REQUEST_METHOD'] == 'POST') {

   $old_pw = $_POST['old_pw'];
   $pw = $_POST['new_pw'];
   $pw2 = $_POST['new_pw2'];


   $stmt = $mysqli->prepare("SELECT password,id,name FROM users WHERE id = ?");
   $stmt->bind_param('i', $_SESSION['user_id']);
   $stmt->execute();
   $stmt->bind_result($password_db,$id,$name);
   $stmt->fetch();
   $stmt->close();

   if ($pw == $pw2) {
     if (password_verify($old_pw, $password_db)) {

       $hash = password_hash($pw, PASSWORD_DEFAULT);

       $stmt = $mysqli->prepare("UPDATE users SET password = ?  WHERE id = ?");
       $stmt->bind_param('si',$hash,$_SESSION['user_id']);
       $stmt->execute();
       $stmt->close();

       $msg = "Okay";
       $success = true;
     } else {
       $msg = "Altes Passwort falsch";
       $success = false;
     }
   }
 }

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
                 if ($msg != "" and $success == false) {
                   msg_error('Something went wrong, '.$msg);
                 } elseif ($msg != "" and $success == true) {
                   msg_okay($msg);
                 }
                  ?>
                 <form class="form-horizontal" action="index.php?page=usettings" method="post">
                 <div class="form-group">
                  <div class="col-lg-8">
                   <label class="control-label col-sm-3">Altes Passwort:</label>
                   <div class="col-sm-3">
                     <input type="password" class="form-control" name="old_pw">
                   </div>
                 </div>
                     <div style="margin-top:2px;" class="col-lg-8">
                   <label class="control-label col-sm-3">Neues Passwort:</label>
                   <div class="col-sm-3">
                     <input type="password" class="form-control" name="new_pw">
                   </div>
                 </div>
                     <div style="margin-top:2px;" class="col-lg-8">
                   <label class="control-label col-sm-3">Wiederholen:</label>
                   <div class="col-sm-3">
                     <input type="password" class="form-control" name="new_pw2">
                   </div>
                 </div>
                      <div style="margin-top:2px;" class="col-lg-8">
                      <div style="margin-top:2px;" class="col-lg-6">
                        <button type="submit" name="confirm" class="btn pull-right btn-success">Abschicken</button
                        </div>
                        </div>
                 </div>
               </form>
                 </div>


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

 } else { header('Location: index.php');}


//Footer
include 'footer.html';
?>
