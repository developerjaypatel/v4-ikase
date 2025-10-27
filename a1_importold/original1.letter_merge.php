<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$dir = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\1033\\";

$file1 = "passing_announce_en.docx";
$file2 = "passing_announce_sp.docx";

require_once '../phpdocx_pro/classes/MultiMerge.inc';

$merge = new MultiMerge();

$merge->mergeDocx($dir . $file1, array($dir . $file2), $dir . 'example_merge_docx.docx', array());

echo "done";
