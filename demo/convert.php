<?php
// "./pdf"->Change with your output folder path
// "./docs/test.docx"->change with input file path
//echo shell_exec('soffice --headless --convert-to pdf --outdir "./" "./docs/1.docx"');
//print_r(Shell_Exec('start/wait soffice --headless --convert-to pdf 1.docx'));
shell_exec('start /wait soffice --convert-to pdf --outdir "./pdf/" "./docs/1.docx"');
// print_r($output);
// var_dump($output);
//shell_exec('start/wait soffice --headless --convert-to pdf 1.docx');
?>
