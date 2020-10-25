<?php
session_start();
if(!$_SESSION["username"]) {
  exit(1);
}

// read params
$submission_id = $_GET["submissionId"];
$form_id = $_GET["formId"];

header('Content-Type: application/json');  

require '../dbconn.php';
try {
  $db = dbconnect();

  $sql = "
  with 
  f as (
    select * from forms where id=:form_id
  ), 
  jsndata as (
    select jsonb_insert(
      jsonb_insert(
        jsonb_object(array_agg(vs.external_question_id), array_agg(vs.answer_text ))
        ,'{submission_id}', to_jsonb(submission_id)
      )
      ,'{received_on}', to_jsonb(vs.received_on)
    ) j
    from v_submissions vs
    where vs.form_id in (select id from f)
    group by submission_id, vs.received_on
  )
  select jsonb_build_object(
    'data', to_json(array_agg(j))
    ,'last_page', 1
  )
  from jsndata
  ";
  $stm = $db->prepare($sql);
  $stm->bindParam(":form_id", $form_id, PDO::PARAM_INT);
  $stm->execute();
  foreach ($stm->fetchAll() as $row) {
    print $row[0] . "\n";
  }
} catch (PDOException $e) {
  echo $e->getMessage() . PHP_EOL;
}
$db = null;
?>