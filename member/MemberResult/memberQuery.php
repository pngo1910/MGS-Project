<?php
    /***********************************************************************************************************************************************/
    /* Be careful when editing the queries in this file. When I first started adding queries for the payperview it stopped retrieving
    * information from the Census tables. After I fixed that, it stopped retrieving from the Books table. I got that working again and now everything
    * is fine. This is just a warning that something might break if anything's changed. */
    /************************************************************************************************************************************************/
    ini_set('memory_limit', '-1'); //fixes a memory cap limit

    // Tables arrays are used for different cases.
    // Main tables has all table names that are used in the main block
    // Census tables has all census table names used in the census block
    // All tables to be searched through must be placed into their respective arrays
    $mainTables = array("CemeteryRecords", "ObituariesRural", "ObituariesWinPap", "BookRecords", "PayPerView", "Purchases");

    //$mainTables = array("CemeteryRecords", "Marriages", "ObituariesRural", "ObituariesWinPap");
    $censusTables = array("Census1827", "Census1831", "Census1834", "Census1870", "Census1891", "Census1901");

    //this will be our static array which contains standardized date feilds on the tables
    // each table will have one or more of these year feilds.
    $yearSearchFields = array("Birth","Death","EventYear");

    //this is an array of special tables that have to be handled
    //marrages needs to be ran twice and have results displayed (check ERD)
    $specialTables = array("Marriages");
    $books = "Books";

    // force checking the years if this is false
    $nullYears = (isset($_POST['nullyears']) && $_POST['nullyears'] === 'on');

    //if there are no firstname or last name we must redirect back, one is required
    if( (!(isset($_POST['lname'])) && !(isset($_POST['fname']))) || ($_POST['lname'] == '' && $_POST['fname'] == '') ) {
        $_SESSION['error'] = "Please enter a first or last name.";
        header("location: /member/");
        exit(0);
    } elseif ( isset($_POST['lname']) && is_numeric($_POST['lname']) || isset($_POST['fname']) && is_numeric($_POST['fname'])) {
        $_SESSION['error'] = "The first and last name can't be a number.";
        header("location: /member/");
        exit(0);
    } else {
        //check and set last name
        if (isset ($_POST['lname'])) {
            $lname = $_POST['lname'];
        } else {
            $lname = "";
        }

        //check and set first name
        if (isset ($_POST['fname'])) {
            $fname = $_POST['fname'];
        } else {
            $fname = "";
        }
    }

    $startOfYearSearch = isset($_POST['start']) && is_numeric($_POST['start']) ?
      $_POST['start'] : ($nullYears ? null : 0);

    // set the end year to the current year if it's not given.
    $endOfYearSearch = isset($_POST['end']) && is_numeric($_POST['end']) ?
      $_POST['end'] : ($nullYears ? null : (int)date('Y'));

    // swap the years around if the end year is not null and the start year
    // is larger than the end year.
    if ($endOfYearSearch && ($startOfYearSearch > $endOfYearSearch))
      list($startOfYearSearch, $endOfYearSearch) = array($endOfYearSearch, $startOfYearSearch);

    // this array will hold the queries we need for results
    $sqlQueriesArray = array();
    $values = array();

    // only search the census data if the birth and death can be null
    // because the census data has no birth or death dates.
    if ($nullYears) {
      // we want to loop through each census table and determine if the
      // year searched falls within the census to qry..
      foreach ($censusTables as $censusTable) {
        // strip the year from the table, all tables are named "CensusYYYY" where YYYY = year
        $censusTableYear = substr($censusTable,6,4); //takes 6th spot over, 4 spots right to isolate year.
        $censusArray = array();

        // check to see if this year falls within our search
        if (($startOfYearSearch == null || $censusTableYear >= $startOfYearSearch)
        && ($endOfYearSearch == null || $censusTableYear <= $endOfYearSearch)) {
          if ($lname != '') {
            $censusArray[] = "LastName LIKE ?";
            $values[] = "%$lname%";
          }

          if ($fname != '') {
            $censusArray[] = "FirstName LIKE ?";
            $values[] = "%$fname%";
          }

          $censusWhereClause = implode(' AND ', $censusArray);
          $sqlQueriesArray[] = "SELECT ID, LastName, FirstName, NULL as 'Birth', NULL as 'Death', $censusTableYear as 'EventYear', NULL AS 'BookCode', NULL AS 'PageNumbers', NULL AS 'RecordID', NULL AS 'Tablename', NULL AS 'FileName', NULL AS 'PageNum', NULL AS 'Record', NULL AS 'Table', TypeCode, 'Link' as 'SingleRecord', '$censusTable' AS 'TableName' FROM $censusTable WHERE $censusWhereClause";
        }
      }
    }

    //*******THIS IS FOR MARRIAGE TABLES**********
    // only search in marriage tables if the birth and death dates can be null
    // because the marriage tables don't have birth or death dates.
    if ($nullYears) {
      $groomArray = array();
      $brideArray = array();
      $groomValues = array();
      $brideValues = array();

      if ($lname != '') {
        $groomArray[] = "GroomLastName LIKE ?";
        $groomValues[] = "%$lname%";
        $brideArray[] = "BrideLastName LIKE ?";
        $brideValues[] = "%$lname%";
      }

      if ($fname != '') {
        $groomArray[] = "GroomFirstName LIKE ?";
        $groomValues[] = "%$fname%";
        $brideArray[] = "GroomFirstName LIKE ?";
        $brideValues[] = "%$fname%";
      }

      if ($startOfYearSearch != null && $endOfYearSearch != null) {
        $groomArray[] = "EventYear BETWEEN ? AND ?";
        $groomValues[] = $startOfYearSearch;
        $groomValues[] = $endOfYearSearch;
        $brideArray[] = "EventYear BETWEEN ? AND ?";
        $brideValues[] = $startOfYearSearch;
        $brideValues[] = $endOfYearSearch;
      }

      $groomWhereClause = implode(' AND ', $groomArray);
      $sqlQueriesArray[] = "SELECT ID, GroomLastName as 'LastName', GroomFirstName as 'FirstName', NULL as 'Birth', NULL as 'Death', EventYear, NULL AS 'BookCode', NULL AS 'PageNumbers', NULL AS 'RecordID', NULL AS 'Tablename', NULL AS 'FileName', NULL AS 'PageNum', NULL AS 'Record', NULL AS 'Table', TypeCode, 'Link' as 'SingleRecord', 'Marriages' AS 'TableName' FROM Marriages WHERE $groomWhereClause";

      $brideWhereClause = implode(' AND ', $brideArray);
      $sqlQueriesArray[] = "SELECT ID, BrideLastName as 'LastName', BrideFirstName as 'FirstName', NULL as 'Birth', NULL as 'Death', EventYear, NULL AS 'BookCode', NULL AS 'PageNumbers', NULL AS 'RecordID', NULL AS 'Tablename', NULL AS 'FileName', NULL AS 'PageNum', NULL AS 'Record', NULL AS 'Table', TypeCode, 'Link' as 'SingleRecord', 'Marriages' AS 'TableName' FROM Marriages WHERE $brideWhereClause";

      $values = array_merge($values, $groomValues, $brideValues);
    }
    //************ END OF MARRIAGES TABLE

    // we want to loop through each of the other tables getting the queries required
    // and these ones we have to check for the years inside the tables
    foreach ($mainTables as $mainTable) {
      $birthPresent = false;
      $deathPresent = false;
      $eventPresent = false;
      $tableArray = array();
      $yearValues = array();

      // we need to query for the columns of these tables
      $columnNames = retrieveColumns($mainTable, 0, $conn);

      //we want to check if any of these columns match our event years static array
      foreach($columnNames as $columnName) {
          //loop though the years search fields
          foreach($yearSearchFields as $field) {
              //if a columnName matches the search field
              if ($field == $columnName) {
                  //we are guarenteed a start and end date aswell so we use that on the
                  //hit searchable year field that has matched the column
                  if ($startOfYearSearch != null && $endOfYearSearch != null) {
                    $tableArray[] = "$field BETWEEN ? AND ?";
                    $yearValues[] = $startOfYearSearch;
                    $yearValues[] = $endOfYearSearch;
                  }

                  // set flag true so we know if its present
                  switch ($field) {
                      case 'Birth': $birthPresent = true; break;
                      case 'Death': $deathPresent = true; break;
                      case 'EventYear': $eventPresent = true; break;
                  }
              }
          }
      }

        $yearFields = implode(' OR ', $tableArray);
        $nameArray = array();

        if ($lname != '') {
            $nameArray[] = "LastName LIKE ?";
            $values[] = "%$lname%";
        }

        if ($fname != '') {
            $nameArray[] = "FirstName LIKE ?";
            $values[] = "%$fname%";
        }

        // here we want to determine which fields are present to generate the
        // correct SQL statements per table
        $yearSelectQry = array();

        if ($birthPresent) {
            $yearSelectQry[] = "Birth";
        } else {
            $yearSelectQry[] = "NULL as 'Birth'";
        }

        if ($deathPresent) {
            $yearSelectQry[] = "Death";
        } else {
            $yearSelectQry[] = "NULL as 'Death'";
        }

        if ($eventPresent) {
            $yearSelectQry[] = "EventYear";
        } else {
            $yearSelectQry[] = "NULL as 'EventYear'";
        }

        $whereClause = implode(' AND ', $nameArray);
        $yearSelects = implode(', ', $yearSelectQry);

        // ignore tables that don't have the birth and death dates
        // and filter null values from tables that do have them.
        if ($nullYears || ($birthPresent && $deathPresent)) {
          $excludeNull = ($nullYears ? '' : ($birthPresent && $deathPresent) ? 'and birth is not null and death is not null' : '');
          if ($startOfYearSearch != null && $endOfYearSearch != null) {
            if($mainTable == 'BookRecords'){
                $sjoin = "";
                $sqlQueriesArray[] = "SELECT ID, LastName, FirstName, NULL AS 'Birth', NULL AS 'Death', EventYear, BookCode, PageNumbers, NULL AS 'RecordID', NULL AS 'Tablename', NULL AS 'FileName', NULL AS 'PageNum', NULL AS 'Record', NULL AS 'Table', TypeCode, NULL AS 'SingleRecord', '$mainTable' AS 'TableName' FROM $mainTable WHERE $whereClause $excludeNull";
            } elseif($mainTable == 'PayPerView'){
                $sjoin = "";
                $sWhere = "WHERE StatusCode = 'CURRENT'";
                $sqlQueriesArray[] = "SELECT ID, NULL AS 'LastName', NULL AS 'FirstName', NULL AS 'Birth', NULL AS 'Death', NULL AS 'EventYear', NULL AS 'BookCode', NULL AS 'PageNumbers', $mainTable.RecordID, $mainTable.Tablename, $mainTable.FileName, $mainTable.PageNum, NULL AS 'Record', NULL AS 'Table', NULL AS 'TypeCode', NULL AS 'SingleRecord', '$mainTable' AS 'TableName' FROM $mainTable $sWhere";
            } elseif($mainTable == 'Purchases'){
                $sjoin = "JOIN Accounts.dbo.Membership ON $mainTable.MemberID = Accounts.dbo.Membership.MemberNum JOIN Accounts.dbo.Members ON Accounts.dbo.Membership.MemberNum = Accounts.dbo.Members.MemberNum WHERE Accounts.dbo.Members.Username = '".$_SESSION['uname']."'";
                $sqlQueriesArray[] = "SELECT ID, NULL AS 'LastName', NULL AS 'FirstName', NULL AS 'Birth', NULL AS 'Death', NULL AS 'EventYear', NULL AS 'BookCode', NULL AS 'PageNumbers', NULL AS 'RecordID', NULL AS 'Tablename', NULL AS 'FileName', $mainTable.PageNum, $mainTable.RecordID AS 'Record', $mainTable.TableName AS 'Table', NULL AS 'TypeCode', NULL AS 'SingleRecord', '$mainTable' AS 'TableName' FROM $mainTable $sjoin";
            } else {
                $sjoin = "";
                //after we finish all the column names we want to append the sql to its array
                $sqlQueriesArray[] = "SELECT ID, LastName, FirstName, $yearSelects, NULL AS 'BookCode', NULL AS 'PageNumbers', NULL AS 'RecordID', NULL AS 'Tablename', NULL AS 'FileName', NULL AS 'PageNum', NULL AS 'Record', NULL AS 'Table', TypeCode, 'Link' as 'SingleRecord', '$mainTable' AS 'TableName' FROM $mainTable WHERE $whereClause AND ($yearFields) $excludeNull";
            }
            $values = array_merge($values, $yearValues);
          } else {
              if($mainTable == 'BookRecords'){
                  $sjoin = "";
                  $sqlQueriesArray[] = "SELECT ID, LastName, FirstName, NULL AS 'Birth', NULL AS 'Death', EventYear, BookCode, PageNumbers, NULL AS 'RecordID', NULL AS 'Tablename', NULL AS 'FileName', NULL AS 'PageNum', NULL AS 'Record', NULL AS 'Table', TypeCode, NULL AS 'SingleRecord', '$mainTable' AS 'TableName' FROM $mainTable WHERE $whereClause $excludeNull";
              } elseif($mainTable == 'PayPerView'){
                    $sjoin = "";
                    $sWhere = "WHERE StatusCode = 'CURRENT'";
                    $sqlQueriesArray[] = "SELECT ID, NULL AS 'LastName', NULL AS 'FirstName', NULL AS 'Birth', NULL AS 'Death', NULL AS 'EventYear', NULL AS 'BookCode', NULL AS 'PageNumbers', $mainTable.RecordID, $mainTable.Tablename, $mainTable.FileName, $mainTable.PageNum, NULL AS 'Record', NULL AS 'Table', NULL AS 'TypeCode', NULL AS 'SingleRecord', '$mainTable' AS 'TableName' FROM $mainTable $sWhere";
              } elseif($mainTable == 'Purchases'){
                    $sjoin = "JOIN Accounts.dbo.Membership ON $mainTable.MemberID = Accounts.dbo.Membership.MemberNum JOIN Accounts.dbo.Members ON Accounts.dbo.Membership.MemberNum = Accounts.dbo.Members.MemberNum WHERE Accounts.dbo.Members.Username = '".$_SESSION['uname']."'";
                    $sqlQueriesArray[] = "SELECT ID, NULL AS 'LastName', NULL AS 'FirstName', NULL AS 'Birth', NULL AS 'Death', NULL AS 'EventYear',NULL AS 'BookCode', NULL AS 'PageNumbers', NULL AS 'RecordID', NULL AS 'Tablename', NULL AS 'FileName', $mainTable.PageNum, $mainTable.RecordID, $mainTable.TableName, NULL AS 'TypeCode', NULL AS 'SingleRecord', '$mainTable' AS 'TableName' FROM $mainTable $sjoin";
              } else {
                  $sjoin = "";
                  //after we finish all the column names we want to append the sql to its array
                  $sqlQueriesArray[] = "SELECT ID, LastName, FirstName, $yearSelects, NULL AS 'BookCode', NULL AS 'PageNumbers', NULL AS 'RecordID', NULL AS 'Tablename', NULL AS 'FileName', NULL AS 'PageNum', NULL AS 'Record', NULL AS 'Table', TypeCode, 'Link' as 'SingleRecord', '$mainTable' AS 'TableName' FROM $mainTable WHERE $whereClause $excludeNull";
              }
          }
        }
    }
    // generates one query from the array with all required queries
    $qry = implode(' UNION ', $sqlQueriesArray);

    //var_dump($values);
    //echo '<br/><br/>';
    //die($qry);

    // run the statement
    $stmt = sqlsrv_query($conn, $qry, $values, array('Scrollable' => 'static'));

    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    /********************/

    //how many rows in the dataset
    $rowsReturned = sqlsrv_num_rows($stmt);

    $rowsPerPage = 20;
    //number of pages for this query
    $numOfPages = ceil($rowsReturned / $rowsPerPage);

    //checks for the page num
    $pageNum = "";
    $firstPageLink = '?pageNum=0';
    $lastPageLink = '?pageNum='. ($numOfPages - 1);
    if (isset($_GET['pageNum'])){
        $pageNum = $_GET['pageNum'];
    }else{
        $pageNum = 0;
        $nextPageLink = '?pageNum=1';
    }

    //gets the current page
    $offset = $rowsPerPage * $pageNum;
    $j = 0;

    //this is used for a JQry table to manage all our data
    $output = array(
        "sEcho" => intval($pageNum),
        "iTotalRecords" => $rowsReturned,
        "iTotalDisplayRecords" => $rowsReturned,
        "aaData" => array()
    );

    //builds our 2D array for the JQry table
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
    {
        $aRow = array();

        foreach($row as $key => $val){
            //here force a link to single record
            // when table name is selected
            if ($key == 'SingleRecord') {
                $val = "<a href=SingleRecord.php?tablename=" . $row['TableName'] . "&amp;id=" . $row['ID'] . " target=\"_blank\">Link";
            }
            $aRow[] = $val;
        }
        $output['aaData'][] = $aRow;
    }
    //echo json_encode( $output );
?>