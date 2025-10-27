<?php
//namespace TextMagic;
require_once('../vendor/autoload.php');
require_once('save.php');

use TextMagic\Models\SendMessageInputObject;
use TextMagic\Api\TextMagicApi;
use TextMagic\Configuration;

// put your Username and API Key from https://my.textmagic.com/online/api/rest-api/keys page.
$config = Configuration::getDefaultConfiguration()
    ->setUsername('thomassmith10')
    ->setPassword('tMCYOBbjU3bUN2O9EGLjXDTDnNDqEv');

$api = new TextMagicApi(
    new GuzzleHttp\Client(['verify' => 'D:\ikase.org\api\textmagic-rest-php-v2-master\cacert.pem']),
    $config
);

// $api = new TextMagicApi(
//     new GuzzleHttp\Client(),
//     $config
// );

//#################################################################################################

// $id = 816986228;

// try {
//     // MessageIn class object
//     $result = $api->getInboundMessage($id);
//     print_r($result);
//     save_to_notes($result);
//     // ...
// } catch (Exception $e) {
//     echo 'Exception when calling TextMagicApi->getInboundMessage: ', $e->getMessage(), PHP_EOL;
// }

//#################################################################################################

$page = 1;
$limit = 100;
$orderBy = "id";
$direction = "desc";

try {
    // GetAllInboundMessagesPaginatedResponse class object
    $result = $api->getAllInboundMessages($page, $limit, $orderBy, $direction);
    //echo '##############  REPLY MESSAGES RETRIEVED SUCCESSFULLY ####################################';
    //print_r($result);

    //$response_json  = file_get_contents($result);
    $decoded_json   = json_decode($result, true);    
    $resources      = $decoded_json['resources'];
    foreach($resources as $resource) {

        $messageId      = $resource['id'];
        $senderPhone    = $resource['sender'];
        $receiverPhone  = $resource['receiver'];
        $text           = $resource['text'];
        $messageTime    = $resource['messageTime'];

        $file_path = "No Attachment";

        if (str_contains($text, 'http')) {
            $file_path = $resource['text'];
        }

        //echo '##############  CALLING TO SAVE TO TABLE ####################################';
        save_to_notes($text,$senderPhone,$file_path);
}

} catch (Exception $e) {
    echo 'Exception when calling TextMagicApi->getAllInboundMessages: ', $e->getMessage(), PHP_EOL;
}

//#################################################################################################

// $ids = "sampleValue";
// $query = "sampleValue";
// $orderBy = "id";
// $direction = "desc";
// $expand = 0;

// try {
//     // SearchInboundMessagesPaginatedResponse class object
//     $result = $api->searchInboundMessages($page, $limit, $ids, $query, $orderBy, $direction, $expand);
//     // ...
// } catch (Exception $e) {
//     echo 'Exception when calling TextMagicApi->searchInboundMessages: ', $e->getMessage(), PHP_EOL;
// }

//#################################################################################################

function delete_inbound_messages($id){ 
$id = 1;

try {
    $api->deleteInboundMessage($id);
} catch (Exception $e) {
    echo 'Exception when calling TextMagicApi->deleteInboundMessage: ', $e->getMessage(), PHP_EOL;
}
}
//#################################################################################################

