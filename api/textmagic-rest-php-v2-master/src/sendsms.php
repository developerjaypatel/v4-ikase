<?php
//namespace TextMagic;
require_once('../vendor/autoload.php');
require_once('save.php');
// Include database configuration file
require_once ('URL_Shortner/dbConfig.php');

// Include URL Shortener library file
require_once ('URL_Shortner/Shortener.class.php');

use TextMagic\Models\SendMessageInputObject;
use TextMagic\Api\TextMagicApi;
use TextMagic\Configuration;

// put your Username and API Key from https://my.textmagic.com/online/api/rest-api/keys page.
$config = Configuration::getDefaultConfiguration()
    ->setUsername('thomassmith10')
    ->setPassword('tMCYOBbjU3bUN2O9EGLjXDTDnNDqEv');

$api = new TextMagicApi(
    new GuzzleHttp\Client(['verify' => 'D:\iKase.org\api\textmagic-rest-php-v2-master\cacert.pem']),
    $config
);

//['verify' => 'D:\iKase.org\api\textmagic-rest-php-v2-master\cacert.pem']
try{
    $shortener = new Shortener($db);
    //print_r($db->errorInfo());
    //print_r($shortener);
}catch(Exception $e){
    // Display error
    echo $e->getMessage();
}


if(isset($_POST['phone']) || isset($_POST['message']) ){ 

$phone_numbers = $_POST["phone"];
$message = $_POST["message"];



// Simple ping request 
// try {
//     $result = $api->ping();
//     print_r($result);
// } catch (Exception $e) {
//     echo 'Exception when calling TextMagicApi->ping: ', $e->getMessage(), PHP_EOL;
// }

// Send a new message request 
$input = new SendMessageInputObject();
$input->setText($message);
$input->setPhones($phone_numbers);


try {
    
    $result = $api->sendMessage($input);
    $fullPathUploadedFile = "";
   // echo '##############  MESSAGE DELIVERED SUCCESSFULLY ####################################';    
    //print_r($result); 
    //$result = "API_response";
    save_complete_API_response($result);
   // echo '##############  CALLING TO SAVE TO TABLE ####################################';
    $phone_numbers_arr = explode (",", $phone_numbers); 
    foreach($phone_numbers_arr as $phone_number) {
       save_to_notes($message,$phone_number,$fullPathUploadedFile);   
    }     
    
    $phone_numbers_arr = explode (",", $phone_numbers); 
    $target_dir = 'uploads/';
    $temp_dir = 'uploads/temp/';

    if(!$_FILES['fileToUpload']['size'][0] == 0)  {

        foreach ($_FILES["fileToUpload"]["name"] as $k => $v) {               
        
           // echo '##############  SENDING DOWNLOAD LINK OF ATTACHMENT ####################################';
           $target_file =$_FILES["fileToUpload"]["name"][$k] ;
           $temp_file = $_FILES["fileToUpload"]["tmp_name"][$k] ;

            // NEW EDIT START
            foreach($phone_numbers_arr as $phone_number) {
                create_upload_directory($target_dir,$phone_number);                 
                $temp = $temp_dir.$target_file;
                move_uploaded_file($temp_file , $temp);
            }

            foreach($phone_numbers_arr as $phone_number) {
                $folder =  $target_dir.'ClientName_'.$phone_number.'/';
                $a = $folder.$target_file;
                copy($temp_dir.$target_file, $a);
            }

                foreach($phone_numbers_arr as $phone_number) {
                try {
                    // $a = $folder.$target_file;
                    // if (move_uploaded_file($temp_file , $a)) {       
                        $folder1 =  $target_dir.'ClientName_'.$phone_number.'/';         
                     $renamedFile = name_attachment($folder1,$target_file,$phone_number);
                     //return $renamedFile;
                     //echo "The Attached files have been uploaded. ";
                  // }
                 }catch(Exception $e){
                     // Display error
                     echo $e->getMessage();
                 }
           // }

           // moveUploadedFilesToAlFolders($phone_numbers_arr,$folder,$target_file,$temp_file);

           // foreach($phone_numbers_arr as $phone_number) {

                


              //  $renamedFile = moveUploadedFiles(trim($phone_number),$folder,$target_file,$temp_file);

                        //$renamedFile = upload_attachment(trim($phone_number),$target_file,$temp_file);
                        //$fullPathUploadedFile = 'http://localhost/textmagic-rest-php-v2-master/src/'.$renamedFile;  
                        $fullPathUploadedFile = 'https://v2.ikase.org/api/textmagic-rest-php-v2-master/src/'.$renamedFile;              
                        //$textMessage = '<a href ="'.$fullPathUploadedFile.'">Click here for the Attachment</a>';        
                        $shortURL_Prefix = 'https://v2.ikase.org/api/textmagic-rest-php-v2-master/src/URL_Shortner/redirect.php?c='; 

                        try{                
                            $shortCode = $shortener->urlToShortCode($fullPathUploadedFile);
                            $shortURL = $shortURL_Prefix.$shortCode;
                            //send this in SMS
                            //echo 'Short URL: <a href= "'.$shortURL.'" target="_blank">'."ikase.xyx".' </a> ';
                        }catch(Exception $e){
                            echo $e->getMessage();
                        }
                        //$textMessage = '<a href= "'.$shortURL.'" target="_blank">'."ikase.xyx.erwer322zx".' </a> ';

                        $textMessage = $shortURL;
                        $input->setText($textMessage);
                        $input->setPhones($phone_number);
                        $result = $api->sendMessage($input);
                        // echo '##############  DONE SENDING DOWNLOAD LINK OF ATTACHMENT ####################################'; 
                        save_to_notes($textMessage,$phone_number,$fullPathUploadedFile);  

            }
            // NEW EDIT END


           // $result = "API_response";
            save_complete_API_response($result);   
            //print_r($result);
        
            //-------------This code segment to upload attachment to Text Magic and get a hyperlink for the file -------------------------------------------
            //echo $renamedFile;
            // $file = new \SplFileObject($renamedFile);
            // try {
            //     $result = $api->uploadMessageMMSAttachment($file);
            //     print_r($result);
            //     $decoded_json       = json_decode($result, true);    
            //     $linkOfAttachedFile = $decoded_json['href'];            
            //     $fullPathOfAttachedFile = 'https://my.textmagic.com/'.$linkOfAttachedFile;            
            //     echo $fullPathOfAttachedFile;
            // } catch (Exception $e) {
            //     echo 'Exception when calling TextMagicApi->uploadMessageMMSAttachment: ', $e->getMessage(), PHP_EOL;
            // }
            //--------------------------------------------

            //save_to_notes("Click Link for the Attachment" . $fullPathUploadedFile,$phone_number);         
        }    
        //$id = $result['id'];
    }      





//}

return "MESSAGE DELIVERED SUCCESSFULLY";
   
} catch (Exception $e) {
    echo 'Exception when calling TextMagicApi->sendMessage: ', $e->getMessage(), PHP_EOL;
}

}


// Get all outgoing messages request 
// try {
//     $result = $api->getAllOutboundMessages(1, 10);
//     print_r($result);
// } catch (Exception $e) {
//     echo 'Exception when calling TextMagicApi->getAllOutboundMessages: ', $e->getMessage(), PHP_EOL;
// }



//$id = 816986228;

// try {
//     // MessageIn class object
//     $result = $api->getInboundMessage($id);
//     print_r($result);
//     // ...
// } catch (Exception $e) {
//     echo 'Exception when calling TextMagicApi->getInboundMessage: ', $e->getMessage(), PHP_EOL;
// }

