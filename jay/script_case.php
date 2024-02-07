<?php
$servername = "localhost";
$username = "NewAdmin";
$password = "NewAccess527!";
$con = mysql_connect($servername,$username,$password);

if($con)
{
   	$db=mysql_select_db("ikase_darshan");
    if($db)
    {
    	$xml=simplexml_load_file("case_file5.xml") or die("Error: Cannot create object");

        $case_created=$xml->created=='' ? '0000-00-00': gmdate('Y-m-d', strtotime($xml->created));
        $case_closeddate= $xml->{'date-closed'}=='' ? '0000-00-00': gmdate('Y-m-d', strtotime($xml->{'date-closed'}));
        $case_enterdate= $xml->{'date-entered'}=='' ? '0000-00-00': gmdate('Y-m-d', strtotime($xml->{'date-entered'}));
        $case_strreplace= str_replace("'", "''", $xml->type);
        $case_uuid = uniqid('KS') ;
        
        $case_sql = "INSERT INTO `cse_case`(`case_uuid`,`case_number`, `file_number` , `case_name`, `source`, `case_date`, `filing_date`, `terminated_date`, `case_type` ,`file_location` ,`supervising_attorney`, `attorney`,`customer_id`)
         VALUES ('".$case_uuid."' , '". $xml->{'file-number'}."','". $xml->{'case-file-number'}."', '". $xml->name."','meruscase',
      '".$case_created."' , '".$case_enterdate."' , '".$case_closeddate."' ,'".$case_strreplace."' ,'".$xml->{'file-location'}."' ,'".$xml->{'attorney-handling-initials'}."' ,'".$xml->{'secretary-handling-initials'}."','1141' )";

        $case_result=mysql_query($case_sql);
        if($case_result){
            $injury_closeddate= $xml->injuries->injury->{'date-of-injury-end'}=='' ? '0000-00-00': gmdate('Y-m-d', strtotime($xml->injuries->injury->{'date-of-injury-end'}));
            $injury_enterdate= $xml->injuries->injury->{'date-of-injury'}=='' ? '0000-00-00': gmdate('Y-m-d', strtotime($xml->injuries->injury->{'date-of-injury'}));
            $injury_number=$xml->injuries->injury->{'injury-type-id'}=='' || $xml->injuries->injury->{'injury-type-id'}== null ? 0 : $xml->injuries->injury->{'injury-type-id'};

            $injury_uuid = uniqid('KS');

            $injury_sql = "INSERT INTO`cse_injury`( `injury_uuid`,`injury_number`,`adj_number`, `occupation`, `start_date`, `end_date`,`body_parts`,`customer_id`)
                    VALUES ('".$injury_uuid."' ,'". $injury_number."','". $xml->injuries->injury->{'eams-case-number'}."','".$xml->injuries->injury->{'q1-occupation'}."', '". $injury_enterdate."','". $injury_closeddate."','".$xml->injuries->injury->{'q1-injury-description-1'}."' ,'1141')";

            $injury_result=mysql_query($injury_sql);
            if($injury_result){
                $user_uuid=uniqid('KS');
                $case_injury_uuid =uniqid('KS');
                $last_updated_date=date("Y-m-d");

               // echo $case_injury_lastdate;
               $cse_case_inj_sql =  "INSERT INTO `cse_case_injury`(`case_injury_uuid`,`injury_uuid`, `case_uuid`,`last_updated_date`,`last_update_user`, `attribute`, `customer_id`)
               VALUES ('".$case_injury_uuid."','".$injury_uuid ."' ,'". $case_uuid."','". $last_updated_date."','". $user_uuid."','main','1141' )";
                $cse_case_inj_result=mysql_query($cse_case_inj_sql);
                if($cse_case_inj_result){
                    $lng=$xml->parties->party->contact->{'geo-lng'}=='' ? '00': $xml->parties->party->contact->{'geo-lng'};
                    $lat=$xml->parties->party->contact->{'geo-lat'}=='' ? '00': $xml->parties->party->contact->{'geo-lng'};
                    $phone=$xml->parties->party->contact->addresses->address->{'phone-deprecated'};
                    $email=$xml->parties->party->contact->addresses->address->{'email-deprecated'};
                    $fax=$xml->parties->party->contact->addresses->address->{'fax-deprecated'};
                    if($phone==''||$email==''||$fax==''){
                       $alter_phone=$xml->parties->party->contact->addresses->address->{'alternate-phone-deprecated'};
                       $phone=$alter_phone;
                       $alter_email=$xml->parties->party->contact->addresses->address->{'alternate-email-deprecated'};
                       $email=$alter_email;
                       $alter_fax=$xml->parties->party->contact->addresses->address->{'alternate-fax-deprecated'};
                       $fax=$alter_fax;                       
                    }else{
                      $phone; $email; $fax;
                    }
                    //echo $phone;

                    $corporation_uuid = uniqid('KS') ;
                    $parent_corporation_uuid = uniqid('KS') ;

                 $corporation_sql = "INSERT INTO`cse_corporation`( `corporation_uuid`,`parent_corporation_uuid`,`full_name`, `type`, `first_name`, `last_name`,  `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `phone`, `email`, `fax`, `ssn`, `dob`,`customer_id`)
                         VALUES ('".$corporation_uuid."' ,'".$parent_corporation_uuid."' ,'". $xml->name."','". $xml->parties->party->type."','".$xml->parties->party->contact->{'first-name'}."', '". $xml->parties->party->contact->{'last-name'}."',  '".$lng."' , '".$lat."' , '".$xml->parties->party->contact->addresses->address->{'street-1'}."' ,'".$xml->parties->party->contact->addresses->address->city."' ,'".$xml->parties->party->contact->addresses->address->state."' ,'".$xml->parties->party->contact->addresses->address->zip."' ,'".$phone."' ,'".$email."' ,'".$fax."' ,'".$xml->parties->party->contact->ssn."' ,'".$xml->parties->party->contact->dob."','1141' )";
                     $corporation_result=mysql_query($corporation_sql);
                     if($corporation_result){
                      $cse_corporation_uuid = uniqid('KS') ;

                    $cse_case_corporation_sql = " INSERT INTO `cse_case_corporation`( `case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`) VALUES ('".$cse_corporation_uuid."' ,'".$case_uuid."' ,'".$corporation_uuid."' ,'".$injury_uuid."' ,'".$xml->parties->party->type."' ,'". $last_updated_date."','". $user_uuid."','1141')";

                    $cse_case_corporation_result=mysql_query($cse_case_corporation_sql);
                    if($cse_case_corporation_result){
                        $cse_partie_type_select_sql = "SELECT * FROM `cse_partie_type` WHERE partie_type='".$xml->parties->party->type."'";
                        $count_type=false;
                       $cse_partie_type_sql_result=mysql_query($cse_partie_type_sql);
                       if($cse_partie_type_sql_result){
                       while ($row = $result -> fetch_row()) {
                                  //echo print_r($row);
                                    $count_type=true;
  
                                }
                              
                                                  
                        if($count_type==false){
  
                         $cse_partie_type_insert_sql = "INSERT INTO`cse_partie_type`( `partie_type`,`blurb`)
                               VALUES ('".$xml->parties->party->type."','".$xml->parties->party->type."' )";
                             $cse_partie_type_insert_sql_result=mysql_query($cse_partie_type_insert_sql);
                               if ($cse_partie_type_insert_sql_result) {
                                echo "success";                      
                      } else{
                            echo "something wrong in cse_partie_type_insert_sql".mysql_error();
                      }
                    }}
                         if($cse_partie_type_sql_result){

                         $cse_corporation_adhoc_uuid=uniqid('KS');
                         $cse_corporation_adhoc_sql = "INSERT INTO`cse_corporation_adhoc`( `adhoc_uuid`,`case_uuid`,`corporation_uuid`,`adhoc`,`adhoc_value`,`customer_id`)
                         VALUES ('".$cse_corporation_adhoc_uuid."','".$case_uuid."','".$corporation_uuid."','','','1141' )";
                         $cse_corporation_adhoc_result=mysql_query($cse_corporation_adhoc_sql);
                         if($cse_corporation_adhoc_result){
                          echo success;
                        }else{
                           echo "something wrong in cse_corporation_adhoc_sql".mysql_error();
                        }
                            }else{
                                echo "something wrong in cse_partie_type_select_sql".mysql_error();
                            }
                    }else{
                      echo "something wrong in cse_case_corporation".mysql_error();
                    }
                }else {
                   echo "something wrong in cse_corporation".mysql_error();

               } 

               }else {
                   echo "something wrong in cse_case_inj".mysql_error();

               }

            }else {
                echo "some thing wrong in cse_injury";
            }     

        }else {
            echo "some thing wrong in  cse_case script";
        }			
	} 		
} else {
	die("connection die");
}
?>






