<?php
//class to manage events
class events
{
	var $name; 
	var $id;
	var $uuid;
	var $description;
	var $description2;
	var $size;
	var $budget;
	var $dates;
	var $approved_dates;
	var $date_begin;
	var $date_completed;
	var $working_days;
	var $working_seconds;
	var $active;
	var $showcase;
	
	var $arrInfo;
	var $verified;
	var $showcasechecked;
	var $approvedchecked;
	//data access
	var $datalink;
	
	function __construct() {
		return true;
	}
	function find_categories ($sortby = "",$keyword = "", $search_field_name= "", $verified = "") {
		$queryreq = "select distinct events.events_id, 
		events.events_uuid, events.name, events.description, events.description2,
		events.size, events.budget, events.dates, events.date_begin, events.date_completed, events.working_days, events.working_seconds,  
		events.active, events.showcase, events.verified,category.category_id,
		category.name category
		from events
		left outer join events_category lcs
		on events.events_uuid = lcs.events_uuid
		left outer join category 
		on lcs.category_uuid = category.category_uuid";
		if ($keyword != "" && $search_field_name == "") {
			//looking, but across all fields
			$queryreq .= " where events.name like '%$keyword%' or
			events.description like '%$keyword%' or
			events.description2 like '%$keyword%' or
			events.size like '%$keyword%' or
			events.budget like '%$keyword%' or
			events.dates like '%$keyword%' or
			events.date_begin like '%$keyword%' or
			events.date_completed like '%$keyword%' or
			category.name like '%$keyword%'";
		}
		if ($keyword != "" && $search_field_name != "") {
			//looking, but a specific field
			$queryreq .= " where $search_field_name like '%$keyword%'";
		}
		if ($keyword == "" && $verified != "") {
			//looking, but a specific field
			$queryreq .= " where events.verified = '$verified'";
		}
		if ($keyword != "" && $verified != "") {
			//looking, but a specific field
			$queryreq .= " and events.verified = '$verified'";
		}
		if ($sortby!="") {
			$queryreq .= " ORDER BY ". $sortby . " ASC";
		} else {
			$queryreq .= " ORDER BY category.sort_order ASC, category.name ASC, events.name ASC";
		}
		//die ($queryreq . "<br>");
		$resultreq = MYSQL_QUERY($queryreq,$this->datalink) or die ("Unable to get the events list<br>$queryreq<br>" . mysql_error());
		return $resultreq;		
	}
	function search($attributes = "",$sort = "",$filter = "",$filtercolumn = "", $verified = "") {
		$filter = addslashes($filter);
		//find a events or events
		$queryevents = "select distinct events.events_id, 
		events.events_uuid, events.name, events.description, events.description2, events.size, events.budget, events.dates,
		events.date_begin, events.date_completed, events.working_days, events.working_seconds, events.active, events.showcase,  events.verified";
		//do we need attributes		-- DO NOT USE YET
		if ($attributes!="") {
			$subfields = ""; 
			$arrAttributes = explode(",",$attributes);
			$intCounter =0;
			while ($intCounter < count($arrAttributes)) {
				$subtable= $arrAttributes[$intCounter];
				$subconnect= "events_" . $arrAttributes[$intCounter];
				//build sub fields
				if ($subfields=="") {
					$subfields .= $subtable . ".*, " . $subconnect . ".attribute " . $subconnect . "attribute" ;
				} else {
					$subfields .= ", " . $subtable . ".*, " . $subconnect . ".attribute " . $subconnect . "attribute" ;
				}
				//build inner joins
				$inners .= " INNER JOIN " . $subconnect;
				$inners .= " ON events.events_uuid = " . $subconnect . ".events_uuid";
				$inners .= " INNER JOIN " . $subtable;	
				$inners .= " ON " . $subconnect. ". " . $subtable . "_uuid = " . $subtable . ".uuid";
				$intCounter++;
			}
		}
		if ($subfields!="") {
			$queryevents .= "," . $subfields;
		}
		$queryevents .= " from `events` events";
		if ($inners!="") {
			$queryevents .= $inners;
		}
		if ($filter!="" and $filtercolumn!="") {
			$whereClause = " where (events." . $filtercolumn . " = '" . $filter . "')";
		}
		if ($filter!="" and $filtercolumn=="") {
			$whereClause = " where (events.events_id like '%" . $filter . "%' or events.events_uuid like '%" . $filter . "%'
			or events.name like '%" . $filter . "%' or events.size like '%" . $filter . "%' 
			or events.budget like '%" . $filter . "%' or events.dates like '%" . $filter . "%' or events.date_begin like '%" . $filter . "%' 
			or events.date_completed like '%" . $filter . "%' or events.description like '%" . $filter . "%'
			or events.description2 like '%" . $filter . "%')";
		}
		if ($verified == "Y") {
			if ($whereClause=="") {
				$whereClause = " where verified = '" . $verified . "'";
			} else {
				$whereClause .= " and (verified = '" . $verified . "')";
			}
		}
		if ($verified == "N") {
			if ($whereClause=="") {
				$whereClause = " where verified = '" . $verified . "' or verified =''";
			} else {
				$whereClause .= " and (verified = '" . $verified . "'  or verified ='')";
			}
		}
		if ($sort == "") {
			$sortby = " order by `events`.events_id desc";
		} else {
			$sortby = " order by `" . $sort . "`";		
		}
		$queryevents .= $whereClause.$sortby;
		//die ("Query to get the events and their sub info<br>$queryevents<br>");
		//get the list		
		$resultevents = MYSQL_QUERY($queryevents,$this->datalink) or die ("Unable to get the events list<br>$queryevents<br>" . mysql_error());
		return $resultevents;
	}
	function events_volunteers($role_uuid, $attribute) {
		$query = "select attribute2 from events_role where role_uuid = '" . $role_uuid . "'
		and attribute = '" . $attribute . "'";
		$resultevents = MYSQL_QUERY($query,$this->datalink) or die ("Unable to get the events list<br>$queryevents<br>" . mysql_error());
		$numberevents = $resultevents->rowCount();
		$attribute2 = "0";
		if ($numberevents>0) {
			$attribute2 = mysql_result($resultevents, 0, "attribute2");
		} 
		return $attribute2;
	}
	function find($sql) {
		//find links using a sql statement, return the resultset
		$queryreq = $sql;
		//get the list		
		$resultreq = MYSQL_QUERY($queryreq,$this->datalink) or die ("Unable to get the events list<br>$queryreq<br>" . mysql_error());
		return $resultreq;
	}	
	function id($id) {
		//give a normal id, set the uuid
		//prep an array
		$this->id=$id;
		if ($this->uuid=="") {
			//get the events name, role person, phone, fax
			$queryevents = "select events.events_uuid
			from `events` events
			where events.events_id = '$this->id'";			
			$resultevents = MYSQL_QUERY($queryevents,$this->datalink) or 
					die ("Unable to get the events uuid<br>$queryevents<br>" . mysql_error());
			$numberupdate = $resultevents->rowCount();
			if ($numberupdate > 0) {
				//fill up the array
				$this->uuid=mysql_result($resultevents,0,"events_uuid");
				//echo "this->uuid: " . $this->uuid . "<br>";
				$this->fetch();
			}
		}
	}
	function fetch() {
		//get a events using the uuid
		if ($this->uuid =="") {
			return "no id";
		}
		//prep an array
		$arrResult["events_id"]="";
		$arrResult["events_uuid"]="";
		$arrResult["name"]="";
		$arrResult["description"]="";
//		$arrResult["description2"]="";
//		$arrResult["size"]="";
//		$arrResult["budget"]="";
		$arrResult["dates"]="";
		$arrResult["approved_dates"]="";
		$arrResult["date_begin"]="";
		$arrResult["date_completed"]="";
		$arrResult["active"]="";
		$arrResult["working_days"]="";
		$arrResult["working_seconds"]="";
		$arrResult["verified"]="";
		
		//get the events name, role person, phone, fax
		$queryevents = "select distinct events.events_id, 
		events.events_uuid, events.name, events.description, events.dates, events.approved_dates, events.date_begin, events.date_completed, events.working_days, events.working_seconds, events.active, events.verified";
		$queryevents .= " from `events` events";
		$queryevents .= " where events.events_uuid = '$this->uuid'";
		//die($queryevents);
		$resultevents = MYSQL_QUERY($queryevents,$this->datalink) or die ("Unable to get the events info<br>$queryevents<br>" . mysql_error());
		$numberupdate = $resultevents->rowCount();
		if ($numberupdate > 0) {
			//fill up the array
			$arrResult["events_id"]=mysql_result($resultevents,0,"events_id");
			$arrResult["events_uuid"]=mysql_result($resultevents,0,"events_uuid");
			$arrResult["name"]=mysql_result($resultevents,0,"name");
			$arrResult["description"]=mysql_result($resultevents,0,"description");
			$arrResult["approved_dates"]=mysql_result($resultevents,0,"approved_dates");
//			$arrResult["description2"]=mysql_result($resultevents,0,"description2");
//			$arrResult["size"]=mysql_result($resultevents,0,"size");
//			$arrResult["budget"]=mysql_result($resultevents,0,"budget");
			$arrResult["dates"]=mysql_result($resultevents,0,"dates");
			$arrResult["date_begin"]=mysql_result($resultevents,0,"date_begin");
			$arrResult["date_completed"]=mysql_result($resultevents,0,"date_completed");
			$arrResult["active"]=mysql_result($resultevents,0,"active");
			$arrResult["working_days"]=mysql_result($resultevents,0,"working_days");
			$arrResult["working_seconds"]=mysql_result($resultevents,0,"working_seconds");
			$arrResult["verified"]=mysql_result($resultevents,0,"verified");
		}
		//die(print_r($arrResult));
		if ($arrResult["name"] =="") {
			//echo "deleted blank events " . $this->uuid . "<br>";
			$this->clear();
			$this->id = "";
			$this->uuid = "";
		} else {
			$this->id = $arrResult["events_id"];
			$this->uuid = $arrResult["events_uuid"];
			$this->name = $arrResult["name"];
			$this->description = $arrResult["description"];
			$this->approved_dates = $arrResult["approved_dates"];
//			$this->description2 = $arrResult["description2"];
//			$this->size = $arrResult["size"];
//			$this->budget = $arrResult["budget"];
			$this->dates = $arrResult["dates"];
			$this->date_begin = $arrResult["date_begin"];
			$this->date_completed = $arrResult["date_completed"];
			$this->active = $arrResult["active"];
			$this->working_days = $arrResult["working_days"];
			$this->working_seconds = $arrResult["working_seconds"];
			$this->verified = $arrResult["verified"];
			if ($this->active=="Y") {
				$this->activechecked = "checked";
			}
/*			if ($this->showcase=="Y") {
				$this->showcasechecked = "checked";
			}
*/
			if ($this->verified=="Y") {
				$this->approvedchecked = "checked";
			}
			$this->arrInfo = $arrResult;
		}
	}
	function insert() {
		$blnInsert=true;
		if ($this->name =="") {
			$blnInsert=false;
		}
		if ($blnInsert==true) {
			//insert it
			$query="insert into `events` (`name`, `description`, 
			`dates`, `date_begin`, `date_completed`, `working_days`, `working_seconds`, `active`";
			if ($this->id!="") {
				$query .= ", `events_id`";
			}
			$query .= ") VALUES ('" . $this->name . "', '" . $this->description . "', '" . $this->dates . "', '" . $this->date_begin . "', '" . $this->date_completed . "', '" . $this->working_days . "', '" . $this->working_seconds . "',
			'" . $this->active . "'";
			if ($this->id!="") {
				$query .= ", '" . $this->id . "'";
			}
			$query .= ")";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to insert new events<br>$query<br>" . mysql_error());
			//get the newly created id
			if ($this->id!="") {
				$this->uuid = getAutoincrement('events', 'events', $result, $this->datalink,"","","",$this->id);
			} else {
				$this->uuid = getAutoincrement('events', 'events', $result, $this->datalink);
			}
		}
	}
	function del() {
		//delete by uuid
		if ($this->uuid =="") {
			return "no id";
		} else {
			$query = "delete from events where events_uuid = '" . $this->uuid . "'";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to delete events<br>$query<br>" . mysql_error());
		}
		$this->uuid = "";
		$this->id = "";
		$this->arrInfo = "";
	}
	function update() {
		if ($this->name=="") {
			$this->clear();
			return false;
		}
		if ($this->uuid =="") {
			$this->insert();
		} else {
			//update a events's name and verified status
			$query = "update `events` set name = '" . $this->name . "', description = '" . $this->description . "',
			dates = '" . $this->dates . "', date_begin = '" . $this->date_begin . "', 
			date_completed = '" . $this->date_completed . "', working_days = '" . $this->working_days . "', 
			working_seconds = '" . $this->working_seconds . "', 
			active = '" . $this->active . "', verified = '" . $this->verified . "'
			where events_uuid = '" . $this->uuid . "'";
			//echo "<br>" . $query . "<br>";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to update events info<br>$query<br>" . mysql_error());
		}
	}
	function activate() {
		if ($this->uuid !="") {
			//update a events's name and verified status
			$query = "update `events` 
			set active = '" . $this->active . "', 
			approved_dates = '" . $this->approved_dates . "',
			verified = '" . $this->active . "'
			where events_uuid = '" . $this->uuid . "'";
			//echo "<br>" . $query . "<br>";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to update events info<br>$query<br>" . mysql_error());
		}
	}
	function fetch_user_events($user_uuid, $blnCurrentEventID = false) {
		//get the events name, role person, phone, fax
		$queryevents = "select distinct events.events_id, 
		events.events_uuid, events.name, events.description, events.dates, events.date_begin, events.date_completed, events.working_days, events.working_seconds,  events.active, events.verified, uevents.attribute
		FROM `events` events
		INNER JOIN user_events uevents
		ON events.events_uuid = uevents.events_uuid
		WHERE uevents.user_uuid = :user_uuid";
		if ($blnCurrentEventID) {
			$queryevents .= " AND events.events_uuid = :events_uuid";
		}
		$queryevents .= " ORDER BY events.date_begin";
//		echo $queryevents . "<BR>";
		//$resultevents = MYSQL_QUERY($queryevents,$this->datalink) or die ("Unable to get the events user info<br>$queryevents<br>" . mysql_error());
		//return $resultevents;
		try {
			$sql = $queryevents;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("user_uuid", $user_uuid);
			if ($blnCurrentEventID) {
				$stmt->bindParam("events_uuid", $this->uuid);
			}
			$stmt->execute();
			$resultevents = $stmt->fetchAll(PDO::FETCH_OBJ);
			
			return $resultevents;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	}
	function make_selectoptions($attribute = "") {
		if ($sortby != "") {
			$sortby = "name ASC, " . $sortby;
		} else {
			$sortby = "name ASC";
		}
		$resultset = $this->search($attribute,$sortby,$attribute,"company_table");
		$numberdrop = $resultset->rowCount();
		//die("numberdrop: " . $numberdrop);
		$xdrop=0;
		$blnFoundit = false;
		if ($numberdrop>0) {	
			while ($xdrop < $numberdrop)
			{
				$company_id = mysql_result($resultset,$xdrop,"company_id"); 
				$company_uuid = mysql_result($resultset,$xdrop,"company_uuid"); 
				$company = mysql_result($resultset,$xdrop,"name");  
	
				if ($company_uuid==$this->uuid) {
					$selected = " selected";
					$blnFoundit = true;
				} else {
					$selected = "";
				}
//				echo $company . "<br>";
				$optionstring .= "<option value='".$company_id."'" . $selected . ">".$company."</option>"; 
				
				 $xdrop++;
			} // end while 
			if ($blnFoundit==false) {
				//select the empty first choice
				$selected = " selected";
			}
			$optionstring = "<option value=''" . $selected . ">Select a Company</option>" . $optionstring; 
		} // end if 
		return $optionstring;
	}
	function getattribute($table, $attribute = "", $attribute2 = "") {
		//get a events sub value (ie: address), by table and by attribute
		$queryevents = "select `". $table . "_uuid`
		from `events_". $table . "` 
		where `events_uuid` = '" . $this->uuid . "' ";
		if ($attribute != "") {
			$queryevents .= "and `attribute` = '" . $attribute . "'";
		}
		if ($attribute2 != "") {
			$queryevents .= "and `attribute2` = '" . $attribute2 . "'";
		}
//		echo "Unable to get the uuid<br>$queryevents<br>";
		$resultevents = MYSQL_QUERY($queryevents,$this->datalink) or 
					die ("Unable to get the uuid<br>$queryevents<br>" . mysql_error());
		$numberupdate = $resultevents->rowCount();
		if ($numberupdate > 0) {
			if ($numberupdate==1) {
				//get the uuid
				$uuid=mysql_result($resultevents,0,$table . "_uuid");
				//echo $uuid . "<br>";
				return $uuid;
			} else {
				return $resultevents;
			}	
		}
	}
	function getimages($category_uuid = "", $primary_image_uuid = "", $level = "") {
		//get a events images, by category and by level
		$queryevents = "select distinct pji.`image_uuid`, pji.`primary_image_uuid`,
		cats.`category_id`, cats.`category_uuid`, cats.`name` category, pji.`attribute2` level
		from `events_image` pji 
		inner join category cats
		on pji.attribute = cats.name
		where pji.`events_uuid` = '" . $this->uuid . "'";
		if ($category_uuid != "") {
			$queryevents .= "and cats.category_uuid = '" . $category_uuid . "'";
		}
		if ($primary_image_uuid != "") {
			$queryevents .= "and pji.primary_image_uuid = '" . $primary_image_uuid . "'";
		}
		if ($attribute2 != "") {
			$queryevents .= "and pji.`attribute2` = '" . $level . "'";
		}
		$queryevents .= "order by cats.sort_order, pji.attribute, pji.primary_image_uuid, pji.attribute2";
		//echo "get the list of images for this events<br>$queryevents<br>";
		$resultevents = MYSQL_QUERY($queryevents,$this->datalink) or 
					die ("Unable to get the uuid<br>$queryevents<br>" . mysql_error());
		$numberupdate = $resultevents->rowCount();
		if ($numberupdate > 0) {			
			return $resultevents;
		}
	}
	function clear() {
		//remove the events, and its addresses, persons, and comms
		if ($this->uuid =="") {
			return "no id";
		}
		
			
		//delete the events_address connection
		/*
		$querydel = "DELETE from `events_address` where events_uuid = '" . $this->uuid. "' 
		and address_uuid = '" . $address_uuid . "'";
		$resultdel = MYSQL_QUERY($querydel,$this->datalink) or 
			die ("Unable to delete the events_address<br>$querydel<br>" . mysql_error());
		*/
		/*
		//comm
		//delete the events_comm connection
		$querydel = "DELETE from `events_comm` where events_uuid = '" . $this->uuid. "' 
		and comm_uuid = '" . $comm_uuid . "'";
		$resultdel = MYSQL_QUERY($querydel,$this->datalink) or 
			die ("Unable to delete the events_comm<br>$querydel<br>" . mysql_error());

		//delete the events_person connection
		$querydel = "DELETE from `events_person` where events_uuid = '" . $this->uuid. "' 
		and person_uuid = '" . $person_uuid . "'";
		$resultdel = MYSQL_QUERY($querydel,$this->datalink) or 
			die ("Unable to delete the events_person<br>$querydel<br>" . mysql_error());
		*/
		//delete the events
		$this->del();
	}
	
	//end of class code
}
