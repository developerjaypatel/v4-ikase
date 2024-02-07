<?php

// PHP Calendar Class Version 1.4 (5th March 2001)
//  
// Copyright David Wilkinson 2000 - 2001. All Rights reserved.
// 
// This software may be used, modified and distributed freely
// providing this copyright notice remains intact at the head 
// of the file.
//
// This software is freeware. The author accepts no liability for
// any loss or damages whatsoever incurred directly or indirectly 
// from the use of this script. The author of this software makes 
// no claims as to its fitness for any purpose whatsoever. If you 
// wish to use this software you should first satisfy yourself that 
// it meets your requirements.
//
// URL:   http://www.cascade.org.uk/software/php/calendar/
// Email: davidw@cascade.org.uk


class Calendar
{
    /* 
        The start day of the week. This is the day that appears in the first column
        of the calendar. Sunday = 0.
    */
    var $startDay = 0;

    /* 
        The start month of the year. This is the month that appears in the first slot
        of the calendar in the year view. January = 1.
    */
    var $startMonth = 1;

    /*
        The labels to display for the days of the week. The first entry in this array
        represents Sunday.
    */
    var $dayNames = array("S", "M", "T", "W", "T", "F", "S");
    
    /*
        The labels to display for the months of the year. The first entry in this array
        represents January.
    */
    var $monthNames = array("January", "February", "March", "April", "May", "June",
                            "July", "August", "September", "October", "November", "December");
                            
                            
    /*
        The number of days in each month. You're unlikely to want to change this...
        The first entry in this array represents January.
    */
    var $daysInMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	var $holidays;
	var $arrSelectedDays;
	
	//additional variables
	var $username;
	var $showall;
	var $hidechecks;	//sometimes, no check boxes for the days
	var $pastonly;		//sometimes, only check boxes before today
	
    //data access
	var $datalink;
	var $user_pwd;
	var $data_hostname;
	var $data_db;
	var $data_username;
	var $admin;
	//callbacks are true by default
	var $standard_report;
	//calendar type
	var $type;
	var $holiday_name;
	var $nonav;	//no navigation allowed
	var $backnav;	//back nav allowed
    
    
    /*
        Get the array of strings used to label the days of the week. This array contains seven 
        elements, one for each day of the week. The first entry in this array represents Sunday. 
    */
    function getDayNames()
    {
        return $this->dayNames;
    }
     function getStandardReport()
    {
        return $this->standard_report;
    }

    /*
        Set the array of strings used to label the days of the week. This array must contain seven 
        elements, one for each day of the week. The first entry in this array represents Sunday. 
    */
    function setDayNames($names)
    {
        $this->dayNames = $names;
    }
    
    /*
        Get the array of strings used to label the months of the year. This array contains twelve 
        elements, one for each month of the year. The first entry in this array represents January. 
    */
    function getMonthNames()
    {
        return $this->monthNames;
    }
    
    /*
        Set the array of strings used to label the months of the year. This array must contain twelve 
        elements, one for each month of the year. The first entry in this array represents January. 
    */
    function setMonthNames($names)
    {
        $this->monthNames = $names;
    }
    
    
    
    /* 
        Gets the start day of the week. This is the day that appears in the first column
        of the calendar. Sunday = 0.
    */
      function getStartDay()
    {
        return $this->startDay;
    }
    
    /* 
        Sets the start day of the week. This is the day that appears in the first column
        of the calendar. Sunday = 0.
    */
    function setStartDay($day)
    {
        $this->startDay = $day;
    }
    
    
    /* 
        Gets the start month of the year. This is the month that appears first in the year
        view. January = 1.
    */
    function getStartMonth()
    {
        return $this->startMonth;
    }
    
    /* 
        Sets the start month of the year. This is the month that appears first in the year
        view. January = 1.
    */
    function setStartMonth($month)
    {
        $this->startMonth = $month;
    }
    
    
    /*
        Return the URL to link to in order to display a calendar for a given month/year.
        You must override this method if you want to activate the "forward" and "back" 
        feature of the calendar.
        
        Note: If you return an empty string from this function, no navigation link will
        be displayed. This is the default behaviour.
        
        If the calendar is being displayed in "year" view, $month will be set to zero.
    */
    function getCalendarLink($month, $year)
    {
        //return "";
		// Redisplay the current page, but with some parameters 
		// to set the new month and year 
		$s = getenv('SCRIPT_NAME'); 
		return "$s?month=$month&year=$year&user=" . $this->username;
    }
    
