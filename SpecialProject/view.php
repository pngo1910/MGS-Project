<?php
	// require('export.php');
	if(isset($_POST["export_all_view"]))
	{
		
		require_once('export.php');

		$table = $_GET['table'];
		
		exportAll($table);
	}
	else if(isset($_POST["export_selected_view"]))
	{
		require_once('export.php');

		$table = $_GET['table'];

		if(isset($_POST['check']))
		{
			exportAll($table, $_POST['check']);
		}
		else {
			require('../../db/adminCheck.php');
			require('../../errorReporter.php');
			$_SESSION['error'] = "No records were selected.";
			header("Location: table.php?tableName=$table");
		}
	}
	else
	{
		require('../db/adminCheck.php');
		require('../errorReporter.php');
		require('../db/adminConnection.php');
		require('../retrieveColumns.php');

		$source = $_GET['table'];
		if($source === 'Products' || $source === 'Category')
			$destination = "Store.dbo.$source";
		else
			$destination = "MGS.dbo.$source";

		$params = array();
		$options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

		/*
	    * Primary Key Columns
	    */
	    $table = $_GET['table'];
	    $primaryKeys = retrievePrimaryKeys($table, $conn);

	    /* Indexed column (used for fast and accurate table cardinality) */
	    $sIndexColumn = $primaryKeys[0];

	    $columns = retrieveColumns($table, 0, $conn);

		// Code to delete all records with status code "CURRENT"
		if(isset($_POST['delete_all_view']))
		{
			$sql = "DELETE FROM $destination WHERE $sIndexColumn IN (SELECT $sIndexColumn FROM $source WHERE StatusCode = 'CURRENT')";
			$stmt = sqlsrv_query($conn, $sql, array(), array( "Scrollable" => 'static' ));

			if ($stmt === false) {
	      errorReport(sqlsrv_errors(), __FILE__, __LINE__);
			}
			else {
				$sql = "DELETE FROM $source WHERE StatusCode = 'CURRENT'";
				$stmt = sqlsrv_query($conn, $sql, array(), array( "Scrollable" => 'static' ));

				if ($stmt === false) {
	        errorReport(sqlsrv_errors(), __FILE__, __LINE__);
				}
				else {
					$_SESSION['success'] = "All records successfully deleted!";
					//header('Location: deletedTables.php');
					header("Location: table.php?tableName=$source");
				}
			}
		}
		elseif(isset ($_POST['delete_selected_view']))
		{
			if(isset($_POST['check']))
			{
					foreach($_POST['check'] as $value)
					{
						$sql = "DELETE FROM $destination WHERE $sIndexColumn = ?";
						$stmt = sqlsrv_query($conn, $sql, array($value), array( "Scrollable" => 'static' ));

						if ($stmt === false) {
							errorReport(sqlsrv_errors(), __FILE__, __LINE__);
						}
						else
						{
							$sql = "DELETE FROM $source WHERE $sIndexColumn = ?";
							$stmt = sqlsrv_query($conn, $sql, array($value), array( "Scrollable" => 'static' ));

							if ($stmt === false) {
				            	errorReport(sqlsrv_errors(), __FILE__, __LINE__);
				            }
						}
					}
					$count = count($_POST['check']);
					$_SESSION['success'] = "{$count} rows successfully deleted!";
					//header('Location: deletedTables.php');
					header("Location: table.php?tableName=$source");
			}
			else {
				$_SESSION['error'] = "No records were selected.";
				//header('Location: deletedTables.php');
				header("Location: table.php?tableName=$source");
			}
		}
		elseif (isset ($_POST['create_selected_pdf']))
		{
			if (isset($_POST['check'])) 
			{
				require_once 'printReport.php';

				foreach ($_POST['check'] as $value) 
				{
					$sql = "SELECT CemDescr, CemCode FROM Cemeteries WHERE ID = $value";
					$stmt = sqlsrv_query($conn, $sql);

					if ($stmt === false) 
					{
				    	errorReport(sqlsrv_errors(), __FILE__, __LINE__);
				    }
				    else
				    {
				    	$row = sqlsrv_fetch_array($stmt);

				    	createCemeteryReport($row['CemDescr'], $row['CemCode']);
				    }
				}
			}
		}
	}	

?>
