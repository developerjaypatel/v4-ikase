<?php

if (!isset($skip_functions)) {
	$skip_functions = false;
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

if (isset($_GET["y"])) {
	if (!$_GET["y"] || ($_GET["y"] < 1583) || ($_GET["y"] > 4099)) {
		$_GET["y"] = date("Y",time());    // use the current year if nothing is specified
	}
} else {
	$_GET["y"] = date("Y",time());    // use the current year if nothing is specified
}

 

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
    if ( (($week != "") && (($week > 5) || ($week < 1))) || ($day_of_week >
6) || ($day_of_week < 0) ) {
        // $day_of_week must be between 0 and 6 (Sun=0, ... Sat=6); $week must be between 1 and 5
        return FALSE;
    } else {
        if (!$week || ($week == "")) {
            $lastday = date("t", mktime(0,0,0,$month,1,$year));
            $temp = (date("w",mktime(0,0,0,$month,$lastday,$year)) -
$day_of_week) % 7;
        } else {
            $temp = ($day_of_week - date("w",mktime(0,0,0,$month,1,$year)))
% 7;
        }

        if ($temp < 0) {
            $temp += 7;
        }

        if (!$week || ($week == "")) {
            $day = $lastday - $temp;
        } else {
            $day = (7 * $week) - 6 + $temp;
        }
		//echo $year.", ".$month.", ".$day ."<br><br>";
        return format_date($year, $month, $day);
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
 
    return format_date($year, $month, $dow);
}

function calculate_easter($y) {
    // In the text below, 'intval($var1/$var2)' represents an integer division neglecting
    // the remainder, while % is division keeping only the remainder. 
    //So 30/7=4, and 30%7=2
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
	
	return format_date($y, $EasterMonth, $EasterDay);

}

 

/////////////////////////////////////////////////////////////////////////////
// end of calculation functions; place the dates you wish to calculate below
/////////////////////////////////////////////////////////////////////////////
function DateAdd($interval, $number, $date) {

    $date_time_array = getdate($date);
	//die(print_r($date_time_array));
	
    $hours = $date_time_array["hours"];
    $minutes = $date_time_array["minutes"];
    $seconds = $date_time_array["seconds"];
    $month = $date_time_array["mon"];
    $day = $date_time_array["mday"];
    $year = $date_time_array["year"];

    switch ($interval) {
    
        case "yyyy":
            $year+=$number;
            break;
        case "q":
            $year+=($number*3);
            break;
        case "m":
            $month+=$number;
            break;
        case "y":
        case "d":
        case "w":
            $day+=$number;
            break;
        case "ww":
            $day+=($number*7);
            break;
        case "h":
            $hours+=$number;
            break;
        case "n":
            $minutes+=$number;
            break;
        case "s":
            $seconds+=$number; 
            break;            
    }
//		echo "day:" . $day;
       $timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
    return $timestamp;
}

function nextWorkingDay($number_days, $start_date = "") {
    $day_counter = 0;
    $intCounter = 0;    

    if ($start_date=="") {
        $today  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
    } else {
        $start_time = strtotime($start_date);
        $today  = mktime(0, 0, 0, date("m", $start_time)  , date("d", $start_time), date("Y", $start_time));
    }
	
	//might just be a validation request
	if ($number_days==0) {
		$working_time = $start_time;
		$working_date = date("Y-m-d", $today);
		
		while (isWeekend($working_date) || confirm_holiday(date("Y-m-d", strtotime($working_date))) ) {
			$working_time = DateAdd("d", 1, $today);    
        	$working_date = date("Y-m-d", $working_time);
			
			$today  = $working_time;
        }
		return $working_date;
	}
    while($day_counter < $number_days) {
        $working_time = DateAdd("d", 1, $today);    
        $working_date = date("Y-m-d", $working_time);
		if (!isWeekend($working_date) && !confirm_holiday(date("Y-m-d", strtotime($working_date))) ) {
			$day_counter++;
        }
        $intCounter++;
        $today  = $working_time;
        if ($intCounter > 1000) {
            //just in case out of control?
            break;
        }
    }

    return $working_date;
}
function isWeekend($check_date) {
	return (date("N",  strtotime($check_date)) > 5);
}
function confirm_holiday($somedate="") {
	if ($somedate=="") {
		$somedate = date("Y-m-d");
	}

	$year = date("Y", strtotime($somedate));

	$blnHoliday = false;

	//newyears
	if ($somedate == observed_day($year, 1, 1)) {
		$blnHoliday = true;
	}

	if ($somedate == format_date($year, 1, 1)) {
		$blnHoliday = true;
	}

	if ($somedate == format_date($year, 12, 31)) {
		$blnHoliday = true;
	}

	//Martin Luther King
	if ($somedate == get_holiday($year, 1, 1, 3)) {
		$blnHoliday = true;
	}

	//President's
	if ($somedate == get_holiday($year, 2, 1, 3)) {
		$blnHoliday = true;
	}

	//easter
	if ($somedate == calculate_easter($year)) {
		$blnHoliday = true;
	}

	//Memorial
	if ($somedate == get_holiday($year, 5, 1)) {
		$blnHoliday = true;
	}

	//july4
	if ($somedate == observed_day($year, 7, 4)) {
		$blnHoliday = true;
	}

	//labor
	if ($somedate == get_holiday($year, 9, 1, 1)) {
		$blnHoliday = true;
	}

	//columbus
	if ($somedate == get_holiday($year, 10, 1, 2)) {
		$blnHoliday = true;
	}

	//thanks
	//die($somedate." == ".get_holiday($year, 11, 4, 4));
	if ($somedate == get_holiday($year, 11, 4, 4)) {
		$blnHoliday = true;
	}

	//xmas
	if ($somedate == format_date($year, 12, 24)) {
		$blnHoliday = true;
	}

	if ($somedate == format_date($year, 12, 25)) {
		$blnHoliday = true;
	}
	return $blnHoliday;
}
//this is still from old code, dont' need i think
$pf_time = strtotime("+3 days");
$test_date = date("Y-m-d", $pf_time);

if (confirm_holiday($test_date)) {
	$pf_time = strtotime("+4 days");
}

//format the date using the timestamp generated

$dow = date("D", $pf_time);

//echo "day: " . $dow . "<br>";

if ($dow=="Sat" || $dow=="Sun") {
	$pf_date = date("m/d/Y", strtotime("this Monday"));
	$test_date = date("Y-m-d", strtotime("this Monday"));
	if (confirm_holiday($test_date)) {
		$pf_date = date("m/d/Y", strtotime("this
Tuesday"));
	}
} else {
	$pf_date = date("m/d/Y", $pf_time);
}

$callback_date = $pf_date;
?>