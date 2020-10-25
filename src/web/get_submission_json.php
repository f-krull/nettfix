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
  s as (
    select * from submissions where form_id in ( select id from f ) and id=:submission_id
  ),
  vs as ( select json_agg(json_build_object(
      'question_id', external_question_id
      ,'element_type', element_type
      ,'answer_text', answer_text
      ,'answer_json', answer_jsn
    )) from v_submissions vs where (vs.form_id , vs.submission_id) in ( select form_id , id from s) 
  )
  select json_build_object(
    'form_id', ( select id from f )
    ,'form_title', ( select title from f )
    ,'form_category', ( select category from f )
    ,'id', s.id
    ,'id_next', ( select distinct (s2.id) from submissions s2 where form_id = s.form_id and s2.id > s.id order by s2.id asc limit 1) 
    ,'id_prev', ( select distinct (s2.id) from submissions s2 where form_id = s.form_id and s2.id < s.id order by s2.id desc limit 1)
    ,'data', (select * from vs)
    ,'received_on', date_trunc('second', s.received_on)
  ) from s
  ";
  $stm = $db->prepare($sql);
  $stm->bindParam(":form_id",       $form_id,       PDO::PARAM_INT);
  $stm->bindParam(":submission_id", $submission_id, PDO::PARAM_INT);
  $stm->execute();
  foreach ($stm->fetchAll() as $row) {
    print $row[0] . "\n";
  }
} catch (PDOException $e) {
  echo $e->getMessage() . PHP_EOL;
}
$db = null;
?>