<?php
//class to manage comms
class comm
{
	var $name; 
	var $id;
	var $uuid;
	var $comm;
	var $comm_type;
	var $arrInfo;
	var $verified;
	var $original_company_id;
	//data access
	var $datalink;
	
	function comm () {
		return true;
	}
	function id($id) {
		//give a normal id, set the uuid
		//prep an array
		$this->id=$id;
		if ($this->uuid=="") {
			//get the firm name, role person, phone, fax
			$querycomp = "select comm.uuid
			from `comm`
			where comm.id = '$this->id'";			
			$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
					die ("Unable to get the comm uuid<br>$querycomp<br>" . mysql_error());
			$numberupdate = mysql_Numrows($resultcomp);
			if ($numberupdate > 0) {
				//fill up the array
				$this->uuid=mysql_result($resultcomp,0,"uuid");
			}
		}
	}
	function check($comm) {
		//give a normal id, set the uuid
		//get the firm name, role person, phone, fax
		$querycomp = "select comm.uuid
		from `comm`
		where comm.comm = '$comm'";			
		//echo "$querycomp<br>";
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
				die ("Unable to get the comm uuid<br>$querycomp<br>" . mysql_error());
		$numberupdate = mysql_Numrows($resultcomp);
		$this->uuid = "";
		if ($numberupdate > 0) {
			$this->uuid = mysql_result($resultcomp,0,"uuid");
			//fill up the array
			$this->fetch();
		}
		return ($numberupdate > 0);
	}
	function fetch() {
		if ($this->uuid =="") {
			return "no id";
		}
		//prep the array, though we may no longer need it
		$arrResult["id"]="";
		$arrResult["uuid"]="";
		$arrResult["comm"]="";
		$arrResult["comm_type"]="";
		$arrResult["verified"]="";
		$arrResult["original_company_id"]="";
		
		//get the comm info
		$querycomp = "select distinct comm.id id, 
		comm.uuid uuid, comm.comm, comm.comm_type, comm.verified, comm.original_company_id
		from `comm`
		where uuid = '" . $this->uuid . "'";
		//echo "$querycomp<br>";
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
							die ("Unable to get the latest comm info<br>$querycomp<br>" . mysql_error());
		$numberupdate = mysql_Numrows($resultcomp);
		if ($numberupdate > 0) {
			//fill up the array
			$arrResult["id"]=mysql_result($resultcomp,0,"id");
			$arrResult["uuid"]=mysql_result($resultcomp,0,"uuid");
			$arrResult["comm"]=mysql_result($resultcomp,0,"comm");
			$arrResult["comm_type"]=mysql_result($resultcomp,0,"comm_type");
			$arrResult["verified"]=mysql_result($resultcomp,0,"verified");
			$arrResult["original_company_id"]=mysql_result($resultcomp,0,"original_company_id");
		}
		$this->id = $arrResult["id"];
		$this->uuid = $arrResult["uuid"];
		$this->comm = $arrResult["comm"];
		
		$this->comm_type = $arrResult["comm_type"];
		$this->verified = $arrResult["verified"];
		$this->original_company_id = $arrResult["original_company_id"];
		$this->arrInfo = $arrResult;
		if ($this->comm=="") {
			$this->del();
			$this->id = "";
			$this->uuid = "";
			$this->arrInfo = "";
		}
	}
	function fetch_empire() {
		if ($this->uuid =="") {
			return "no id";
		}
		//prep the array, though we may no longer need it
		$arrResult["id"]="";
		$arrResult["uuid"]="";
		$arrResult["comm"]="";
		$arrResult["comm_type"]="";
		$arrResult["verified"]="";
		$arrResult["original_company_id"]="";
		
		//get the comm info
		$querycomp = "select distinct comm.comm_id id, 
		comm.comm_uuid uuid, comm.comm, comm.comm_type, comm.verified
		from `comm`
		where comm_uuid = :comm_uuid";
		//echo "$querycomp<br>";
		//$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or die ("Unable to get the latest empire comm info<br>$querycomp<br>" . mysql_error());
		//$numberupdate = mysql_Numrows($resultcomp);
		
		try {
			$sql = $querycomp;
			$db = getConnection();
			$stmt = $db->prepare($sql); 
			$stmt->bindParam("comm_uuid", $this->uuid); 
			$stmt->execute();
			$comm = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		//die(print_r($comm));
		if (is_object($comm)) {
			//fill up the array
			$arrResult["id"] = $comm->id;
			$arrResult["uuid"] = $comm->uuid;
			$arrResult["comm"] = $comm->comm;
			$arrResult["comm_type"] = $comm->comm_type;
			$arrResult["verified"] = $comm->verified;
			$arrResult["original_company_id"]="";
			//print_r($arrResult);
		}
		$this->id = $arrResult["id"];
		$this->uuid = $arrResult["uuid"];
		$this->comm = $arrResult["comm"];
		
		$this->comm_type = $arrResult["comm_type"];
		$this->verified = $arrResult["verified"];
		$this->original_company_id = $arrResult["original_company_id"];
		$this->arrInfo = $arrResult;
		if ($this->comm=="") {
			$this->del_empire();
			$this->id = "";
			$this->uuid = "";
			$this->arrInfo = "";
		}
	}
	function insert($blnClean = "true") {
		$blnInsert=true;
		if ($this->comm == "") {
			$blnInsert=false;
		}
		if ($blnClean=="true") {
			$this->clean_uuid();
		}
		if ($blnInsert==true) {
			$this->uuid = uniqid("CO");
			//insert it
			$query="insert into comm (`comm`,`comm_type`";
			if ($this->original_company_id!="") {
				$query .= ",`original_company_id`";
			}
			if ($this->uuid !="") {
				$query .= ", `comm_uuid`"; 	
			}
			$query .= ")"; 
			$query .= " VALUES ('$this->comm','$this->comm_type'";
			if ($this->original_company_id!="") {
				$query .= ", '$this->original_company_id'";
			}
			if ($this->uuid !="") {
				$query .= ", '$this->uuid'"; 	
			}
			$query .= ")";
			//echo "insert: " . $query . "<br>";
			//$result = MYSQL_QUERY($query, $this->datalink) or die("unable to insert new comm<br>$query<br>" . mysql_error());
			//get the newly created id
			try {
				$sql = $query;
				$db = getConnection();
				$stmt = $db->prepare($sql); 
				$stmt->execute();
				$stmt = null; $db = null;
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}
	}
	function clean_uuid() {
		//if any company is not quite inserted
		$querycheck = "select id from comm where uuid = ''";
		$resultcheck = MYSQL_QUERY($querycheck, $this->datalink) or 
			die("unable to check empty uuid comm<br>$query<br>" . mysql_error());
		$numbercheck = mysql_Numrows($resultcheck);
		if ($numbercheck > 0) {
			$xCount = 0;
			$nextCount = 0;
			while ($xCount<$numbercheck){
				$id = mysql_result($resultcheck, $xCount, "id");
				$nextCount = $xCount + 3;
				$uuidtemp = uniqid('0{$nextCount}') ;
				$queryuuid = "update comm set uuid = '" . $uuidtemp . "' where id = '" . $id . "'";
				$resultuuid = MYSQL_QUERY($queryuuid, $this->datalink) or 
					die("unable to fill empty uuid comm<br>$queryuuid<br>" . mysql_error());
				$xCount++;
			}
		}
	}
	function del() {
		if ($this->uuid =="") {
			return "no id";
		} else {
			$query = "delete from `comm` where uuid = '" . $this->uuid . "'";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to delete comm<br>$query<br>" . mysql_error());
		}
		$this->uuid = "";
		$this->id = "";
		$this->arrInfo = "";
	}
	function del_empire() {
		if ($this->uuid =="") {
			return "no id";
		} else {
			$query = "delete from `comm` where comm_uuid = '" . $this->uuid . "'";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to delete comm<br>$query<br>" . mysql_error());
		}
		$this->uuid = "";
		$this->id = "";
		$this->arrInfo = "";
	}
	function update($blnClean = "true") {
		if ($this->comm == "" && $this->comm_type == "") {
			//delete instead of update
			$this->del();
			return false;
		}
		if ($this->uuid =="") {
			$this->insert($blnClean);
		} else {
			$query = "update `comm` set `comm` = '" . $this->comm . "', `comm_type` = '" . $this->comm_type . 
			"', `original_company_id` = '" . $this->original_company_id . "', `verified` = '" . $this->verified . "'
			where `uuid` = '" . $this->uuid . "'";
			//echo "update: " . $query . "<BR>";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to update comm<br>$query<br>" . mysql_error());
		}
	}
	function update_empire($blnClean = "true") {
		if ($this->comm == "" && $this->comm_type == "") {
			//delete instead of update
			//$this->del();
			return false;
		}
		if ($this->uuid =="") {
			$this->insert($blnClean);
		} else {
			$query = "update `comm` 
			SET `comm` = :comm, 
			`comm_type` = :comm_type, 
			`verified` = :verified
			where `comm_uuid` = :comm_uuid";
			//echo "update: " . $query . "<BR>";
			//$result = MYSQL_QUERY($query, $this->datalink) or die("unable to update comm<br>$query<br>" . mysql_error());
			try {
				$sql = $query;
				$db = getConnection();
				$stmt = $db->prepare($sql); 
				$stmt->bindParam("comm", $this->comm);
				$stmt->bindParam("comm_type", $this->comm_type);
				$stmt->bindParam("verified", $this->verified);
				$stmt->bindParam("comm_uuid", $this->uuid); 
				$stmt->execute();
				$stmt = null; $db = null;
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}
	}
	function latest() {
		//prep an array
		$arrResult["id"]="";
		$arrResult["uuid"]="";
		$arrResult["comm"]="";
		$arrResult["comm_type"]="";
		$arrResult["verified"]="";

		$querycomp = "select distinct comm.id id, 
		comm.uuid uuid, comm.comm, comm.comm_type, comm.verified
		from `comm`
		order by id desc
		limit 0,1";
		
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
							die ("Unable to get the latest comm info<br>$querycomp<br>" . mysql_error());
		$numberupdate = mysql_Numrows($resultcomp);
		if ($numberupdate > 0) {
			//fill up the array
			$arrResult["id"]=mysql_result($resultcomp,0,"id");
			$arrResult["uuid"]=mysql_result($resultcomp,0,"uuid");
			$arrResult["comm"]=mysql_result($resultcomp,0,"comm");
			$arrResult["comm_type"]=mysql_result($resultcomp,0,"comm_type");
			$arrResult["verified"]=mysql_result($resultcomp,0,"verified");
		}
		$this->id = $arrResult["id"];
		$this->uuid = $arrResult["uuid"];
		$this->comm = $arrResult["comm"];
		$this->comm_type = $arrResult["comm_type"];
		$this->verified = $arrResult["verified"];
		$this->arrInfo = $arrResult;
	}
}
?>
