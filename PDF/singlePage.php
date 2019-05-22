<?php
	require('../db/memberCheck.php');

	//get the filename and page number
	$path = $_GET['path'];
	$fileName = $_GET['fileName'];
	$pageNum = $_GET['pageNum'];
	$singlePage = $fileName.'_page'.$pageNum.'.pdf';
 
	//create a single page from the document
	exec('gswin64c -sDEVICE=pdfwrite -dNOPAUSE -dBATCH -dSAFER -dFirstPage='.$pageNum.' -dLastPage='.$pageNum.' -sOutputFile='.$singlePage." $path/".$fileName.".pdf");

	//provide the newly created single page for download
	header("Content-disposition: attachment; filename=".$singlePage);
	header("Content-type: application/pdf");
	readfile($singlePage);

	//delete the sinle page file from the server
	unlink($singlePage);
?>
