<?php
//header
$title = "Gameserver Vorlagen";
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
                 <?php

                     $query = "SELECT id FROM templates ORDER by id";

                        if ($result = $mysqli->query($query)) {

                         /* fetch object array */
                        while ($row = $result->fetch_row()) {

                          if ($page == "templates?delete-".$row[0]) {
                            $stmt = $mysqli->prepare("DELETE FROM templates WHERE id = ?");
                            $stmt->bind_param('i', $row[0]);
                            $stmt->execute();
                            $stmt->close();
                          }
                        }
                        /* free result set */
                        $result->close();
                        }

                  if ($_SERVER['REQUEST_METHOD'] == 'POST') {


                     if (isset($_POST['confirm'])) {

                       $error = false;

                       $name = $_POST['name'];
                       $type = $_POST['type'];
                       $type_name = $_POST['type_name'];
                       $internal = $_POST['internal'];


                       if (exists_entry("name","templates","name",$name) == true) { $error = true;}

                       if ($error == false) {


                         $stmt = $mysqli->prepare("INSERT INTO templates(name,type,type_name,name_internal) VALUES (?, ?, ?, ?)");
                         $stmt->bind_param('ssss', $name, $type,$type_name,$internal);
                         $stmt->execute();
                         $stmt->close();

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

                  <form class="form-horizontal" action="index.php?page=templates" method="post">
                    <div class="form-group">
                      <label class="control-label col-sm-2">Name/Internal:</label>
                      <div class="col-sm-3">
                        <input type="text" class="form-control" name="name" placeholder="Garrysmod">
                      </div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control" name="internal" placeholder="garrysmod">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2">Type:</label>
                      <div class="col-sm-3">
                        <input type="text" class="form-control" name="type" placeholder="steamcmd">
                      </div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control" name="type_name" placeholder="4020">
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" name="confirm" class="btn btn-default">Abschicken</button>
                      </div>
                    </div>
                  </form>



                  <?php }
                  } else {
                    ?>
                    <form action="index.php?page=templates" method="post">
                    <button style="margin-bottom:2px;" type="submit" name="add" class="btn pull-right btn-success">+</button>
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th>Name</th>
                          <th>Internal</th>
                          <th>Type</th>
                          <th>Type Name</th>
                          <th>Aktion</th>
                        </tr>
                      </thead>
                      <tbody>
                     <?php

                     $query = "SELECT name, type,type_name,name_internal,id FROM templates ORDER by id";

                      if ($stmt = $mysqli->prepare($query)) {
                          $stmt->execute();
                          $stmt->bind_result($db_name, $db_type,$db_type_name,$db_name_internal,$db_id);

                          while ($stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>" . $db_name . "</td>";
                            echo "<td>" . $db_name_internal . "</td>";
                            echo "<td>" . $db_type . "</td>";
                            echo "<td>" . $db_type_name . "</td>";
                            echo '<td> <a href="index.php?page=templates?delete-'.$db_id.'"  class="btn btn-danger btn-sm">X</a></td>';
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
