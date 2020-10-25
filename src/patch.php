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

    $sql = "SELECT nf_apply_patch(:patch_jsn, :date, false);";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
      echo "Error updating record:\n";
      die(sprintf("Error updating record form_id=%d submission_id=%d", $patch->form_id, $patch->submission_id));
    }
    $stmt->bindParam(':patch_jsn', json_encode($patch), PDO::PARAM_STR);
    $stmt->bindParam(':date', date(DATE_RFC3339), PDO::PARAM_STR);
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
