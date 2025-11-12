<?php

function AddForDelivery() { 
         
        $charge = array("Standard","None","Special"); 
        $params = new Struct(array( 
            'case_number' => preg_replace("/[^\d]+/", "", uniqid()), 
            'billing_code' => "code-".uniqid(), 
            'file_number' => "file-".uniqid(), 
            'charge' => $this->pickRand($charge), 
            'comment' => "comment ".uniqid(), 
            'pos_wording' => "These are the documents I served on ".date("M D d Y", time())." at ".date("H:i:s.u a", 
time()).".", 
        )); 
         
        $result = $this->client->Submittals->AddForDelivery($params); 
         
         
        //set id for other calls 
        $this->vendorSubmittalID = $result; 
         
         
        //assert 
        $assert = "danger"; 
        if (is_string($result) && $result != "") $assert = "success"; 
         
         
        return array( 
            "params" => $params, 
            "result" => $result, 
            "assert" => $assert, 
        ); 
         
} 
 
 
function AddPartyForDelivery() { 
         
        $params = new Struct(array( 
            'id_number'=>"6E99E1A8DF902D78",    //string    id_number        Case Number or Vendor Submittal ID 
            'company_name'=>"yamaha racing",    //string    company_name    1-30 char max 
            'last_name'=>"josh",                //string    last_name        1-20 char max 
            'first_name'=>"hayes",                //string    first_name        0-30 char max 
            'middle_initial'=>"j",                //string    middle_initial    0 or 1 char max 
            'address1'=>"1384 N Moorpark Rd",    //string    address1        /^.{0,50}$/ 
            'address2'=>"",                        //string    address2        /^.{0,50}$/, suite etc. allowed 
            'city'=>"Thousand Oaks",            //string    city            /^[a-zA-Z -]{0,35}$/   (city && state) || zip 
            'state'=>"ca",                        //string    state            /^[A-Z]{0,2}$/         (city && state) || zip 
            'zip'=>"91360",                        //string    zip                /^[0-9A-Z -]{0,9}$/, 5 or 9 digits, or Canadian 
            'phone'=>"8182922123",                //string    phone            /^[2-9][0-9]{9}$/, 8054840333, anything 
other than digits will be removed 
        )); 
         
        $result = $this->api->call("PartyData.AddForDelivery", $params); 
         
         
        $assert = "danger"; 
        if (is_string($result) || is_int($result)) $assert = "success"; 
 
 
        return array( 
            "params" => $params, 
            "result" => $result, 
            "assert" => $assert, 
        ); 
        } 
 
function AddAttachment() { 
 
        $file_name = "FILE_".rand(1,5).".pdf"; 
        $file_path = "/srv/data/uploads/pdfs/".$file_name; 
        $file = new Zend\XmlRpc\Value\Base64(file_get_contents($file_path)); 
 
        $attachment = array( 
            'file_name' => $file_name, 
            'type' => "title-".uniqid(), 
            'title' => "type-".uniqid(), 
            'unit' => "ADJ", 
            'date' => date("Y-m-d", time()), //"2014-09-23", 
            'author' => "author-".uniqid(), 
            'base64' => $file, 
 
        ); 
                      
        $result = $this->client->Submittals->AddAttachment( 
            $this->vendorSubmittalID, //$this->vendorSubmittalID, //vendor_submittal_id     globally unique ID 
number 
            $attachment, //structure   attachment 
            (bool)rand(0, 1) //boolean     duplex      -OPTIONAL false 
        ); 
 
 
        return array( 
            "params" => array(), 
            "result" => $result, 
            "assert" => "success", 
        ); 
} 
 
function SetSubmittalStatus() { 
     
        /** 
         * Submittals.SetSubmittalStatus 
         * TODO: Get the rest of actions here 
         */ 
        $action = array( 
            'DeletedRecord', 
            'Submitted', 
            'Recalled', 
            'RetrievedForOFile', 
            'SubmitalRetrieved', 
            'RetrievedForPrintDeliver', 
            'RetrievedForEFile', 
            'RetrievedForJetFile', 
        ); 
 
        $r = $this->client->Submittals->SetSubmittalStatus( 
            $this->vendorSubmittalID, //string|integer  vendor_submittal_id     globally unique ID number 
            'Submitted', //$this->pickRand($action), //string          action                  This is one of the "action_name" 
values from "status codes" on Google Docs. 
            array() //array|object    baf                     -OPTIONAL flattened BAF contents 
        ); 
         
         
        //integer         submittal_status_id 
        return $r;          
}

?>