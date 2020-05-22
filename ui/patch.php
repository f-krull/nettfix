<?php declare(strict_types=1);

$servername = "localhost";
$username = "dbuser";
$dbname = "nettquik";
$dbport = "3732";

$patchdir = dirname(__FILE__) . "/../data/";

/*----------------------------------------------------------------------------*/

function apply_patch_file(string $patch_fn) {
  echo "$patch_fn: ";
  $f = fopen($patch_fn, "r") or die("Unable to open file!");
  $patch = json_decode(fread($f,filesize($patch_fn)));
  fclose($f);

  echo sprintf("form_id=%d submission_id=%d\n", $patch->form_id, $patch->submission_id);

  // Create connection
  global $servername;
  global $dbport;
  global $dbname;
  global $username;
  $conn = new PDO(sprintf("pgsql:host=%s;port=%d;dbname=%s;user=%s"
      ,$servername
      ,$dbport
      ,$dbname
      ,$username
    )
  );
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  if ($patch->op->type != "update") {
    die(sprintf("Error unknown patch type %s", $patch->op->type));
  }

  $sql = "SELECT update_form_data(:form_id, :submission_id, :external_question_id, :value_from, :value_to);";
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    echo "Error updating record:\n";
    print_r($stmt->errorInfo());
    die(sprintf("Error updating record form_id=%d submission_id=%d", $patch->form_id, $patch->submission_id));
  }
  $stmt->bindParam(':form_id',                $patch->form_id,        PDO::PARAM_INT);
  $stmt->bindParam(':submission_id',          $patch->submission_id,  PDO::PARAM_STR);
  $stmt->bindParam(':external_question_id',   $patch->op->column_id,  PDO::PARAM_STR);
  $stmt->bindParam(':value_from', json_encode($patch->op->value_from), PDO::PARAM_STR);
  $stmt->bindParam(':value_to',   json_encode($patch->op->value_to),   PDO::PARAM_STR);
  if ( $stmt->execute()) {
    "Record updated successfully\n";
  } else {
    echo "Error updating record:\n";
    print_r($stmt->errorInfo());
    die(sprintf("Error updating record form_id=%d submission_id=%d", $patch->form_id, $patch->submission_id));
  }

  $conn = null;
}

/*----------------------------------------------------------------------------*/

foreach (glob($patchdir . "/*/*.json") as $filename) {
  apply_patch_file($filename);
}

echo "\nok\n"

?>
