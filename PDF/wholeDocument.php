<?php
	require('../db/memberCheck.php');

	//get the filename and path
	$path = $_GET['path'];
	$fileName = $path . "/" . $_GET['fileName'].'.pdf';

	//provide the file for download
	header("Content-disposition: attachment; filename=".basename($fileName));
	header("Content-type: application/pdf");
	readfile($fileName);
?>
