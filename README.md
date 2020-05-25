# Nettfix

Tool to update or delete submissions in Nettquik.

  * version control
  * user access control
  * relies in DB access to Nettquik with write permissions

## TODO:

  * use patch id in json  - check if it's next in line when applying
  * get_patch_template(form_id, submission_id, type)
  * check if patch update answer option length match those in DB

Workflow 
  * start by external trigger
  * create tables if not exists 
    * patch_state: form_id(forms with patch data), last_timestamp
    * v_patch_state: form_id(all forms), last_timestamp, num_patches, (num removed?)
    * patch_data: form_id, submission_id, patch_json, date, comment
  * get all form info (form_id and last patch timestamp)
  * check for new patch files (newer than last timestamp)
  * if found, add file to db and and apply
    * apply patch (dryrun)
    * add patch (fails if already present)
    * apply patch
    * return ok

