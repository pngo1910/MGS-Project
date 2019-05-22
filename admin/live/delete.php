<?php
	if(isset($_POST["export_all_delete"]))
	{
		require_once('export.php');

		$table = $_GET['table'];
		
		exportcsv($table, "DELETED");
	}
	else if(isset($_POST["export_selected_delete"]))
	{
		require_once('export.php');

		$table = $_GET['table'];

		if(isset($_POST['check']))
		{
			exportcsv($table, "DELETED", $_POST['check']);
		}
		else {
			require('../../db/adminCheck.php');
			require('../../errorReporter.php');
			$_SESSION['error'] = "No records were selected.";
			header("Location: deletedTables.php");
		}
	}
	else
	{
		require('../../db/adminCheck.php');
		require('../../errorReporter.php');
		require('../../db/adminConnection.php');
		require('../../retrieveColumns.php');

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

		// Code to delete all records with status code "DELETED"
		if(isset($_POST['all']))
		{
			$sql = "DELETE FROM $destination WHERE $sIndexColumn IN (SELECT $sIndexColumn FROM $source WHERE StatusCode = 'DELETED')";
			$stmt = sqlsrv_query($conn, $sql, array(), array( "Scrollable" => 'static' ));

			if ($stmt === false) {
	      errorReport(sqlsrv_errors(), __FILE__, __LINE__);
			}
			else {
				$sql = "DELETE FROM $source WHERE StatusCode = 'DELETED'";
				$stmt = sqlsrv_query($conn, $sql, array(), array( "Scrollable" => 'static' ));

				if ($stmt === false) {
	        errorReport(sqlsrv_errors(), __FILE__, __LINE__);
				}
				else {
					$_SESSION['success'] = "All marked records successfully deleted!";
					header('Location: deletedTables.php');
				}
			}
		}
		elseif(isset ($_POST['selected']))
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
					header('Location: deletedTables.php');
			}
			else {
				$_SESSION['error'] = "No records were selected.";
				header("Location: deletedTables.php");
			}
		}
		elseif(isset($_POST['revert_all'])) {
			$sqlarray = Array();

			foreach ($columns as $col)
				if ($col != $sIndexColumn)
					$sqlarray[] = " dev.$col = live.$col";

			//$sql = "UPDATE dev SET " . implode(",", $sqlarray) . " FROM " . $source . " DEV INNER JOIN " . $destination . " live ON dev." . $sIndexColumn . " = live." . $sIndexColumn . " WHERE dev.StatusCode = 'DELETED'";
			$sql = "UPDATE dev SET dev.StatusCode = 'CURRENT' FROM " . $source . " DEV WHERE dev.StatusCode = 'DELETED';";
			$stmt = sqlsrv_query($conn, $sql, array(), array( "Scrollable" => 'static' ));
			if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
			$_SESSION['success'] = "All changes successfully reverted!";
			//$_SESSION['success'] = $sql;
			header('Location: deletedTables.php');
		}
		elseif(isset($_POST['revert_selected'])) {
			if (isset($_POST['check'])) {
				$sqlarray = Array();

				foreach ($columns as $col)
					if ($col != $sIndexColumn)
						$sqlarray[] = " dev.$col = live.$col";

				foreach ($_POST['check'] as $value) {
					//$sql = "UPDATE dev SET " . implode(",", $sqlarray) . " FROM " . $source . " DEV INNER JOIN " . $destination . " live ON dev." . $sIndexColumn . " = live." . $sIndexColumn . " WHERE dev." .$sIndexColumn . " = ?";
					$sql = "UPDATE dev SET dev.StatusCode = 'CURRENT' FROM " . $source . " DEV WHERE dev." .$sIndexColumn . " = ?";
					$stmt = sqlsrv_query($conn, $sql, array($value), array( "Scrollable" => 'static' ));
					if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
				}

				$count = count($_POST['check']);
				$_SESSION['success'] = "{$count} rows successfully reverted!";
				//$_SESSION['success'] = $sql;
				header('Location: deletedTables.php');
			}
			else {
				$_SESSION['error'] = "No records were selected.";
				header("Location: deletedTables.php");
			}
		}
	}
  
?>
