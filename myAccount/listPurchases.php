<?php
    header('X-UA-Compatible: IE=edge,chrome=1');
 
    require('../db/loginCheck.php');
    require('../db/memberConnection.php');
    require('../db/mgsConnection.php');
    require('../retrieveColumns.php');
   
    $query = "";
    $title = "";
    if(isset($_POST) && !empty($_POST)){
        $fileName = "";
        $pageNum = "";
        foreach($_POST as $key => $value){
            switch($key){
                case "purchases":
                    $query = "listPurchasesQuery.php";
                    $title = "Pay-Per-View Purchases";
                    break;
                case "renewals":
                    $query = "listRenewalsQuery.php";
                    $title = "Account Purchases & Renewals";
                    break;
                case "store":
                    $query = "listStorePurchasesQuery.php";
                    $title = "Store Purchases";
                    break;
                default:
                    $values = explode(",", $value);
                    $fileName = $values[0];
                    if(isset($values[1]))
                        $pageNum = $values[1];
            }
        }

        if($fileName != ""){
            preg_match('/^\D*(?=\d)/', $fileName, $m);
            $folder = $m[0];

            //Get the type description that matches the type code
            $qry = "SELECT TypeDescr FROM TypeCodes WHERE TypeID = ?";
            $stmt = sqlsrv_query($conn, $qry, array($folder));
            if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
            $folder = sqlsrv_fetch_array($stmt)['TypeDescr'];
                
            //Find if there's a forward slash in the folder name
            if (strpos($folder, '/') !== FALSE){
                //Replace the forward slash with an underscore
                $folder = preg_replace('/\//', "_", $folder);
                //Find if there's whitespace in the type description
                if(strpos($folder, ' ') !== FALSE){
                    //Capitalize the words in the description
                    $folder = ucwords($folder);
                }
            } else{
                //Capitalize the words in the description
                $folder = ucwords($folder);
            }
            //Replace any whitespace with nothing
            $folder = preg_replace('/\s*/', "", $folder);
            //Add a forward slash to the end of the name
            $folder .= "/";
        }
        
        if($pageNum == "" && $fileName != "")
            header("location:../../PDF/wholeDocument.php?fileName=$fileName&path=$folder");
        elseif($pageNum != "" && $fileName !="")
            header("location:../../PDF/singlePage.php?fileName=$fileName&pageNum=$pageNum&path=$folder");

        $_POST = array();
    } else{
        $title = "Pay-Per-View Purchases";
        $query = "listPurchasesQuery.php";
    }

    if($query === "listPurchasesQuery.php"){
        $and = "AND COLUMN_NAME NOT IN('BookCode', 'PageNumbers', 'EventYear', 'Info', 'TypeCode', 'StatusCode') OR TABLE_NAME = 'Purchases' AND COLUMN_NAME IN('FileName', 'PageNum')";
        $aCol = retrieveColumns('BookRecords', $and, $conn);
        $aCol[] = "View";
    } else{
        $and = "AND COLUMN_NAME NOT IN('ID')";
        $aCol = retrieveColumns('Transactions', $and, $userConn);
    }
?>
<!DOCTYPE HTML>
<html lang="en-US">
	<head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>MGS Member</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="/css/normalize.css">
        <link rel="stylesheet" href="/css/main.css">
        <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
        <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables_themeroller.css">

        <script src="/DataTables-1.10.6/media/js/jquery.js"></script>
        <script src="/DataTables-1.10.6/media/js/jquery.dataTables.min.js"></script>
        <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
        <script src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
        <script type="text/javascript">

        var asInitVals = new Array();

        var j_cols = new Array();
        <?php foreach ($aCol as $key => $value) : ?>
          j_cols.push({'sTitle' : '<?= $value ?>'});          
            <?php endforeach; ?>

            $(document).ready(function() {
                window.alert = function(){return null;};

                var oTable = $('#example').dataTable( {
                    "bProcessing": true,
                    "bPaginate": true, 
                    "bServerSide": true,                 
                    "bsortClasses": false,              
                    "sPaginationType": 'full_numbers',
                    "aLengthMenu": [ 10, 25, 50, 100, 500 ],
                    "bFilter": true,
                    "bInput" : true,
                    "aoColumns": j_cols,
                    "sAjaxSource": "<?= $query ?>",  
                    "oLanguage": {
                        "sSearch": "Search all columns:"
                    }
                    
                } );
             
                $("tfoot input").keyup( function () {
                     //Filter on the column (the index) of this element 
                    oTable.fnFilter( this.value, $("tfoot input").index(this) );
                } );

                /*
                 * Support functions to provide a little bit of 'user friendlyness' to the textboxes in 
                 * the footer
                 */
                $("tfoot input").each( function (i) {
                    asInitVals[i] = this.value;
                } );
                
                $("tfoot input").focus( function () {
                    if ( this.className == "search_init" )
                    {
                        this.className = "";
                        this.value = "";
                    }
                } );
                
                $("tfoot input").blur( function (i) {
                    if ( this.value == "" )
                    {
                        this.className = "search_init";
                        this.value = asInitVals[$("tfoot input").index(this)];
                    }
                } );
            } );

            function view(file, page){
                var theForm, newInput1;
                theForm = document.createElement('form');
                theForm.action = 'listPurchases.php';
                theForm.method = 'post';
                newInput1 = document.createElement('input');
                newInput1.type = 'hidden';
                newInput1.name = 'input_1';
                if(page != null)
                    newInput1.value = file + "," + page;
                else
                    newInput1.value = file;
                theForm.appendChild(newInput1);
                document.getElementById('hidden_form_container').appendChild(theForm);
                theForm.submit();
            }
        </script>
      <style>
      tfoot{
        display: table-header-group;
      }
      </style>
    </head>
	<body>
		<div id="resultsbackground">
			<div id="container" class="home">
				<div id="searchresults">
                    <?php require('../header.php'); ?>
				</div>
			
        <div id="content">
            <h2><?= $title ?></h2>
            <br />
            <p><?= $_SESSION['error'] ?></p>
            <?php $_SESSION['error'] = ""; ?>
            <div id="hidden_form_container" style="display:none;"></div>
            <form method="POST" action="listPurchases.php">
                <p>
                    <input type="submit" name="purchases" value="View PPV Purchases" />
                    <input type="submit" name="renewals" value="View Purchases/Renewals" />
                    <input type="submit" name="store" value="View Store Purchases" />
                </p>
            </form>
            <div>
            <table class="display" id="example">
                <thead>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                      <?php
                          foreach ($aCol as $col){
                            if($col != "View")
                              echo "<th><input type='text' name='search_$col' value='$col' class='search_init' /></th>";
                          }                          
                      ?>
                      </tr>
                  </tfoot>
            </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>