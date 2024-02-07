<?php
//class to manage notes
class note
{
	var $name; 
	var $id;
	var $uuid;
	var $note;
	var $dateandtime;
	var $arrInfo;
	var $verified;
	//data access
	var $datalink;
	var $serve_id;
	var $cus_id;
	var $entered_by;
	
	function note ($datalink) {
		$this->datalink = $datalink;
		if ($datalink=="") {
			return false;
		} else {
			return true;
		}
	}
	function id($id) {
		//give a normal id, set the uuid
		//prep an array
		$this->id=$id;
		/*
		if ($this->uuid=="") {
			//get the firm name, role person, phone, fax
			$querycomp = "SELECT note.uuid
			from `note`
			where note.note_id = '$this->id'";			
			$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
					die ("Unable to get the note uuid<br>$querycomp<br>" . mysql_error());
			$numberupdate = mysql_Numrows($resultcomp);
			if ($numberupdate > 0) {
				//fill up the array
				$this->uuid=mysql_result($resultcomp,0,"note_id");
			}
		}
		*/
	}
	function getnotes($object, $object_id, $verified = "", $attribute = "") {
		//find a link or object
		$querynote = "SELECT distinct note.note_id, 
		note.note_id, note.note, note.dateandtime, note.user_name, note.status, note.verified";
		$querynote.= " from `note` note";
		$inners = " INNER JOIN " . $object . "_note";
		$inners .= " ON note.note_id = " . $object . "_note.note_id";
//		$inners .= " INNER JOIN " . $object . "";	
//		$inners .= " ON " . $object . "_note." . $object . "_id = " . $object . "." . $object . "_id";

		$querynote.= $inners;
		$whereClause = " where " . $object . "_note." . $object . "_id = '" . $object_id . "'";
		if ($verified!="") {
			$whereClause .= " and note.verified = '" . $verified . "'";
		}
		if ($attribute!="") {
			$whereClause .= " and " . $object . "_note.attribute = '" . $attribute . "'";
		}
		$sortby = " order by `note`.note_id desc";
	
		$querynote.= $whereClause.$sortby;
//		echo $querynote . "<br>";
		//get the list		
		$resultcomp = MYSQL_QUERY($querynote,$this->datalink) or die ("Unable to get the note list<br>$querynote<br>" . mysql_error());
		return $resultcomp;
	}
	function search($attributes = "",$sort = "",$filter = "",$filtercolumn = "", $verified = "") {
//		$filter = addslashes($filter);
		//find a link or person
		$querynote = "SELECT distinct note.note_id, note.note, note.dateandtime, note.verified, note.entered_by";
		//do we need attributes		-- DO NOT USE YET
		if ($attributes!="") {
			$subfields = ""; 
			$arrAttributes = explode(",",$attributes);
			$intCounter =0;
			while ($intCounter < count($arrAttributes)) {
				$subtable= $arrAttributes[$intCounter];
				//$subconnect= "note_" . $arrAttributes[$intCounter];
				$subconnect= "note_" . $arrAttributes[$intCounter];
				//build sub fields
				if ($subfields=="") {
					$subfields .= $subtable . ".*, " . $subconnect . ".attribute " . $subconnect . "attribute" ;
				} else {
					$subfields .= ", " . $subtable . ".*, " . $subconnect . ".attribute " . $subconnect . "attribute" ;
				}
				//build inner joins
				$inners .= " INNER JOIN " . $subconnect;
				$inners .= " ON note.note_id = " . $subconnect . ".note_id";
				$inners .= " INNER JOIN " . $subtable;	
				$inners .= " ON " . $subconnect. ". " . $subtable . "_id = " . $subtable . "." . $subtable . "_id";
				$intCounter++;
			}
		}
		if ($subfields!="") {
			$querynote.= "," . $subfields;
		}
		$querynote.= " from `note` note";

		if ($this->serve_id!="") {
			$querynote.= " INNER JOIN serve_note cnote
			ON note.note_id = cnote.note_id";
		}
		if ($this->cus_id!="") {
			$querynote.= " INNER JOIN customer_note cnote
			ON note.note_id = cnote.note_id";
		}
		if ($inners!="") {
			$querynote.= $inners;
		}
		$whereClause = " where 1";
		if ($this->serve_id!="") {
			$whereClause .= " and cnote.serve_id = '" . $this->serve_id . "'";
		}
		if ($filter!="" and $filtercolumn!="") {
			$whereClause .= " and (note." . $filtercolumn . " = '" . $filter . "')";
		}
		if ($filter!="" and $filtercolumn=="") {
			$whereClause .= " and (note.note_id like '%" . $filter . "%' or note.note_id like '%" . $filter . "%'
			or note.note like '%" . $filter . "%' or note.dateandtime like '%" . $filter . "%')";
		}
		if ($verified == "Y") {
				$whereClause .= " and (verified = '" . $verified . "')";
		}
		if ($verified == "N") {
			$whereClause .= " and (verified = '" . $verified . "'  or verified ='')";
		}
		if ($deleted != "Y") {
			$whereClause .= " and (note.deleted = 'N')";
		}
		if ($sort == "") {
			$sortby = " order by `note`.note_id desc";
		} else {
			$sortby = " order by " . $sort;		
		}
		$querynote.= $whereClause.$sortby;
		//echo $querynote . "<br>";
		//get the list		
		$resultcomp = MYSQL_QUERY($querynote,$this->datalink) or die ("Unable to get the note list<br>$querynote<br>" . mysql_error());
		return $resultcomp;
	}
	function getperson($person_id) {
		//find a link or person
		$querynote = "SELECT distinct note.note_id, 
		note.note_id, note.note, note.dateandtime, note.verified";
		
		$querynote.= " from `note` note";
		$inners = " INNER JOIN person_note";
		$inners .= " ON note.note_id = person_note.note_id";
//		$inners .= " INNER JOIN person";	
//		$inners .= " ON person_note.person_id = person.person_id";

		$querynote.= $inners;
		$whereClause = " where person_note.person_id = '" . $person_id . "'";		
		$sortby = " order by `note`.note_id desc";
	
		$querynote.= $whereClause.$sortby;
		//die ("Query to get the companies and their sub info<br>$querynote<br>");
		//get the list		
		$resultcomp = MYSQL_QUERY($querynote,$this->datalink) or die ("Unable to get the note list<br>$querynote<br>" . mysql_error());
		return $resultcomp;
	}
	function fetch() {
		if ($this->uuid =="") {
			return "no id";
		}
		//prep the array, though we may no longer need it
		$arrResult["note_id"]="";
		$arrResult["note_id"]="";
		$arrResult["note"]="";
		$arrResult["dateandtime"]="";
		$arrResult["verified"]="";
		//get the note info
		$querycomp = "SELECT distinct note.note_id, 
		note.note_id, note.note, note.dateandtime, note.verified
		from `note`
		where note_id = '" . $this->uuid . "'";
		
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
							die ("Unable to get the latest note info<br>$querycomp<br>" . mysql_error());
		$numberupdate = mysql_Numrows($resultcomp);
		if ($numberupdate > 0) {
			//fill up the array
			$arrResult["note_id"]=mysql_result($resultcomp,0,"note_id");
			$arrResult["note_id"]=mysql_result($resultcomp,0,"note_id");
			$arrResult["note"]=mysql_result($resultcomp,0,"note");
			$arrResult["dateandtime"]=mysql_result($resultcomp,0,"dateandtime");
			$arrResult["verified"]=mysql_result($resultcomp,0,"verified");
		}
		$this->id = $arrResult["note_id"];
		$this->uuid = $arrResult["note_id"];
		$this->note = $arrResult["note"];
		
		$this->dateandtime = $arrResult["dateandtime"];
		$this->verified = $arrResult["verified"];
		$this->arrInfo = $arrResult;
		if ($this->note=="") {
			$this->del();
			$this->id = "";
			$this->uuid = "";
			$this->arrInfo = "";
		}
	}
	function insert() {
		$blnInsert=true;
		if ($this->note == "" && $this->dateandtime == "") {
			$blnInsert=false;
		}
		if ($blnInsert==true) {
			//insert it
			$query="insert into note (`entered_by`,`note`) 
				VALUES ('".  $this->entered_by . "', '".  $this->note . "')";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to insert new note<br>$query<br>" . mysql_error());
			//get the newly created id
			//$this->uuid = getAutoincrement('note', 'note', $result, $this->datalink);
			$this->id = mysql_insert_id($this->datalink);
		}
	}
	function del() {
		if ($this->id =="") {
			return "no id";
		} else {
			$query = "UPDATE `note` SET deleted = 'Y' where note_id = '" . $this->id . "'";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to delete note<br>$query<br>" . mysql_error());
		}
		$this->id = "";
		$this->arrInfo = "";
	}
	function update() {
		if ($this->note == "" && $this->dateandtime == "") {
			//delete instead of update
			$this->del();
			return false;
		}
		if ($this->id =="") {
			$this->insert();
		} else {
			$query = "update `note` set `note` = '" . $this->note . "', `dateandtime` = '" . $this->dateandtime . 
			"', `verified` = '" . $this->verified . "'
			where `note_id` = '" . $this->id . "'";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to update note<br>$query<br>" . mysql_error());
		}
	}
}
?>
