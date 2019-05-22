
<?php
  require('../errorReporter.php');
  require('../db/memberCheck.php');

  $path = '../generations/';
  $pdfs = array();

  if (is_dir($path)) {
    $dh = opendir($path);
    $matches = array();

    while ($file = readdir($dh) and $file !== null) {
      if (preg_match('/^ (\d{4}) _ V(\d{2,}) N(\d{2,}) \. pdf$/ix', $file, $matches)) {
        array_shift($matches);
        list($year, $volume, $quarter) = $matches;

        $pdfs[] = json_encode(array(
          $year,
          preg_replace('/^0+/', '', $volume),
          preg_replace('/^0+/', '', $quarter),
          sprintf('<a target="_blank" href="%s">pdf</a>', "$path/$file")
        ));
      }
    }
  }
?>

<!DOCTYPE HTML>
<html lang="en-US">
  <head>
    <meta charset="utf-8">
    <title>MGS Member</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
    <link rel="stylesheet" href="/css/demo_table.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
    <script src="/DataTables-1.10.6/media/js/jquery.js"></script>
    <script src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>

    <script type="text/javascript">
      $(document).ready(function(){
        var calcDataTableHeight = function() {
          return $(window).height()*55/100;
        };

        var asInitVals = [];
        var tableData = [];

        <?php foreach ($pdfs as $pdf) : ?>
          tableData.push(JSON.parse('<?= addslashes($pdf) ?>').map(function(text){return '' + text}));
        <?php endforeach ?>

        var oTable = $('#generations').dataTable({
          'scrollY': calcDataTableHeight(),
          'scrollCollapse': true,
          'bFilter': true,
          'bProcessing': true,
          'bsortClasses': false,
	  'order': [[1, "desc"], [2, "desc"],[3, "desc"]],
          'sPaginationType': 'full_numbers',
          "aLengthMenu": [ 10, 25, 50, 100, 500 ],
          'bFilter': true,
          'bInput': true,
          'fnInitComplete': function() {
            $('.dataTables_scrollFoot').insertAfter($('.dataTables_scrollHead'));
          },
          'aaData': tableData,
          'aoColumns': [{'sTitle': 'Year'},
                        {'sTitle': 'Volume'},
                        {'sTitle': 'Quarter'},
                        {'sTitle': 'Link'}]});

        $(window).resize(function () {
          var oSettings = oTable.fnSettings();
          oSettings.oScroll.sY = calcDataTableHeight();
          oTable.fnDraw();
        });

        $("tfoot input").keyup(function () {
          /* Filter on the column (the index) of this element */
          oTable.fnFilter(this.value, $("tfoot input").index(this));
        });

        /*
         * Support functions to provide a little bit of 'user friendlyness' to the textboxes in
         * the footer
         */
        $("tfoot input").each(function (i) {
          asInitVals[i] = this.value;
        });

        $("tfoot input").focus(function() {
          if (this.className == "search_init") {
            this.className = "";
            this.value = "";
          }
        });

        $("tfoot input").blur(function(i) {
          if (this.value == "") {
            this.className = "search_init";
            this.value = asInitVals[$("tfoot input").index(this)];
          }
        });
      });
    </script>
  </head>
  <body>
    <div id="resultsbackground"></div>
    <div id="container" class="home">
      <?php require('header.php'); ?>
      <div id="searchresults">
	<h1>Generations Magazine</h2>
	<p>Most Recent Issues: 

	<?php
	$dir    = 'C:\inetpub\wwwroot\generations';
	$files = array_diff(scandir($dir,1), array('.', '..'));
	
for ($i=0; $i<6; $i++)
{
	if ($files[$i] != "index.php") {
		if ($files[$i] != "tmp") {	
		$filename = $files[$i]; 

	echo "<a target='_blank' href='/generations/$filename'>$filename</a> | ";
		}
	}
} 

	
	?>
	</p>

        <h2>Generations Magazines (Back Issues)</h2><p>
        NOTE: Fall 2016 </p>
        As part of the Joint Journal Project, with the Alberta and Saskatchewan Societies, highlighting the NWMP, we present their journals as volume 5 and 6 for the year 2016.  The contributed articles have been divided among the three Society’s journals so there is no duplicated content..</p>
        <table class="display dataTable" id="generations">
          <tfoot>
            <tr>
              <th><input type='text' value='Year' class='search_init' /></th>
              <th><input type='text' value='Volume' class='search_init' /></th>
              <th><input type='text' value='Quarter' class='search_init' /></th>
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </body>
</html>
