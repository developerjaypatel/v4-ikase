<?php
//class to manage companies
class department
{
	var $name; 
	var $id;
	var $uuid;
	var $parent_uuid;
	var $table;
	var $description;
	var $sort_order;
	var $arrInfo;
	var $verified;
	//data access
	var $datalink;
	var $names;
	
	function __construct() {
		return true;
	}
	function make_selectoptions($attribute = "") {
		if ($sortby != "") {
			$sortby = "sort_order ASC, " . $sortby;
		} else {
			$sortby = "sort_order ASC";
		}
		$resultset = $this->search($attribute,$sortby,$attribute);
		
		//die("numberdrop: " . $numberdrop);
		$xdrop=0;
		$blnFoundit = false;
		foreach($resultset as $dept) {
			$department_id = $dept->department_id; 
			$department_uuid = $dept->department_uuid; 
			$department = $dept->name;  

			if ($department_uuid==$this->uuid) {
				$selected = " selected";
				$blnFoundit = true;
			} else {
				$selected = "";
			}
//				echo $department . "<br>";
			$optionstring .= "<option value='".$department_id."'" . $selected . ">".$department."</option>"; 
			
			 $xdrop++;
		} // end foreach 
		if ($blnFoundit==false) {
			//select the empty first choice
			$selected = " selected";
		}
		$optionstring = "<option value=''" . $selected . ">Select a Category</option>" . $optionstring; 
		return $optionstring;
	}
	function make_checkboxes($attribute = "", $groups = "", $sortby = "") {
		//break up the groups
		$arrGroup = array();
		if ($groups!="") {
			$arrGroup = explode(",", $groups);
		}
		if ($sortby != "") {
			$sortby = "sort_order ASC, " . $sortby;
		} else {
			$sortby = "sort_order ASC";
		}
		$resultset = $this->search($attribute,$sortby,$attribute);
		//die(print_r($resultset));
		$arrBoxes = array();
		foreach($resultset as $dept) {
			$department_id = $dept->department_id; 
			$department_uuid = $dept->department_uuid; 
			$department = $dept->name;  
			if ($_SESSION['user_type']!="3") {
				if ($department=="admins") {
					return;
				}
			}
			//$this->names[] = $department;
			if ($department==$this->name || in_array($department, $arrGroup)) {
				$selected = " checked";
			} else {
				$selected = "";
			}
//				echo $department . "<br>";
			$arrBoxes[] = "<input name='department_select[]' type='checkbox' value='".$department_id."'" . $selected . " class='department'>&nbsp;" . ucwords(str_replace("_", " ", $department)); 
		} // end foreach
		//die(print_r($arrBoxes));
		$intCounter = -1;
		$arrRow = array();
		for ($intB=0;$intB<count($arrBoxes);$intB++) {
			if (($intB%3)==0 && $intB!=0) {
				$intCounter++;
			}
			$department = $arrBoxes[$intB];
			$arrRow[$intCounter][] = $department;
		}
		//die(print_r($arrRow));
		$therow = "";
		//if (isset($arrRow[$intB])) {
			for ($intB=-1;$intB<$intCounter+1;$intB++) {
				$therow .= "<tr><td nowrap>" . implode("</td><td nowrap>", $arrRow[$intB]) . "</td></tr>";
			}
		//}
		$boxes = "";
		if ($therow!="") {
			$boxes = "<table border=0 cellspacing=0 cellpadding=2>" . $therow . "</table>";
		}

		return $boxes;
	}
	function make_list($attribute = "") {
		if ($sortby != "") {
			$sortby = "sort_order ASC, " . $sortby;
		} else {
			$sortby = "sort_order ASC";
		}
		$resultset = $this->search($attribute,$sortby,$attribute);
		$numberdrop = $resultset->rowCount();
		//die("numberdrop: " . $numberdrop);
		$xdrop=0;
		$blnFoundit = false;
		foreach($resultset as $dept) {
				$department_id = $dept->department_id; 
				$department_uuid = $dept->department_uuid; 
				$department = $dept->name;  
				
				if ($optionstring=="") {
					$optionstring = $department_id . "::" . $department;
				} else {
					$optionstring .= "|" . $department_id . "::" . $department; 
				}
				
				 $xdrop++;
		} // end if 
		return $optionstring;
	}
	function search($attributes = "",$sort = "",$filter = "",$filtercolumn = "", $verified = "",$showattributefields = false) {
//		$filter = addslashes($filter);
		//find a company or companies
		$querycat = "SELECT DISTINCT `department`.department_id, `department`.description,
		`department`.department_uuid, `department`.parent_uuid, `department`.restrictions, `department`.name,
		`department`.sort_order,`department`.verified";
		//do we need attributes		-- DO NOT USE YET
		$subfields = ""; 
		$inners = "";
		$whereClause = "";
		if ($attributes!="") {
			$arrAttributes = explode(",",$attributes);
			$intCounter =0;
			while ($intCounter < count($arrAttributes)) {
				$subtable= $arrAttributes[$intCounter];
				//if ($arrAttributes[$intCounter]!="project") {
				//	$subconnect= "department_" . $arrAttributes[$intCounter];
				//} else {
					$subconnect= $arrAttributes[$intCounter] . "_department";
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
				$inners .= " ON `department`.department_uuid = " . $subconnect . ".department_uuid";
				$inners .= " LEFT OUTER JOIN " . $subtable;	
				$inners .= " ON " . $subconnect. ". " . $subtable . "_uuid = " . $subtable . "." . $subtable . "_uuid";
				$intCounter++;
			}
		}
		if ($subfields!="") {
			$querycat.= "," . $subfields;
		}
		$querycat.= " FROM `department`";
		if ($inners!="") {
			$querycat.= $inners;
		}
		$whereClause = " 
		WHERE 1
		";
		if ($filter!="" and $filtercolumn!="") {
			$whereClause .= " 
			AND (`department`." . $filtercolumn . " = '" . $filter . "')";
		}
		if ($filter!="" and $filtercolumn=="") {
			$whereClause .= " 
			AND (`department`.department_id like '%" . $filter . "%' 
			OR `department`.department_uuid like '%" . $filter . "%'
			OR `department`.parent_uuid like '%" . $filter . "%' 
			OR `department`.name like '%" . $filter . "%'
			OR `department`.restrictions like '%" . $filter . "%'
			OR `department`.description like '%" . $filter . "%'
			OR `department`.sort_order like '%" . $filter . "%'
			OR `department`.name like '%" . $filter . "%')";
		}
		if ($verified == "Y") {
			$whereClause .= " AND (verified = '" . $verified . "')";
		}
		if ($verified == "N") {
			$whereClause .= " AND (verified = '" . $verified . "'  or verified ='')";
		}
		$whereClause .= "
		AND `department`.deleted = 'N'
		AND `department`.customer_id  = " . $_SESSION["user_customer_id"];
			
		if ($sort == "") {
			$sortby .= " 
			ORDER BY `department`.name";
		} else {
			$sortby = " 
			ORDER BY " . $sort . "";		
		}
		$querycat .= $whereClause.$sortby;
		//echo "Query to get the categories into a select tag<br>$querycat<br>";
		//get the list		
		//$resultdepartment = MYSQL_QUERY($querycat,$this->datalink) or die ("Unable to get the department list<br>$querycat<br>" . mysql_error());
		
		try {
			$sql = $querycat;
			$resultdepartment = DB::select($sql);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		return $resultdepartment;
	}
	function getmaster($table) {
		//get a department sub value (ie: address), by table and by attribute
		$querycomp = "select distinct `". $table . "_uuid`
		from `". $table . "_department`  
		where `department_uuid` = '" . $this->uuid . "' ";

		//die ("Unable to get the uuid<br>$querycomp<br>" . mysql_error());
		$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
					die ("Unable to get the uuid<br>$querycomp<br>" . mysql_error());
		$numberupdate = $resultcomp->rowCount();
		if ($numberupdate > 0) {
			if ($numberupdate==1) {
				//get the uuid
				$uuid=mysql_result($resultcomp,0,$table . "_uuid");
				return $uuid;
			} else {
				return $resultcomp;
			}
		}
	}
	function id($id) {
		//give a normal id, set the uuid
		//prep an array
		$this->id=$id;
		if ($this->uuid=="") {
			//get the department name, role person, phone, fax
			$querydepartment = "select department_uuid
			from `department`
			where department_id = '$this->id'";			
			$resultdepartment = MYSQL_QUERY($querydepartment,$this->datalink) or 
					die ("Unable to get the department uuid<br>$querydepartment<br>" . mysql_error());
			$numberdepartment = $resultdepartment->rowCount();
			if ($numberdepartment > 0) {
				//fill up the array
				$this->uuid=mysql_result($resultdepartment,0,"department_uuid");
				$this->fetch();
			}
		}
	}
	function fetch($department_id = "") {
		//get a department using the uuid
		if ($department_id=="") {
			if ($this->uuid =="") {
				return "no id";
			}
		} else {
			$this->id = $department_id;
		}
		//prep an array
		$arrResult["id"]="";
		$arrResult["department_id"]="";
		$arrResult["department_uuid"]="";
		$arrResult["parent_uuid"]="";
		$arrResult["table"]="";
		$arrResult["description"]="";
		$arrResult["name"]="";
		$arrResult["sort_order"]="";
		
		//get the department name, role person, phone, fax
		$querydepartment = "select distinct department_id, department.description,
		department_uuid, parent_uuid, restrictions, department.name, department.sort_order, department.verified";
		$querydepartment.= " from `department`";
		if ($department_id=="") {
			$querydepartment.= " where department_uuid = '$this->uuid'";
		} else {
			$querydepartment.= " where department_id = '$this->id'";
		}
		$querydepartment.= " AND customer_id = " . $_SESSION["user_customer_id"];
		$querydepartment.= " AND deleted = 'N'";

		try {
			$sql = $querydepartment;
			$stmt = DB::run($sql);
			$department = $stmt->fetchObject();
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		if (is_object($department)) {
			//fill up the array
			$arrResult["department_id"] = $department->department_id;
			$arrResult["department_uuid"] = $department->department_uuid;
			$arrResult["parent_uuid"] = $department->parent_uuid;
			$arrResult["table"] = $department->restrictions;
			$arrResult["name"] = $department->name;
			$arrResult["description"] = $department->description;			
			$arrResult["sort_order"] = $department->sort_order;
			$arrResult["verified"] = $department->verified;
		}
		if ($arrResult["name"] =="") {
			//echo "deleted blank department " . $this->uuid . "<br>";
			$this->clear();
			$this->id = "";
			$this->uuid = "";
		} else {
			$this->id = $arrResult["department_id"];
			$this->uuid = $arrResult["department_uuid"];
			//echo "1: " . $arrResult["department_id"] . "<br>";
			$this->parent_uuid = $arrResult["parent_uuid"];
			$this->table = $arrResult["table"];
			$this->name = $arrResult["name"];
			$this->description = $arrResult["description"];
			$this->sort_order = $arrResult["sort_order"];
			$this->verified = $arrResult["verified"];
			$this->arrInfo = $arrResult;
		}
	}
	function insert($name="") {
		//new department, name is minimum necessary
		if ($name !="") {
			$this->name = $name;
		}
		//die("now");
		$blnInsert=true;
		if ($this->name =="") {
			$blnInsert=false;
		}
		if ($blnInsert==true) {
			//insert it
			$query="insert into `department` (`name`";
			if ($this->id!="") {
				$query .= ", `id`";
			}
			if ($this->table!="") {
				$query .= ", `restrictions`";
			}
			$query .= ", `description`";
			$query .= ", `sort_order`";
			$query .= ", `verified`";
			$query .= ") VALUES ('" . $this->name . "'";
			if ($this->id!="") {
				$query .= ", '" . $this->id . "'";
			}
			if ($this->table!="") {
				$query .= ", '" . $this->table . "'";
			}
			$query .= ", '" . $this->description . "'";
			$query .= ", '" . $this->sort_order . "'";
			//not verified as default
			$query .= ", 'Y'";
			$query .= ")";
			//echo "$query<br>";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to insert new department<br>$query<br>" . mysql_error());
			//get the newly created id
			if ($this->id!="") {
				$this->uuid = getAutoincrement('department', 'department', $result, $this->datalink,"","","",$this->id);
			} else {
				$this->uuid = getAutoincrement('department', 'department', $result, $this->datalink);
			}
			$this->parent_uuid = $this->uuid;
			$this->update();
			$this->fetch();
		}
	}
	function del() {
		//delete by uuid
		if ($this->uuid =="") {
			return "no id";
		} else {
			$query = "delete from department where department_uuid = '" . $this->uuid . "'";
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to delete department<br>$query<br>" . mysql_error());
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
			//update a department's name and verified status
			$query = "update `department` set name = '" . $this->name . 
			"', parent_uuid = '" . $this->parent_uuid . 
			"', restrictions = '" . $this->table . 
			"', description = '" . $this->description . 
			"', sort_order = '" . $this->sort_order . 
			"', verified = '" . $this->verified . "'
			where department_uuid = '" . $this->uuid . "'";
			//die("query: " . $query);
			$result = MYSQL_QUERY($query, $this->datalink) or die("unable to update department info<br>$query<br>" . mysql_error());
		}
	}
	
	function getattribute($table, $attribute) {
		//get a link sub value (ie: address), by table and by attribute
		$querydepartment = "select `". $table . "_uuid`
		from `department_". $table . "` 
		where `department_uuid` = '" . $this->uuid . "' ";
		if ($attribute != "") {
			$querydepartment.= "and `attribute` = '" . $attribute . "'";
		}
		//die ("Unable to get the department_uuid<br>$querydepartment<br>" . mysql_error());
		$resultdepartment = MYSQL_QUERY($querydepartment,$this->datalink) or 
					die ("Unable to get the department_uuid<br>$querydepartment<br>" . mysql_error());
		$numberdepartment = $resultdepartment->rowCount();
		if ($numberdepartment > 0) {
			if ($numberdepartment==1) {
				//get the department_uuid
				$uuid=mysql_result($resultdepartment,0,$table . "_uuid");
				return $uuid;
			} else {
				return $resultdepartment;
			}
		}
	}
	function getuser($user_uuid, $attribute = "") {
		//find a link or user
		$querydepartment = "SELECT distinct department.*";
		$querydepartment .= " FROM `department` department";
		$inners = " INNER JOIN user_department";
		$inners .= " ON department.department_uuid = user_department.department_uuid";


		$querydepartment.= $inners;
		$whereClause = " WHERE user_department.user_uuid = :user_uuid";
		if ($attribute!="") {
			$whereClause .= " AND user_department.attribute = :attribute";
		}		
		$sortby = " ORDER BY `department`.department_id desc";	
		$querydepartment.= $whereClause.$sortby;

		try {
			$sql = $querydepartment;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("user_uuid", $user_uuid);
			if ($attribute!="") {
				$stmt->bindParam("attribute", $attribute);
			}
			$stmt->execute();
			$departments = $stmt->fetchAll(PDO::FETCH_OBJ);
			
			return $departments;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	}
	//end of class code
}
