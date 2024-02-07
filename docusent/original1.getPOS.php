<?php
    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

    error_reporting(-1);
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);

    include("cls_docucents.php");
    include("../api/connection.php");
    
    if ($_GET && $_GET['vendor_submittal_id']) {
        $api_key = getCustomerDocucentsAPIKey($_GET['user_customer_id']);
        $vendor_id = $_GET['vendor_submittal_id'];
        $obj = new docucents("cmd",$api_key);
        $pdfcode = $obj->GetPOS($vendor_id);
        if($pdfcode && trim($pdfcode)){
        header("Content-type: pdf");
        header("Content-Disposition: attachment; Filename = fullapp_".trim(strtolower($vendor_id)).".pdf");
        echo $pdfcode;
    }else{
        echo "POS has not been generated yet please try again later.";
    }
}
   
    ?>