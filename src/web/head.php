<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <link rel="shortcut icon" href="img/favicon.ico">
  <link rel="stylesheet" href="./css/bootstrap.min.css">
  <link rel="stylesheet" href="./css/nf.css">
  <title><?= isset($PageTitle) ? $PageTitle : "Nettfix"?></title>
  <?php if (function_exists('customPageHeader')){
    customPageHeader();
  }?>
</head>
<body>
