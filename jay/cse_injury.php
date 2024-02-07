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
	$xml=simplexml_load_file("case_file1.xml") or die("Error: Cannot create object");
$closeddate= $xml->injuries->injury->{'permanent-end-date'}=='' ? '0000-00-00': gmdate('Y-m-d', strtotime($xml->injuries->injury->{'permanent-end-date'}));
$enterdate= $xml->injuries->injury->{'permanent-start-date'}=='' ? '0000-00-00': gmdate('Y-m-d', strtotime($xml->injuries->injury->{'permanent-start-date'}));
$injury_number=$xml->injuries->injury->{'injury-type-id'}=='' || $xml->injuries->injury->{'injury-type-id'}== null ? 0 : $xml->injuries->injury->{'injury-type-id'};

 $uuid = uniqid('KS') ;

 $sql = "INSERT INTO`cse_injury`( `injury_uuid`,`injury_number`,`adj_number`, `occupation`, `start_date`, `end_date`,`body_parts`)
         VALUES ('".$uuid."' ,'". $injury_number."','". $xml->injuries->injury->{'eams-case-number'}."','".$xml->injuries->injury->{'q1-occupation'}."', '". $enterdate."','". $closeddate."','".$xml->injuries->injury->{'q1-injury-description-1'}."' )";

$result=mysql_query($sql);
if($result){
  echo "New Record Inserted Successfully";	
}else {
	  echo "some thing wrong";
}

			
		} 
		
	} else {
		die("connection die");
	} //$con close
 // $rowcounts=mysql_num_rows($results);
                                                // $colcounts=mysql_num_fields($results);
												// $rows=mysql_fetch_row($results);



?>






