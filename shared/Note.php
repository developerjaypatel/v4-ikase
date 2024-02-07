<?php /** @noinspection SqlResolve */
//TODO: reconfigure who imports this file, besides the mysql stuff

class Note extends DbAccess {

    public $name;
    public $id;
    public $uuid;
    public $note;
    public $dateandtime;
    public $arrInfo;
    public $verified;
    //data access
    public $serve_id;
    public $cus_id;
    public $entered_by;

    function id($id) {
        return $this->id = $id;
    }

    function getnotes($object, $object_id, $verified = "", $attribute = "") {
        //find a link or object
        $querynote = "SELECT distinct note.note_id, 
		note.note_id, note.note, note.dateandtime, note.user_name, note.status, note.verified";
        $querynote .= " from `note` note";
        $inners    = " INNER JOIN {$object}_note";
        $inners    .= " ON note.note_id = {$object}_note.note_id";

        $querynote   .= $inners;
        $whereClause = " where ".$object."_note.".$object."_id = '".$object_id."'";
        if ($verified != "") {
            $whereClause .= " and note.verified = '".$verified."'";
        }
        if ($attribute != "") {
            $whereClause .= " and ".$object."_note.attribute = '".$attribute."'";
        }
        $sortby = " order by `note`.note_id desc";

        $querynote .= $whereClause.$sortby;

        return $this->query($querynote);
    }

    function search($attributes = '', $sort = '', $filter = '', $filtercolumn = '', $verified = '', $deleted = '') {
//		$filter = addslashes($filter);
        //find a link or person
        $querynote = "SELECT distinct note.note_id, note.note, note.dateandtime, note.verified, note.entered_by";
        //do we need attributes		-- DO NOT USE YET
        $subfields = $inners = "";
        if ($attributes != "") {
            $arrAttributes = explode(",", $attributes);
            $intCounter    = 0;
            while ($intCounter < count($arrAttributes)) {
                $subtable = $arrAttributes[$intCounter];
                //$subconnect= "note_" . $arrAttributes[$intCounter];
                $subconnect = "note_".$arrAttributes[$intCounter];
                //build sub fields
                if ($subfields == "") {
                    $subfields .= $subtable.".*, ".$subconnect.".attribute ".$subconnect."attribute";
                } else {
                    $subfields .= ", ".$subtable.".*, ".$subconnect.".attribute ".$subconnect."attribute";
                }
                //build inner joins
                $inners .= " INNER JOIN ".$subconnect;
                $inners .= " ON note.note_id = ".$subconnect.".note_id";
                $inners .= " INNER JOIN ".$subtable;
                $inners .= " ON ".$subconnect.". ".$subtable."_id = ".$subtable.".".$subtable."_id";
                $intCounter++;
            }
        }
        if ($subfields != "") {
            $querynote .= ",".$subfields;
        }
        $querynote .= " from `note` note";

        if ($this->serve_id != "") {
            $querynote .= " INNER JOIN serve_note cnote
			ON note.note_id = cnote.note_id";
        }
        if ($this->cus_id != "") {
            $querynote .= " INNER JOIN customer_note cnote
			ON note.note_id = cnote.note_id";
        }
        if ($inners != "") {
            $querynote .= $inners;
        }
        $whereClause = " where 1";
        if ($this->serve_id != "") {
            $whereClause .= " and cnote.serve_id = '".$this->serve_id."'";
        }
        if ($filter != "" and $filtercolumn != "") {
            $whereClause .= " and (note.".$filtercolumn." = '".$filter."')";
        }
        if ($filter != "" and $filtercolumn == "") {
            $whereClause .= " and (note.note_id like '%".$filter."%' or note.note_id like '%".$filter."%'
			or note.note like '%".$filter."%' or note.dateandtime like '%".$filter."%')";
        }
        if ($verified == "Y") {
            $whereClause .= " and (verified = '".$verified."')";
        }
        if ($verified == "N") {
            $whereClause .= " and (verified = '".$verified."'  or verified ='')";
        }
        if ($deleted != "Y") {
            $whereClause .= " and (note.deleted = 'N')";
        }
        if ($sort == "") {
            $sortby = " order by `note`.note_id desc";
        } else {
            $sortby = " order by ".$sort;
        }
        $querynote .= $whereClause.$sortby;

        return $this->query($querynote);
    }

    function getperson($person_id) {
        $sql = "SELECT
            DISTINCT note.note_id, 
            note.note,
            note.dateandtime,
            note.verified
        FROM note
            INNER JOIN person_note ON note.note_id = person_note.note_id
        WHERE person_note.person_id = '$person_id'
        ORDER BY note.note_id DESC";

        return $this->query($sql);
    }

    function fetch() {
        if ($this->uuid == "") {
            return "no id";
        }

        //prep the array, though we may no longer need it
        $arrResult = [
            'note_id'     => '',
            'note'        => '',
            'dateandtime' => '',
            'verified'    => '',
        ];

        $sql = <<<SQL
        SELECT
            DISTINCT note.note_id, 
            note.note_id,
            note.note,
            note.dateandtime,
            note.verified
        FROM note
        WHERE note_id = '$this->uuid'
        SQL;

        $result = $this->query($sql);
        if ($result->rowCount()) {
            $arrResult = $result->fetch(PDO::FETCH_ASSOC);
        }

        $this->id   = $arrResult['note_id'];
        $this->uuid = $arrResult['note_id'];
        $this->note = $arrResult['note'];

        $this->dateandtime = $arrResult['dateandtime'];
        $this->verified    = $arrResult['verified'];
        $this->arrInfo     = $arrResult;

        if ($this->note == '') {
            $this->del();
            $this->id      = '';
            $this->uuid    = '';
            $this->arrInfo = '';
        }
    }

    function insert() {
        if ($this->note == '' && $this->dateandtime == '') {
            return;
        }

        $this->query("INSERT INTO note (entered_by, note) VALUES ('$this->entered_by', '$this->note')");
        $this->id = $this->getLastInsertedId();
    }

    function del() {
        if ($this->id == '') {
            return "no id";
        } else {
            $this->query("UPDATE note SET deleted = 'Y' where note_id = '$this->id'");
        }
        $this->id      = '';
        $this->arrInfo = '';
    }

    function update() {
        if ($this->note == '' && $this->dateandtime == '') {
            //delete instead of update
            return $this->del();
        }

        if ($this->id == '') {
            return $this->insert();
        }

        $this->query(<<<SQL
            UPDATE note SET
                note = '$this->note',
                dateandtime = '$this->dateandtime',
                verified = '$this->verified'
            WHERE note_id = '$this->id'
            SQL);
    }
}
