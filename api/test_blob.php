<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
die();
$filename = "http://www.superiorcourt.maricopa.gov/docket/CivilCourtCases/caseInfo.asp?caseNumber=CV1993-002421";

$response = get_web_page($filename);
die($response);

function get_web_page($url) {
    $options = array(
        CURLOPT_RETURNTRANSFER => true,   // return web page
        CURLOPT_HEADER         => false,  // don't return headers
        CURLOPT_FOLLOWLOCATION => true,   // follow redirects
        CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
        CURLOPT_ENCODING       => "",     // handle compressed
        CURLOPT_USERAGENT      => "test", // name of client
        CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
        CURLOPT_TIMEOUT        => 120,    // time-out on response
    ); 

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);

    $content  = curl_exec($ch);

    curl_close($ch);

    return $content;
}
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("connection.php");
$out = convertNumberToWord(16);

die("out:" . $out);

$fp = fopen('scrape_date.txt', 'w');
fwrite($fp, "wrote cron on " . date("m/d/Y H:i:s"));
fclose($fp);

die("blobbed");
// the content of 'data.txt' is now 123 and not 23!

$json = '[
{"name":"personal_injury_dateInput","value":"04/23/2016 03:00pm"},
{"name":"personal_injury_dayInput","value":"Saturday"},
{"name":"personal_injury_timeInput","value":"03:00pm"},
{"name":"personal_injury_locationInput","value":"8787 San Fernando Road, CA, United States"},
{"name":"personal_injury_countyInput","value":""},
{"name":"personal_injury_accident_descriptionInput","value":"test"},
{"name":"personal_injury_other_detailsInput","value":"test"}
]';

$json = '[{"name":"personal_injury_dateInput","value":"04/23/2016
03:00pm"},{"name":"personal_injury_dayInput","value":"Saturday"},{"name":"personal_injury_timeInput","value":"03:00pm"},{"name":"personal_injury_locationInput","value":"8787
San Fernando Road, CA, United
States"},{"name":"personal_injury_countyInput","value":""},{"name":"personal_injury_accident_descriptionInput","value":"test"},{"name":"personal_injury_other_detailsInput","value":"test"}]';

die(print_r(json_decode($json)));
include("connection.php");

$recno = passed_var("recno", "get");
$docs = passed_var("docs", "get");
$customer_id = passed_var("customer_id", "get");

if (!is_numeric($customer_id)) {
	die();
}
try {
	$db = getConnection();
	
	//lookup the customer name
	$sql_customer = "SELECT data_source
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id";
	
	$stmt = $db->prepare($sql_customer);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$customer = $stmt->fetchObject();
	$data_source = $customer->data_source;
	
	$query = "SELECT form_desc, `document`
	FROM `" . $data_source . "_docs`.`docs" . $docs . "`
	WHERE `recno` = " . $recno;
	//die($query);
	$stmt = DB::run($query);
	$docs = $stmt->fetchObject();
	
	$content = $docs->document;
	$form_desc = $docs->form_desc;
	
	$type = "application/msword";
	$extension = "doc";
	
	if(strpos($content, "verypdf.com") > 0) {
		$type = "pdf";
		$extension = "pdf";
	}
	header("Content-type: " . $type);
	header("Content-Disposition: attachment; filename=file." . $extension);
	echo $content;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
