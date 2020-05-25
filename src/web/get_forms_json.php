<?php
session_start();
if(!$_SESSION["username"]) {
  exit(1);
}

header('Content-Type: application/json');  

require '../dbconn.php';
try {
  $db = dbconnect();

  $sql = "SELECT json_agg(v_nf_status) from v_nf_status";

  
  foreach ($db->query($sql) as $row) {
    print $row[0] . "\n";
  }
} catch (PDOException $e) {
  echo $e->getMessage() . PHP_EOL;
}
$db = null;
?>