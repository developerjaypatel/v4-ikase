<?php
$conn = new mysqli("localhost","root","","ikase");

// Check connection
if ($conn -> connect_errno) {
  echo "Failed to connect to MySQL: " . $conn -> connect_error;
  exit();
}
?>

<!DOCTYPE html>
<html>
<body>
<?php
$xml=simplexml_load_file("case_file5.xml") or die("Error: Cannot create object");


$sql = "INSERT INTO`cse_partie_type`( `partie_type`)
         VALUES ('".$xml->parties->party->type."' )";





if (mysqli_query($conn, $sql)) {
  echo "New Record Inserted Successfully";
} else {
  echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}




?>
</body>
</html>






<!-- INSERT INTO `cse_partie_type`(`partie_type_id`, `partie_type`, `employee_title`, `blurb`, `color`, `show_employee`, `adhoc_fields`, `sort_order`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5],[value-6],[value-7],[value-8]) -->