CREATE OR REPLACE FUNCTION nf_get_questionid(form_id integer, external_question_id text)
RETURNS text AS $$
DECLARE
  in_form_id int := form_id;
  in_external_question_id text := external_question_id;
  out_question_id text;
BEGIN
  SELECT question_id
  FROM v_form_spec vsf
  WHERE vsf.form_id = in_form_id AND vsf.external_question_id = in_external_question_id 
  INTO out_question_id;
  RETURN out_question_id;
END
$$ 
LANGUAGE plpgsql;

--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION nf_has_externalansweroptionid(p_form_id int, p_submission_id int, p_question_id text)
RETURNS bool as $$
declare has_ext bool := false;
begin
  select jsonb_extract_path(form_data, 'answersAsMap', p_question_id, 'answerOptions','0','externalAnswerOptionId') is not null 
    from submissions s where s.form_id = p_form_id and id = p_submission_id 
    into has_ext;
  RETURN has_ext;
END;
$$ LANGUAGE plpgsql;

--------------------------------------------------------------------------------
-- get answers as from elements withexternalAnswerOptionId like 'RADIO','CHECKBOX','SELECT','MATRIX_RADIO','MATRIX_CHECKBOX', ... 
CREATE OR REPLACE FUNCTION nf_get_answerids_jsonrows(form_id int, submission_id int, question_id text)
RETURNS setof jsonb as $$
declare 
  in_form_id int := form_id;
  in_submission_id int := submission_id;
begin 
  return query
  select jsonb_extract_path(
      jsonb_array_elements(
        jsonb_extract_path(form_data, 'answersAsMap',question_id,'answerOptions')
      ),'externalAnswerOptionId') 
    from submissions s where s.form_id = in_form_id and id = in_submission_id;
end;
$$ 
LANGUAGE plpgsql;

--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION nf_isequal_answerids(answerids jsonb, patch jsonb)
RETURNS bool as $$
declare 
  is_same bool;
begin
  with 
    values_present  as ( select value from jsonb_array_elements(answerids)),
    values_expected as ( select value from jsonb_array_elements(patch->'value_from')),
    values_diff as (
      (select value from values_present except (select value from values_expected))
      union all 
      (select value from values_expected except (select value from values_present))
  )
  select count(*) = 0 from values_diff into is_same;
  return is_same;
end;
$$ 
LANGUAGE plpgsql;

--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION nf_get_updated_form_json_answerids(form_id int, submission_id int, question_id text, value_to jsonb)
RETURNS jsonb as $$
declare 
  in_form_id int := form_id;
  in_submission_id int := submission_id;
  updated_form_json jsonb;
begin 
  -- get unpacked answer-option rows
  with aopts as (
    select jsonb_extract_path(form_data, 'answersAsMap',question_id,'answerOptions') as c
      from submissions s where s.form_id = in_form_id and id = in_submission_id
  ),
  rep as (select value_to as c),
  -- add index
  aopts_idx as (select value, ordinality from aopts, jsonb_array_elements(aopts.c) with ordinality),
  value_to_idx as (select value, ordinality from rep, jsonb_array_elements(rep.c) with ordinality),
  -- patch answer options data
  aopts_patched as (select to_json(array_agg(jsonb_set(aopts_idx.value, '{externalAnswerOptionId}', value_to_idx.value, false))) as c from aopts_idx inner join value_to_idx on aopts_idx.ordinality = value_to_idx.ordinality)
  -- pack rows array 
  select aopts_patched.c from aopts_patched
    into updated_form_json;
  -- replace in whole json
  select jsonb_set(form_data, array['answersAsMap',question_id,'answerOptions'], updated_form_json, false)
    from submissions s where s.form_id = in_form_id and id = in_submission_id
    into updated_form_json;
  return updated_form_json;
end;
$$ 
LANGUAGE plpgsql;

--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION nf_apply_update_answerids(form_id int, submission_id int, question_id text, patch jsonb)
RETURNS VOID as $$
DECLARE 
  in_form_id int := form_id;
  in_submission_id int := submission_id;
  question_id text := nf_get_questionid(form_id, patch->>'column_id');
  answerids jsonb;
  form_json jsonb;
