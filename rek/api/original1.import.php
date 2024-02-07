<?php
$app->post('/debtors/add/:list_id/:uploaded_filename', 'addDebtors');

function addDebtors($list_id, $uploaded_filename) {
	if ($uploaded_filename=="") {
		die("Cannot proceed without uploaded file.");
	} 
    // die(print_r($_SERVER));
	$arrFilename = explode(".", $uploaded_filename);
	$extension = $arrFilename[count($arrFilename)-1];
	if ($extension!="csv") {
		die("You must upload a CSV file.");
	}
	$table_name = "debtor";
	$arrResult = array();
	$arrColumns = array();
	//just in case   
    // die($uploaded_filename);
	$uploaded_filename = str_replace("/home1/infoaccount/public_html/uploads/unprocessed/", "", $uploaded_filename);
	$handle = fopen("/home1/infoaccount/public_html/uploads/unprocessed/" . $uploaded_filename, "r");
    // die(print_r($handle));
	if( $handle ) {
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$arrResult[] = $data;
            // echo print_r($data);
		}
		fclose($handle);
	}
	if (count($arrResult) >0 ) {
		//let's get the field names from csv
		$fields = $arrResult[0];
		
		foreach($fields as $field_key=>$field) {
			$arrColumns[$field_key] = str_replace(" ", "_", $field);
		}
	}
    // die(print_r($arrResult));
	//validate the csv
	$arrValidate = array("first_name", "last_name", "email", "phone", "cellphone", "address1", "city", "state", "zip", "invoiced", "invoice_date", "reason");  //
	foreach($arrValidate as $validator) {
        // echo $validator;
		if (!in_array($validator, $arrColumns)){
            $arrColumns[] = $validator;
        }
    }
    
    // die(print_r($arrResult));
	$list = getListInfo($list_id);
    if(!$list) {
        die("Add list information first");
    }
    $sheet_uuid = $list[0]->uuid;
    
	// die($sheet_uuid);
	$db = getConnection();
	foreach($arrResult as $row_key => $row) {
		//category
		if ($row_key==0) {
            continue;
		}
		// echo print_r($row);
		$arrOutput = array();
		foreach($row as $key => $field) {	
			$current_column = $arrColumns[$key];
			$arrOutput[$current_column] = $field;
		}
		// die(print_r($arrOutput));
        $arrFields = array();
        $arrSet = array();
        foreach ($arrOutput as $fieldname => $value) {
            
            if($fieldname == "address1"){
                $fieldname = "street";
            }
           
            $arrFields[] = "`" . $fieldname . "`";
            
            if ($fieldname=="invoice_date") {
                if ($value=="" || $value=="__/__/____") {
                    $value = "";
                } else {
                    $value = date("Y-m-d H:i:s", strtotime($value));
                }
            }
        
            $arrSet[] = "'" . addslashes($value) . "'";
        }
		//insert
		$pre_debtor_uuid = uniqid("DB", false);
        $debtor_uuid = uniqid("DB", false);
        
        //now insert into pre table
		$sql = "INSERT INTO `tbl_pre_debtors` (`pre_debtor_uuid`, `sheet_uuid`, " . implode(",", $arrFields) . ", `customer_id`)
		VALUES ('" . $pre_debtor_uuid . "', '" . $sheet_uuid . "', " . implode(",", $arrSet) . ", '" . $_SESSION["user_customer_id"] . "')";
		echo $sql . "\r\n";
        
        /*
        
        HERE PUT ALL VERIFICATION LOGIC BEFORE MOVING THE DEBTORS TO TBL_DEBTOR
        
        */
                                                                //pre_debtor_uuid
        $sql_debtor = "INSERT INTO `tbl_debtor` (`debtor_uuid`, `parent_uuid`, " . implode(",", $arrFields) . ", `customer_id`)
                       VALUES ('" . $debtor_uuid . "', '" . $pre_debtor_uuid . "', " . implode(",", $arrSet) . ", " . $_SESSION["user_customer_id"] . ")";
		echo $sql_debtor . "\r\n";        
        
        try{
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            
            $stmt_debtor = $db->prepare($sql_debtor);  
            $stmt_debtor->execute();
        } catch(PDOException $e) {	
            echo '{"error":{"text2":'. $e->getMessage() .'}}'; 
        }	
	}
	$db = null;
} ?>