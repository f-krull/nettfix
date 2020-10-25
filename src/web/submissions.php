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

<div class="container mt-4">
  <div id="table" class="nf-frame mb-4"></div>
</div>

<script>

  var table;
  const paramFormId = 123456;

  function onTableBuilt() {
    // reorder array after loading data; place submission_id and received_on first
    const sortedArr = table.getColumnDefinitions().reduce((acc, element) => {
    if (element.field == "submission_id") {
      return [element, ...acc];
    }
    if (element.field == "received_on") {
      return [element, ...acc];
    }
    return [...acc, element];
  }, []);
    table.setColumns(sortedArr);
  }


  async function doStuff() {
    table = new Tabulator("#table", {
      autoColumns: true,
      dataLoaded: onTableBuilt,
      pagination: "remote",
      ajaxURL: "./get_submissions_page_json.php", 
      ajaxParams: {formId: paramFormId},
      paginationSize: 16, 
      layout:"fitDataStretch",
      height: "100%",
      rowClick:function(e, row) {
        window.open(`./fix.php?formId=${paramFormId}&submissionId=${row.getData().submission_id}`,"_self");
      },
    });

    
  }
  doStuff();
</script>

<?php
include_once('footer.php');
include_once('tail.php');
?>
