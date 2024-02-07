<?php
include("../eamsjetfiler/datacon.php");
include("../eamsjetfiler/functions.php");

$cus_id = passed_var("cus_id");
$pdf_id = passed_var("pdf_id");

//look up customer
$sql = "SELECT * 
FROM tbl_customer 
WHERE cus_id = :cus_id";

$customer = DB::runOrApiError($sql, ['cus_id' => $cus_id])->fetchObject();

//look up file
$sql = "SELECT * 
FROM tbl_pdf
WHERE pdf_id = :pdf_id";

//FIXME: wrong binding here
$pdf = DB::runOrApiError($sql, ['cus_id' => $cus_id])->fetchObject();
$filename = $pdf->path;

$dev_apikey = "fe80c97ec2711d46:f3c6ccbfde94914a9eb11f8100b89a46";//dev
$dev_apiurl = "https://staging.api.docucents.com/xmlrpc/";//dev
	
$apikey = "70b5d9ed0de0ca61:0c93b97f3e40685f1f9767809bc0e573";//live
$apiurl = "https://api.docucents.com/xmlrpc/";//live
	
$host = $dev_apiurl . "?api_key=" . $dev_apikey;
//$host = "https://staging.api.docucents.com/xmlrpc/?api_key=fe80c97ec2711d46:f3c6ccbfde94914a9eb11f8100b89a46";

date_default_timezone_set('America/Los_Angeles');

/* Example attachment check */
if (!is_readable($filename)) {
    die("The file " . $filename . " does not exist");
}

$billing_code = '111111';
$charge = 'Standard';
$comment = "Test Submission";

/* AddForDelivery */
$submittal = new StdClass();
$submittal->case_number = $pdf->case_id;
$submittal->billing_code = $billing_code;
$submittal->file_number = '222222';
$submittal->charge = 'Standard';
$submittal->comment = $comment;
$submittal->pos_wording = $comment;

$request = xmlrpc_encode_request('Submittals.AddForDelivery', $submittal);
$response = do_call($host, $request);
$submittal_id = xmlrpc_decode($response);
//echo "Submittal ID: {$submittal_id}\n";

/* AddParty */


// Party 1
$party = new StdClass();
$party->dwc_id = '';
$party->name1 = $customer->cus_name;
$party->name2 = $customer->cus_name;
$party->given_name = $customer->cus_name_first .  " " . $customer->cus_name_last;
$party->middle_initial = $customer->cus_name_middle;
$party->address1 = $customer->cus_street;
$party->address2 = '';
$party->city = $customer->cus_city;
$party->state = $customer->cus_state;
$party->zip = $customer->cus_zip;
$party->phone = '';
$party->delivery_pref = '';
$party->role_sub_type = '';

$party_data = array(
    "Submittal",
    $submittal_id,
    "LX",
    $party
);

$request = xmlrpc_encode_request('PartyData.AddPartyV2', $party_data);
$response = do_call($host, $request);
//echo "Add Party 1";
//echo " - " . xmlrpc_decode($response) . "\n";

// Attachment 2-1
$base64 = base64_encode(file_get_contents($filename));
$attachment = new StdClass();
$attachment->file_name = 'attachment_1.pdf';
$attachment->type = '';
$attachment->title = '';
$attachment->unit = '';
$attachment->date = date('Y-m-d');
$attachment->author = 'Tester';
$attachment->base64 = $base64; // TODO file contents

$attach_data = array(
    $submittal_id,
    $attachment,
    false
);
$request = xmlrpc_encode_request('Submittals.AddAttachment', $attach_data);
$response = do_call($host, $request);
//echo "Added Attachment 1\n";

/*
// Attachment 2-2
$base64 = base64_encode(file_get_contents('test.pdf'));
$attachment = new StdClass();
$attachment->file_name = 'attachment_2.pdf';
$attachment->type = '';
$attachment->title = '';
$attachment->unit = '';
$attachment->date = date('Y-m-d');
$attachment->author = 'Tester';
$attachment->base64 = $base64; // TODO file contents

$attach_data = array(
    $submittal_id,
    $attachment,
    false
);
$request = xmlrpc_encode_request('Submittals.AddAttachment', $attach_data);
$response = do_call($host, $request);
echo "Added Attachment 2\n";
*/

/* Set Submittal Status */

$api_data = array(
    (string)$submittal_id,
    'Submitted'
);
$request = xmlrpc_encode_request('Submittals.SetSubmittalStatus', $api_data);
$response = do_call($host, $request);
$status = xmlrpc_decode($response);
//echo "Set Submittal Status: \n";
//print_r($status);
