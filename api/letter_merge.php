<?php
require_once '../bootstrap.php';
$dir = ROOT_PATH.'uploads'.DC.'1033'.DC;

//FIXME: another script with hardcoded files from uploads dir...
$file1 = "passing_announce_en.docx";
$file2 = "passing_announce_sp.docx";

$merge = new MultiMerge();

$merge->mergeDocx($dir . $file1, array($dir . $file2), $dir . 'example_merge_docx.docx', array());

echo "done";
