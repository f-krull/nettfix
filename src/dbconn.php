<?php

function dbconnect() {
  $params = parse_ini_file(dirname(__FILE__) . '/../config.ini');
  if ($params === false) {
      throw new Exception("Error reading database configuration file" . PHP_EOL);
  }
  $conStr = sprintf("pgsql:host=%s;port=%d;dbname=%s;user=%s", 
    $params['dbhost'], 
    $params['dbport'], 
    $params['dbname'], 
    $params['dbuser']
  );
  $pdo = new PDO($conStr);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  return $pdo;
}

?>
