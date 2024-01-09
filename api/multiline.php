<?php
require_once '../bootstrap.php';

$template = "../uploads/1033/templates/EAMS Proof of Service.docx";

$docx = new CreateDocx();

//FIXME: there are no template methods at CreateDocx
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
