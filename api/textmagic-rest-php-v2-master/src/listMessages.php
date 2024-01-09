<?php

require_once('save.php');


$conn = getConnection();

$query = $conn->query("SELECT COUNT(DISTINCT sender,receiver) FROM sms_notes");
$totalRecords = $query->fetch_row()[0];
  
$length = "";
$start = "";

if (isset($_GET['length']) && isset($_GET['start'])) {
  $length = $_GET['length'];
  $start = $_GET['start'];
}

  
$sql = "SELECT notes_id, sender,receiver,dateandtime, note,customer_id FROM sms_notes ";
  
if (isset($_GET['search']) && !empty($_GET['search']['value'])) {
    $search = $_GET['search']['value'];
    $sql .= sprintf(" WHERE sender like '%s' OR receiver like '%s' OR note like '%s'", 
    
    '%'.$conn->real_escape_string($search).'%', '%'.$conn->real_escape_string($search).'%', '%'.$conn->real_escape_string($search).'%');
}
 
$sql .= "ORDER BY notes_id DESC";
$sql .= " LIMIT $start, $length" ;

$query = $conn->query($sql);
$result = [];
while ($row = $query->fetch_assoc()) {
    $result[] = [
        $row['sender'],
        $row['receiver'],
        $row['customer_id'],    
        $row['dateandtime'],
        $row['note'],
            
    ];
}
//"<a href='edit.php?id=".$row['notes_id']."'>Edit</a> | <a href='delete.php?id=".$row['notes_id']."''>Delete</a>"
 
echo json_encode([
    'draw' => $_GET['draw'],
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $totalRecords,
    'data' => $result,
]);



// $sql = "SELECT * FROM sms_notes order By sender";
// $result = $conn->query($sql);
// $smsArray = array();
//     while($row =mysqli_fetch_assoc($result))
//     {
//         $smsArray[] = $row;
//     }
// echo json_encode($smsArray);

  
  

$conn->close();




?>


