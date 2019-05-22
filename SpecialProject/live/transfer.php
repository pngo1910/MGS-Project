<?php
	if(isset($_POST["export_all_new"]))
	{
		require_once('export.php');

		$table = $_GET['table'];
		
		exportcsv($table, "NEW");
	}
	else if(isset($_POST["export_selected_new"]))
	{
		require_once('export.php');

		$table = $_GET['table'];

		if(isset($_POST['check']))
		{
			exportcsv($table, "NEW", $_POST['check']);
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

	  /* Indexed column (used for fast and accurate table cardinality) */
	  $primaryKeys = retrievePrimaryKeys($source, $conn);
	  $sIndexColumn = $primaryKeys[0];

		if (isset($_POST['all'])) {
			
			$sql = "INSERT INTO $destination SELECT * FROM $source WHERE StatusCode = 'NEW'";
			$stmt = sqlsrv_query($conn, $sql, array(), array( "Scrollable" => 'static' ));

			if ($stmt === false) {
	      errorReport(sqlsrv_errors(), __FILE__, __LINE__);
			}
			else {
				$sql = "UPDATE $source SET StatusCode = 'CURRENT' WHERE StatusCode = 'NEW'";
				$stmt = sqlsrv_query($conn, $sql, array(), array( "Scrollable" => 'static' ));

				if ($stmt === false) {
	        errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	      }
				else {
					$sql = "UPDATE $destination SET StatusCode = 'CURRENT' WHERE StatusCode = 'NEW'";
					$stmt = sqlsrv_query( $conn, $sql, array(), array( "Scrollable" => 'static' ));

					if ($stmt === false) {
	          errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	        }
					else
					{
						$_SESSION['success'] = "All new records successfully transfered!";
						header('Location: newTables.php');
					}
				}
			}
		}
		elseif(isset($_POST['selected']))
		{
			if(isset($_POST['check']))
			{
				foreach($_POST['check'] as $value)
				{
					$sql = "INSERT INTO $destination SELECT * FROM $source WHERE $sIndexColumn = ?";
					$stmt = sqlsrv_query($conn, $sql, array($value), array( "Scrollable" => 'static' ));

					if ($stmt === false) {
	          errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	        }
					else
					{
						$sql = "UPDATE $source SET StatusCode = 'CURRENT' WHERE $sIndexColumn = ?";

						$stmt = sqlsrv_query($conn, $sql, array($value), array( "Scrollable" => 'static' ));

						if ($stmt === false) {
	            errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	          }
					}
				}

				$sql = "UPDATE " . $destination . " SET StatusCode = 'CURRENT' WHERE StatusCode = 'NEW'";
				$stmt = sqlsrv_query($conn, $sql, array(), array( "Scrollable" => 'static' ));

				if ($stmt === false) {
	        errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	      }
				else
				{
					$count = count($_POST['check']);
					$_SESSION['success'] = "{$count} rows successfully transfered!";
					header('Location: newTables.php');
				}
			}
			else
			{
				$_SESSION['error'] = "No records were selected.";
				header("Location: newTables.php");
			}
		}
		elseif(isset($_POST['revert_all']))
		{
			$sql = "DELETE FROM $source WHERE StatusCode = 'NEW'";
			$stmt = sqlsrv_query($conn, $sql, array(), array( "Scrollable" => 'static' ));

			if ($stmt === false) {
	      errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	    }
			else {
				$_SESSION['success'] = "All new uploads deleted!";
				header('Location: newTables.php');
			}
		}
		elseif (isset($_POST['revert_selected'])) {
			if (isset($_POST['check'])) {
				foreach ($_POST['check'] as $value) {
					$sql = "DELETE FROM $source WHERE $sIndexColumn = ?";
					$stmt = sqlsrv_query($conn, $sql, array($value), array( "Scrollable" => 'static' ));

					if ($stmt === false) {
						errorReport(sqlsrv_errors(), __FILE__, __LINE__);
					}
				}

				$count = count($_POST['check']);
				$_SESSION['success'] = "{$count} new uploads successfully deleted!";
				header('Location: newTables.php');
			}
			else {
				$_SESSION['error'] = "No records were selected.";
				header("Location: newTables.php");
			}
		}
	}
  
?>
