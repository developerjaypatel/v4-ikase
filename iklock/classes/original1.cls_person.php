<?php
//class to manage persons
class person
{
	var $name; 
	var $id;
	var $uuid;
	var $parent_uuid;
	var $first_name;
	var $last_name;
	var $middle_name;
	var $full_name;
	var $date_of_birth;
	var $ssn;
	var $gender;
	var $office_request;
	var $doi;		//this value is not stored with the person, but can be assigned to a person
	//doi is assignment/person related, as the person may have multiple injuries over time.
	var $rolodex;	//read only
	var $aka;
	var $arrInfo;
	var $verified;
	var $original_company_id;
	
	//data access
	var $datalink;
	
	function person () {
		return true;
	}
	function search($attributes = "",$sort = "",$filter = "",$filtercolumn = "", $verified = "", $blnParent = false) {
		//echo "par:" . $blnParent . "<BR>";
		$filter = addslashes($filter);
		//echo "[" . $this->phone . "]<br>";
		//find a person or persanies
		$querypers = "select distinct pers.person_id, 
		pers.person_uuid, pers.parent_uuid, pers.first_name, pers.middle_name, pers.last_name, pers.full_name, pers.verified";
		//do we need attributes		-- DO NOT USE YET
		$arrAttributeValues = array();
		$arrAttributes = array();
		if ($attributes=="") {
			if ($this->city!="") {
				$arrAttributes[] = "address";
				$arrAttributeValues[] = "";
			}
			if ($this->phone!="") {
				$arrAttributes[] = "comm";
				$arrAttributeValues[] = "phone";
			}
			$attributes = implode(",", $arrAttributes);
		}
		//die($attributes);
		if ($attributes!="") {
			$subfields = ""; 
			$arrAttributes = explode(",",$attributes);
			$intCounter =0;
			$additionalWhereClause = "";
			while ($intCounter < count($arrAttributes)) {
				$subtable= $arrAttributes[$intCounter];
				$subconnect= "person_" . $arrAttributes[$intCounter];
				//build sub fields
				if ($subfields=="") {
					$subfields .= $subtable . ".*, " . $subconnect . ".attribute " . $subconnect . "attribute" ;
				} else {
					$subfields .= ", " . $subtable . ".*, " . $subconnect . ".attribute " . $subconnect . "attribute" ;
				}
				//build inner joins
				$inners .= " LEFT OUTER JOIN " . $subconnect;
				$inners .= " ON pers.person_uuid = " . $subconnect . ".person_uuid";
				$inners .= " LEFT OUTER JOIN " . $subtable;	
				$inners .= " ON " . $subconnect. ". " . $subtable . "_uuid = " . $subtable . "." . $subtable . "_uuid";
				if ($arrAttributeValues[$intCounter]!="") {
					if ($subtable = "comm") {
						$additionalWhereClause .= " AND (person_comm.attribute = 'phone' OR person_comm.attribute = 'email')";
					} else {
						//standard
						$additionalWhereClause .= " AND " . $subconnect . ".attribute = '" .  $arrAttributeValues[$intCounter] . "'";
					}
				}
				$intCounter++;
			}
		}
		if ($subfields!="") {
			$querypers .= "," . $subfields;
		}
		$querypers .= " from `person` pers";
		if ($inners!="") {
			$querypers .= $inners;
		}
		$querypers .= " where 1 ";
		if ($blnParent) {
			$querypers .= " AND pers.person_uuid = pers.parent_uuid";
		}
		if ($this->uuid!="") {
			//just get it
			$whereClause .= " (pers.person_uuid = '" . $this->uuid . "')";
		} else {
			if ($filter!="" and $filtercolumn!="") {
				$whereClause = " (pers." . $filtercolumn . " = '" . $filter . "')";
			}
			if ($filter!="" and $filtercolumn=="") {
				$whereClause = " (pers.person_id like '%" . $filter . "%' or pers.person_uuid = '" . $filter . "'
				or pers.first_name like '%" . $filter . "%' or pers.full_name like '%" . $filter . "%' or pers.last_name like '%" . $filter . "%')";
			}
			if ($verified == "Y") {
				$whereClause .= " (verified = '" . $verified . "')";
			}
			if ($verified == "N") {
				$whereClause .= " (verified = '" . $verified . "'  or verified ='')";
			}
			
			if ($this->city!="") {
				$whereClause .= " OR (address.street like '%" . $this->city . "%'
		OR address.city like '%" . $this->city . "%'
		OR address.zip like '%" . $this->city . "%')";
			}
			if ($this->phone!="" && ($filter!="" and $filtercolumn!="")) {
				$whereClause .= " OR (comm.comm like '%" . $this->phone . "%')";
			}
			if ($this->phone!="" && $filter=="") {
				$whereClause = " comm.comm like '%" . $this->phone . "%'";
			}
			if ($whereClause!="") {
				$querypers .= " AND (";
				$whereClause .= ")";
				$whereClause .= $additionalWhereClause;
			}
		}
		
		if ($sort == "") {
			$sortby = " order by `pers`.person_id desc";
		} else {
			$sortby = " order by " . $sort;		
		}
		$querypers .= $whereClause.$sortby;
		//echo $querypers . "<br>";
		//get the list		
		$resultpers = MYSQL_QUERY($querypers,$this->datalink) or die ("Unable to get the person list<br>$querypers<br>" . mysql_error());
		return $resultpers;
	}
	function getmaster($table) {
		//get a person sub value (ie: address), by table and by attribute
		$querycomp = "select distinct `". $table . "_uuid`
		from `". $table . "_person`  
		where `person_uuid` = '" . $this->uuid . "' ";

		//die ("Unable to get the uuid<br>$querycomp<br>" . mysql_error());
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
					die ("Unable to get the uuid<br>$querycomp<br>" . mysql_error());
		$numberupdate = mysql_Numrows($resultcomp);
		if ($numberupdate > 0) {
			if ($numberupdate==1) {
				//get the uuid
				$uuid=mysql_result($resultcomp,0,$table . "_uuid");
				return $uuid;
				break;
			} else {
				return $resultcomp;
				break;
			}	
		}
	}
	function getrole($table, $table_uuid) {
		//get a person sub value (ie: address), by table and by attribute
		$querycomp = "select distinct `attribute`
		from `". $table . "_person`  
		where `". $table . "_uuid` = '" . $table_uuid . "' ";

		//die ("Unable to get the uuid<br>$querycomp<br>" . mysql_error());
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
					die ("Unable to get the uuid<br>$querycomp<br>" . mysql_error());
		$numberupdate = mysql_Numrows($resultcomp);
		if ($numberupdate > 0) {
			if ($numberupdate==1) {
				//get the uuid
				$attribute=mysql_result($resultcomp,0,$attribute);
				return $attribute;
				break;
			} else {
				return $resultcomp;
				break;
			}	
		}
	}
	function getattribute($table, $attribute) {
		//get a person sub value (ie: address), by table and by attribute
		$querycomp = "select `". $table . "_uuid`
		from `person_". $table . "` 
		where `person_uuid` = '" . $this->uuid . "' ";
		if ($attribute != "") {
			$querycomp .= "and `attribute` = '" . $attribute . "'";
		}
		//die ("Unable to get the uuid<br>$querycomp<br>" . mysql_error());
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
					die ("Unable to get the uuid<br>$querycomp<br>" . mysql_error());
		$numberupdate = mysql_Numrows($resultcomp);
		if ($numberupdate > 0) {
			if ($numberupdate==1) {
				//get the uuid
				$uuid=mysql_result($resultcomp,0,$table . "_uuid");
				return $uuid;
				break;
			} else {
				return $resultcomp;
				break;
			}	
		}
	}
	function id($id) {
		//give a normal id, set the uuid
		//prep an array
		$this->id=$id;
		if ($this->uuid=="") {
			//get the firm name, role person, phone, fax
			$querycomp = "select person.uuid
			from `person`
			where person.id = '$this->id'";			
			$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
					die ("Unable to get the person uuid<br>$querycomp<br>" . mysql_error());
			$numberupdate = mysql_Numrows($resultcomp);
			if ($numberupdate > 0) {
				//fill up the array
				$this->uuid=mysql_result($resultcomp,0,"uuid");
			}
		}
	}
	function ready() {
		$query="update person set office_request='R' where uuid = '" . $this->uuid . "'";
		//die("update assignment<br>$query<br>" . mysql_error());
		$result = MYSQL_QUERY($query, $this->datalink) or die("unable to ready assignment<br>$query<br>" . mysql_error());
		return $result;
	}
	function check($full_name) {
		//give a normal id, set the uuid
			//get the firm name, role person, phone, fax
		$querycomp = "select person.uuid
		from `person`
		where soundex(person.full_name) = soundex('" . str_replace("Esq", "", $full_name) . "')";			
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
				die ("Unable to get the person name<br>$querycomp<br>" . mysql_error());
		$numberupdate = mysql_Numrows($resultcomp);
		//echo $querycomp . "<BR>" . $numberupdate . "<BR>";
		if ($numberupdate > 0) {
			$this->uuid = mysql_result($resultcomp,0,"uuid");
			$this->fetch(); 
		}
		return ($numberupdate > 0);
	}
	function check_strict($full_name) {
		//give a normal id, set the uuid
			//get the firm name, role person, phone, fax
		$querycomp = "select person.uuid
		from `person`
		where person.full_name = '" . str_replace("Esq", "", $full_name) . "'";			
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
				die ("Unable to get the person name<br>$querycomp<br>" . mysql_error());
		$numberupdate = mysql_Numrows($resultcomp);
		//echo $querycomp . "<BR>" . $numberupdate . "<BR>";
		$this->uuid = "";
		if ($numberupdate > 0) {
			$this->uuid = mysql_result($resultcomp,0,"uuid");
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
		$arrResult["person_id"]="";
		$arrResult["firm_name"]="";
		$arrResult["first_name"]="";
		$arrResult["last_name"]="";
		$arrResult["middle_name"]="";
		$arrResult["full_name"]="";
		$arrResult["verified"]="";
		$arrResult["date_of_birth"]="";
		$arrResult["ssn"]="";
		$arrResult["office_request"]="";
		$arrResult["aka"]="";
		$arrResult["verified"]="";
		$arrResult["original_company_id"]="";
		
		//get the person info
		$querycomp = "select distinct person.id id, 
		person.uuid person_id, person.first_name, person.last_name, person.middle_name, person.full_name, 
		person.date_of_birth, person.ssn, person.office_request, person.aka, person.verified, person.original_company_id
		from `person`
		where uuid = '" . $this->uuid . "'";
		
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
							die ("Unable to get the latest person info<br>$querycomp<br>" . mysql_error());
		$numberupdate = mysql_Numrows($resultcomp);
		if ($numberupdate > 0) {
			//fill up the array
			$arrResult["id"]=mysql_result($resultcomp,0,"id");
			$arrResult["person_id"]=mysql_result($resultcomp,0,"person_id");
			$arrResult["first_name"]=mysql_result($resultcomp,0,"first_name");
			$arrResult["last_name"]=mysql_result($resultcomp,0,"last_name");
			$arrResult["middle_name"]=mysql_result($resultcomp,0,"middle_name");
			$arrResult["full_name"]=mysql_result($resultcomp,0,"full_name");
			$arrResult["date_of_birth"]=mysql_result($resultcomp,0,"date_of_birth");
			$arrResult["ssn"]=mysql_result($resultcomp,0,"ssn");
			$arrResult["office_request"]=mysql_result($resultcomp,0,"office_request");
			$arrResult["aka"]=mysql_result($resultcomp,0,"aka");
			$arrResult["verified"]=mysql_result($resultcomp,0,"verified");
			$arrResult["original_company_id"]=mysql_result($resultcomp,0,"original_company_id");
		}
		$this->id = $arrResult["id"];
		$this->uuid = $arrResult["person_id"];
		$this->first_name = $arrResult["first_name"];
		$this->last_name = $arrResult["last_name"];
		$this->middle_name = $arrResult["middle_name"];
		$this->full_name = $arrResult["full_name"];
		$this->date_of_birth = $arrResult["date_of_birth"];
		$this->ssn = $arrResult["ssn"];
		$this->office_request = $arrResult["office_request"];
		$this->aka = $arrResult["aka"];
		$this->verified = $arrResult["verified"];
		$this->original_company_id = $arrResult["original_company_id"];
		$this->arrInfo = $arrResult;
		//last check
		if ($this->first_name == "" && $this->last_name == "" && $this->middle_name == "" && $this->full_name == "") {
			//kill any empty contact
			$this->del();
			$this->id = "";
			$this->uuid = "";
		}
	}
	function fetch_empire($id="") {
		if ($this->uuid =="" && $id=="") {
			return "no id";
		}
		//prep the array, though we may no longer need it
		$arrResult["id"]="";
		$arrResult["person_id"]="";
		$arrResult["parent_id"]="";
		$arrResult["firm_name"]="";
		$arrResult["first_name"]="";
		$arrResult["last_name"]="";
		$arrResult["middle_name"]="";
		$arrResult["full_name"]="";
		$arrResult["verified"]="";
		$arrResult["date_of_birth"]="";
		$arrResult["ssn"]="";
		$arrResult["gender"]="";
		$arrResult["office_request"]="";
		$arrResult["aka"]="";
		$arrResult["verified"]="";

		//get the person info
		$querycomp = "select distinct person.person_id id, 
		person.person_uuid person_id, person.gender, person.first_name, person.last_name, person.middle_name, person.full_name, 
		person.date_of_birth, person.ssn, person.aka, person.verified
		from `person`";
		if ($this->uuid!="") {
			$querycomp .= " where person_uuid = '" . $this->uuid . "'";
		}
		if ($id!="") {
			$querycomp .= " where person_id = '" . $id . "'";
		}
		
		//$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or die ("Unable to get the latest person info<br>$querycomp<br>" . mysql_error());
		//$numberupdate = mysql_Numrows($resultcomp);
		
		try {
			$sql = $querycomp;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$person = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		if(is_object($person)) {
			//fill up the array
			$arrResult["id"] = $person->id;
			$arrResult["person_id"] = $person->person_id;
			$arrResult["first_name"] = $person->first_name;
			$arrResult["last_name"] = $person->last_name;
			$arrResult["middle_name"] = $person->middle_name;
			$arrResult["full_name"] = $person->full_name;
			$arrResult["date_of_birth"] = $person->date_of_birth;
			$arrResult["ssn"] = $person->ssn;
			$arrResult["gender"] = $person->gender;
			$arrResult["aka"] = $person->aka;
			$arrResult["verified"] = $person->verified;
		}
		$this->id = $arrResult["id"];
		$this->uuid = $arrResult["person_id"];
		$this->parent_uuid = $arrResult["parent_id"];
		$this->rolodex = ($this->uuid == $this->parent_uuid);
		$this->first_name = $arrResult["first_name"];
		$this->last_name = $arrResult["last_name"];
		$this->middle_name = $arrResult["middle_name"];
		$this->full_name = $arrResult["full_name"];
		$this->date_of_birth = $arrResult["date_of_birth"];
		$this->ssn = $arrResult["ssn"];
		$this->office_request = $arrResult["office_request"];
		$this->aka = $arrResult["aka"];
		$this->gender = $arrResult["gender"];
		$this->verified = $arrResult["verified"];
		$this->arrInfo = $arrResult;
		//last check
		if ($this->first_name == "" && $this->last_name == "" && $this->middle_name == "" && $this->full_name == "") {
			//kill any empty contact
			$this->del();
			$this->id = "";
			$this->uuid = "";
		}
	}
	function clean_uuid() {
		//if any company is not quite inserted
		$querycheck = "select id from person where uuid = ''";
		$resultcheck = MYSQL_QUERY($querycheck, $this->datalink) or 
			die("unable to check empty uuid person<br>$query<br>" . mysql_error());
		$numbercheck = mysql_Numrows($resultcheck);
		if ($numbercheck > 0) {
			$xCount = 0;
			$nextCount = 0;
			while ($xCount<$numbercheck){
				$id = mysql_result($resultcheck, $xCount, "id");
				$nextCount = $xCount + 3;
				$uuidtemp = uniqid('0{$nextCount}') ;
				$queryuuid = "update person set uuid = '" . $uuidtemp . "' where id = '" . $id . "'";
				$resultuuid = MYSQL_QUERY($queryuuid, $this->datalink) or 
					die("unable to fill empty uuid person<br>$queryuuid<br>" . mysql_error());
				$xCount++;
			}
		}
	}
	function insert($blnPassValues = false,$first_name = "",$last_name = "",$middle_name = "",$full_name = "",
						$date_of_birth = "",$ssn = "",$aka = "") {
		if ($blnPassValues == true) {
			$this->first_name = $first_name;
			$this->last_name = $last_name;
			$this->middle_name = $middle_name;
			$this->full_name = $full_name;
			$this->date_of_birth = $date_of_birth;
			$this->ssn = $ssn;
			$this->aka= $aka;
		}
		if ($this->full_name =="" && (trim($this->first_name) != "" && trim($this->last_name) != "")) {
			if ($this->middle_name!="") {
				$this->full_name = $this->first_name . " " . $this->middle_name . " " . $this->last_name;
			} else {
				$this->full_name = $this->first_name . " " . $this->last_name;
			}
		} 
//		die("full: " . $this->full_name);
		$this->verified= 'N';
		$blnInsert=true;
		if (trim($this->ssn) == "" && trim($this->aka) == "" && trim($this->first_name) == "" && trim($this->last_name) == "" && trim($this->middle_name) == "" && trim($this->full_name) == "") {
			$blnInsert=false;
			$this->uuid =  "";
		}
		//if the record already exists, just fetch

		/*
		if ($blnInsert==true) {
			$query="select uuid from person
			where `first_name` = '$this->first_name'
			and `last_name` = '$this->last_name'
			and `middle_name` = '$this->middle_name'
			and `date_of_birth` = '$this=>date_of_birth'
			and `ssn` = '$this=>ssn'
			and `aka` = '$this->aka'";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to check new person<br>$query<br>" . mysql_error());
			$numberperson = mysql_numrows($result);
			if ($numberperson>0) {
				$this->uuid = mysql_result($result,0,"uuid");
				//cancel the insert
				$blnInsert = false;	
			}
		}
		
		if ($blnInsert==true && $this->uuid != "") {
			//passed a uuid, may be already in there
			$query="select uuid from person
			where `uuid` = '$this->uuid'";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to check new person<br>$query<br>" . mysql_error());
			$numberperson = mysql_numrows($result);
			if ($numberperson>0) {
				//cancel the insert
				$blnInsert = false;	
			}
		}
		*/
		if ($blnInsert==true) {
			$this->clean_uuid();
			//insert it
			$query="INSERT INTO person (`first_name`,`last_name`,`middle_name`,`full_name`,`date_of_birth`,`ssn`,`office_request`, `aka`,`verified`,`original_company_id`";
			if ($this->uuid!="") {
				$query.=", uuid";	
			}
			$query.=")";  
			$query.= " VALUES ('$this->first_name','$this->last_name','$this->middle_name','$this->full_name','$this->date_of_birth','$this->ssn','$this->office_request','$this->aka','$this->verified','$this->original_company_id'";
			if ($this->uuid!="") {
				$query.=",'$this->uuid'";	
			}
			$query.= ")";
			//echo $query . "<Br>";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to insert new person<br>$query<br>" . mysql_error());
			//get the newly created id
			if ($this->uuid=="") {
				$this->uuid = getAutoincrement('person', 'person', $result, $this->datalink);
			}
		}
	}
	function insert_empire() {
		$this->verified= 'N';
		$blnInsert=true;
		if (trim($this->first_name) == "" && trim($this->last_name) == "" && trim($this->middle_name) == "" && trim($this->full_name) == "") {
			$blnInsert=false;
			$this->uuid =  "";
		}
		
		if ($blnInsert==true) {
			//insert it
			$this->uuid = uniqid("PE");
			$query="INSERT INTO person (`first_name`,`last_name`,`middle_name`,`full_name`,`date_of_birth`, `gender`, `ssn`,`aka`,`verified`";
			if ($this->uuid!="") {
				$query.=", person_uuid";	
			}
			$query.=")";  
			$query.= " VALUES (:first_name, :last_name, :middle_name, :full_name, :date_of_birth, :gender, :ssn, :aka, :verified";
			if ($this->uuid!="") {
				$query.=", :uuid";	
			}
			$query.= ")";
//			die($query);
			//$result = MYSQL_QUERY($query, $this->datalink) or die("unable to insert new person<br>$query<br>" . mysql_error());
			//get the newly created id
			try {
				$sql = $query;
				$db = getConnection();
				$stmt = $db->prepare($sql); 
				$stmt->bindParam("first_name", $this->first_name);
				$stmt->bindParam("last_name", $this->last_name);
				$stmt->bindParam("middle_name", $this->middle_name);
				$stmt->bindParam("full_name", $this->full_name);
				$stmt->bindParam("date_of_birth", $this->date_of_birth);
				$stmt->bindParam("gender", $this->gender);
				$stmt->bindParam("ssn", $this->ssn);
				$stmt->bindParam("aka", $this->aka);
				$stmt->bindParam("verified", $this->verified);
				$stmt->bindParam("uuid", $this->uuid);
				$stmt->execute();
				$stmt = null; $db = null;
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
			$this->fetch_empire();
		}
	}
	function del() {
		if ($this->uuid =="") {
			//
		} else {
			$query = "delete from `person` where uuid = '" . $this->uuid . "'";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to delete person<br>$query<br>" . mysql_error());
		}
		$this->uuid = "";
		$this->id = "";
		$this->arrInfo = "";
	}
	function clear() {
		//remove the request, and its addresses, persons, and comms
		if ($this->uuid =="") {
			return "no id";
			break;
		}
		//delete the request_person connection
		$querydel = "DELETE from `request_person` where person_uuid = '" . $this->uuid. "'";
		$resultdel = MYSQL_QUERY($querydel,$this->datalink) or 
			die ("Unable to delete the request_person<br>$querydel<br>" . mysql_error());
		//delete the person_company connection
		$querydel = "DELETE from `company_person` where person_uuid = '" . $this->uuid. "'";
		$resultdel = MYSQL_QUERY($querydel,$this->datalink) or 
			die ("Unable to delete the person_company<br>$querydel<br>" . mysql_error());
		//delete the person_address connection
		$querydel = "DELETE from `person_address` where person_uuid = '" . $this->uuid. "'";
		$resultdel = MYSQL_QUERY($querydel,$this->datalink) or 
			die ("Unable to delete the person_address<br>$querydel<br>" . mysql_error());
		//delete the person_role connection
		$querydel = "DELETE from `person_role` where person_uuid = '" . $this->uuid. "'";
		$resultdel = MYSQL_QUERY($querydel,$this->datalink) or 
			die ("Unable to delete the person_role<br>$querydel<br>" . mysql_error());
		//delete the person_comm connection
		$querydel = "DELETE from `person_comm` where person_uuid = '" . $this->uuid. "'";
		$resultdel = MYSQL_QUERY($querydel,$this->datalink) or 
			die ("Unable to delete the person_comm<br>$querydel<br>" . mysql_error());

		//delete the request
		$this->del();
	}
	function update() {
		if ($this->first_name == "" && $this->last_name == "" && $this->middle_name == "" && $this->full_name == "") {
			//delete instead of update
			$this->del();
			return false;
		}
		if ($this->uuid =="") {
			$this->insert();
		}  else {
			$query = "update `person` set first_name = '" . $this->first_name . "', last_name = '" . $this->last_name . 
			"', middle_name = '" . $this->middle_name . "', full_name = '" . $this->full_name . 
			"', original_company_id = '". $this->original_company_id . "', aka = '" . $this->aka .
			"', ssn = '" . $this->ssn . "', gender = '" . $this->gender . "', date_of_birth = '" . date("Y-m-d", strtotime($this->date_of_birth)) . "'";
			if ($this->verified!="") {
				$query .= ", verified = '" . $this->verified . "'";
			}
			$query .= " where uuid = '" . $this->uuid . "'";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to update person<br>$query<br>" . mysql_error());
		}
	}
	function update_empire() {
		if ($this->first_name == "" && $this->last_name == "" && $this->middle_name == "" && $this->full_name == "") {
			//delete instead of update
			$this->del();
			return false;
		}
		if ($this->uuid =="") {
			$this->insert_empire();
		}  else {
			$query = "update `person` 
			set first_name = '" . $this->first_name . "', last_name = '" . $this->last_name . 
			"', middle_name = '" . $this->middle_name . "', full_name = '" . $this->full_name . 
			"', aka = '" . $this->aka .
			"', ssn = '" . $this->ssn . "', gender = '" . $this->gender . "', date_of_birth = '" . date("Y-m-d", strtotime($this->date_of_birth)) . "'";
			if ($this->verified!="") {
				$query .= ", verified = '" . $this->verified . "'";
			}
			$query .= " where person_uuid = '" . $this->uuid . "'";
			//echo $query . "<BR>";
			//$result = MYSQL_QUERY($query, $this->datalink) or die("unable to update person<br>$query<br>" . mysql_error());
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
	function cascade() {
		//cascade all the values of a parent person to all the children
		$query = "UPDATE `person` 
		SET `first_name` = '" . $this->first_name . "', 
		`last_name` = '" . $this->last_name . "', `middle_name` = '" . $this->middle_name . "',  `full_name` = '" . $this->full_name . "',  `date_of_birth` = '" . $this->date_of_birth . "',  `gender` = '" . $this->gender . "',  `ethnicity` = '" . $this->ethnicity . "',  `ssn` = '" . $this->ssn . "',  `aka` = '" . $this->aka . "',  `bar_number` = '" . $this->bar_number . "',  `code` = '" . $this->code . "',  
		`credits` = '" . $this->credits . "',  `talents` = '" . $this->talents . "',  `type` = '" . $this->type . "',  `sort_order` = '" . $this->sort_order . "',  `verified` = '" . $this->verified . "' 
		WHERE parent_uuid = '" . $this->parent_uuid . "' AND person_uuid != '" . $this->uuid . "'";
		//echo $query . "<BR>";
		$result = MYSQL_QUERY($query, $this->datalink) or die("unable to update persons<br>$query<br>" . mysql_error());
		return $result;
	}
}
?>
