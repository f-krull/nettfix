<?php
  require 'dbconn.php';

  $basedir = realpath(dirname(__FILE__) . '/../');

  $sql = file_get_contents($basedir . '/dbschema.sql');

  try {
    $db = dbconnect();
    $db->exec($sql);
  } catch (PDOException $e) {
      echo $e->getMessage() . PHP_EOL;
  }
?>
