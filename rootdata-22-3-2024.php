<?php

// $document_root_dir = "C:\\inetpub\\wwwroot\\ikase.org";
$document_root_dir = "D:\ikase.org";

// $root_api_url = "https://v4.ikase.org";
// // $root_api_url = "https://v4.ikase.org";



// // BatchScan values
// // $saprate_barcode_pdf = "https://v4.ikase.org/uploads/batchscan_barcode_plain.pdf";
// // $batchscan_form_iframe = "https://v4.ikase.org/barcode/ikase_form.php";
// $saprate_barcode_pdf = "https://v4.ikase.org/uploads/batchscan_barcode_plain.pdf";
// $batchscan_form_iframe = "https://v4.ikase.org/barcode/ikase_form.php";


// // $document_servername = "https://www.ikase.org/";
// $document_servername = "https://v4.ikase.org/";
if($_SERVER['SERVER_NAME']=="starlinkcms.com")
{
	$server_name="https://starlinkcms.com";
}
else
{
	$server_name="https://v4.ikase.org";
}
$css_server_name="https://www.ikase.website";
?>