    /*
        Return the URL to link to  for a given date.
        You must override this method if you want to activate the date linking
        feature of the calendar.
        
        Note: If you return an empty string from this function, no navigation link will
        be displayed. This is the default behaviour.
    */
    function getDateLink($day, $month, $year)
    {
        //echo "bn: " . $this->standard_report . "<br>";
		$thisday = date("m/d/Y", mktime(0, 0, 0, $month  , $day, $year));
		$thistime = date("Y-m-d", mktime(0, 0, 0, $month  , $day, $year));
		$yesterday = date("Y-m-d", mktime(0, 0, 0, $month  , $day-1, $year));
		$tomorrow = date("Y-m-d", mktime(0, 0, 0, $month  , $day+1, $year));
		
		
		//output
//		$event_table = "<tr><td align=left colspan='2'>&nbsp;</td></tr>";
		$event_table = "<tr><td align=left colspan='2'>&nbsp;</td></tr>";
		//get the days off for today
		//$resultdayoff = $this->user_dayoff($yesterday, $tomorrow, $this->username);	
		//die($thisday.", ".$this->username);	
		$resultdayoff = $this->user_dayoff($thisday, $this->username);	
		$numberdayoff = $resultdayoff->rowCount();
		$arrUsers = array();
		$arrApproved = array();
		$arrReviewed = array();
		for ($intD=0;$intD<$numberdayoff;$intD++) {
			$the_event_uuid = $row->events_uuid;
			$arrEventsID[] = $the_event_uuid;
			$the_user_logon = $row->user_logon;
			$arrUsers[] = $the_user_logon;
			$arrUserUUID[] = $row->user_uuid;
			$description = str_replace("\r\n", "", $description);
			$description = str_replace("\n", "", $description);
			$description = str_replace(chr(13), "", $description);
			$arrDesc[] = str_replace("'", "&acute;", $description);
			
			//check on approved dates here
			$approved_dates  = $row->approved_dates;
			$type  = $row->attribute;
			$description  = $row->description;
			$arrApprovedDates = explode("|", $approved_dates);
			$active = $row->active;
			$verified = $row->verified;
			$time_stamp = $row->time_stamp;
			if ($active == "Y") {
				//the whole group may be approved, but maybe only some dates in it
				$active = "N";
				if (in_array($thisday, $arrApprovedDates)) {
					$active = "Y";
				}	
			}
			$arrApproved[] = $active;
			if ($time_stamp!="0000-00-00 00:00:00") {
				//echo $the_user_logon . " - " . $verified . "<BR>";
				if ($verified=="") {
					$verified = "Y";
				}
				$arrReviewed[] = $verified;
			} else {
				$arrReviewed[] = "Y";
			}
			$arrType[] = $type;
			
			//just for that user, if we need it later
			$arrDays[$user_logon]++;
		}
		//print_r($arrType);
		//timeoff
		$resulttimeoff = $this->user_timeoff($thistime, $this->username);	
		$numbertimeoff = $resulttimeoff->rowCount();
/*
		for ($intD=0;$intD<$numbertimeoff;$intD++) {
			$arrNotesID[] = $row->notes_id;
			$arrTimeUsers[] = $row->user_logon;
			$arrTimeApproved[] = $row->active;
			//just for that user, if we need it later
			$arrTimes[$user_logon]++;
		}
*/
		
		if (is_array($arrUsers)) {
			if (count($arrUsers)>0) {
			//die("count:" . print_r($arrUsers) . print_r($arrApproved) . print_r($arrReviewed));
				for ($intU=0;$intU<count($arrUsers);$intU++) {
					//i'm bold
					$approve_link = "";
					$backcolor = "";
					if ($arrApproved[$intU]=="Y") {
						$backcolor = "bgcolor='#66CC66'";
						
						if ($this->admin) {
							//$approve_link = "<a href='edit.php?id=" . $arrEventsID[$intU] . "&user=" . $arrUsers[$intU] . "' title='Click to review'>R</a>&nbsp;|&nbsp;<a href='approve_dayoff.php?approved=n&id=" . $arrEventsID[$intU] . "&user=" . $arrUsers[$intU] . "' title='Click to disapprove'>D</a>";
							$approve_link = "<a href='events_edit.php?id=" . $arrEventsID[$intU] . "&user_uuid=" . $arrUserUUID[$intU] . "' title='Click to review'>R</a>&nbsp;|&nbsp;<a href='approve_dayoff.php?approved=n&id=" . $arrEventsID[$intU] . "&user=" . $arrUsers[$intU] . "' title='Click to disapprove'>D</a>";
						}
					}
					if ($arrApproved[$intU]=="N") {
						//echo $arrType[$intU] . " = ". $arrReviewed[$intU] . "<BR>";
						$backcolor = "bgcolor='#FFCC00'";
						//it might be timeoff request?
						//it might be timeoff request?
						if ($arrReviewed[$intU]=="Y") {
							if ($arrType[$intU]=="timeoff") { 
								$backcolor = "bgcolor='#FFCCFF'";
							}
							if ($arrType[$intU]=="dayoff") { 
								$backcolor = "bgcolor='#990000'";
							}
						}
						if ($this->admin) {
							//$approve_link = "<a href='edit.php?id=" . $arrEventsID[$intU] . "&user=" . $arrUsers[$intU] . "' title='Click to review'>R</a>&nbsp;|&nbsp;<a href='approve_dayoff.php?approved=y&id=" . $arrEventsID[$intU] . "&user=" . $arrUsers[$intU] . "' title='Click to approve'>A</a>";
							$approve_link = "<a href='events_edit.php?id=" . $arrEventsID[$intU] . "&user_uuid=" . $arrUserUUID[$intU] . "' title='Click to review'>R</a>&nbsp;|&nbsp;<a href='approve_dayoff.php?approved=y&id=" . $arrEventsID[$intU] . "&user=" . $arrUsers[$intU] . "&date=" . $thisday . "' title='Click to approve this day'>A</a>&nbsp;|&nbsp;<a href='approve_dayoff.php?approved=n&id=" . $arrEventsID[$intU] . "&user=" . $arrUsers[$intU] . "' title='Click to disapprove'>D</a>";
						}
					}
					if ($arrUsers[$intU] == $this->username) {
						$arrUsers[$intU] = "<B>" . $arrUsers[$intU] . "</B>";
					}
					if ($this->admin) {
						$arrUsers[$intU] = "<span id='" . $month . "-" . $day . "-" . $intU . "' onmouseover='displayTag(\"" . $month . "-" . $day . "-" . $intU . "\", \"" . $arrDesc[$intU] . "\")' style='background:white'>" . $arrUsers[$intU] . "</span>";
					}
					$event_table .= "<tr><td align=left " . $backcolor . ">" . $arrUsers[$intU] . "</td>";
					//$event_table .= "<td align=left>" . $arrApproved[$intU] . "</td></tr>";
					$event_table .= "<td align=right " . $backcolor . ">" . $approve_link;
					$event_table .= "&nbsp;</td>";
					$event_table .= "</tr>";
				}
			}
		}
		
		if (is_array($arrTimeUsers)) {
			if (count($arrTimeUsers)>0) {
//				print_r($arrTimeUsers);
//				die(print_r($arrNotesID));
				for ($intU=0;$intU<count($arrTimeUsers);$intU++) {
					//i'm bold
					$blnThisUser = false;
					
					if ($arrTimeUsers[$intU] == $this->username) {
						$arrTimeUsers[$intU] = "<B>" . $arrTimeUsers[$intU] . "</B>";
						$blnThisUser = true;
					}
					$arrTimeUsers[$intU] = "<span id='" . $month . "-" . $day . "-" . $intU . "' onmouseover='displayTag(\"" . $month . "-" . $day . "-" . $intU . "\", \"" . $arrDesc[$intU] . "\")'>" . $arrTimeUsers[$intU] . "</span>";
					$backcolor = "";
					$approvelink = "";
					if ($arrTimeApproved[$intU]=="Y") {
						$backcolor = "bgcolor='#66CC66'";
						if ($this->admin) {
							$approvelink = "<a name='" . $arrNotesID[$intU] . "'></a><a href='timeclock.php?the_date=" . $thisday . "#" . $arrTimeUsers[$intU] . "' title='Click to review'>R</a>&nbsp;|&nbsp;<a href='approve_timeoff.php?approved=n&id=" . $arrNotesID[$intU] . "&user=" . $arrTimeUsers[$intU] . "' title='Click to disapprove'>D</a>";
						}
					}
					
					if ($arrTimeApproved[$intU]=="N") {
						$backcolor = "bgcolor='#FFFF99'";
						if ($this->admin) {
							$approvelink = "<a name='" . $arrNotesID[$intU] . "'></a><a href='timeclock.php?the_date=" . $thisday . "#" . $arrTimeUsers[$intU] . "' title='Click to review'>R</a>&nbsp;|&nbsp;<a href='approve_timeoff.php?approved=y&id=" . $arrNotesID[$intU] . "&user=" . $arrTimeUsers[$intU] . "' title='Click to approve'>A</a>";
						}
					} else {
						if ($blnThisUser) {
							$approvelink = "<a name='" . $arrNotesID[$intU] . "'></a><a href='../users/employees_clock.php?the_date=" . $thisday . "' title='Click to review'>R</a>";
						}
					}
					if ($approvelink != "") {
						//die($arrNotesID[$intU] . "user:" . $approvelink);
					}
					//if ($this->admin || $blnThisUser) {
						$event_table .= "<tr><td align='left' width='50%' " . $backcolor . ">" . $arrTimeUsers[$intU] . "</td><td  width='50%' align='right' " . $backcolor . ">" . $approvelink . "&nbsp;</td>";
	//					$event_table .= "<td align=left>" . $arrTimeApproved[$intU] . "</td></tr>";
						$event_table .= "</tr>";
					//}
				}
			}
			
		}
		$event_table = "<table border='0' cellspacing='0' cellpadding='2' width='100%'>" . $event_table . "</table>";
		return $event_table;
    }
	//function user_dayoff($start_date, $end_date, $USERNAME) {
	function user_dayoff($day_date, $USERNAME) {
		$statusquery = "SELECT DISTINCT `events`.*, `user`.*, uev.attribute
		FROM `events` 
		INNER JOIN `user_events` uev
		on `events`.events_uuid = uev.`events_uuid`
		INNER JOIN `user`
		ON uev.user_uuid = `user`.user_uuid
		WHERE 1";
		$statusquery .= " AND `events`.dates LIKE '%" . $day_date . "%'";
		/*
		if ($day_date!="") { 
			$statusquery .= " AND `events`.date_begin > '" . $day_date . "'";
		}
		$statusquery .= " AND `events`.date_begin < '" . $end_date . "'";
		*/
		if ($USERNAME!="" && $this->showall!=true) {
			$statusquery .= " AND `user`.`user_logon` = '" . $USERNAME . "'";
		}
		$statusquery .= " ORDER BY `events`.date_begin, `user`.user_logon";
		//die($statusquery . "<Br><Br>");
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve order notes info<br>" . mysql_error());
		$numberstat = $resultstat->rowCount();
		return $resultstat;
	}
	function user_timeoff($day_date, $USERNAME) {
		$statusquery = "SELECT distinct `hour_notes`.`notes_id`, `hour_notes`.login_id, user_login.status, `hour_notes`.active, 
		`hour_notes`.user_name user_logon, user_login.timestamp
		FROM `hour_notes` 
		INNER JOIN userlogin user_login
		on hour_notes.login_id = user_login.userlogin_id
		WHERE 1
		AND `user_login`.timestamp LIKE '" . $day_date . "%'";
		//die ($statusquery . "<br>");
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve order notes info<br>" . mysql_error());
		$numberstat = $resultstat->rowCount();
		return $resultstat;

	}
    /*
        Return the HTML for the current month
    */
    function getCurrentMonthView()
    {
        $d = getdate(time());
        return $this->getMonthView($d["mon"], $d["year"]);
    }
    

