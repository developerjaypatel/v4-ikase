<?php

require_once('save.php');

    
$conn = getConnection();
   
     // Get search term 
$searchTerm = $_GET['query']; 

   // Fetch matched data from the database 
$query = $conn->query("SELECT customer_id,cus_name,cus_phone FROM cse_customer WHERE (cus_name LIKE '%".$searchTerm."%' OR cus_phone LIKE '%".$searchTerm."%') AND cus_name != '' AND cus_phone != '' ORDER BY cus_name ASC"); 

//SELECT customer_id,cus_name,cus_phone FROM cse_customer WHERE (cus_name LIKE '2%' OR cus_phone LIKE '2%')
//AND cus_name != "" AND cus_phone != "" ORDER BY cus_name ASC
 
// Generate array with skills data 
$skillData = array(); 
if($query->num_rows > 0){ 
    while($row = $query->fetch_assoc()){ 
        $data['id'] = $row['customer_id']; 
        $data['label'] = $row['cus_phone']." | ".$row['cus_name']; 
        $data['value'] = $row['cus_phone']; 
        array_push($skillData, $data); 
    } 
} 
 
// Return results as json encoded array 
echo json_encode($skillData); 






















