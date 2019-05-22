<?php
	if(isset($_POST["export_all_updated"]))
	{
		
		require_once('export.php');

		$table = $_GET['table'];
		
		exportcsv($table, "UPDATED");
	}
	else if(isset($_POST["export_selected_updated"]))
	{
		require_once('export.php');

		$table = $_GET['table'];

		if(isset($_POST['check']))
		{
			exportcsv($table, "UPDATED", $_POST['check']);
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

		if($source === 'Books') {
			//$key = 'BookCode';
			$key = 'ID';
			$source = "MGSTemp_Dev.dbo.{$source}";
		}
		else
			$key = retrievePrimaryKeys($source, $conn)[0];

		//	if Transfer All is posted
		if(isset($_POST['all'])) {

			//	sql statement to delete from table, execute it and show errors if any
			$sql = "DELETE FROM $destination WHERE $key IN (SELECT $key FROM $source WHERE StatusCode = 'UPDATED')";

			$stmt = sqlsrv_query($conn, $sql, array(), array("Scrollable" => 'static'));

			if($stmt === false) {
				errorReport(sqlsrv_errors(), __FILE__, __LINE__);
			}
			else {
				//	sql statement to insert into table, and execute
				$sql = "INSERT INTO $destination SELECT * FROM $source WHERE StatusCode = 'UPDATED'";
				$stmt = sqlsrv_query($conn, $sql, array(), array("Scrollable" => 'static'));

				if($stmt === false) {
					errorReport(sqlsrv_errors(), __FILE__, __LINE__);
				}
				else {
					$sql = "UPDATE $source SET StatusCode = 'CURRENT' WHERE StatusCode = 'UPDATED'";
					$stmt = sqlsrv_query($conn, $sql, array(), array("Scrollable" => 'static'));

					if($stmt === false) {
						errorReport(sqlsrv_errors(), __FILE__, __LINE__);
					}
					else {
						//	sql statement to update, and execute statement
						$sql = "UPDATE $destination SET StatusCode = 'CURRENT' WHERE StatusCode = 'UPDATED'";
						$stmt = sqlsrv_query($conn, $sql, array(), array("Scrollable" => 'static'));

						if($stmt === false) {
							errorReport(sqlsrv_errors(), __FILE__, __LINE__);
						}
						else {
							//	store success message in session variable and
							//	redirect to updatedTables
							$_SESSION['success'] = "All updated records successfully updated!";
							header('Location: updatedTables.php');
						}
					}
				}
			}
		}
		//	if Selected is posted
		elseif(isset($_POST['selected'])) {
			if(isset($_POST['check'])) {
				foreach($_POST['check'] as $value) {
					$sql = "DELETE FROM $destination WHERE $key = ?";
		//die($sql);		
					$stmt = sqlsrv_query($conn, $sql, array($value), array("Scrollable" => 'static'));

					if($stmt === false) {

						errorReport(sqlsrv_errors(), __FILE__, __LINE__);
						
					}
					else {
						$sql = "INSERT INTO $destination SELECT * FROM $source WHERE $key = ?";
						$stmt = sqlsrv_query($conn, $sql, array($value), array("Scrollable" => 'static'));

						if($stmt === false) {
							errorReport(sqlsrv_errors(), __FILE__, __LINE__);
						}
						else {
							$sql = "UPDATE $source SET StatusCode = 'CURRENT' WHERE $key = ?";
							$stmt = sqlsrv_query($conn, $sql, array($value), array("Scrollable" => 'static'));

							if($stmt === false) {
								errorReport(sqlsrv_errors(), __FILE__, __LINE__);
							}
						}
					}
				}

				$sql = "UPDATE $destination SET StatusCode = 'CURRENT' WHERE StatusCode = 'UPDATED'";
				$stmt = sqlsrv_query($conn, $sql, array(), array("Scrollable" => 'static'));

				if($stmt === false) {
					errorReport(sqlsrv_errors(), __FILE__, __LINE__);
				}
				else {
					$count = count($_POST['check']);
					$_SESSION['success'] = "{$count} rows successfully updated!";
					header('Location: updatedTables.php');
				}
			}
			else {
				$_SESSION['error'] = "No records were selected.";
				header("Location: updatedTables.php");
			}
		}
		//	if Revert All is posted
		elseif(isset($_POST['revert_all'])) {
			$sqlarray = Array();
			$columns = retrieveColumns($source, 0, $conn);

			foreach($columns as $column) {
				if($column != $key)
					$sqlarray[] = " dev.$column = live.$column";
			}

			$sql = "UPDATE dev SET " . implode(",", $sqlarray) . " FROM " . $source . " DEV INNER JOIN " . $destination . " live ON dev.$key = live.$key WHERE dev.StatusCode = 'UPDATED'";
			$stmt = sqlsrv_query($conn, $sql, array(), array("Scrollable" => 'static'));

			if($stmt === false) {
				errorReport(sqlsrv_errors(), __FILE__, __LINE__);
			}
			else {
				$_SESSION['success'] = "All changes successfully reverted!";
				header('Location: updatedTables.php');
			}
		}
		//	if Revert Selected is posted
		elseif(isset($_POST['revert_selected'])) {
			if(isset($_POST['check'])) {
				$sqlarray = Array();
				$columns = retrieveColumns($source, 0, $conn);

				foreach($columns as $column) {
					if($column != $key)
						$sqlarray[] = " dev.$column = live.$column";
				}

				foreach($_POST['check'] as $value) {
					$sql = "UPDATE dev SET " . implode(",", $sqlarray) . " FROM " . $source . " DEV INNER JOIN " . $destination . " live ON dev.$key = live.$key WHERE dev.$key = ?";
					$stmt = sqlsrv_query($conn, $sql, array($value), array("Scrollable" => 'static'));

					if($stmt === false) {
						errorReport(sqlsrv_errors(), __FILE__, __LINE__);
					}
				}
				$count = count($_POST['check']);
				$_SESSION['success'] = "{$count} rows successfully reverted!";
				header('Location: updatedTables.php');
			}
			else {
				$_SESSION['error'] = "No records were selected.";
				header("Location: updatedTables.php");
			}
		}
	}

?>