    /*
        Return the HTML for the current year
    */
    function getCurrentYearView()
    {
        $d = getdate(time());
        return $this->getYearView($d["year"]);
    }
    
    
    /*
        Return the HTML for a specified month
    */
    function getMonthView($month, $year)
    {
        //echo "dd: " . $this->standard_report . "<br>";
		return $this->getMonthHTML($month, $year);
    }
    

    /*
        Return the HTML for a specified year
    */
    function getYearView($year)
    {
        return $this->getYearHTML($year);
    }
    
    
    
    /********************************************************************************
    
        The rest are private methods. No user-servicable parts inside.
        
        You shouldn't need to call any of these functions directly.
        
    *********************************************************************************/


    /*
        Calculate the number of days in a month, taking into account leap years.
    */
    function getDaysInMonth($month, $year)
    {
        if ($month < 1 || $month > 12)
        {
            return 0;
        }
   
        $d = $this->daysInMonth[$month - 1];
   
        if ($month == 2)
        {
            // Check for leap year
            // Forget the 4000 rule, I doubt I'll be around then...
        
            if ($year%4 == 0)
            {
                if ($year%100 == 0)
                {
                    if ($year%400 == 0)
                    {
                        $d = 29;
                    }
                }
                else
                {
                    $d = 29;
                }
            }
        }
    
        return $d;
    }

