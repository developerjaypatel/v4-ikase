<?php
//class to manage addresses
class address
{
	var $name; 
	var $id;
	var $uuid;
	var $parent_uuid;	//obsolete
	var $full_address;
	var $formatted_address;
	var $street;
	var $city;
	var $state;
	var $zip;
	var $arrInfo;
	var $verified;
	//data access
	var $datalink;
	var $company_uuid;
	var $rolodex;	//main entry
	
	function __construct() {
		return true;
	}
	function id($id) {
		//give a normal id, set the uuid
		//prep an array
		$this->id=$id;
		if ($this->uuid=="") {
			//get the firm name, role person, phone, fax
			$querycomp = "select address_uuid
			from `address`
			WHERE address_id = '$this->id'";			
			$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
					die ("Unable to get the address uuid<br>$querycomp<br>" . mysql_error());
			$numberUPDATE = $resultcomp->rowCount();
			if ($numberUPDATE > 0) {
				//fill up the array
				$this->uuid=mysql_result($resultcomp,0,"uuid");
			}
		}
	}
	function fetch() {
		if ($this->uuid =="") {
			return "no id";
		}
		//prep the array, though we may no longer need it
		$arrResult["address_id"]="";
		$arrResult["address_uuid"]="";
		$arrResult["parent_uuid"]="";
		$arrResult["street"]="";
		$arrResult["city"]="";
		$arrResult["name"]="";
		$arrResult["state"]="";
		$arrResult["zip"]="";
		$arrResult["verified"]="";
		//get the address info
		$querycomp = "select distinct address_id, 
		#parent_uuid, name,
		address_uuid, address.street, address.city, address.state, address.zip, address.verified
		from `address`
		WHERE address_uuid = :address_uuid
		AND deleted = 'N'";
		
		//$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or die ("Unable to get the latest address info<br>$querycomp<br>" . mysql_error());
		//$numberUPDATE = $resultcomp->rowCount();
		
		try {
			$sql = $querycomp;
			$db = getConnection();
			$stmt = $db->prepare($sql); 
			$stmt->bindParam("address_uuid", $this->uuid);
			$stmt->execute();
			$address = $stmt->fetchObject();
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		if (is_object($address)) {
			//fill up the array
			$arrResult["address_id"] = $address->address_id;
			$arrResult["address_uuid"] = $address->address_uuid;
			//$arrResult["parent_uuid"] = $address->parent_uuid;
			$arrResult["street"] = $address->street;
			$arrResult["city"] = $address->city;
			$arrResult["state"] = $address->state;
			//$arrResult["name"] = $address->name;
			$arrResult["zip"] = $address->zip;
			$arrResult["verified"] = $address->verified;
		}
		$this->id = $arrResult["address_id"];
		$this->uuid = $arrResult["address_uuid"];
		$this->parent_uuid = $arrResult["parent_uuid"];
		$this->rolodex = ($this->uuid == $this->parent_uuid);
		$this->street = $arrResult["street"];
		$this->city = $arrResult["city"];
		$this->name = $arrResult["name"];
		$this->state = $arrResult["state"];
		$this->zip = $arrResult["zip"];
		if ($this->street == "" && $this->city == "" && $this->state == "" && $this->zip == "") {
			//die("error: about to delete address");
			//$this-del();
			$this->id = "";
			$this->uuid = "";
		} else {
			$this->verified = $arrResult["verified"];
			$this->full_address = $this->street . ", " . $this->city . ", " . $this->state . " " . $this->zip;
			$this->formatted_address = $this->street . "<br>" . $this->city . ",&nbsp;" . $this->state . "&nbsp;" . $this->zip;
			$this->arrInfo = $arrResult;
		}
	}
	function insert($blnPassedValues = false,$street = "",$city = "",$state = "",$zip = "") {
		if ($blnPassedValues == true) {
			$this->street = $street;
			$this->city = $city;
			$this->state = $state;
			$this->zip = $zip;
		}
		$blnInsert=true;
		if ($this->street == "" && $this->city == "" && $this->state == "" && $this->zip == "") {
			$blnInsert=false;
		}
		if ($blnInsert==true) {
			$this->uuid = uniqid('AD') ;
			//insert it
			$query="insert into address (address_uuid, street,city,state,zip) 
				VALUES (:address_uuid, :street,:city,:state,:zip)";
			//$result = MYSQL_QUERY($query, $this->datalink) or die("unable to insert new address<br>$query<br>" . mysql_error());
			
			try {
				$sql = $query;
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("address_uuid", $this->uuid);  
				$stmt->bindParam("street", $this->street);  
				//$stmt->bindParam("parent_uuid", $this->parent_uuid);  
				$stmt->bindParam("city", $this->city);  
				//$stmt->bindParam("name", $this->name);  
				$stmt->bindParam("state", $this->state);  
				$stmt->bindParam("zip", $this->zip);    
				$stmt->execute();
				
				$address_id = $db->lastInsertId();
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}
	}
	function del() {
		if ($this->uuid =="") {
			return "no id";
		} else {
			$query = "UPDATE `address` 
			SET deleted = 'N'
			WHERE address_uuid = :uuid";
			//$result = MYSQL_QUERY($query, $this->datalink) or die("unable to delete address<br>$query<br>" . mysql_error());
			try {
				$sql = $query;
				$db = getConnection();
				$stmt = $db->prepare($sql); 
				$stmt->bindParam("uuid", $this->uuid);  
				
				$stmt->execute();
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}
		$this->uuid = "";
		$this->id = "";
		$this->arrInfo = "";
	}
	function update() {
		if ($this->street == "" && $this->city == "" && $this->state == "" && $this->zip == "") {
			//delete instead of update
			//$this->del();
			$this->uuid ="";
			return false;
		}
		if ($this->uuid =="") {
			//die("error: about to insert address");
			$this->insert();
		} else {
			$query = "UPDATE `address` 
			set street = :street, 
			city = :city, 
			state = :state, zip = :zip, verified = :verified
			WHERE address_uuid = :uuid";
			//echo $query ."<br>";
			//$result = MYSQL_QUERY($query, $this->datalink) or die("unable to UPDATE address<br>$query<br>" . mysql_error());
			//die(print_r($this));
			try {
				$sql = $query;
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("street", $this->street);  
				//$stmt->bindParam("parent_uuid", $this->parent_uuid);  
				$stmt->bindParam("city", $this->city);  
				//$stmt->bindParam("name", $this->name);  
				$stmt->bindParam("state", $this->state);  
				$stmt->bindParam("zip", $this->zip);  
				$stmt->bindParam("verified", $this->verified); 
				$stmt->bindParam("uuid", $this->uuid);  
				
				$stmt->execute();
			} catch(PDOException $e) {
				echo $sql . "\r\n";
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}
	}
	function cascade() {
		//cascade all the values of a parent comm to all the children
		$query = "UPDATE `address` 
		set street = '" . $this->street . "', `parent_uuid` = '" . $this->parent_uuid . "', city = '" . $this->city . "', 
			name = '" . $this->name . 
			"', state = '" . $this->state . "', zip = '" . $this->zip . "', verified = '" . $this->verified . "'
		WHERE parent_uuid = '" . $this->parent_uuid . "' AND address_uuid != '" . $this->uuid . "'";
		//echo $query . "<BR>";
		$result = MYSQL_QUERY($query, $this->datalink) or die("unable to UPDATE addresses<br>$query<br>" . mysql_error());
		return $result;
	}
	function search($attributes = "", $sort = "") {
		$querycomp = "select distinct address.address_id, 
		address.address_uuid, address.full_address, address.street, address.city, address.state, address.zip, address.verified";
		if ($this->company_uuid!="") {
			$querycomp.= ",  cper.attribute";
		}
		$querycomp.= " from `address`";
		if ($this->company_uuid!="") {
			$querycomp.= " INNER JOIN company_address cper ON (address.address_uuid = cper.address_uuid AND cper.company_uuid = '" . $this->company_uuid . "')";
		}
		$querycomp.= " order by address.address_id desc";
		//echo $querycomp . "<BR>";
		$resultcomp = mysql_query($querycomp, $this->datalink) or die("unable to list addresses<br>$query<br>" . mysql_error());
		return $resultcomp;
	}
	function getattribute($table, $attribute="", $attribute2="") {
		//get a address sub value (ie: address), by table and by attribute
		$querycomp = "select distinct `". $table . "_uuid`
		from `address_". $table . "` 
		WHERE `address_uuid` = '" . $this->uuid . "' ";
		if ($attribute != "") {
			$querycomp .= " and `attribute` = '" . $attribute . "'";
		}
		if ($attribute2 != "") {
			$querycomp .= " and `attribute2` = '" . $attribute2 . "'";
		}
		//echo "$querycomp<br>";
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
					die ("Unable to get the uuid<br>$querycomp<br>" . mysql_error());
		$numberUPDATE = $resultcomp->rowCount();
		//echo "numb: " . $numberUPDATE . "<br>";
		if ($numberUPDATE > 0) {
			if ($numberupdate==1) {
				//get the uuid
				$uuid=mysql_result($resultcomp,0,$table . "_uuid");
				return $uuid;
			} else {
				return $resultcomp;
			}
		} else {
			//echo "$querycomp<br>";
		}
	}
	function getmaster($table, $attribute = "") {
		//get a address master value (ie: person), by table and by attribute
		$querycomp = "select distinct `". $table . "_uuid`
		from `". $table . "_address`  
		WHERE `address_uuid` = '" . $this->uuid . "' ";
		if ($attribute!="") {
			$querycomp .= " AND attribute = '" . $attribute . "'";
		}

		//echo "<br>$querycomp<br>";
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
					die ("Unable to get the uuid<br>$querycomp<br>" . mysql_error());
		$numberUPDATE = $resultcomp->rowCount();
		if ($numberUPDATE > 0) {
			if ($numberupdate==1) {
				//get the uuid
				$uuid=mysql_result($resultcomp,0,$table . "_uuid");
				return $uuid;
			} else {
				return $resultcomp;
			}
		}
	}
	function latest() {
		//prep an array
		$arrResult["id"]="";
		$arrResult["address_id"]="";
		$arrResult["firm_name"]="";
		$arrResult["street"]="";
		$arrResult["city"]="";
		$arrResult["state"]="";
		$arrResult["zip"]="";
		$arrResult["verified"]="";

		$querycomp = "select distinct address_id, 
		address_uuid, address.street, address.city, address.state, address.zip, address.verified
		from `address`
		order by address_id desc
		limit 0,1";
		
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
							die ("Unable to get the latest address info<br>$querycomp<br>" . mysql_error());
		$numberUPDATE = $resultcomp->rowCount();
		if ($numberUPDATE > 0) {
			//fill up the array
			$arrResult["address_id"]=mysql_result($resultcomp,0,"address_id");
			$arrResult["address_uuid"]=mysql_result($resultcomp,0,"address_uuid");
			$arrResult["street"]=mysql_result($resultcomp,0,"street");
			$arrResult["city"]=mysql_result($resultcomp,0,"city");
			$arrResult["state"]=mysql_result($resultcomp,0,"state");
			$arrResult["zip"]=mysql_result($resultcomp,0,"zip");
			$arrResult["verified"]=mysql_result($resultcomp,0,"verified");
		}
		$this->id = $arrResult["address_id"];
		$this->uuid = $arrResult["address_uuid"];
		$this->street = $arrResult["street"];
		$this->city = $arrResult["city"];
		$this->state = $arrResult["state"];
		$this->zip = $arrResult["zip"];
		$this->verified = $arrResult["verified"];
		$this->arrInfo = $arrResult;
	}
}
