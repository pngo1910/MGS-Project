<?php
    require('../../db/adminCheck.php');
    require('../../db/mgsConnection.php');
    require('../../retrieveColumns.php');
    require('../../errorReporter.php');
    error_reporting(0);
?>
<!DOCTYPE HTML>
<html> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <?php header('X-UA-Compatible: IE=edge,chrome=1');?>
        <title> Manitoba Genealogical Society</title>
        <link rel="stylesheet" href="/css/normalize.css">
        <link rel="stylesheet" href="/css/main.css">
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">
       <link rel="stylesheet" href="/css/demo_table.css">

    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/extensions/ColReorder/css/dataTables.colReorder.css">
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/extensions/ColVis/css/dataTables.colVis.css">
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/extensions/TableTools/css/dataTables.tableTools.css">

    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/media/js/jquery.js"></script>
    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/extensions/ColReorder/js/dataTables.colReorder.js"></script>
    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/extensions/ColVis/js/dataTables.colVis.js"></script>
    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/extensions/TableTools/js/dataTables.tableTools.js"></script>
        <?php
            if(isset($_GET['tables'])){
                $_SESSION['table'] = $_GET['tables'];
                $and = "AND COLUMN_NAME NOT IN ('CemLink', 'StatusCode')";
                $aCol = retrieveColumns($_SESSION['table'], $and, $conn);
                $aCol[] = "Add";
            }
        ?>
        <script type="text/javascript">
            <?php if(isset($_GET['tables'])): ?>
                var asInitVals = new Array();

                var j_cols = new Array();
                var hash_cols = {};
                <?php foreach ($aCol as $key => $value) : ?>;
                    hash_cols = {'sTitle' : '<?php echo ($value); ?>'};
                    j_cols.push(hash_cols);
                <?php endforeach; ?>

                $(document).ready(function() {
                    window.alert = function(){return null;};
                    var calcDataTableHeight = function() {
                      return $(window).height()*55/100;
                    };

                    var oTable = $('#example').dataTable( {
                        "scrollY": calcDataTableHeight(),
                        "scrollCollapse": true,
                        "scrollX": true,
                        "bProcessing": true,
                        "bPaginate": true,
                        "bServerSide": true,
                        "bsortClasses": false,
                        "sPaginationType": 'full_numbers',
                        "aLengthMenu": [ 10, 25, 50, 100, 500 ],
                        "bFilter": true,
                        "bInput" : true,
                        "aoColumns": j_cols,
                        "sAjaxSource": "addpayperviewQuery.php",
                        "oLanguage": { "sSearch": "Search all columns:" },
                        "fnInitComplete": function() {
                          $('.dataTables_scrollFoot').insertAfter($('.dataTables_scrollHead'));
                        }
                    } );

                    $("tfoot input").keyup( function () {
                        /* Filter on the column (the index) of this element */
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
            <?php endif; ?>
        </script>
        <!-- css for tfoot inputs to show as thead -->
        <style>
            tfoot{
                display: table-header-group;
            }
        </style>
    </head>
    <body>
        <div class = 'adminTables'>
           <div id="resultsbackground">
                <div id="container" class="home">
                    <div id="searchresults">
                        <?php require 'header.php'; ?>
                    </div>
                    <p>Choose a table to associate a file with:</p>
                    <?php $and = "AND TABLE_NAME NOT IN ('Books', 'Cemeteries', 'Countries', 'Municipalities', 'Newspapers', 'Provinces', 'TypeCodes', 'PayPerView', 'Purchases', 'Products', 'Category')"; ?>
                    <form action="addPayPerViewForm.php?tables=<?= $_POST['tables'] ?>">
                        <select name="tables">
                        <?php foreach($tables = retrieveTableNames($conn, $and) as $table): ?>
                            <option value="<?= $table ?>"><?= $table ?></option>
                        <?php endforeach; ?>
                        </select>
                        <input type="submit" name="submit" id="submit" value="Submit" />
                    </form>
                </div>
                <div id="content">
                <h2><?= isset($_GET['tables']) ? "Table: " . $_GET['tables'] : "" ?></h2>
                <table id="example" cellpadding="0" cellspacing="0" border="0" class="display">
                    <thead>
                    </thead>
                    <tfoot>
                        <tr>
                            <?php
                                if(isset($_GET['tables'])){
                                    foreach($aCol as $col_data){
                                        if($col_data != 'Add')
                                            echo "<th><input type='text' name='search_" . $col_data . "' value='" . $col_data . "' class='search_init' /></th>";
                                    }
                                }
                            ?>
                        </tr>
                    </tfoot>
                    <tbody>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </body>
</html>