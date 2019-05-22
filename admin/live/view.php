<?php
	//require('export.php');
	if(isset($_POST["export_all_view"]) || isset($_GET["export_all_view"]))
	{
		
		require_once('export.php');

		$table = $_GET['table'];

		exportAll($table);
	}
	else if(isset($_POST["export_selected_view"]) || isset($_GET["export_selected_view"]))
	{
		require_once('export.php');

		$table = $_GET['table'];

		if(isset($_POST['check']))
		{
			exportAll($table, $_POST['check']);
		}
		else if(isset($_GET['check']))
		{
			exportAll($table, $_GET['check']);
		}
		else {
			require('../../db/adminCheck.php');
			require('../../errorReporter.php');
			$_SESSION['error'] = "No records were selected.";
			header("Location: table.php?tableName=$table");
		}
	}

?>