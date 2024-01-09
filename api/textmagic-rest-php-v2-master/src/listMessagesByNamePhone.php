<?php

require_once('save.php');

$searchTerm = $_GET['term']; 
$conn = getConnection();

//$sql = "SELECT * FROM cse_notes order By entered_by";
$sql = "SELECT * FROM sms_notes WHERE sender LIKE '%".$searchTerm."%' OR receiver LIKE '%".$searchTerm."%' ORDER BY sender ASC";

$result = $conn->query($sql);

$smsArray = array();
    while($row =mysqli_fetch_assoc($result))
    {
        $smsArray[] = $row;
    }

    echo json_encode($smsArray);

// if ($result->num_rows > 0) {   
  
  
  
  
//   $tmp = null;  
//   while($row = $result->fetch_assoc()) {    
//     echo "<table border=0> <tr> <td>----------------------------</td><td>-------</td></tr>";
   

//     if ($tmp != $row['sender']) {  // determine if a new group / sid value
      
//           // close the previous table
          
//       echo "<tr> <td>Client Phone Number</td><td>". $row["sender"].  "</td></tr>";
//       echo "<tr> <td>----------------------------</td><td>-------</td></tr>";
//       echo "<tr> <td>Date/Time</td><td>". $row["dateandtime"].  "</td></tr>"; 
//       echo "<tr> <td>Message</td><td>". $row["note"].  "</td></tr>";
      
      
     
     

//   }else {
//     echo "<tr> <td>Date/Time</td><td>". $row["dateandtime"].  "</td></tr>"; 
//     echo "<tr> <td>Message</td><td>". $row["note"].  "</td></tr>";
    
//   }
  
//   echo "</table>";
//   $tmp = $row['sender'];   // DUH, I FORGOT TO UPDATE $tmp!

   
    
//   }
  

  

// } else {
//   echo "0 results";
// }


$conn->close();




?>


