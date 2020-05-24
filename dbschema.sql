CREATE OR REPLACE FUNCTION nf_get_questionid(_form_id integer, _external_question_id text)
RETURNS text AS $$
DECLARE
  _question_id text;
BEGIN
  SELECT question_id
  FROM v_form_spec vsf
  WHERE vsf.form_id = _form_id AND vsf.external_question_id = _external_question_id 
  INTO _question_id;
  RETURN _question_id;
END
$$ 
LANGUAGE plpgsql;

--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION nfHasExternalAnswerOptionId(p_form_id integer, p_submission_id int, p_question_id text)
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
  values_present  jsonb := answerids;
  values_expected jsonb := jsonb_array_elements(patch->'value_from');
  is_same bool;
begin 
  with values_diff as (
    (select * from values_present except (select * from values_expected))
    union all 
    (select * from values_expected except (select * from values_present))
  )
  select count(*) = 0 from values_diff into is_same;
  return is_same;
end;
$$ 
LANGUAGE plpgsql;

--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION nf_getupdated_answerids_json(form_id int, submission_id int, question_id text, value_to jsonb)
RETURNS jsonb as $$
declare 
  in_form_id int := form_id;
  in_submission_id int := submission_id;
  updated_form_json jsonb;
begin 
  -- get unpacked answer-option rows
  with aopts as (
    select jsonb_array_elements(jsonb_extract_path(form_data, 'answersAsMap','1904646','answerOptions')) as arr
      from submissions s where s.form_id = in_form_id and id = in_submission_id
  )
  select jsonb_build_array(aopts.arr) from aopts
  into updated_form_json;
  select value_to  into  updated_form_json;
  -- patch data
  -- pack array 
  -- replace in whole json
  return updated_form_json;
end;
$$ 
LANGUAGE plpgsql;




-- set answer

--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION nfApplyPatchUpdateText(form_id int, submission_id int, patch jsonb)
RETURNS VOID as $$
DECLARE 
  value_from  text := patch->>'value_from';
  value_to    text := patch->>'value_to';
  question_id text := nfGetQuestionId(form_id, patch->>'column_id');
begin
end
$$ 
LANGUAGE plpgsql;
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION nfApplyPatchUpdate(form_id int, submission_id int, patch jsonb)
RETURNS VOID as $$
DECLARE 
  question_id text := nfGetQuestionId(form_id, patch->>'column_id');
begin
  -- check question id
  if question_id is null THEN
    RAISE EXCEPTION 'nfGetQuestionId(%,"%") returned NULL',form_id, patch->>'column_id'
      USING HINT = 'Check spelling of the external question ID (column_id)';
  END IF;
  -- check question type
  if (nfHasExternalAnswerOptionId(form_id, submission_id, question_id)) then
    RAISE exception 'unsupported column type (column %)', patch->>'column_id';
  else
  	RAISE exception 'supported column type (column %)', patch->>'column_id';
  end if;
  
end
$$ 
LANGUAGE plpgsql;

--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION nfApplyPatch(form_id int, submission_id int, patch jsonb)
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
	perform nfApplyPatchUpdate(in_form_id, in_submission_id, patch);
  else
    RAISE EXCEPTION 'unexpected patch type (%)', patch_type USING HINT = 'Revise patch and specify the correct patch type';
  end case;
end
$$ 
LANGUAGE plpgsql;

--------------------------------------------------------------------------------
--
--select nfApplyPatch(123456, 6040698, '{
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

select * from nf_getupdated_answerids_json(123456, 6053582, nf_get_questionid(123456, 'checkboxquestion'), '[ "asd", "dsff"]');
