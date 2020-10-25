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
  async function doStuff() {
    var table = new Tabulator("#table", {
      columns: [
        {field: "form_id", title: "Form ID", align:"center" , minWidth:100},
        {field: "title", title: "Title"},
        {field: "category", title: "Category"},
        {field: "num_patches", title: "Num. patches"},
        {field: "updated", title: "Last submission"},
        {field: "last_patch_ts", title: "Last patch on"},
      ],
      ajaxURL: `get_forms_json.php`,
      layout:"fitDataStretch",
      height: "100%",
      rowClick:function(e, row) {
        window.open(`./submissions.php?formId=${row.getData().form_id}`,"_self");
      },
    });
  }
  doStuff();
</script>

<?php
include_once('footer.php');
include_once('tail.php');
?>
