<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	

die();
//phpinfo()

include("api/connection.php");
try {
	echo make_bitly_url("https://www.ikase.org/api/sync_calendar_kase.php?&token=e1e61d48ad7f05e47030c5a0510b55cc.TS5acfc8a0f1cab");
} catch (Exception $e) {
	die(print_r($e));
}
die();

$key = "12345";
$url = "https://www.ikase.org/down.php?key=" . $key;
$short_url = make_bitly_url($url);

die($short_url);
$params = '{"activity_id":"616523","customer_id":"1033"}';
$encoded = base64_encode($params);

$encoded = "eyJhY3Rpdml0eV9pZCI6IjYxNjUyNiIsImN1c3RvbWVyX2lkIjoiMTAzMyJ9";
die(base64_decode($encoded));
die("tests:". date("h:i:s"));
die(print_r($_SERVER));

include("api/manage_session.php");



?>
<a href="http://kustomweb.xyz/tritek/get_archive.php?db=goldberg2&recno=327&archive_number=3&case_id=8354&cpointer=999279&sess_id=<?php echo $_SESSION["user"]; ?>">Test</a>
<?php
die();
$list = array();
$list[] = array(
	"case_number" 		=> 	"CC2014-012345",
	"judgment_date"		=>	"50/08/2014",
	"cost"				=>	"1514.89",
	"plaintiff"			=>	"JOE SOMEONE",
	"defendants"		=>	array("MARY HER", "JOHN HIM")
);
$list[] = array(
	"case_number" 		=> 	"CC2014-987654",
	"judgment_date"		=>	"06/08/2014",
	"cost"				=>	"2300.00",
	"plaintiff"			=>	"MIRIAM LANDLORD",
	"defendants"		=>	array("KATE WOMAN", "KEN MAN")
);

die(json_encode($list));

//passthru('whoami');

$cmd = "PowerShell.exe -ExecutionPolicy Bypass -File c:\\bat\\topdf.ps1 'C:\\inetpub\\wwwroot\\ikase.org\\uploads\\1033\\invoices\\kase_bill__20180705103030.docx'";
$fp = fopen('C:\\inetpub\\wwwroot\\ikase.org\\uploads\invoices\\pdf.ps1', 'w');
fwrite($fp, $cmd);
fclose($fp);

die();


passthru($cmd);




//die(phpinfo());
include("api/manage_session.php");
die($_SESSION["current_kase_query"]);
die(print_r($_SESSION));

$targetFile = "C:\\inetpub\\wwwroot\\ikase\\uploads\\1033\\42\\f132_16011294321.pdf";
$thumbFile = "C:\\inetpub\\wwwroot\\ikase\\pdfimage\\1033\\42\\f132_16011294321.jpg";
$thumbnail_path = "C:\\inetpub\\wwwroot\\ikase\\uploads\\1033\\42\\medium\\f132_16011294321.jpg";

$targetFile = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\1094\\6235\\jetfiler\\eams_combine_appcover_24815.pdf";
//echo "from " . $targetFile . " to " . $thumbFile;	// . "<br>" . $thumbnail_path . "<br />";


if (!file_exists($targetFile)) {
	die($targetFile . " -> no file");
} else {
	echo $targetFile . " exists<br />";
}
die();

$image_magick = new imagick();

//error here
$image_magick->readImage($targetFile . "[0]");

$image_magick = $image_magick->flattenImages();
$image_magick->setResolution(300,300);
$image_magick->thumbnailImage(800, 800, true);
$image_magick->setImageFormat('jpg');

$image_magick->writeImage($thumbnail_path);
?>