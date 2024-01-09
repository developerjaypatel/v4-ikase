<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

include("connection.php");

$sql_statement = $_POST["sql"];
	
$sql = "SELECT `schema_name`
FROM `information_schema`.schemata 
WHERE schema_name LIKE 'ikase%'";

try {
	$schemas = DB::select($sql);
	
	//die(print_r($schemas));
	
	foreach($schemas as $schema) {
		//skip
		if ($schema->schema_name=="ikase_glauber" || $schema->schema_name=="ikase_glauber2") {
			continue;
		}
		$sql = "SELECT '" . $schema->schema_name . "' `database`, pers.* 
		FROM `" . $schema->schema_name . "`.`cse_person` pers
		WHERE full_name = 'ODILON CORIA'";
		
		//echo $sql . "\r\n\r\n";
		$arrSQL[] = $sql . "
		";
	}
	$sql = implode(" UNION ", $arrSQL) . "
	ORDER BY `database`, `last_name`";
	
	die($sql);
	
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
