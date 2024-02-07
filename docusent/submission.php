<?php
function do_call($host, $request) {
  
    $url = $host;
    $header[] = "Content-type: text/xml";
    $header[] = "Content-length: ".strlen($request);
    
    $ch = curl_init();   
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    
    $data = curl_exec($ch);       
    if (curl_errno($ch)) {
        print curl_error($ch);
    } else {
        curl_close($ch);
        return $data;
    }
}

$host = "https://staging.api.docucents.com/xmlrpc/?api_key=fe80c97ec2711d46:f3c6ccbfde94914a9eb11f8100b89a46";

date_default_timezone_set('America/Los_Angeles');

/* Example attachment check */
if (!is_readable('test.pdf')) {
    echo "Please add arbitrary PDF file into this directory and name it as test.pdf\n";
    exit(-1);
}

/* AddForDelivery */
$submittal = new StdClass();
$submittal->case_number = 'DEU123123';
$submittal->billing_code = '111111';
$submittal->file_number = '222222';
$submittal->charge = 'Standard';
$submittal->comment = 'Test child submission';
$submittal->pos_wording = 'Test child submission';

$request = xmlrpc_encode_request('Submittals.AddForDelivery', $submittal);
$response = do_call($host, $request);
$submittal_id = xmlrpc_decode($response);
echo "Submittal ID: {$submittal_id}\n";

/* AddParty */

// Party 1
$party = new StdClass();
$party->dwc_id = '';
$party->name1 = 'Test Customer';
$party->name2 = 'Test Company';
$party->given_name = 'Test Customer';
$party->middle_initial = '';
$party->address1 = '2211 E LA DENEY WAY';
$party->address2 = '';
$party->city = 'ONTARIO';
$party->state = 'CA';
$party->zip = '91764';
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
echo "Add Party 1";
echo " - " . xmlrpc_decode($response) . "\n";

// Party 2
$party = new StdClass();
$party->dwc_id = '';
$party->name1 = 'Test Customer 2';
$party->name2 = 'Test Company 2';
$party->given_name = 'Test Customer 2';
$party->middle_initial = '';
$party->address1 = '2212 E LA DENEY WAY';
$party->address2 = '';
$party->city = 'ONTARIO';
$party->state = 'CA';
$party->zip = '91764';
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
echo "Add Party 2";
echo " - " . xmlrpc_decode($response) . "\n";


// Attachment 2-1
$base64 = base64_encode(file_get_contents('test.pdf'));
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
echo "Added Attachment 1\n";

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


/* Set Submittal Status */

$api_data = array(
    (string)$submittal_id,
    'Submitted'
);
$request = xmlrpc_encode_request('Submittals.SetSubmittalStatus', $api_data);
$response = do_call($host, $request);
$status = xmlrpc_decode($response);
echo "Set Submittal Status: \n";
print_r($status);
