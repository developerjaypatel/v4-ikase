<?php
$servername = "localhost";
$username = "root";
$password = "admin527#";
$dbname = "ikase_dbname"; // change with DB name
$table_count = 0;
$column_count = 0;
$column_names="";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$table_sql="SELECT table_name FROM information_schema.tables WHERE table_schema = '".$dbname."'";
$table_result = $conn->query($table_sql);
$table_names="";
if ($table_result->num_rows > 0) {
    // output data of each row
    while($table_row = $table_result->fetch_assoc()) {
        if($table_names==""){
            $table_names = $table_row['table_name'];
        }else{
            $table_names = $table_names.",".$table_row['table_name'];
        }
        echo "<h3>".$table_row['table_name']."</h3>";
        $sql = "SELECT COLUMN_NAME 
        FROM information_schema.columns 
        WHERE table_schema='".$dbname."' 
        AND table_name='".$table_row['table_name']."' 
        AND COLUMN_NAME LIKE '%id%'
        AND COLUMN_NAME NOT IN (
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = '".$dbname."'
        AND table_name='".$table_row['table_name']."' 
        )";
        // echo $sql;
        $result = $conn->query($sql);

        
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                if($column_names==""){
                    $column_names = $row['COLUMN_NAME'];
                }else{
                    $column_names = $column_names.",".$row['COLUMN_NAME'];
                }
                echo "<br>".$row['COLUMN_NAME'];
                $column_count++;
            }
        } else {
            echo "no column found";
        }
        // echo $column_names."<br>";

        $sql3="ALTER TABLE `".$dbname."`.`".$table_row['table_name']."`   
        ADD KEY (".$column_names.");";
        $result = $conn->query($sql3);
        echo "<br>Result : ".$result."<br><hr><br>";
        $table_count++;
        $column_names="";
    }
} else {
    echo "no table found";
}

echo "<br><h1>Finish</h1>";
echo "Total Tables : ".$table_count."<br>";
echo "Total Column : ".$column_count."<br>";





$conn->close();

?>

