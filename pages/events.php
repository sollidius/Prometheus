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
$title = _title_events;
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
               <div class="col-lg-8">
                 <table class="table table-bordered">
                   <thead>
                     <tr>
                       <th><?php echo _events_date; ?></th>
                       <th><?php echo _events_message; ?></th>
                     </tr>
                   </thead>
                   <tbody>
                 <?php
                 $query = "SELECT type,message,timestamp FROM events ORDER by id DESC";

                 if ($result = $mysqli->query($query)) {

                   /* fetch object array */
                   while ($row = $result->fetch_assoc()) {
                     $type = $row['type'];
                     echo '<tr>';
                     echo '<td>'.date('d-m-Y H:i:s', $row['timestamp']).'</td>';
                     echo '<td><i class="fa fa-'.event_id_to_ico($type).' fa-fw"></i> ';
                     if ($db_language == "de") {
                       if ($type == 1) {
                        echo "Der Gameserver ".$row['message']." wurde gestartet.</td>";
                      } elseif ($type == 2) {
                         echo "Der Gameserver ".$row['message']." wurde angehalten.</td>";
                      } elseif ($type == 3) {
                         echo "Der Gameserver ".$row['message']." wurde gelöscht.</td>";
                       } elseif ($type == 4) {
                         echo "Der Gameserver ".$row['message']." wird aktualisiert.</td>";
                       } elseif ($type == 5) {
                         echo "Der Gameserver ".$row['message']." wird neuinstalliert.</td>";
                       } elseif ($type == 6) {
                         echo "Der Gameserver ".$row['message']." wurde hinzugefügt.</td>";
                       } elseif ($type == 7) {
                         $tmp = explode(":",$row['message']);
                         echo "Das Template ".$tmp[0]. " auf dem Rootserver ".$tmp[1]. " wird aktualisiert.</td>";
                       } elseif ($type == 8) {
                         echo "Der Gameserver ".$row['message']." wurde aktualisiert.</td>";
                       } elseif ($type == 9) {
                         echo "Der Gameserver ".$row['message']." wurde neugestartet.</td>";
                       } elseif ($type == 10) {
                         echo "Der Gameserver ".$row['message']." ist abgestürtzt und wurde neu gestartet.</td>";
                       } elseif ($type == 11) {
                         echo "Der Gameserver ".$row['message']." wurde wegen hoher CPU Last neugestartet.</td>";
                       } elseif ($type == 12) {
                         echo "Das FTP Passwort wurde für den Gameserver ".$row['message']." geändert.</td>";
                       } elseif ($type == 13) {
                         $tmp = explode(":",$row['message']);
                         echo "Das Template ".$tmp[0]. " auf dem Rootserver ".$tmp[1]. " wurde aktualisiert.</td>";
                       } else {
                          echo $row['message'].'</td>';
                       }
                     } elseif ($db_language == "en") {
                       if ($type == 1) {
                         echo "The Gameserver ".$row['message']." was started.</td>";
                       } elseif ($type == 2) {
                          echo "The Gameserver ".$row['message']." was stopped.</td>";
                       } elseif ($type == 3) {
                           echo "The Gameserver ".$row['message']." was deleted.</td>";
                       } elseif ($type == 4) {
                         echo "The Gameserver ".$row['message']." gets updated.</td>";
                       } elseif ($type == 5) {
                         echo "The Gameserver ".$row['message']." gets reinstalled.</td>";
                       } elseif ($type == 6) {
                         echo "The Gameserver ".$row['message']." was added.</td>";
                       } elseif ($type == 7) {
                         $tmp = explode(":",$row['message']);
                         echo "The Template ".$tmp[0]. " on the Dedi ".$tmp[1]. " gets updated.</td>";
                       } elseif ($type == 8) {
                         echo "The Gameserver ".$row['message']." was updated.</td>";
                       } elseif ($type == 9) {
                         echo "The Gameserver ".$row['message']." has been restarted.</td>";
                       } elseif ($type == 10) {
                         echo "The Gameserver ".$row['message']." crashed and was restarted.</td>";
                       } elseif ($type == 11) {
                         echo "The Gameserver ".$row['message']." was restarted due to high CPU load.</td>";
                       } elseif ($type == 12) {
                         echo "The FTP Password was changed for the Gameserver ".$row['message'].".</td>";
                       } elseif ($type == 13) {
                         $tmp = explode(":",$row['message']);
                         echo "The Template ".$tmp[0]. " on the Dedi ".$tmp[1]. " was updated.</td>";
                       } else {
                          echo $row['message'].'</td>';
                       }
                     }

                     echo '<tr>';
                    }
                    /* free result set */
                  $result->close();
                }  ?>
                </tbody>
              </table>
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
