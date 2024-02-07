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

$created=$xml->created=='' ? '0000-00-00': gmdate('Y-m-d', strtotime($xml->created));
$closeddate= $xml->{'date-closed'}=='' ? '0000-00-00': gmdate('Y-m-d', strtotime($xml->{'date-closed'}));
$enterdate= $xml->{'date-entered'}=='' ? '0000-00-00': gmdate('Y-m-d', strtotime($xml->{'date-entered'}));
$strreplace= str_replace("'", "''", $xml->type);


 $uuid = uniqid('KS') ;

 $sql = "INSERT INTO `cse_case`(`case_uuid`,`case_number`, `file_number` , `case_name`, `source`, `case_date`, `filing_date`, `terminated_date`, `case_type` ,`file_location` ,`supervising_attorney`, `attorney`)
         VALUES ('".$uuid."' , '". $xml->{'file-number'}."','". $xml->{'case-file-number'}."', '". $xml->name."','meruscase',
      '".$created."' , '".$enterdate."' , '".$closeddate."' ,'".$strreplace."' ,'".$xml->{'file-location'}."' ,'".$xml->{'attorney-handling-initials'}."' ,'".$xml->{'secretary-handling-initials'}."' )";

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






