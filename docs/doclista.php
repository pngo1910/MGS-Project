<div id="documents">
<ul id="doclist">
<?php
$dir    = '.';
// $files = array_diff(scandir($dir,1), array('.', '..'));
$files = scandir($dir);
$number = count($files);

for ($i=2; $i<$number; $i++)
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
<div>