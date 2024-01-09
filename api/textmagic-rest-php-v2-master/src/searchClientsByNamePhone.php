<?php 


require_once('save.php');


$conn = getConnection();

$searchTerm = $_GET['term']; 
 
// Fetch matched data from the database 
$query = $conn->query("SELECT * FROM sms_notes WHERE sender LIKE '%".$searchTerm."%' ORDER BY sender ASC"); 
//$query = $conn->query("SELECT * FROM sms_notes WHERE sender LIKE '%".$searchTerm."%' AND status = 1 ORDER BY sender ASC"); 
 
// Generate array with skills data 
$skillData = array(); 
if($query->num_rows > 0){ 
    while($row = $query->fetch_assoc()){ 
        $data['id'] = $row['notes_id']; 
        $data['value'] = $row['sender']; 
        //$data['value'] = $row['receiver']; 
       // $data['value'] = $row['text']; 
        //$data['value'] = $row['messageTime']; 
        array_push($skillData, $data); 
    } 
} 
 
// Return results as json encoded array 
echo json_encode($skillData); 

?>