<?php
//The default image that gets parsed when no parameter filename is given
$conf['default_image']	= $image_path;

$filename = $conf['default_image'];
echo $filename  . "<br />";
//*******************************************************
//This is the main function. Format of the output array is $retmas[$line_number][$letter_number][$type]
//where $type is 0 for digit and 1 for relative closeness
$retmas = parse_image($filename,$conf['font_file']);
if (count($retmas)==1) {
	$crit = "89012345678934567012";
	for ($int=0;$int<count($retmas[0]);$int++) {
		$val = $retmas[0][$int][0];
		if ($val!=substr($crit, $int, 1)) {
			break;
		}
	}
	echo $filename . " is a separator<br />";
}

?>