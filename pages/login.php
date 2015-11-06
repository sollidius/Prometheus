<?php
//header
$title = "Login";
include 'header.php';
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

if (strlen($_POST['email']) > 3 and strlen($_POST['password']) > 3) {

  $password = $_POST['password'];

  $stmt = $mysqli->prepare("SELECT password,id,name FROM users WHERE email = ?");
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

    }
}



}

?>
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Please Sign In</h3>
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
                                <div class="checkbox">
                                    <label>
                                        <input name="remember" type="checkbox" value="Remember Me">Remember Me
                                    </label>
                                </div>
                                <!-- Change this to a button or input when using this as a form -->
                                <button type="submit" class="btn btn-lg btn-success btn-block">Login</button>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php





//Footer
include 'footer.html';
?>
