<?php
	require('../db/mgsConnection.php');
  	require('../db/memberCheck.php');
  	
	function sendLink(){
		if(!isset($_GET['p']))
			$link = "../PDF/wholeDocument.php?fileName=".$_GET['f'];
		else
		 	$link = "../PDF/singlePage.php?fileName=".$_GET['f']."&pageNum=".$_GET['p'];
		return $link;
	}
?>
<script type="text/javascript">
//This was the only way I could think of to let the user download the file without seeing the path to the script
//If they can see the path it would be easy for them to copy the href into the search bar, remove the pageNum and download the entire pdf
//or just start randomly putting in type codes and ids for the fileName to download a bunch of pdfs without paying for them
location.href="<?= sendLink() ?>";
setTimeout(function() {history.go(-1)},400);
</script>