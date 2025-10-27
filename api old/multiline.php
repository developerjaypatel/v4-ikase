<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once '../phpdocx_pro/classes/CreateDocx.inc';

$template = "../uploads/1033/templates/EAMS Proof of Service.docx";

$docx = new CreateDocx();

$docx->addTemplate($template);

$text = array(
'David Hume',
'13343 Wingo Street',
'Arleta, CA 91331'
);

$docx->addTemplateVariable('PARTIESBLOCK', $text);

$docx->createDocx('../uploads/1033/template_text.docx');
/*
$docx = new CreateDocx();

// parse styles of the default template
$docx->parseStyles();

$docx->createDocx('../uploads/1033/example_parseStyles_1');  
*/
?>