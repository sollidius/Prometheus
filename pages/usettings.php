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
$title = _title_usettings;
include 'header.php';

if ($_SESSION['login'] === 1 AND ($db_rank === 1 OR $db_rank === 2)) {

  $msg = "";
  $success = false;


 if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  if (isset($_POST['confirm'])) {

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

       $msg = "Das Passwort wurde geändert.";
       $success = true;
     } else {
       $msg = "Altes Passwort falsch";
       $success = false;
     }
   }
 }    elseif (isset($_POST['general'])) {

      $language = htmlentities($_POST['language']);

      $lang = "en";
      if ($language == "Deutsch") {
          $lang = "de";
      } elseif ($language == "English") {
          $lang = "en";
      }

      $stmt = $mysqli->prepare("UPDATE users SET language = ?  WHERE id = ?");
      $stmt->bind_param('si',$lang,$_SESSION['user_id']);
      $stmt->execute();
      $stmt->close();





 }
 }

?>
<div id="wrapper">

      <?php include 'navbar.php'; ?>

      <script>
        function addLoadEvent(func) {
        var oldonload = window.onload;
        if (typeof window.onload != 'function') {
          window.onload = func;
        } else {
          window.onload = function() {
            if (oldonload) {
              oldonload();
            }
            func();
          }
        }
      }
      </script>

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
               <h2><?php echo _usettings_password; ?></h2>
                <form class="form-horizontal" action="index.php?page=usettings" method="post">
                <div class="form-group">
                  <label class="control-label col-sm-2" for="email"><?php echo _usettings_oldpwd; ?>:</label>
                  <div class="col-sm-10">
                    <input type="password" class="form-control input-sm" name="old_pw">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-2" for="pwd"><?php echo _usettings_newpwd; ?>:</label>
                  <div class="col-sm-10">
                    <input type="password" class="form-control input-sm" name="new_pw">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-2" for="pwd"><?php echo _usettings_repeatpwd; ?>:</label>
                  <div class="col-sm-10">
                    <input type="password" class="form-control input-sm" name="new_pw2">
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" name="confirm" class="btn btn-default btn-sm"><?php echo _button_save; ?></button>
                  </div>
                </div>
              </form>
              <h2><?php echo _usettings_general; ?></h2>
              <form class="form-horizontal" action="index.php?page=usettings" method="post">
              <div class="form-group">
                <label class="control-label col-sm-2">Language:</label>
                <div class="col-sm-2">
                  <select class="form-control input-sm" name="language">
                    <?php if ($db_language == "de") { echo '<option selected="selected">Deutsch</option>';  } else { echo "<option>Deutsch</option>"; } ?>
                    <?php if ($db_language == "en") { echo '<option selected="selected">English</option>';  } else { echo "<option>English</option>"; } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                  <button type="submit" name="general" class="btn btn-default btn-sm"><?php echo _button_save; ?></button>
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