	//get blocked days
	function getDaysBlocked($month, $year) {
		$first_day = $year . "-" . $month . "-01";
		$arrBlockedDates = array();
		
		//get any blocked days for this month
		$query = "SELECT DISTINCT blocked_date FROM `blocked_dates`
		WHERE `blocked_date` BETWEEN '" . $first_day . "'
		AND '" . $year . "-" . $month . "-" . date("t", strtotime($first_day)) . "'";
		//echo $query;
		$result = mysql_query($query, $this->datalink) or die("unable to get blocked dates");
		$numbers = $result->rowCount();
		for ($intX=0;$intX<$numbers;$intX++) {
			$blocked_date = $row->blocked_date;
			$arrBlockedDates[] = $blocked_date;
		}
		return $arrBlockedDates;
	}
    /*
        Generate the HTML for a given month
    */
    function getMonthHTML($m, $y, $showYear = 1)
    {
        $s = "";
        
        $a = $this->adjustDate($m, $y);
        $month = $a[0];
        $year = $a[1];        
        
    	$daysInMonth = $this->getDaysInMonth($month, $year);
    	$date = getdate(mktime(12, 0, 0, $month, 1, $year));
		
		$daysBlocked = $this->getDaysBlocked($month, $year);
    	//print_r($daysBlocked );
    	$first = $date["wday"];
    	$monthName = $this->monthNames[$month - 1];
    	
    	$prev = $this->adjustDate($month - 1, $year);
    	$next = $this->adjustDate($month + 1, $year);
    	
    	if ($showYear == 1)
    	{
    	    $prevMonth = $this->getCalendarLink($prev[0], $prev[1]);
    	    $nextMonth = $this->getCalendarLink($next[0], $next[1]);
    	}
    	else
    	{
    	    $prevMonth = "";
    	    $nextMonth = "";
    	}
    	
    	$header = $monthName . (($showYear > 0) ? " " . $year : "");
//    	die("head: " . $prevMonth . " <br> " . $nextMonth);
    	$s .= "<table class='calendar' border='0'>\n";
    	$s .= "<tr>\n";
		if ($this->nonav && !$this->backnav) {
			$s .= "<td align=\"center\" valign=\"top\">&nbsp;</td>\n";
		} else {
    		$s .= "<td align=\"center\" valign=\"top\">" . (($prevMonth == "") ? "&nbsp;" : "<a href=\"$prevMonth\">&lt;&lt;</a>")  . "</td>\n";
		}
    	$s .= "<td align=\"center\" valign=\"top\" class=\"calendarHeader\" colspan=\"5\">$header</td>\n"; 
    	if ($this->nonav) {
			$s .= "<td align=\"center\" valign=\"top\">&nbsp;</td>\n";
		} else {
    		$s .= "<td align=\"center\" valign=\"top\">" . (($nextMonth == "") ? "&nbsp;" : "<a href=\"$nextMonth\">&gt;&gt;</a>")  . "</td>\n";
    	}
		$s .= "</tr>\n";
    	
    	$s .= "<tr>\n";
    	$s .= "<td align=\"center\" valign=\"top\" class=\"calendarHeader\">" . $this->dayNames[($this->startDay)%7] . "</td>\n";
    	$s .= "<td align=\"center\" valign=\"top\" class=\"calendarHeader\">" . $this->dayNames[($this->startDay+1)%7] . "</td>\n";
    	$s .= "<td align=\"center\" valign=\"top\" class=\"calendarHeader\">" . $this->dayNames[($this->startDay+2)%7] . "</td>\n";
    	$s .= "<td align=\"center\" valign=\"top\" class=\"calendarHeader\">" . $this->dayNames[($this->startDay+3)%7] . "</td>\n";
    	$s .= "<td align=\"center\" valign=\"top\" class=\"calendarHeader\">" . $this->dayNames[($this->startDay+4)%7] . "</td>\n";
    	$s .= "<td align=\"center\" valign=\"top\" class=\"calendarHeader\">" . $this->dayNames[($this->startDay+5)%7] . "</td>\n";
    	$s .= "<td align=\"center\" valign=\"top\" class=\"calendarHeader\">" . $this->dayNames[($this->startDay+6)%7] . "</td>\n";
    	$s .= "</tr>\n";
    	
    	// We need to work out what date to start at so that the first appears in the correct column
    	$theday = $this->startDay + 1 - $first;
    	while ($theday > 1)
    	{
    	    $theday -= 7;
    	}

        // Make sure we know when today is, so that we can use a different CSS style
        $today = getdate(time());
    	//echo $month . "<BR>";
		//die(print_r($today));
    	while ($theday <= $daysInMonth)
    	{
    	    $s .= "<tr>\n";       
    	    
    	    for ($i = 0; $i < 7; $i++)
    	    {
  //      	    echo $theday ."==". $today["mday"] . " - " . intval($month)  . " - " . $today["mon"] . " / " . $year ."==". $today["year"] . "<BR>";
//				die("done");
				//if ($year == $today["year"] && intval($month) == $today["mon"] && $theday == $today["mday"]) {
				if ($month.$theday.$year == date("ndY")) {
//					die("today");
					$class = "calendarToday";
				} else {
					$class = "calendarAnyDay";
				}
//				$class = ($year == $today["year"] && $month == $today["mon"] && $theday == $today["mday"]) ? "calendarToday" : "calendarAnyDay";
				$dow = date("w", mktime(0, 0, 0, $month, $theday, $year));
				$this->holidays[$theday] = false;
				//show holiday
				if ($this->confirm_holiday(date("Y-m-d", mktime(0, 0, 0, $month  , $theday, $year) ) ) ) {
					$class = "calendarHoliday";
					$this->holidays[$theday] = true; 
					
				} 
				//show weekend
				if ($dow==0 || $dow==6) {
					$class = "calendarWeekend";
					$this->holidays[$theday] = true; 
				}
    	        $s .= "<td class=\"$class\" align=\"right\" valign=\"top\">";       
				$content = "";
    	        if ($theday > 0 && $theday <= $daysInMonth)
    	        {
    	            if ($this->standard_report=="") {
						$this->standard_report = "true";
					}
					$link = $this->getDateLink($theday, $month, $year);
					if ($class=="calendarHoliday") {
						$link .= "<BR><strong>" . $this->holiday_name . "</strong>";
					}					
					$hui = date("m/d/Y", mktime(0, 0, 0, $month  , $theday, $year));
					$yesterday = date("Y-m-d", mktime(0, 0, 0, $month  , $theday-1, $year));
					$thisday = date("Y-m-d", mktime(0, 0, 0, $month  , $theday, $year));
					$tomorrow = date("Y-m-d", mktime(0, 0, 0, $month  , $theday+1, $year));
					$inserts = "";
					if (($this->showall && $this->dates!="") || (!$this->holidays[$theday] && 
					(mktime(0, 0, 0, $month  , $theday+1, $year) > mktime(0, 0, 0, date("m")  , date("d"), date("Y")))) ) {						
						//check box for day selection
						//should it be checked
						$checked = "";
						if (is_array($this->arrSelectedDays)) {
							//die(print_r($this->arrSelectedDays));
							//echo $hui . "<BR>";
							if (in_array($hui, $this->arrSelectedDays)) {
								//echo $hui . " - " . $today . "<BR>";
								$checked = " checked";
							}
						}
						//$s .= "<input type='checkbox' name='chkDay_" . mktime(0, 0, 0, $month  , $theday, $year) . "' value='Y'" . $checked . " />";
						if (!$this->pastonly) {
							if (!$this->hidechecks) {
								if (!in_array($thisday, $daysBlocked)) {
									$content = "<input type='checkbox' name='chkDay_" . mktime(0, 0, 0, $month  , $theday, $year) . "' value='Y'" . $checked . " />";
								} else {
									$content = "<div style='float:right;font-style:italic;font-size:10px;color:#999999'>blocked</div>";
								}
							}
						}
//						die($theday . " - " . $content);
						$blnDone = true;
						
					} else {
						//day in the past
						$checked = "";
						if (is_array($this->arrSelectedDays)) {
							if (in_array($hui, $this->arrSelectedDays)) {
								//echo $hui . " - " . $today . "<BR>";
								$checked = " checked";
							}
						}
						if (!$this->holidays[$theday]) {
							if (!in_array($thisday, $daysBlocked)) {
								$content = "<div style='float:right'><input type='checkbox' name='chkDay_" . mktime(0, 0, 0, $month  , $theday, $year) . "' value='Y'" . $checked . " /></div>";
							}
						}
					}
					if ($link == "") {
						$content .= "$theday<br>&nbsp;";
					} else {
						$content .= "$theday<br>$link";
					}
					if ($this->admin) {
						if (in_array($thisday, $daysBlocked)) {
							$content = "<div style='float:right'><a href='block_dates.php?del=y&month=" . $month . "&day=" . $theday . "&year=" . $year . "' title='Click to unblock this date' style='font-size:9px;color:red'>unblock</a></div>" . $content;
						} else {
							$content = "<div style='float:right'><a href='block_dates.php?month=" . $month . "&day=" . $theday . "&year=" . $year . "' title='Click to block this date' style='font-size:9px'>block</a></div>" . $content;
						}
					}
					$s .= "<table width='105' border='0' cellspacing='0' cellpadding='0'><tr><td colspan='2'><img src='https://www.matrixdocuments.com/dis/pws/images/spacer.gif' width='105' height='1' /></td></tr><tr><td width='1'><img src='https://www.matrixdocuments.com/dis/pws/images/spacer.gif' width='1' height='105' /></td><td align='left' valign='top'>" . $content . "</td></tr></table>";
					$content = "";
    	            //$s .= (($link == "") ? "$theday<br>&nbsp;" : "$theday<br>$link");
    	        }
    	        else
    	        {
    	            $s .= "<table width='105' border='0' cellspacing='0' cellpadding='0'><tr><td colspan='2'><img src='https://www.matrixdocuments.com/dis/pws/images/spacer.gif' width='105' height='1' /></td></tr><tr><td width='1'><img src='https://www.matrixdocuments.com/dis/pws/images/spacer.gif' width='1' height='105' /></td><td width='74'>&nbsp;</td></tr></table>";
    	        }
				
      	        $s .= "</td>\n";       
        	    $theday++;
    	    }
    	    $s .= "</tr>\n";    
    	}
    	
    	$s .= "</table>\n";
    	
    	return $s;  	
    }
    
