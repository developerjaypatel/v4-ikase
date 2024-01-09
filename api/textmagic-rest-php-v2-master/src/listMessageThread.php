<?php

require_once('save.php');


$conn = getConnection();


$length = 10;
$start = 0;

if (isset($_GET['length']) && isset($_GET['start'])) {
  $length = $_GET['length'];
  $start = $_GET['start'];
}

  
$sql = "SELECT notes_id, sender,receiver,dateandtime, note,customer_id FROM sms_notes ";
  
if (isset($_GET['phone']) && !empty($_GET['phone'])) {
    $search = $_GET['phone'];
    $sql .= sprintf(" WHERE sender like '%s' OR receiver like '%s' ", 
    
    '%'.$conn->real_escape_string($search).'%', '%'.$conn->real_escape_string($search).'%');
}
 
$sql .= "ORDER BY notes_id DESC";
$sql .= " LIMIT $start, $length" ;

//echo $_GET['phone'];

//echo $sql;

$query = $conn->query($sql);

$sql2 = "SELECT COUNT(DISTINCT sender,receiver) FROM sms_notes";
if (isset($_GET['phone']) && !empty($_GET['phone'])) {
    $search = $_GET['phone'];
    $sql2 .= sprintf(" WHERE sender like '%s' OR receiver like '%s' ", 
    
    '%'.$conn->real_escape_string($search).'%', '%'.$conn->real_escape_string($search).'%');
}
 
$sql2 .= "ORDER BY notes_id DESC";
$sql2 .= " LIMIT $start, $length" ;

$query2 = $conn->query($sql2);
//$totalRecords = $query->fetch_row()[0];
if (mysqli_num_rows($query2)==0) { $totalRecords = 0; }
else{
    $totalRecords = $query2->fetch_row()[0];
}  




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


