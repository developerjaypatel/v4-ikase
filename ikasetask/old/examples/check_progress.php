<?php
$filename = "progress.txt";
$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);

echo json_encode(array("progress"=>$contents));
?>