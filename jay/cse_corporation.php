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
	$xml=simplexml_load_file("case_file2.xml") or die("Error: Cannot create object");


 $lng=$xml->parties->party->contact->{'geo-lng'}=='' ? '00': $xml->parties->party->contact->{'geo-lng'};
  $lat=$xml->parties->party->contact->{'geo-lat'}=='' ? '00': $xml->parties->party->contact->{'geo-lat'};


$uuid = uniqid('KS') ;

 $sql = "INSERT INTO`cse_corporation`( `corporation_uuid`,`parent_corporation_uuid`,`full_name`, `type`, `first_name`, `last_name`,  `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `phone`, `email`, `fax`, `ssn`, `dob`)
         VALUES ('".$uuid."' ,'".$uuid."' ,'". $xml->name."','". $xml->parties->party->type."','".$xml->parties->party->contact->{'first-name'}."', '". $xml->parties->party->contact->{'last-name'}."',  '".$lng."' , '".$lat."' , '".$xml->parties->party->contact->addresses->address->{'street-1'}."' ,'".$xml->parties->party->contact->addresses->address->city."' ,'".$xml->parties->party->contact->addresses->address->state."' ,'".$xml->parties->party->contact->addresses->address->zip."' ,'".$xml->parties->party->contact->addresses->address->{'phone-deprecated'}."' ,'".$xml->parties->party->contact->addresses->address->{'email-deprecated'}."' ,'".$xml->parties->party->contact->addresses->address->{'fax-deprecated'}."' ,'".$xml->parties->party->contact->ssn."' ,'".$xml->parties->party->contact->dob."' )";
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






