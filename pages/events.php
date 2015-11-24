<?php
//header
$title = "Events";
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
                 <table class="table table-bordered">
                   <thead>
                     <tr>
                       <th>Datum</th>
                       <th>Nachricht</th>
                     </tr>
                   </thead>
                   <tbody>
                 <?php
                 $query = "SELECT type,message,timestamp FROM events ORDER by id DESC";

                 if ($result = $mysqli->query($query)) {

                   /* fetch object array */
                   while ($row = $result->fetch_assoc()) {
                     echo '<tr>';
                     echo '<td>'.date('d-m-Y H:i:s', $row['timestamp']).'</td>';
                     echo '<td>'.$row['message'].'</td>';

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