<?php
//header
$title = "Login";
include 'header.php';


$error = false; $msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  if (isValidEmail($_POST['email']) == false) { $msg ="E-Mail ungültig."; $error = true;}
  if (strlen($_POST['email']) < 6) { $msg ="E-Mail zu kurz."; $error = true;}
  if (strlen($_POST['password']) < 8 ) {$msg = "Passwort zu kurz"; $error = true;}

  if ($error == false) {

  $password = $_POST['password'];

  $stmt = $mysqli->prepare("SELECT password,id,name FROM users WHERE email = ?  LIMIT 1");
  $stmt->bind_param('s', $_POST['email']);
  $stmt->execute();
  $stmt->bind_result($password_db,$id,$name);
  $stmt->fetch();
  $stmt->close();

    if (password_verify($password, $password_db)) {
        // Success!
        session_start();
        $_SESSION['login'] = 1;
        $_SESSION['user_id'] = $id;
        header('Location: index.php?page=dashboard');
    }
    else {
        // Invalid credentials
        $error = true;
        $msg = "E-Mail/Passwort ungültig.";
    }
}


}

?>
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 style="margin-bottom:2px;" class="panel-title">Please Sign In</h3>
                        <?php if ($error == true) { msg_warning($msg);} ?>
                    </div>
                    <div class="panel-body">
                        <form action="index.php?page=login" method="post">
                            <fieldset>
                                <div class="form-group">
                                    <input class="form-control" placeholder="E-mail" name="email" type="email" autofocus>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Password" name="password" type="password" value="">
                                </div>
                                <button type="submit" class="btn btn-lg btn-success btn-block">Login</button>
                            </fieldset>
                        </form>
                    </div>
                </div>
                <div class="text-center">
                      <p></p>
                </div>
            </div>
        </div>
    </div>

<?php



//Footer
include 'footer.html';
?>
