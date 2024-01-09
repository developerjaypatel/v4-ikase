<?php

$phpdocx_pro = ROOT_PATH.'vendor'.DC.'phpdocx_pro';
$examplesDir = $phpdocx_pro.DC.'examples'.DC;
$converter = $phpdocx_pro.DC.'lib'.DC.'openoffice'.DC.'jodconverter-2.2.2'.DC.'lib'.DC.'jodconverter-cli-2.2.2.jar';
$cmd = "java -jar $converter {$examplesDir}nicks_word1_docx.docx {$examplesDir}test.pdf";

//die($cmd);

$docx = new TransformDoc();

//FIXME: another script with hardcoded files from uploads dir...
$filename = $examplesDir . DC.'nicks_word2_docx';
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
