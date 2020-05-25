<?php declare(strict_types=1);

require 'dbconn.php';

$patchdir = dirname(__FILE__) . "/../data/";

/*----------------------------------------------------------------------------*/

function apply_patch_file(string $patch_fn) {
  echo "$patch_fn: ";
  $f = fopen($patch_fn, "r") or die("Unable to open file!");
  $patch = json_decode(fread($f,filesize($patch_fn)));
  fclose($f);

  echo sprintf("form_id=%d submission_id=%d", $patch->form_id, $patch->submission_id) . PHP_EOL;

  try {
    $db = dbconnect();

    $sql = "SELECT nf_apply_operation(:form_id, :submission_id, :patch_jsn);";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
      echo "Error updating record:\n";
      print_r($stmt->errorInfo());
      die(sprintf("Error updating record form_id=%d submission_id=%d", $patch->form_id, $patch->submission_id));
    }
    $stmt->bindParam(':form_id',       $patch->form_id,       PDO::PARAM_INT);
    $stmt->bindParam(':submission_id', $patch->submission_id, PDO::PARAM_STR);
    $stmt->bindParam(':patch_jsn', json_encode($patch->action), PDO::PARAM_STR);
    if ( $stmt->execute()) {
      "Record updated successfully" . PHP_EOL;
    } else {
      echo "Error updating record:" . PHP_EOL;
      print_r($stmt->errorInfo());
      die(sprintf("Error updating record form_id=%d submission_id=%d", $patch->form_id, $patch->submission_id));
    }
  } catch (PDOException $e) {
    echo $e->getMessage() . PHP_EOL;
  }

  $db = null;
}

/*----------------------------------------------------------------------------*/

foreach (glob($patchdir . "/*/*.json") as $filename) {
  apply_patch_file($filename);
}

echo "\nok\n"

?>
