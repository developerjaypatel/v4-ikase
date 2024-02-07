<?php

class docucents {

    var $dev_apikey = "4c56ee9a5d1d4093:e42bca0f386157aae42bca0f386157aa"; //dev
    var $dev_apiurl = "https://staging.api.docucents.com/xmlrpc/"; //dev
    var $apikey = "70b5d9ed0de0ca61:0c93b97f3e40685f1f9767809bc0e573"; //live
    var $apiurl = "https://api.docucents.com/xmlrpc/"; //live
    var $vendor_submittal_id = '';
    var $party_address_id;
    var $attachment_error = 0;
    var $submittal_status_id;
    var $attachment_xml;
    var $attachment_params;

    function __construct($loadtype = "cmd",$api_key) {
        if (getenv('APPLICATION_ENV') != "production") {
            if($api_key){}
            $this->apikey = $api_key;
        }else{
            $this->apikey = $this->dev_apikey;
        }
            $this->apiurl = $this->dev_apiurl;
            // print_r($this);die;
    }

    public function SetSubmittalStatus($status = "Submitted", $vendor_submittal_id = "") {
        $host = $this->buildhost();
        $params = array();
        if ($vendor_submittal_id == "") {
            $params[] = $this->vendor_submittal_id;
        } else {
            $params[] = $vendor_submittal_id;
        }
        $params[] = $status;
        $params[] = array();

//        print_r($params);

        $request = xmlrpc_encode_request('Submittals.SetSubmittalStatus', $params);
        $response = $this->do_call($host, $request);

//        die($response);
        $temp = simplexml_load_string($response);
        $retval = isset($temp->params->param->value->string[0]) ? $temp->params->param->value->string[0] : "";
        $this->submittal_status_id = $retval;
        return $this->submittal_status_id;
    }

    public function adddoc() {

        $retdocument = setdocument($docdata);

        $errors = $retdocument["errors"];

        $filedate = date("Ymd", time());
        $filecontent = $retdocument["base64"];
        $fnamepdforig = $retdocument["file"];

        xmlrpc_set_type($filecontent, "base64");

        xmlrpc_set_type($filedate, "datetime");

        $temp = $this->AddAttachment(array(
            "file_name" => "FILE_" . rand(1, 5) . ".pdf",
            "type" => "type-" . uniqid(),
            "title" => "title-" . uniqid(),
            "unit" => "ADJ",
            "date" => $filedate,
            "author" => "author-" . uniqid(),
            "base64" => $filecontent
                ), false);
    }

    public function AddAttachment($attachment, $duplex) {
        $host = $this->buildhost();
        $params = array();

        $params[] = $this->vendor_submittal_id;
        $params[] = $attachment;
        $params[] = $duplex;
        $this->attachment_params = serialize($params);
        $request = xmlrpc_encode_request('Submittals.AddAttachment', $params);
        $response = $this->do_call($host, $request);
        $this->attachment_xml = $response;
        $temp = simplexml_load_string($response);
        //echo "<pre>".print_r($temp)."</pre>";
        $retval = 0;
        if (isset($temp->params->param->value->int)) {
            $retval = (int) $temp->params->param->value->int;
        }
        if ($retval == 0) {
            $this->attachment_error = 0;
            return true;
        } else {
            $this->attachment_error = $retval;
            return false;
        }
        return $response;
    }

    public function PartyData_AddForDelivery($company_name, $first_name, $last_name, $middle_initial, $address1, $address2, $city, $state, $zip, $phone) {

        if (empty($this->vendor_submittal_id)) {
            return "";
        }
        $host = $this->buildhost();
        $params = array();
        $params["id_number"] = $this->vendor_submittal_id;
        $params["company_name"] = $company_name;
        $params["first_name"] = $first_name;
        $params["last_name"] = $last_name;
        $params["middle_initial"] = $middle_initial;
        $params["address1"] = $address1;
        $params["address2"] = $address2;
        $params["city"] = $city;
        $params["state"] = $state;
        $params["zip"] = $zip;
        $params["phone"] = $phone;

        $request = xmlrpc_encode_request('PartyData.AddForDelivery', $params);
        $response = $this->do_call($host, $request);
        $temp = simplexml_load_string($response);
        if (isset($temp->params->param->value->string)) {
            $tempclientid = (string) $temp->params->param->value->string;
        }
        $this->party_address_id = $tempclientid;
        return $this->party_address_id;
    }

