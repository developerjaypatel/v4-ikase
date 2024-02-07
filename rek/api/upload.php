<?php
$app->get('/uploads', 'getUploads');

function getUploads() {
	$arrFiles = array();
	$intCounter = 0;
	if ($handle = opendir('../uploads')) {
		while (false !== ($entry = readdir($handle))) {
			
			if ($entry != "." && $entry != "..") {
				$arrFiles[] = array("id"=>$intCounter, "filename"=>$entry);
				$intCounter++;
			}
		}
		closedir($handle);
	}
	echo json_encode($arrFiles);
}
?>