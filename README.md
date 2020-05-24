# Nettfix


## TODO:

  * use patch id n json  - check if it's next in line when applying
  * get_patch_template(form_id, submission_id, type)

Workflow 
  * start by external trigger
  * create tables if not exists 
    * patch_state: form_id(forms with patch data), last_timestamp
    * v_patch_state: form_id(all forms), last_timestamp, num_patches, (num removed?)
    * patch_data: form_id, submission_id, patch_json, date, comment
  * get all form info (form_id and last patch timestamp)
  * check for new patch files (newer than last timestamp)
  * if found, add file to db and and apply

