<?php
      
     /* if(isset($_POST['Import'])) {
        $resultStatus = execute_act_import();
      }*/
        
 
			  error_reporting(E_ALL);
			  $middle_db = isset($GET["db"]);
                $sql = "SELECT CONTACTID
                      ,FULLNAME
                      ,CUST_EAMS01_095807683
                      ,CUST_CaseStatus_094003650
                      ,CUST_WCAB_094426540
                      ,CUST_DOI01_095107818
                      ,CUST_Claim01_095258261
                      ,BIRTHDATE
                  FROM tbl_contact";
                
                $link = mysqli_connect("kustomweb.xyz", "root", "admin527#") or die("Cannot connect to db</br>" . mysqli_error());
                mysqli_select_db($link,$middle_db);

                $destination_db = $middle_db . "_ikase";

                $result = mysqli_query($link,$sql);
                die($result . " - result<br/>" . $destination_db . " " . $sql);
                $arrRows = array();
                $numbs = mysqli_num_rows($result);
                for ($i = 0; $i < $numbs; $i++) {

                  $row = $result -> fetch_assoc();
                  
                  $contact_id = $row["CONTACTID"];
                  $name = $row["FULLNAME"];
                  if (strpos($name, "'")) {
                    $name = str_replace("'", '|', $name);
                  }
                  $adj = $row["CUST_EAMS01_095807683"];
                  $status = $row["CUST_CaseStatus_094003650"];
                  if (strpos($status, "'")) {
                    $status = str_replace("'", '-', $status);
                  }
                  $venue = $row["CUST_WCAB_094426540"];
                  $doi = $row["CUST_DOI01_095107818"];
                  if (strpos($doi, "'")) {
                    $doi = str_replace("'", ';', $doi);
                  }
                  $claim = $row["CUST_Claim01_095258261"];
                  $dob = $row["BIRTHDATE"];


                  // $contact_id = mysqli_result($result, $i, "CONTACTID");
                  // $name = mysqli_result($result, $i, "FULLNAME");
                  // if (strpos($name, "'")) {
                  //   $name = str_replace("'", '|', $name);
                  // }
                  // $adj = mysqli_result($result, $i, "CUST_EAMS01_095807683");
                  // $status = mysqli_result($result, $i, "CUST_CaseStatus_094003650");
                  // if (strpos($status, "'")) {
                  //   $status = str_replace("'", '-', $status);
                  // }
                  // $venue = mysqli_result($result, $i, "CUST_WCAB_094426540");
                  // $doi = mysqli_result($result, $i, "CUST_DOI01_095107818");
                  // if (strpos($doi, "'")) {
                  //   $doi = str_replace("'", ';', $doi);
                  // }
                  // $claim = mysqli_result($result, $i, "CUST_Claim01_095258261");
                  // $dob = mysqli_result($result, $i, "BIRTHDATE");

                  //echo $contact_id .'</br>';
                  $row = $contact_id . ", " . $name . ", " . $adj . ", " . $status . ", " . $venue . ", " . $doi . ", " . $claim . ", " . $dob; 
                  array_push($arrRows, $row);
                  $sql_insert = "INSERT INTO `" . $destination_db . "`.`contact_adj_import` (`contact_id`, `full_name`, `adj_number`, `case_status`, `venue`, `doi`, `claim`, `contact_dob`) 
                  VALUES
                  ('" . $contact_id . "', '" . $name . "', '" . $adj . "', '" . $status . "', '" . $venue . "', '" . $doi . "', '" . $claim . "', '" . $dob . "');";
                  //die($sql_insert);
                  //$link_insert = mysqli_connect("127.0.0.1", "terriel", "tdm1966tdm") or die("Cannot connect to db</br>" . mysqli_error());
                  echo $contact_id . ' - ' . $name . ' - ' . $adj . ' - ' . $status . ' - ' . $venue . ' - ' . $doi . ' - ' . $claim . ' - ' . $dob;
                  $result_insert = mysqli_query($link,$sql_insert) or die(mysqli_error());
                }
                $sql_count = "SELECT *
                  FROM `" . $destination_db . "`.`contact_adj_import`";
                  
                $result_count = mysqli_query($link,$sql_count);
                //die($result);
                //$arrRows = array();
                //$numbs_count = mysqli_num_rows($result_count);
                  
                //echo "Done - " . $numbs_count; 
                die(print_r($arrRows));

                //return "ACT Import Executed";
   
    ?>
