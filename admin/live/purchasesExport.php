<?php
	if(isset($_POST["export_all"]))
	{
		require_once('export.php');

		$table = "Transactions";
		
		exportcsv($table, "", "", "storeConnection");
	}
	else if(isset($_POST["export_selected"]))
	{
		require_once('export.php');

		$table = "Transactions";

		if(isset($_POST['check']))
		{
			exportcsv($table, "", $_POST['check'], "storeConnection");
		}
		else {
			require('../../db/adminCheck.php');
			require('../../errorReporter.php');
			$_SESSION['error'] = "No records were selected.";
			header("Location: purchases.php");
		}
	}
?>