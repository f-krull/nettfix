<nav class="navbar navbar-expand-sm">
  <a class="navbar-brand" href="./index.php"><img src="img/nettfix_x40.png" /></a>
  <ul class="navbar-nav ml-auto">
    <? if($_SESSION["username"]): ?>
    <li><a href="./logout.php">Logout (<?= $_SESSION["username"] ?>)</a></li>
    <? else: ?>
    <li><a href="./login.php">Login</a></li>
    <? endif; ?>
  </ul>
</nav>