<?php

$imageOptions = array(
'src' => 'D:/uploads/1042/templates/letterhead_060717.png', 
'dpi' => 72,  
'scaling' => 30
);
$headerImage = new WordFragment($docx, 'defaultHeader');
$headerImage->addImage($imageOptions);

$headerImageText = new WordFragment($docx, 'defaultHeader');
$headerImageText->addBreak();

//text for secondary header
$textOptions = array(
'fontSize' => 10,
'b' => 'on',
);
$thedate = date("m/d/Y");
$the_provider = $primary->full_name;
$the_casename = "Re:" . str_replace("&", "&amp;", $kase->name);

$headerText = new WordFragment($docx, 'defaultHeader');
$headerText->addText($thedate, $textOptions);
if ($the_provider!="") {
	$headerText->addText($the_provider, $textOptions);
}
$headerText->addText($the_casename, $textOptions);
$headerText->addText("________________________________________________________________", $textOptions);
$headerText->addBreak();

$valuesTable = array(
	array(
		array('value' =>$headerImage, 'vAlign' => 'center'),
		array('value' =>$headerImageText, 'vAlign' => 'center')
	)
);
$valuesTextTable = array(
	array(
		array('value' =>$headerText, 'vAlign' => 'center')
	)
);
$widthTableCols = array(
700,
7500,
500
);
$paramsTable = array(
'border' => 'nil',
'columnWidths' => $widthTableCols,
);


$widthTextTableCols = array(
8700
);
$paramsTextTable = array(
'border' => 'nil',
'columnWidths' => $widthTextTableCols,
);


$firstTable = new WordFragment($docx, 'defaultHeader');
$nontitleTable = new WordFragment($docx, 'defaultHeader');
$firstTable->addTable($valuesTable, $paramsTable);
$nontitleTable->addTable($valuesTextTable, $paramsTextTable);

$docx->addHeader(array('default' => $nontitleTable, 'first' => $firstTable));

//die("patel");
