<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$cmd = 'java -jar C:\\inetpub\\wwwroot\\iKase.org\\phpdocx_pro\\lib\\openoffice\\jodconverter-2.2.2\\lib\\jodconverter-cli-2.2.2.jar C:\\inetpub\\wwwroot\\iKase.org\\phpdocx_pro\\examples\\nicks_word1_docx.docx C:\\inetpub\\wwwroot\\iKase.org\\phpdocx_pro\\examples\\test.pdf';

die($cmd);

require_once '../phpdocx_pro/classes/CreateDocx.inc';
$docx = new TransformDoc();

$dir = 'C:\\inetpub\\wwwroot\\iKase.org\\phpdocx_pro\\examples\\';
$filename = $dir . 'nicks_word2_docx';
$destination = str_replace(".docx", ".pdf", $filename);

$docx->setStrFile($filename);
//$docx->generateXHTML();
//$html = $docx->getStrXHTML();
//Also, you can export the docx to PDF with

$content = $docx->generatePDF();

$myfile = fopen("newfile.pdf", "w");
fwrite($myfile, $content);
fclose($myfile);

die("done");
?>