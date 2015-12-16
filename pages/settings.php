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
$title = _title_settings;
include 'header.php';
set_include_path('components/phpseclib');
include('Net/SSH2.php');


if ($_SESSION['login'] === 1 and $db_rank === 1) {


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

                 if ($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['confirm'])) {

                   $maintance = 0;$gs_log_cleanup = 0; $gs_crash = 0;$gs_cpu = 0;$gs_cpu_msg = 0;
                   if (isset($_POST['maintance'])) {  $maintance = 1;}
                   if (isset($_POST['gs_log_cleanup'])) { $gs_log_cleanup = 1;}
                   if (isset($_POST['gs_crash'])) { $gs_crash = 1;}
                   if (isset($_POST['gs_cpu'])) { $gs_cpu = 1;}
                   if (isset($_POST['gs_cpu_msg'])) { $gs_cpu_msg = 1;}

                   $id = 1;
                   $stmt = $mysqli->prepare("UPDATE wi_settings SET log_gs_cleanup = ?, wi_maintance = ?,gs_check_crash = ?,gs_check_cpu = ?,gs_check_cpu_msg = ?  WHERE id = ?");
                   $stmt->bind_param('iiiiii',$gs_log_cleanup,$maintance,$gs_crash,$gs_cpu,$gs_cpu_msg,$id);
                   $stmt->execute();
                   $stmt->close();

                 }

                 $wi_id = 1;
                 $stmt = $mysqli->prepare("SELECT log_gs_cleanup,wi_maintance,cronjob_lastrun,gs_check_crash,gs_check_cpu,gs_check_cpu_msg FROM wi_settings WHERE id = ?");
                 $stmt->bind_param('i', $wi_id);
                 $stmt->execute();
                 $stmt->bind_result($db_log_gs_cleanup,$db_wi_maintance,$db_cronjob_lastrun,$gs_check_crash,$gs_check_cpu,$gs_check_cpu_msg);
                 $stmt->fetch();
                 $stmt->close();

                 $time = time();
                 $lastrun = strtotime('+2 minutes', $db_cronjob_lastrun);
                 if ($time > $lastrun) {
                   ?>
                   <div class="alert alert-danger" role="alert">
                    <span class="fa fa-warning" aria-hidden="true"></span>
                    <span class="sr-only">Error:</span>
                    <?php echo _settings_msgbox; ?> <?php echo  date('d-m-Y H:i:s', $db_cronjob_lastrun); ?> <?php echo _settings_msgbox_executed; ?>
                  </div>
                  <?php
                } else {
                  ?>
                  <div class="alert alert-success" role="success">
                    <span class="fa fa-check" aria-hidden="true"></span>
                    <span class="sr-only">Success:</span>
                    <?php echo _settings_msgbox; ?> <?php echo  date('d-m-Y H:i:s', $db_cronjob_lastrun); ?> <?php echo _settings_msgbox_executed; ?>
                  </div>
                  <?php
                }
                if (isSecure() == true) {
                  ?>
                  <div class="alert alert-dismissible alert-info">
                    <span class="fa fa-check" aria-hidden="true"></span>
                    <?php echo _settings_message_ssl_true; ?>
                 </div>
                 <?php
               } else {
                 ?>
                 <div class="alert alert-danger" role="alert">
                  <span class="fa fa-warning" aria-hidden="true"></span>
                  <span class="sr-only">Error:</span>
                  <?php echo _settings_message_ssl_false; ?>
                </div>
                 <?php
               }
                ?>
                <form action="index.php?page=settings" method="post">
                <div class="form-group col-sm-8">
                  <label class="control-label">
                    <input data-size="mini" id="toggle-maintance" type="checkbox" name="maintance" data-toggle="toggle" disabled>
                    <?php echo _settings_maintance; ?></label>
                    <?php
                     if ($db_wi_maintance == 1) {
                      ?>
                      <script> function toggleOnmaintance() { $('#toggle-maintance').bootstrapToggle('on'); } addLoadEvent(toggleOnmaintance); </script>
                      <?php
                    } elseif ($db_wi_maintance == 0) { ?>
                      <script> function toggleOffmaintance() { $('#toggle-maintance').bootstrapToggle('off'); }  addLoadEvent(toggleOffmaintance); </script>
                      <?php
                    }
                    ?>
                  </div>
                <div class="form-group col-sm-8">
                  <label class="control-label">
                    <input data-size="mini" id="toggle-log" type="checkbox" name="gs_log_cleanup" data-toggle="toggle">
                    <?php echo _settings_cleanup; ?></label>
                    <?php
                     if ($db_log_gs_cleanup == 1) {
                      ?>
                      <script> function toggleOncleanup() { $('#toggle-log').bootstrapToggle('on'); } addLoadEvent(toggleOncleanup); </script>
                      <?php
                    } elseif ($db_log_gs_cleanup == 0) { ?>
                      <script> function toggleOffcleanup() { $('#toggle-log').bootstrapToggle('off'); } addLoadEvent(toggleOffcleanup); </script>
                      <?php
                    }
                    ?>
                </div>
                <div class="form-group col-sm-8">
                  <label class="control-label">
                    <input data-size="mini" id="toggle-crash" type="checkbox" name="gs_crash" data-toggle="toggle">
                    <?php echo _settings_restart; ?></label>
                      <?php
                     if ($gs_check_crash == 1) {
                      ?>
                      <script> function toggleOncleanup() { $('#toggle-crash').bootstrapToggle('on'); } addLoadEvent(toggleOncleanup); </script>
                      <?php
                    } elseif ($gs_check_crash == 0) { ?>
                      <script> function toggleOffcleanup() { $('#toggle-crash').bootstrapToggle('off'); } addLoadEvent(toggleOffcleanup); </script>
                      <?php
                    }
                    ?>
                </div>
                <div class="form-group col-sm-8">
                  <label class="control-label">
                    <input data-size="mini" id="toggle-cpu" type="checkbox" name="gs_cpu" data-toggle="toggle">
                    <?php echo _settings_restart_cpu_usage; ?></label>
                      <?php
                     if ($gs_check_cpu == 1) {
                      ?>
                      <script> function toggleOncpu() { $('#toggle-cpu').bootstrapToggle('on'); } addLoadEvent(toggleOncpu); </script>
                      <?php
                    } elseif ($gs_check_cpu == 0) { ?>
                      <script> function toggleOffcpu() { $('#toggle-cpu').bootstrapToggle('off'); } addLoadEvent(toggleOffcpu); </script>
                      <?php
                    }
                    ?>
                </div>
                <div class="form-group col-sm-8">
                  <label class="control-label">
                    <input data-size="mini" id="toggle-msg" type="checkbox" name="gs_cpu_msg" data-toggle="toggle">
                    <?php echo _settings_message_cpu_load; ?></label>
                      <?php
                     if ($gs_check_cpu_msg == 1) {
                      ?>
                      <script> function toggleOnmsg() { $('#toggle-msg').bootstrapToggle('on'); } addLoadEvent(toggleOnmsg); </script>
                      <?php
                    } elseif ($gs_check_cpu_msg == 0) { ?>
                      <script> function toggleOffmsg() { $('#toggle-msg').bootstrapToggle('off'); } addLoadEvent(toggleOffmsg); </script>
                      <?php
                    }
                    ?>
                </div>
                <div class="form-group col-sm-8">
                    <button type="submit" name="confirm" class="btn btn-default btn-sm"><?php echo _button_save; ?></button>
                </div>
              </form>







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

} elseif ($_SESSION['login'] == 1 and $db_rank != 1) { header('Location: index.php?page=dashboard');
} else {  header('Location: index.php');}


//Footer
include 'footer.html';
?>
