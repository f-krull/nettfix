<?php
include 'basedir.php';
$fn_custom_login = get_customdir() . '/login.php';
if(file_exists($fn_custom_login)) {
  include $fn_custom_login;
  exit(0);
}


session_start();
$message="";
if(count($_POST)>0) {
  $user_name = $_POST["user_name"];
  // check if user exists on system
  if(shell_exec("getent passwd | grep ^$user_name:") != "") {
    $_SESSION["id"] = $user_name;
    $_SESSION["username"] = $user_name;
  } else {
    $message = "Invalid Username!";
  }
}
if(isset($_SESSION["id"])) {
header("Location:index.php");
}


include_once('head.php');
include_once('header.php');
?>

<div class="container text-center vh-100">
  <form name="frmUser" method="post" action="" align="center" class="nf-responsive-width my-5">
    <? if($message!=""): ?>
    <div class="alert alert-warning"><?php if($message!="") { echo $message; } ?></div>
    <? endif; ?>
    <h3 align="center">Please login</h3>
    <div class="entry">
      <label for="username">Username:</label>
      <input class="form-control" type="text" id="username" name="user_name" autocomplete="username" required="" autofocus="">
    </div>
    <input type="submit" name="submit" value="Submit" class="btn btn-primary btn-lg entry button btn-block mt-4">
  </form>
</div>

<?php
include_once('footer.php');
include_once('tail.php');
?>