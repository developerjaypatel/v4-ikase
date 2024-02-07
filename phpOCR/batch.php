<?php
include_once("header.php");

/**************************************************************************************************/
//MAIN
/**************************************************************************************************/

//If you create a new font include file replace char_inc_6.php with your own
$conf['font_file']					= 'char_inc_6.php';


//The default output format. You can chose from xml,html,plain,template.
$conf['default_output_format']		= 'html';

//You shold probably not need to change thees
$conf['word_lines_min_dispersion']	= 0;
$conf['letters_min_dispersion']		= 0;

$pages = $_GET["pages"];
for ($jnt=0;$jnt<$pages;$jnt++) { 
	$image_path = "test_" . $jnt . ".png";
	include("index.php");
}
?>