    public function Submittals_AddForDelivery($case_number, $billing_code = "", $file_number = "", $charge = "", $comment = "", $pos_wording = "") {

        $host = $this->buildhost();
        $params = array();
        $params["case_number"] = preg_replace("/[^\d]+/", "", $case_number);
        $params["billing_code"] = $billing_code;
        $params["file_number"] = $file_number;
        $params["charge"] = $this->getCharge($charge);
        $params["comment"] = $comment;
        $params["pos_wording"] = $pos_wording; //"These are the documents I served on ".date("M D d Y", time()).".";

        $request = xmlrpc_encode_request('Submittals.AddForDelivery', $params);
        $response = $this->do_call($host, $request);

        $temp = simplexml_load_string($response);

        $retval = isset($temp->params->param->value->string[0]) ? $temp->params->param->value->string[0] : "";
        $this->vendor_submittal_id = (string) $retval;

        return $this->vendor_submittal_id;
    }

    public function Submittles_GetDocument($submittal_id, $docs_id) {
        $host = $this->buildhost();
        $params = array();
        $params[] = (int) $submittal_id;
        $params[] = (int) $docs_id;
        $request = xmlrpc_encode_request('Submittals.GetDocument', $params);
        $response = $this->do_call($host, $request);
        return $response;
    }

    public function GetPackageStatus($check_vendor_submittal_id) {
        $host = $this->buildhost();
        $params = array();
        $params[] = $check_vendor_submittal_id;

        $request = xmlrpc_encode_request('Submittals.GetPackageStatus', $params);
        $response = $this->do_call($host, $request);
        $temp = simplexml_load_string($response);

        $this->member = isset($temp->params->param->value->array->data->value->struct) ? $temp->params->param->value->array->data->value->struct : null;
        return $this->member;
    }

    public function GetPOS($vendor_submittal_id) {
        $host = $this->buildhost();
        $params = array();
        $params[] = $vendor_submittal_id;
//        print_r($params);
        $request = xmlrpc_encode_request('Submittals.GetPOS', $params);
        $response = $this->do_call($host, $request);
        //        echo $response;
        //        die();
        $temp = simplexml_load_string($response);
        $posdoc = "";
        if (isset($temp->params->param->value->struct->member[0]->value->base64)) {
            $posdoc = base64_decode((string) $temp->params->param->value->struct->member[0]->value->base64);
        }
        return $posdoc;
    }

    public function dwcd_PrintCostEstimate($total_parties, $total_pages, $isduplex) {
        $host = $this->buildhost();
        $params = array();
        $params[] = $total_parties;
        $params[] = $total_pages;
        $params[] = $isduplex;

        $request = xmlrpc_encode_request('dwcd.PrintCostEstimate', $params);
        $response = $this->do_call($host, $request);
        return $response;
    }

    public function dwcd_validateKey() {
        $host = $this->buildhost();
        $params = array();
        $params[] = "4c56ee9a5d1d4093:e42bca0f386157aae42bca0f386157aa";
        $request = xmlrpc_encode_request('dwcd.ValidateApiKey', $params);
        $response = $this->do_call($host, $request);
        return $response;
    }

    public function reset() {
        
    }

    //private methods

    public function buildhost() {
        return $this->apiurl . "?api_key=" . $this->apikey;
    }

    private function strlimit128($vstr) {
        if (strlen($vstr) > 128) {
            return substr($vstr, 0, 128);
        } else {
            return $vstr;
        }
    }

    private function getCharge($charge) {
        if ($charge == "None") {
            return "None";
        } elseif ($charge == "Special") {
            return "Special";
        } else {
            return "Standard";
        }
    }

    private function do_call($host, $request, $retry = 0) {

        $url = $host;
        $header[] = "Content-type: text/xml";
        $header[] = "Content-length: " . strlen($request);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            syslog(LOG_ERR, "docucents.php::do_call() : " . curl_error($ch));
            curl_close($ch);

            // retry once more on failure
            if (!$retry)
                return $this->do_call($host, $request, 1);
        } else {
            curl_close($ch);
            return $data;
        }
    }

}

?>