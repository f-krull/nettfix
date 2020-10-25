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

  <div class="container mt-5">
    <div class="row">
      <div class="col">
        <h2 id='title1'></h2>
        <span id='title2'></span>
      </div>
    </div>
    <div class="row vertical-align-bottom mt-3">
      <div class="col-md-2">
        <div id="btnPrev" class="btn btn-secondary btn-block mb-2 disabled">Prev</div>
      </div>
      <div class="col-md-2">
        <div id="btnNext" class="btn btn-secondary btn-block mb-2 disabled">Next</div>
      </div>
      <div class="col-8"></div>
    </div>

    <div class="row">
      <div class="col">
        <table id="submission-edit-table" class="mt-3"></table>
      </div>
    </div>
  </div>

<script>

  function getOnValueChangeCb(row_json) {
    return function(e) {
      const value_changed = e.target.innerText != row_json.answer_text;
      e.target.classList[value_changed ? "add" : "remove"]("nf-edit-changed")
    }
  }

  function setNavBtn(qsel, submission_id) {
    if (submission_id != null) {
      var e = document.querySelector(qsel);
      e.classList.remove("disabled");
      e.onclick = () => updateId(submission_id);
    }
  }

  async function doUpdate() {
    // reset data
    document.querySelector('#btnNext').classList.add("disabled");
    document.querySelector('#btnNext').onclick = null;
    document.querySelector('#btnPrev').classList.add("disabled");
    document.querySelector('#btnPrev').onclick = null;
    // read parameters
    const urlParams = new URLSearchParams(window.location.search);
    const paramFormId = urlParams.get('formId');
    const paramSubmissionId = urlParams.get('submissionId');
    document.title = `Nettfix ${paramSubmissionId} (${paramFormId})`;
    // get submission data
    const url = `./get_submission_json.php?formId=${paramFormId}&submissionId=${paramSubmissionId}`;
    const data_json = await (await fetch(url, {
      method: 'GET'
    })).json();
    console.log(data_json);
    document.title = `Nettfix ${paramSubmissionId} (${data_json.form_title})`
    document.querySelector("#title1").innerHTML = `Submission ${data_json.id} (${data_json.form_title})`;
    document.querySelector("#title2").innerHTML = `received on ${data_json.received_on}`;
    setNavBtn('#btnNext', data_json['id_next']);
    setNavBtn('#btnPrev', data_json['id_prev']);
    var etable = document.querySelector("#submission-edit-table");
    etable.innerHTML = '';
    data_json.data.forEach(e => {
      var etr = document.createElement("tr");
      etable.appendChild(etr);
      var etd1 = document.createElement("td");
      etd1.innerHTML = e.question_id;
      etr.appendChild(etd1);
      var etd2 = document.createElement("td");
      etd2.innerHTML = e.answer_text;
      etd2.contentEditable = true;
      etd2.classList.add("form-control");
      etd2.addEventListener('input', getOnValueChangeCb(e));
      etr.appendChild(etd2);
    });
  }

  async function updateId(submission_id) {
    var urlParams = new URLSearchParams(window.location.search);
    const paramFormId = urlParams.set("submissionId", submission_id);
    window.history.replaceState({}, '', `${location.pathname}?${urlParams}`);
    await doUpdate();
  }

  doUpdate();
  
</script>

<?php
include_once('footer.php');
include_once('tail.php');
?>