begin
  select to_jsonb(array_agg(arows.c)) from ( select nf_get_answerids_jsonrows(in_form_id, in_submission_id, question_id) as c) as arows into answerids;
  -- check if old answerids match 
  if not nf_isequal_answerids(answerids, patch->'value_from') then
    RAISE exception 'old externalAnswerOptionId ("%") do not match values in patch ("%")', answerids, patch->'value_from'  USING HINT = 'Revise patch and specify correct value_from';
  end if;
  select nf_get_updated_form_json_answerids(in_form_id, in_submission_id, question_id, patch->'value_to') into form_json;
  update submissions s set form_data = form_json where s.form_id = in_form_id and s.id  = in_submission_id;
end
$$ 
LANGUAGE plpgsql;

--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION nfApplyPatchUpdateText(form_id int, submission_id int, patch jsonb)
RETURNS VOID as $$
DECLARE 
  value_from  text := patch->>'value_from';
  value_to    text := patch->>'value_to';
  question_id text := nf_get_questionid(form_id, patch->>'column_id');
begin
end
$$ 
LANGUAGE plpgsql;
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION nf_apply_update(form_id int, submission_id int, patch jsonb)
RETURNS VOID as $$
DECLARE 
  question_id text := nf_get_questionid(form_id, patch->>'column_id');
begin
  -- check question id
  if question_id is null THEN
    RAISE EXCEPTION 'nf_get_questionid(%,"%") returned NULL',form_id, patch->>'column_id'
      USING HINT = 'Check spelling of the external question ID (column_id)';
  END IF;
  -- check question type
  if (nf_has_externalansweroptionid(form_id, submission_id, question_id)) then
    perform nf_apply_update_answerids(form_id, submission_id, question_id, patch);
  else
  	RAISE exception 'supported column type (column %)', patch->>'column_id';
  end if;
  
end
$$ 
LANGUAGE plpgsql;

--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION nf_apply_patch(form_id int, submission_id int, patch jsonb)
RETURNS VOID as $$
DECLARE 
  in_form_id int := form_id;
  in_submission_id int := submission_id;
  patch_type text := patch->>'type';
  has_submission bool := (select count(*) > 0 from submissions s2 where s2.form_id = in_form_id and s2.id = in_submission_id);
begin
  if not has_submission THEN
    RAISE EXCEPTION 'form_id or (form_id, submission_id) not found' USING HINT = 'Revise patch and specify correct form ID and submission ID';
  END IF;
  case 
  when patch_type = 'update' then 
	  perform nf_apply_update(in_form_id, in_submission_id, patch);
  else
    RAISE EXCEPTION 'unexpected patch type (%)', patch_type USING HINT = 'Revise patch and specify the correct patch type';
  end case;
end
$$ 
LANGUAGE plpgsql;

--------------------------------------------------------------------------------
--
--select nf_apply_patch(123456, 6040698, '{
--  "type": "update",
--  "column_id": "textquestion",
--  "value_from": "This is a text answer",
--  "value_to": "This is a patched text answer"
--}');



--select nfHasExternalAnswerOptionId(123456, 6040698, '1904644');
--select nfHasExternalAnswerOptionId(123456, 6040698, '1904646');

-- select form_data #> '{answersAsMap,1904646,answerOptions,0,externalAnswerOptionId}' is not null from submissions s where s.form_id = 123456 and id = 6040698;
-- select jsonb_extract_path(jsonb_array_elements(jsonb_extract_path(form_data, 'answersAsMap','1904646','answerOptions')),'externalAnswerOptionId') from submissions s where s.form_id = 123456 and id = 6040698;

-- select * from nf_get_answerids_json(123456, 6053582, nf_get_questionid(123456, 'checkboxquestion'));

-- select form_data #> '{answersAsMap,1904646,answerOptions}' from submissions s where s.form_id = 123456 and id = 6040698;

select * from nf_get_updated_form_json_answerids(123456, 6053582, nf_get_questionid(123456, 'checkboxquestion'), '[ "asd", "dsff"]');
