<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=Windows-1252">
    </head>
</html>
<?php
echo '<pre>';
error_reporting(-1);
ini_set('display_errors', 'On');
error_reporting(E_ALL);

include("cls_docucents.php");

$obj = new docucents("cmd");
//print_r($obj->dwcd_validateKey());
//die;

$obj->Submittals_AddForDelivery(2001, 2001, "Solulabfile-" . uniqid(), "None", "comment:" . uniqid(), "");

$obj->PartyData_AddForDelivery("test.com", "testSolulab", "testSolulab", "", 'address1', "", "testSolulab", "testSolulab", "test", "test");
//die(print_r($obj));

$origpdf = file_get_contents("doc_9798_89.pdf");
//$origpdf = file_get_contents("/var/www/html/docusent/sample1.pdf");
$filecontent = base64_encode($origpdf);
$filedate = date("Ymd", time());
//die($filedate);

xmlrpc_set_type($filecontent, "base64");

xmlrpc_set_type($filedate, "datetime");
$attachment = array(
    "file_name" => "SOLULABFILE_" . rand(1, 5) . ".pdf",
    "type" => "type-" . uniqid(),
    "title" => "title-" . uniqid(),
    "unit" => "ADJ",
    "date" => $filedate,
    "author" => "author-" . uniqid(),
    "base64" => $filecontent
);
//die(print_r($attachment));
$temp = $obj->AddAttachment($attachment, false);

//echo "Submittal ID:" . $obj->vendor_submittal_id . "<br />";
$post_result = $obj->SetSubmittalStatus();
echo 'POS REsult : ';
print_r($post_result);
//die;
//$docs_details = $obj->Submittles_GetDocument(21123551, 21123551);
//$docs_details = $obj->Submittles_GetDocument("2111BD6E286E329C", "21123569");
//print_r($docs_details);
//echo '<br>';
print_r("vendor_submittal_id:" . $obj->vendor_submittal_id);
