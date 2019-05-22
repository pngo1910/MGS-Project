<?php
    require('../../db/adminCheck.php');
    require('../../db/adminConnection.php');
    require('../../retrieveColumns.php');
    require('../../errorReporter.php');

    error_reporting(0);

    $tableName = $_GET['table'];
    if($tableName != 'products' && $tableName != 'category' && $tableName != 'cemeterytranscriptions')
        header('location: /admin/store/');
    
    $and = "AND COLUMN_NAME NOT IN('ID', 'StatusCode')";
    $cols = retrieveColumns($tableName, $and, $conn);

    if($tableName === 'products'){
        $qry = "SELECT * FROM Category";
        $stmt = sqlsrv_query($conn, $qry, array(), array("Scrollable" => "static"));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

        $categories = array();
        while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
            $categories[] = $row['ID'];
            $categories[] = $row['Category'];
        }
    }
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
    </head>
    <body>   
        <div class = 'adminTables'>    
           <div id="resultsbackground">
                <div id="container" class="home">
                    <div id="searchresults">
                        <?php require 'header.php'; ?>
                    </div>
                    <?php if($tableName === 'products' || $tableName === 'cemeterytranscriptions'): ?>
                        <div class="bulkForm">
                            <h1><?= $tableName === 'products' ? "Products" : "Cemetery Transcriptions" ?></h1>
                            <h2>Bulk Upload</h2>
                            <ul>
                                <li class ="csvInfo">Uploaded files can only be ".csv" files. </li>
                                <li class ="csvInfo">IDs should be omitted from the CSV file. </li>
                                <li class ="csvInfo">Commas should be the leading character in a line.</li>
                                <li class ="csvInfo">NULL values must still be seperated using a comma.</li>
                                <?php if($tableName === 'products'): ?>
                                <li class ="csvInfo">When entering the Category, the entered value MUST be the Category's ID.</li>
                                <?php endif; ?>
                                <?php if($tableName === 'cemeterytranscriptions'): ?>
                                <li class ="csvInfo">The entered Municipality MUST refer to an existing Municipality in the Cemeteries table.</li>
                                <?php endif; ?>
                                <li class ="csvInfo">When entering if a file can be downloaded, put 1 for 'yes' or 0 for 'no'.</li>
                            </ul>

                            <br/>
                        
                        <form action="bulkUpload.php?tableName=<?= $tableName ?>" method="post" enctype="multipart/form-data">
                          <h3>File Upload</h3>
                          <input type="file" class="searching"  name="file" id="file"><br>
                          <input type="submit" name="submit" class="submit" value="Submit">
                        </form>
                        <hr />
                        <h2>Single Upload</h2>
                    <?php endif; ?>
                        <form id="form" method="post" action="insert.php?table=<?= $tableName ?>">
                            <?php require('addPHP.php'); ?>
                            <br /><br />
                            <input type="submit" name="submit" id="submit" value="Submit"  />
                        </form>
                    <?php if($tableName === 'products'): ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
</html>