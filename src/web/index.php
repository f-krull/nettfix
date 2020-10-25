<?php
session_start();
if(!$_SESSION["username"]) {
  header("Location:login.php");
}

function customPageHeader() { ?>
  <link rel="stylesheet" href="./css/tabulator.min.css">
  <link rel="stylesheet" href="./css/nf.css">
  <script type="text/javascript" src="./js/tabulator.min.js"></script>
<?php }

include_once('head.php');
include_once('header.php');
?>

<div class="nf-responsive-width my-5">
  <button id="btn-forms" class="btn btn-secondary btn-lg entry button btn-block mt-4">Show forms</button>
  <div class="row mt-4">
    <div class="col"><hr></div>
    <div class="col-md-auto">or</div>
    <div class="col"><hr></div>
  </div>
  <? if($message!=""): ?>
  <div class="alert alert-warning"><?php if($message!="") { echo $message; } ?></div>
  <? endif; ?>
  <div class="entry">
    <label for="form_id">Form ID:</label>
    <input class="form-control" type="number" id="form_id" name="form_id" autocomplete="form_id" required="" autofocus="">
    <label for="submission_id">Submission ID:</label>
    <input class="form-control" type="number" id="submission_id" name="submission_id" required="" autofocus="">
  </div>
  <button id="btn-submit" class="btn btn-secondary btn-lg entry button btn-block mt-4">Show Submission</button>
</div>

<script>
  document.querySelector("#btn-submit").onclick = function() { 
    const form_id = document.querySelector("#form_id").value;
    const submission_id = document.querySelector("#submission_id").value;
    window.open(`./fix.php?formId=${form_id}&submissionId=${submission_id}`,"_self");
  }
  document.querySelector("#btn-forms").onclick = function() { 
    window.open(`./forms.php`,"_self");
  }
</script>

<?php
include_once('footer.php');
include_once('tail.php');
?>
