<div id="documents">
<ul id="doclist">
<?php
$dir    = '.';
$files = scandir($dir,1);
$number = count($files);

for ($i=0; $i<$number-2; $i++)
{
	if ($files[$i] != "index.php") {
		if ($files[$i] != "tmp") {	
		$filename = $files[$i]; 

	echo "<li><a target='_blank' href='$filename'>$filename</a></li> ";
		}
	}
} 

if ($number < 4) {
	echo "<p><i>Nothing found</i></p>";
}


?>
</ul>
</div>