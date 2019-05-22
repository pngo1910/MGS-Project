<?php
    require('../../db/mgsConnection.php');
    require('../../db/memberCheck.php');
    require('../../retrieveColumns.php');
    require('../../errorReporter.php');
    require('memberQuery.php');
?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <title> Manitoba Genealogical Society</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables_themeroller.css">

    <script src="/DataTables-1.10.6/media/js/jquery.js"></script>
    <script src="/DataTables-1.10.6/media/js/jquery.dataTables.min.js"></script>
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
    <script src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
    <script src="/DataTables-1.10.6/extensions/ColumnFilter/jquery.dataTables.columnFilter.js"></script>
    <?php require('memberJS.php'); ?>
    <style>
        tfoot {
            display: table-header-group;
        }
    </style>
</head>
<body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

        <!-- Add your site or application content here -->
        <div id="resultsbackground">
            <div id="container" class="home">
                <div id="searchresults">
                    <?php require('header.php'); ?>
                </div>
                <div class='pageAlign'>
                  <?php require('typelegend.php'); ?>

                    <h3 id="h3searchresults">Search Results</h3>
                    <table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
                        <thead>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>
                                    <input type='text' name='search_LastName' value='LastName' class='search_init' />
                                </th>
                                <th>
                                    <input type='text' name='search_FirstName' value='FirstName' class='search_init' />
                                </th>
                                <th>
                                    <input type='text' name='search_Birth' value='Birth' class='search_init' />
                                </th>
                                <th>
                                    <input type='text' name='search_Death' value='Death' class='search_init' />
                                </th>
                                <th>
                                    <input type='text' name='search_EventYear' value='EventYear' class='search_init' />
                                </th>
                                <th>
                                    <input type='text' name='search_BookCode' value='BookCode' class='search_init' />
                                </th>
                                <th>
                                    <input type='text' name='search_PageNumbers' value='PageNumbers' class='search_init' />
                                </th>
                                <th>
                                    <input type='text' name='search_TypeCode' value='TypeCode' class='search_init' />
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
    <div id="resultsbackground_table">
        <div id="container" class="home">
          <div id="searchresults">
            <?php require('header.php'); ?>
          </div>
        </div>
    </body>
</html>