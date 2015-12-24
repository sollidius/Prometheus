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
$title = _title_bans;
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
               <div class="col-lg-12">
                 <?php

                     $query = "SELECT id FROM blacklist ORDER by id";

                        if ($result = $mysqli->query($query)) {

                         /* fetch object array */
                        while ($row = $result->fetch_row()) {

                          if ($page == "bans?delete-".$row[0]) {
                            $error = false;
                            if ($error == false) {

                              $stmt = $mysqli->prepare("DELETE FROM blacklist WHERE id = ?");
                              $stmt->bind_param('i', $row[0]);
                              $stmt->execute();
                              $stmt->close();
                              msg_okay(_bans_message_removed);
                            } else {
                              msg_warning($msg);
                            }
                          }
                        }
                        /* free result set */
                        $result->close();
                        }

                  if ($page == "bans" or startsWith($page, "bans?delete")) {
                    ?>
                    <p><?php echo _bans_message; ?></p>
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th>IP</th>
                          <th>IP Proxy</th>
                          <th><?php echo _bans_date; ?></th>
                          <th><?php echo _bans_banned; ?></th>
                          <th><?php echo _table_action; ?></th>
                        </tr>
                      </thead>
                      <tbody>
                     <?php

                     $query = "SELECT id, ip_remote, ip_forward, timestamp, timestamp_expires FROM blacklist ORDER by id";

                     if ($result = $mysqli->query($query)) {

                       /* fetch object array */
                       while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            if ($row['ip_remote'] == "0") { echo "<td>"._dedicated_message_ip_invalid."</td>"; } else { echo "<td>" . htmlentities($row['ip_remote']) . "</td>"; }
                            if ($row['ip_forward'] == "0") { echo "<td>Kein Proxy</td>"; } elseif ($row['ip_forward'] == "0") {  echo "<td>"._dedicated_message_ip_invalid."</td>"; } else { echo "<td>" . htmlentities($row['ip_forward']) . "</td>"; }
                            echo "<td>".date('d-m-Y H:i:s', $row['timestamp'])."</td>";
                            echo "<td>".date('d-m-Y H:i:s', $row['timestamp_expires'])."</td>";
                            echo '<td> <a style="margin-left:2px" href="index.php?page=bans?delete-'.$row['id'].'"  class="btn btn-danger btn-xs"><i class="fa fa-remove"></i></a>';
                            echo "</tr>";
                          }
                          /* free result set */
                        $result->close();
                      } ?>
                      </tbody>
                    </table>
                  <?php }
                 ?>
            </div>
               <!-- /.col-lg-12 -->
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

 } elseif ($_SESSION['login'] === 1 and $db_rank != 1) { header('Location: index.php?page=dashboard');
 } else {  header('Location: index.php');}


//Footer
include 'footer.html';
?>