    function processMonth($m, $y, $showYear = 1)
    {
        $a = $this->adjustDate($m, $y);
        $month = $a[0];
        $year = $a[1];        
        
    	$daysInMonth = $this->getDaysInMonth($month, $year);
    	$date = getdate(mktime(12, 0, 0, $month, 1, $year));
    	
    	$first = $date["wday"];
    	$monthName = $this->monthNames[$month - 1];
    	
    	$prev = $this->adjustDate($month - 1, $year);
    	$next = $this->adjustDate($month + 1, $year);   	
    	
    	// We need to work out what date to start at so that the first appears in the correct column
//		die("start: ". $this->startDay);
    	$theday = $this->startDay + 1 - $first;
    	while ($theday > 1)
    	{
    	    $theday -= 7;
    	}

        // Make sure we know when today is, so that we can use a different CSS style
        $today = getdate(time());
    	
    	while ($theday <= $daysInMonth)
    	{    
    	    for ($i = 0; $i < 7; $i++)
    	    {
        	    
				$dow = date("w", mktime(0, 0, 0, $month, $theday, $year));
				//echo $dow . "<BR>";
				$this->holidays[$theday] = false;
				//show holiday
				if ($this->confirm_holiday(date("Y-m-d", mktime(0, 0, 0, $month  , $theday, $year) ) ) ) {
					$this->holidays[$theday] = true; 
				} 
				//not saturday
				if ($dow==6) {
					$this->holidays[$theday] = true; 
				}
//				echo $theday . "<BR>";
//				die("d:" . $daysInMonth);
    	        if ($theday > 0 && $theday <= $daysInMonth) {
					$current_day = passed_var("chkDay_" . mktime(0, 0, 0, $month  , $theday, $year));
					//echo $theday . " - " . $current_day  ."<BR>";
					if ($current_day == "Y") {
						$arrSelectedDays[] = date("m/d/Y", mktime(0, 0, 0, $month  , $theday, $year));
					}
    	        }
    	    
        	    $theday++;
    	    }
    	}
		//die("done");
    	if (is_array($arrSelectedDays)) {
	    	return $arrSelectedDays;  	
		} else {
			return "";
		}
    }
    /*
        Generate the HTML for a given year
    */
    function getYearHTML($year)
    {
        $s = "";
    	$prev = $this->getCalendarLink(0, $year - 1);
    	$next = $this->getCalendarLink(0, $year + 1);
        
        $s .= "<table class=\"calendar\">\n";
        $s .= "<tr>";
    	$s .= "<td align=\"center\" valign=\"top\" align=\"left\">" . (($prev == "") ? "&nbsp;" : "<a href=\"$prev\">&lt;&lt;</a>")  . "</td>\n";
        $s .= "<td class=\"calendarHeader\" valign=\"top\" align=\"center\">" . (($this->startMonth > 1) ? $year . " - " . ($year + 1) : $year) ."</td>\n";
    	$s .= "<td align=\"center\" valign=\"top\" align=\"right\">" . (($next == "") ? "&nbsp;" : "<a href=\"$next\">&gt;&gt;</a>")  . "</td>\n";
        $s .= "</tr>\n";
        $s .= "<tr>";
        $s .= "<td class=\"calendar\" valign=\"top\">" . $this->getMonthHTML(0 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"calendar\" valign=\"top\">" . $this->getMonthHTML(1 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"calendar\" valign=\"top\">" . $this->getMonthHTML(2 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "</tr>\n";
        $s .= "<tr>\n";
        $s .= "<td class=\"calendar\" valign=\"top\">" . $this->getMonthHTML(3 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"calendar\" valign=\"top\">" . $this->getMonthHTML(4 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"calendar\" valign=\"top\">" . $this->getMonthHTML(5 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "</tr>\n";
        $s .= "<tr>\n";
        $s .= "<td class=\"calendar\" valign=\"top\">" . $this->getMonthHTML(6 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"calendar\" valign=\"top\">" . $this->getMonthHTML(7 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"calendar\" valign=\"top\">" . $this->getMonthHTML(8 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "</tr>\n";
        $s .= "<tr>\n";
        $s .= "<td class=\"calendar\" valign=\"top\">" . $this->getMonthHTML(9 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"calendar\" valign=\"top\">" . $this->getMonthHTML(10 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"calendar\" valign=\"top\">" . $this->getMonthHTML(11 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "</tr>\n";
        $s .= "</table>\n";
        
        return $s;
    }

    /*
        Adjust dates to allow months > 12 and < 0. Just adjust the years appropriately.
        e.g. Month 14 of the year 2001 is actually month 2 of year 2002.
    */
    function adjustDate($month, $year)
    {
        $a = array();  
        $a[0] = $month;
        $a[1] = $year;
        
        while ($a[0] > 12)
        {
            $a[0] -= 12;
            $a[1]++;
        }
        
        while ($a[0] <= 0)
        {
            $a[0] += 12;
            $a[1]--;
        }
        
        return $a;
    }
	function reset_link() {
		//
	}
	/* US Holiday Calculations in PHP
	 * Version 1.02
	 * by Dan Kaplan <design@abledesign.com>
	 * Last Modified: April 15, 2001
	 * ------------------------------------------------------------------------
	 * The holiday calculations on this page were assembled for
	 * use in MyCalendar:  http://abledesign.com/programs/MyCalendar/
	 * 
	 * USE THIS LIBRARY AT YOUR OWN RISK; no warranties are expressed or
	 * implied. You may modify the file however you see fit, so long as
	 * you retain this header information and any credits to other sources
	 * throughout the file.  If you make any modifications or improvements,
	 * please send them via email to Dan Kaplan <design@abledesign.com>.
	 * ------------------------------------------------------------------------
	*/
	
	// Gregorian Calendar = 1583 or later
	//if (!$_GET["y"] || ($_GET["y"] < 1583) || ($_GET["y"] > 4099)) {
	//	$_GET["y"] = date("Y",time());    // use the current year if nothing is specified
	//}
	
	function format_date($year, $month, $day) {
		// pad single digit months/days with a leading zero for consistency (aesthetics)
		// and format the date as desired: YYYY-MM-DD by default
	
		if (strlen($month) == 1) {
			$month = "0". $month;
		}
		if (strlen($day) == 1) {
			$day = "0". $day;
		}
		$date = $year ."-". $month ."-". $day;
		return $date;
	}
	
	// the following function get_holiday() is based on the work done by
	// Marcos J. Montes: http://www.smart.net/~mmontes/ushols.html
	//
	// if $week is not passed in, then we are checking for the last week of the month
	function get_holiday($year, $month, $day_of_week, $week="") {
		//echo $year.", ".$month.", ".$day_of_week . " -><br>";
		if ( (($week != "") && (($week > 5) || ($week < 1))) || ($day_of_week > 6) || ($day_of_week < 0) ) {
			// $day_of_week must be between 0 and 6 (Sun=0, ... Sat=6); $week must be between 1 and 5
			return FALSE;
		} else {
			if (!$week || ($week == "")) {
				$lastday = date("t", mktime(0,0,0,$month,1,$year));
				$temp = (date("w",mktime(0,0,0,$month,$lastday,$year)) - $day_of_week) % 7;
			} else {
				$temp = ($day_of_week - date("w",mktime(0,0,0,$month,1,$year))) % 7;
			}
			
			if ($temp < 0) {
				$temp += 7;
			}
	
			if (!$week || ($week == "")) {
				$day = $lastday - $temp;
			} else {
				$day = (7 * $week) - 6 + $temp;
			}
			//echo $year.", ".$month.", ".$day . "<br><br>";
			return $this->format_date($year, $month, $day);
		}
	}
	
	function observed_day($year, $month, $day) {
		// sat -> fri & sun -> mon, any exceptions?
		//
		// should check $lastday for bumping forward and $firstday for bumping back,
		// although New Year's & Easter look to be the only holidays that potentially
		// move to a different month, and both are accounted for.
		//echo "Year: " . $year . "<br>";
		$dow = date("w", mktime(0, 0, 0, $month, $day, $year));
		
		if ($dow == 0) {
			$dow = $day + 1;
		} elseif ($dow == 6) {
			if (($month == 1) && ($day == 1)) {    // New Year's on a Saturday
				$year--;
				$month = 12;
				$dow = 31;
			} else {
				$dow = $day - 1;
			}
		} else {
			$dow = $day;
		}
	
		return $this->format_date($year, $month, $dow);
	}
	
	function calculate_easter($y) {
		// In the text below, 'intval($var1/$var2)' represents an integer division neglecting
		// the remainder, while % is division keeping only the remainder. So 30/7=4, and 30%7=2
		//
		// This algorithm is from Practical Astronomy With Your Calculator, 2nd Edition by Peter
		// Duffett-Smith. It was originally from Butcher's Ecclesiastical Calendar, published in
		// 1876. This algorithm has also been published in the 1922 book General Astronomy by
		// Spencer Jones; in The Journal of the British Astronomical Association (Vol.88, page
		// 91, December 1977); and in Astronomical Algorithms (1991) by Jean Meeus. 
	
		$a = $y%19;
		$b = intval($y/100);
		$c = $y%100;
		$d = intval($b/4);
		$e = $b%4;
		$f = intval(($b+8)/25);
		$g = intval(($b-$f+1)/3);
		$h = (19*$a+$b-$d-$g+15)%30;
		$i = intval($c/4);
		$k = $c%4;
		$l = (32+2*$e+2*$i-$h-$k)%7;
		$m = intval(($a+11*$h+22*$l)/451);
		$p = ($h+$l-7*$m+114)%31;
		$EasterMonth = intval(($h+$l-7*$m+114)/31);    // [3 = March, 4 = April]
		$EasterDay = $p+1;    // (day in Easter Month)
		
		return $this->format_date($y, $EasterMonth, $EasterDay);
	}
	
	/////////////////////////////////////////////////////////////////////////////
	// end of calculation functions; place the dates you wish to calculate below
	/////////////////////////////////////////////////////////////////////////////
	
	function confirm_holiday($somedate="") {
		if ($somedate=="") {
			$somedate = date("Y-m-d");
		}
		$year = date("Y", strtotime($somedate));
		$blnHoliday = false;
		//newyears
		if ($somedate == $this->format_date($year, 12, 31)) {
			$blnHoliday = true;
			$this->holiday_name = "New Year's Eve";
		}
		if ($somedate == $this->observed_day($year, 1, 1)) {
			$blnHoliday = true;
			$this->holiday_name = "New Year's Day";
		}
		//Martin Luther King
		if ($somedate == $this->get_holiday($year, 1, 1, 3)) {
			//$blnHoliday = true;
			//$this->holiday_name = "Martin Luther King Day";
		}
		//President's
		if ($somedate == $this->get_holiday($year, 2, 1, 3)) {
			$blnHoliday = true;
			$this->holiday_name = "Presidents Day";
		}
		//easter
		if ($somedate == $this->calculate_easter($year)) {
			//$blnHoliday = true;
			//$this->holiday_name = "Easter";
		}
		//Memorial
		if ($somedate == $this->get_holiday($year, 5, 1)) {
			$blnHoliday = true;
			$this->holiday_name = "Memorial Day";
		}
		//july4
		if ($somedate == $this->observed_day($year, 7, 4)) {
			$blnHoliday = true;
			$this->holiday_name = "Independence Day";
		}
		//labor
		if ($somedate == $this->get_holiday($year, 9, 1, 1)) {
			$blnHoliday = true;
			$this->holiday_name = "Labor Day";
		}
		//columbus
		if ($somedate == $this->get_holiday($year, 10, 1, 2)) {
			//$blnHoliday = true;
			//$this->holiday_name = "Columbus Day";
		}
		//thanks
		if ($somedate == $this->get_holiday($year, 11, 4, 4)) {
			$blnHoliday = true;
			$this->holiday_name = "Thanksgiving";
		}
		if ($somedate == $this->get_holiday($year, 11, 5, 4)) {
			$blnHoliday = true;
			$this->holiday_name = "Day After Thanksgiving";
		}
		//xmas
		if ($somedate == $this->format_date($year, 12, 25)) {
			$blnHoliday = true;
			$this->holiday_name = "Christmas Day";
		}
		if ($somedate == $this->format_date($year, 12, 24)) {
			$blnHoliday = true;
			$this->holiday_name = "Christmas Eve";
		}
		return $blnHoliday;
	}
}
