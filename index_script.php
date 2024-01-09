<?php 

if($stmt = $connection->query("SHOW DATABASES")){
  echo "No of records : ".$stmt->num_rows."<br>";
  while ($row = $stmt->fetch_assoc()) {
	echo $row['Database']."<br>";
  }
}else{
echo $connection->error;
}

?>