<?php
//class to manage documents
class document
{
	var $name; 
	var $id;
	var $uuid;
	var $family_uuid;
	var $parent_uuid;	//parent document dictates which letter is brought up from booking agreement
	var $user_uuid;
	var $category_uuid;
	var $location_id;
	var $document_name;
	var $document_date;
	var $expiration_date;
	var $reminder_days;
	var $document_filename;
	var $document_extension;
	var $description;
	var $type;
	var $arrInfo;
	var $verified;
	var $approvedchecked;
	//data access
	var $datalink;
	var $subscriber_uuid;
	
	function __construct() {
		return true;
	}
	function id($id) {
		//give a normal id, set the uuid
		//prep an array
		$this->id=$id;
//		die("id: " . $id);
		if ($this->uuid=="") {
			//get the uuid
			$querycomp = "select document.document_uuid
			from `document`
			where document.document_id = '$this->id'";			
			$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
					die ("Unable to get the document uuid<br>$querycomp<br>" . mysql_error());
			$numberupdate = $resultcomp->rowCount();
			if ($numberupdate > 0) {
				//fill up the array
				$this->uuid=mysql_result($resultcomp,0,"document_uuid");
				$this->fetch();
			}
		}
	}
	function getmaster($table, $attribute = "") {
		//get a document sub value (ie: address), by table and by attribute
		$querycomp = "select distinct `". $table . "_uuid`
		from `". $table . "_document`  
		where `document_uuid` = '" . $this->uuid . "' ";
		if ($attribute != "") {
			$querycomp .= " and attribute = '". $attribute . "'";
		}

		//die ("Unable to get the uuid<br>$querycomp<br>" . mysql_error());
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
					die ("Unable to get the uuid<br>$querycomp<br>" . mysql_error());
		$numberupdate = $resultcomp->rowCount();
		if ($numberupdate > 0) {
			if ($numberupdate==1) {
				//get the uuid
				$uuid=mysql_result($resultcomp,0,$table . "_uuid");
				if ($attribute=="owner") {
					$this->subscriber_uuid=mysql_result($resultcomp,0,$table . "_uuid");
				}
				return $uuid;
			} else {
				if ($attribute=="owner") {
					$this->subscriber_uuid=mysql_result($resultcomp,0,$table . "_uuid");
				}
				return $resultcomp;
			}
		}
	}
	function getattribute($table, $attribute="", $attribute2="") {
		//get a person sub value (ie: address), by table and by attribute
		$querycomp = "select distinct `". $table . "_uuid`
		from `document_". $table . "` 
		where `document_uuid` = '" . $this->uuid . "' ";
		if ($attribute != "") {
			$querycomp .= " and `attribute` = '" . $attribute . "'";
		}
		if ($attribute2 != "") {
			$querycomp .= " and `attribute2` = '" . $attribute2 . "'";
		}
		//echo "$querycomp<br>";
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
					die ("Unable to get the uuid<br>$querycomp<br>" . mysql_error());
		$numberupdate = $resultcomp->rowCount();
		//echo "numb: " . $numberupdate . "<br>";
		if ($numberupdate > 0) {
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
	function getchild() {
		$return = "";
		$query = "SELECT document_uuid 
		FROM document 
		WHERE parent_document_uuid = '" . $this->uuid . "'
		and parent_document_uuid != document_uuid";
//		echo $query . "<BR>";
		$result = mysql_query($query, $this->datalink) or die("unable to get child<BR>" . mysql_error());
		if ($result) {
            if ($result->rowCount() >0) {
				$return = mysql_result($result, 0, "document_uuid");
			}
		}
		return $return;
	}
	function fetch() {
		if ($this->uuid =="") {
			return "no id";
		}
		//prep the array, though we may no longer need it
		$arrResult["document_id"]="";
		$arrResult["document_uuid"]="";
		$arrResult["parent_document_uuid"]="";
		$arrResult["document_name"]="";
		$arrResult["document_date"]="";
		$arrResult["expiration_date"]="";
		$arrResult["reminder_days"]="";
		$arrResult["document_filename"]="";
		$arrResult["document_extension"]="";
		$arrResult["description"]="";
		$arrResult["type"]="";
		$arrResult["verified"]="";
		//get the document. info
		$querycomp = "select distinct document.document_id, 
		document.document_uuid, document.parent_document_uuid, document.document_name, document.document_date, document.expiration_date, document.reminder_days, document.document_filename, document.description, document.type, document.document_extension, document.verified
		from `document`
		where document_uuid = '" . $this->uuid . "'";
		
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
							die ("Unable to get the latest document. info<br>$querycomp<br>" . mysql_error());
		$numberupdate = $resultcomp->rowCount();
		if ($numberupdate > 0) {
			//fill up the array
			$arrResult["document_id"]=mysql_result($resultcomp,0,"document_id");
			$arrResult["document_uuid"]=mysql_result($resultcomp,0,"document_uuid");
			$arrResult["document_name"]=mysql_result($resultcomp,0,"document_name");
			$arrResult["parent_document_uuid"]=mysql_result($resultcomp,0,"parent_document_uuid");
			$arrResult["document_date"]=mysql_result($resultcomp,0,"document_date");
			$arrResult["expiration_date"]=mysql_result($resultcomp,0,"expiration_date");
			$arrResult["reminder_days"]=mysql_result($resultcomp,0,"reminder_days");
			$arrResult["document_filename"]=mysql_result($resultcomp,0,"document_filename");
			$arrResult["document_extension"]=mysql_result($resultcomp,0,"document_extension");
			$arrResult["description"]=mysql_result($resultcomp,0,"description");
			$arrResult["type"]=mysql_result($resultcomp,0,"type");
			$arrResult["verified"]=mysql_result($resultcomp,0,"verified");
		}
		$this->id = $arrResult["document_id"];
		$this->uuid = $arrResult["document_uuid"];
		$this->parent_uuid = $arrResult["parent_document_uuid"];
		$this->document_name = $arrResult["document_name"];
		
		$this->document_date = $arrResult["document_date"];
		$this->expiration_date = $arrResult["expiration_date"];
		$this->reminder_days = $arrResult["reminder_days"];
		$this->document_filename = $arrResult["document_filename"];
		$this->document_extension = $arrResult["document_extension"];
		$this->description = $arrResult["description"];
		$this->type = $arrResult["type"];
		//echo "my: " . $this->type . "<BR>";
		$this->verified = $arrResult["verified"];
		if ($this->verified=="Y") {
			$this->approvedchecked = "checked";
		}
		$this->arrInfo = $arrResult;
		if ($this->document_name=="") {
			$this->del();
			$this->id = "";
			$this->uuid = "";
			$this->arrInfo = "";
		}
	}
	function make_selectoptions() {
//		echo "uuid: " . $this->category_uuid . "<BR>";
		$query = "select distinct document.document_uuid, document.parent_document_uuid, document.document_id, document.document_name 
		from `document`
		inner join document_category pdc
		on document.document_uuid = pdc.document_uuid
		where pdc.category_uuid = '" . $this->category_uuid . "'
		ORDER BY document.document_name";
//		echo $query . "<BR>";
		$resultcategory = MYSQL_QUERY($query, $this->datalink) or 
			die("unable to get category documents<br>$query<br>" . mysql_error());
		$numbercategory = $resultcategory->rowCount();
		if ($numbercategory>0) {
			for ($xprod=0;$xprod<$numbercategory;$xprod++) {
				$current_document_uuid = mysql_result($resultcategory,$xprod,"document_uuid"); 
				$current_parent_document_uuid = mysql_result($resultcategory,$xprod,"parent_document_uuid"); 
				$current_document_id = mysql_result($resultcategory,$xprod,"document_id"); 
				$current_document = mysql_result($resultcategory,$xprod,"document_name"); 
				//echo $this->uuid." == ".$current_document_uuid . "<BR>";
				$selected = "";
				if ($this->uuid == $current_document_uuid) {
					$selected = " selected";
					$blnFoundit = true;
				}
				//options go here
				$optionstring .= '<option value="' .$current_document_uuid. '" ' . $selected . '>'. $current_document_id . "] ". $current_document . '</option>'; 
			}
			if ($blnFoundit==false) {
				//select the empty first choice
				$selected = " selected";
			}
			$optionstring = '<option value=""' . $selected . '>Select from List</option>' . $optionstring; 
		}
		return $optionstring;
	}
	function list_all($family_uuid = "") {
		$querycomp = "CREATE TEMPORARY TABLE `signed_document` (
		  `document_id` int(11) NOT NULL default '0',
		  `document_uuid` varchar(15) NOT NULL default '',
		  `document_name` varchar(255) NOT NULL default '',
		  `document_filename` varchar(255) NOT NULL default '',
		  `signature_name` varchar(255) NOT NULL default '',
		  `signed` enum('Y','N') NOT NULL default 'N'
		) ";
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
							die ("Unable to create temp info<br>$querycomp<br>" . mysql_error());
		$querycomp = "insert into signed_document (document_id, document_uuid, document_name, document_filename)
		select distinct document.document_id, document.document_uuid, 
		document.document_name, document.document_filename from document";
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
							die ("Unable to fill temp info<br>$querycomp<br>" . mysql_error());
		
		$querycomp = "select distinct document.document_id, document.document_uuid, 
		document.document_name, document.document_date, document.expiration_date, document.reminder_days, 
		document.document_filename, 
		document.document_extension, document.description, document.type, 
		signature.signature_id, signature.signature_uuid, signature.signature_name, 
		signature.signature_initials, signature.verified
		from `document`
		left outer join document_signature dsig
		on document.document_uuid = dsig.document_uuid
		left outer join signature
		on dsig.signature_uuid = signature.signature_uuid ";
		if ($family_uuid != "") {
			$querycomp .= " left outer join family_signature fsig
			on signature.signature_uuid = fsig.signature_uuid
			where fsig.family_uuid = '" . $family_uuid . "'";
		}
		$querycomp .= " order by document_name asc";
		//echo $querycomp . "<br>";
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
							die ("Unable to get the latest signature. info<br>$querycomp<br>" . mysql_error());
		$numbercomp = $resultcomp->rowCount();
		for ($intX=0;$intX<$numbercomp;$intX++) {
			$document_uuid = mysql_result($resultcomp,$intX, "document_uuid");
			$signature_name = mysql_result($resultcomp,$intX, "signature_name");
			$queryupdate = "UPDATE `signed_document` SET signed = 'Y',
			signature_name = '" . $signature_name . "' 
			where document_uuid = '" . $document_uuid . "'";
			$resultupdate = MYSQL_QUERY($queryupdate,$this->datalink) or 
							die ("Unable to update signature info<br>$queryupdate<br>" . mysql_error());
		}
		$querycomp = "select * from signed_document";
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
							die ("Unable to get the signatures<br>$querycomp<br>" . mysql_error());
							
		return $resultcomp;
	}
	function count_all($family_uuid = "") {
		$querycomp = "select distinct document.document_id
		from `document`
		inner join document_signature dsig
		on document.document_uuid = dsig.document_uuid
		inner join signature
		on dsig.signature_uuid = signature.signature_uuid ";
		if ($family_uuid != "") {
			$querycomp .= " inner join family_signature fsig
			on signature.signature_uuid = fsig.signature_uuid
			where fsig.family_uuid = '" . $family_uuid . "'";
		}
		//echo $querycomp . "<br>";
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
							die ("Unable to get the latest signature. info<br>$querycomp<br>" . mysql_error());
		$numbercomp = $resultcomp->rowCount();
		return $numbercomp;
	}
	function textformat() {
		$query = "truncate table `person_document`";
		//$result = MYSQL_QUERY($query, $this->datalink) or die("unable to updated document.<br>$query<br>" . mysql_error());
		return "...";
	}
	function insert() {
		$blnInsert=true;
		if ($this->document_name == "") {
			$blnInsert=false;
		}
		if ($blnInsert==true) {
			//insert it
			$query="insert into document (`parent_document_uuid`, `document_name`, `document_date`, `expiration_date`, `reminder_days`, `document_filename`, 
			`document_extension`, `description`, `type`) 
				VALUES ('$this->parent_uuid','$this->document_name','$this->document_date','$this->expiration_date','$this->reminder_days','$this->document_filename', 
				'$this->document_extension','$this->description','$this->type')";
			//die( $query . "<br>");
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to insert new document.<br>$query<br>" . mysql_error());
			//get the newly created id
			$this->uuid = getAutoincrement('document', 'document', $result, $this->datalink);
			//now track it
			$time_stamp = date("Y-m-d h:i:s");
			$query="insert into document_track (`user_uuid`, `location_id`, `operation`, `time_stamp`, `document_id`,  `document_uuid`,  `document_name`, `document_date`, `expiration_date`, `reminder_days`, `document_filename`, `document_extension`, `description`) 
				VALUES ('$this->user_uuid', '" . $this->location_id . "', 'insert','$time_stamp', '$this->id', '$this->uuid', '$this->document_name', '$this->document_date', '$this->expiration_date','$this->reminder_days',
				'$this->document_filename','$this->document_extension','$this->description')";
			//echo $query . "<br>";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to insert new document.<br>$query<br>" . mysql_error());
			//now update the parent document if empty
			if ($this->parent_uuid=="") {
				$query = "update `document` set `parent_document_uuid` = `document_uuid` where `document_uuid` = '" . $this->uuid . "'";
				$result = MYSQL_QUERY($query, $this->datalink) or die("unable to update new document parent<br>$query<br>" . mysql_error());
			}
		}
	}
	function getperson($person_uuid, $attribute = "") {
		//find a link or person
		$querydocument = "select distinct document.document_id, 
		document.document_uuid, document.document_filename, document.document_extension, document.document_date,
		document.expiration_date, document.reminder_days, 
		document.description, document.verified";
		$querydocument .= " from `document` document";
		$inners = " INNER JOIN person_document";
		$inners .= " ON document.document_uuid = person_document.document_uuid";
//		$inners .= " INNER JOIN person";	
//		$inners .= " ON person_document.person_uuid = person.person_uuid";

		$querydocument.= $inners;
		$whereClause = " where person_document.person_uuid = '" . $person_uuid . "'";
		if ($attribute!="") {
			$whereClause .= " AND person_document.attribute = '" . $attribute . "'";
		}		
		$sortby = " order by `document`.document_id desc";
	
		$querydocument.= $whereClause.$sortby;
//		echo $querydocument . "<br>";
		//get the list		
		$resultcomp = MYSQL_QUERY($querydocument,$this->datalink) or die ("Unable to get the document list<br>$querydocument<br>" . mysql_error());
		return $resultcomp;
	}
	function getuser($user_uuid, $attribute = "") {
		//find a link or user
		$querydocument = "select distinct document.document_id, 
		document.document_uuid, document.document_filename, document.type, document.document_extension, document.document_date,
		document.expiration_date, document.reminder_days, 
		document.description, document.verified";
		$querydocument .= " from `document` document";
		$inners = " INNER JOIN user_document";
		$inners .= " ON document.document_uuid = user_document.document_uuid";
//		$inners .= " INNER JOIN user";	
//		$inners .= " ON user_document.user_uuid = user.user_uuid";

		$querydocument.= $inners;
		$whereClause = " where user_document.user_uuid = '" . $user_uuid . "'";
		if ($attribute!="") {
			$whereClause .= " AND user_document.attribute = '" . $attribute . "'";
		}		
		$sortby = " order by `document`.document_id desc";
	
		$querydocument.= $whereClause.$sortby;
//		echo $querydocument . "<br>";
		//get the list		
		//$resultcomp = MYSQL_QUERY($querydocument,$this->datalink) or die ("Unable to get the document list<br>$querydocument<br>" . mysql_error());
		//return $resultcomp;
		try {
			$sql = $querydocument;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("notification", $notification);
			$stmt->execute();
			$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
			
			return $documents;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	}
	function gettask($notes_id, $attribute = "") {
		//find a link or company
		$querydocument = "select distinct document.document_id, 
		document.document_uuid, document.document_filename, document.type, document.document_extension, document.document_date,
		document.expiration_date, document.reminder_days, 
		document.description, document.verified";
		$querydocument .= " from `document` document";
		$inners = " INNER JOIN todo_notes_document";
		$inners .= " ON document.document_id = todo_notes_document.document_id";
//		$inners .= " INNER JOIN todo_notes";	
//		$inners .= " ON todo_notes_document.todo_notes_uuid = todo_notes.todo_notes_uuid";

		$querydocument.= $inners;
		$whereClause = " where todo_notes_document.todo_notes_id = '" . $notes_id . "'";
		if ($attribute!="") {
			$whereClause .= " AND todo_notes_document.attribute = '" . $attribute . "'";
		}		
		$sortby = " order by `document`.document_id desc";
	
		$querydocument.= $whereClause.$sortby;
		//echo $querydocument . "<br>";
		//get the list		
		$resultcomp = MYSQL_QUERY($querydocument,$this->datalink) or die ("Unable to get the document list<br>$querydocument<br>" . mysql_error());
		return $resultcomp;
	}
	function getlocation($location_id, $attribute = "") {
		//find a link or booking
		$querydocument = "select distinct document.document_id, 
		document.document_uuid, document.document_filename, document.type, document.document_extension, document.document_date,
		document.expiration_date, document.reminder_days, 
		document.description, document.verified";
		$querydocument .= " from `document` document";
		$inners = " INNER JOIN location_document";
		$inners .= " ON document.document_id = location_document.document_id";
//		$inners .= " INNER JOIN location";	
//		$inners .= " ON location_document.location_uuid = location.location_uuid";

		$querydocument.= $inners;
		$whereClause = " where location_document.location_id = '" . $location_id . "'";
		if ($attribute!="") {
			$whereClause .= " AND location_document.attribute = '" . $attribute . "'";
		}		
		$sortby = " order by `document`.document_id desc";
	
		$querydocument.= $whereClause.$sortby;
//		echo $querydocument . "<br>";
		//get the list		
		$resultcomp = MYSQL_QUERY($querydocument,$this->datalink) or die ("Unable to get the document list<br>$querydocument<br>" . mysql_error());
		return $resultcomp;
	}
	function getterm($term_uuid, $attribute = "") {
		//find a link or booking
		$querydocument = "select distinct document.document_id, 
		document.document_uuid, document.document_filename, document.type, document.document_extension, document.document_date,
		document.expiration_date, document.reminder_days, 
		document.description, document.verified";
		$querydocument .= " from `document` document";
		$inners = " INNER JOIN term_document";
		$inners .= " ON document.document_uuid = term_document.document_uuid";
//		$inners .= " INNER JOIN term";	
//		$inners .= " ON term_document.term_uuid = term.term_uuid";

		$querydocument.= $inners;
		$whereClause = " where term_document.term_uuid = '" . $term_uuid . "'";
		if ($attribute!="") {
			$whereClause .= " AND term_document.attribute = '" . $attribute . "'";
		}		
		$sortby = " order by `document`.document_id desc";
	
		$querydocument.= $whereClause.$sortby;
//		echo $querydocument . "<br>";
		//get the list		
		$resultcomp = MYSQL_QUERY($querydocument,$this->datalink) or die ("Unable to get the document list<br>$querydocument<br>" . mysql_error());
		return $resultcomp;
	}
	function del() {
		if ($this->uuid =="") {
			return "no id";
		} else {
			$query = "delete from `document` where document_uuid = '" . $this->uuid . "'";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to delete document.<br>$query<br>" . mysql_error());
		}
		$this->uuid = "";
		$this->id = "";
		$this->arrInfo = "";
	}
	function update() {
		if ($this->document_name == "") {
			//delete instead of update
			die("about to delete");
//			$this->del();
			return false;
		}
		if ($this->uuid =="") {
			$this->insert();
		} else {
			$query = "update `document` set `document_name` = '" . $this->document_name . "',
			 `document_date` = '" . $this->document_date . 
			"', `expiration_date` = '" . $this->expiration_date . 
			"', `reminder_days` = '" . $this->reminder_days . 
			"', `document_filename` = '" . $this->document_filename . 
			"', `document_extension` = '" . $this->document_extension . 
			"', `description` = '" . $this->description . 
			"', `type` = '" . $this->type . 
			"', `verified` = '" . $this->verified . "'
			where `document_uuid` = '" . $this->uuid . "'";
			//die($query);
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to update document.<br>$query<br>" . mysql_error());
		}
	}
	function search($attributes = "",$sort = "",$filter = "",$filtercolumn = "", $verified = "",$showattributefields = false) {
//		$filter = addslashes($filter);
		//find a company or companies
		$querycat = "select distinct `document`.document_id, `document`.description, document.type,
		`document`.document_uuid, `document`.document_name, 
		`document`.document_filename, `document`.document_date, document.expiration_date, document.reminder_days, `document`.document_extension,`document`.verified";
		//do we need attributes		-- DO NOT USE YET
		if ($attributes!="") {
			$subfields = ""; 
			$arrAttributes = explode(",",$attributes);
			$intCounter =0;
			while ($intCounter < count($arrAttributes)) {
				$subtable= $arrAttributes[$intCounter];
				//if ($arrAttributes[$intCounter]!="project") {
				//	$subconnect= "document_" . $arrAttributes[$intCounter];
				//} else {
					$subconnect= $arrAttributes[$intCounter] . "_document";
				//}
				//build sub fields
				if ($showattributefields!=false) {
					//only show the sub fields if asked for them
					if ($subfields=="") {
						$subfields .= $subtable . ".*, " . $subconnect . ".attribute " . $subconnect . "attribute" ;
					} else {
						$subfields .= ", " . $subtable . ".*, " . $subconnect . ".attribute " . $subconnect . "attribute" ;
					}
				}
				//build inner joins
				$inners .= " LEFT OUTER JOIN " . $subconnect;
				$inners .= " ON `document`.document_uuid = " . $subconnect . ".document_uuid";
				$inners .= " LEFT OUTER JOIN " . $subtable;	
				$inners .= " ON " . $subconnect. ". " . $subtable . "_uuid = " . $subtable . "." . $subtable . "_uuid";
				$intCounter++;
			}
		}
		if ($subfields!="") {
			$querycat.= "," . $subfields;
		}
		$querycat.= " from `document`";
		if ($inners!="") {
			$querycat.= $inners;
		}
		if ($filter!="" and $filtercolumn!="") {
			$whereClause = " where (`document`." . $filtercolumn . " like '%" . $filter . "%')";
		}
		if ($filter!="" and $filtercolumn=="") {
			$whereClause = " where (`document`.document_id like '%" . $filter . "%' or `document`.document_uuid like '%" . $filter . "%'
			or `document`.document_name like '%" . $filter . "%'
			or `document`.document_filename like '%" . $filter . "%'
			or `document`.description like '%" . $filter . "%'
			or `document`.document_extension like '%" . $filter . "%')";
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
			$sortby = " order by `document`.document_name";
		} else {
			$sortby = " order by " . $sort . "";		
		}
		$querycat .= $whereClause.$sortby;
		//echo $querycat. "<br>";
		//get the list		
		$resultdocument = MYSQL_QUERY($querycat,$this->datalink) or die ("Unable to get the document list<br>$querycat<br>" . mysql_error());
		return $resultdocument;
	}
	function search_category($attributes = "",$sort = "",$filter = "",$filtercolumn = "", $verified = "",$showattributefields = false, $category_uuid = "", $category_name = "", $subscribe_attribute = "owner") {
//		$filter = addslashes($filter);
		//find a company or companies
		$querycat = "select distinct `document`.document_id, `document`.description, document.type,
		`document`.document_uuid, `document`.document_name, 
		`document`.document_filename, `document`.document_date, document.expiration_date, document.reminder_days, `document`.document_extension,`document`.verified";
		$querycat.= " from `document`";
		if ($category_uuid!="") {
			$querycat.= " inner join document_category dcat
			on (document.document_uuid = dcat.document_uuid
			and dcat.category_uuid = '" . $category_uuid . "')";
		}
		if ($this->subscriber_uuid!="") {
			$queryreq .= " inner join company_document cpers 
			ON (document.document_uuid = cpers.document_uuid AND cpers.attribute = '" . $subscribe_attribute . "'
			AND cpers.company_uuid = '" . $this->subscriber_uuid . "')";
		}
		if ($category_name!="") {
			$querycat.= " inner join document_category dcat
			on document.document_uuid = dcat.document_uuid
			inner join category cat
			on dcat.category_uuid = cat.category_uuid
			and cat.category = '" . $category_name . "')";
		}
		$querycat.= " where 1 ";
		if ($filter!="" and $filtercolumn!="") {
			$whereClause = " and (`document`." . $filtercolumn . " like '%" . $filter . "%')";
		}
		if ($filter!="" and $filtercolumn=="") {
			$whereClause = " and (`document`.document_id like '%" . $filter . "%' or `document`.document_uuid like '%" . $filter . "%'
			or `document`.document_name like '%" . $filter . "%'
			or `document`.document_filename like '%" . $filter . "%'
			or `document`.description like '%" . $filter . "%'
			or `document`.document_extension like '%" . $filter . "%')";
		}
		if ($verified == "Y") {
			$whereClause .= " and (verified = '" . $verified . "')";
		}
		if ($verified == "N") {
			$whereClause .= " and (verified = '" . $verified . "'  or verified ='')";
		}
		if ($sort == "") {
			$sortby = " order by `document`.document_name";
		} else {
			$sortby = " order by " . $sort . "";		
		}
		$querycat .= $whereClause.$sortby;
		//echo $querycat. "<br>";
		//get the list		
		$resultdocument = MYSQL_QUERY($querycat,$this->datalink) or die ("Unable to get the document list<br>$querycat<br>" . mysql_error());
		return $resultdocument;
	}
	function role_check($role_uuid, $attribute = "") {
		$query = "SELECT document_role_id, attribute
		FROM document_role 
		WHERE document_uuid = '" . $this->uuid . "'
		AND role_uuid = '" . $role_uuid . "'";
		if ($attribute!="") {
			$query .= " AND attribute = '" . $attribute . "'";
		}
		//die($query);
		$result = mysql_query($query, $this->datalink) or die("Unable to get role check<br>" . mysql_error());
        //return $numbs;
		$rate = "";
		if ($result->rowCount() >0) {
			$rate = mysql_result($result, 0, "attribute");
			if ($rate =="" || $rate =="main") {
				$rate = "0";
			}
		}
		//die("rate: " . $rate);
		return $rate;
	}
	function role_type ($role_uuid, $type) {
		$query = "SELECT distinct document.document_id
		FROM document
		INNER JOIN document_role drole
		ON (document.document_uuid = drole.document_uuid
		AND drole.role_uuid = '" . $role_uuid . "')
		WHERE document.type like '" . $type . "%'";
		$result = mysql_query($query, $this->datalink) or die("unable to get document id<br>" . mysql_error());
        $return = "";
		if ($result->rowCount() >0) {
			$return = mysql_result($result, 0, "document_id");
		}
		return $return;
	}
	function clear() {
		//remove the request, and its addresses, persons, and comms
		if ($this->uuid =="") {
			return "no id";
		}
		//delete the person_address connection
		$querydel = "DELETE from `person_document` where document_uuid = '" . $this->uuid. "'";
		$resultdel = MYSQL_QUERY($querydel,$this->datalink) or 
			die ("Unable to delete the person_document<br>$querydel<br>" . mysql_error());

		//delete the request
		$this->del();
	}
}
