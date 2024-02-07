<?php /** @noinspection PhpUnused */
/*******************************************************************************
 *                ZIP Code and Distance Claculation Class
 *******************************************************************************
 *      Author:     Micah Carrick
 *      Email:      email@micahcarrick.com
 *      Website:    http://www.micahcarrick.com
 *
 *      File:       zipcode.class.php
 *      Version:    2.0.0
 *      Copyright:  (c) 2005 - Micah Carrick
 *                  You are free to use, distribute, and modify this software
 *                  under the terms of the GNU General Public License.  See the
 *                  included license.txt file.
 *
 *******************************************************************************
 *  VERSION HISTORY:
 *      v2.0.0 [May 17, 2020] - Why am I (igorsantos07) even writing this? Updated code to use PDO after throwing a fit on this ancient code. Now I understand why I found a "lattitude" somewhere else in our codebase... This class is FULL OF TYPOS!!!
 *      v1.2.0 [Oct 22, 2006] - Using a completely new database based on user
 * contributions which resolves many data bugs.
 * - Added sorting to get_zips_in_range()
 * - Added ability to include/exclude the base zip
 * from get_zips_in_range()
 *      v1.1.0 [Apr 30, 2005] - Added Jeff Bearer's code to make it MUCH faster!
 *      v1.0.1 [Apr 22, 2005] - Fixed a typo :)
 *      v1.0.0 [Apr 12, 2005] - Initial Version
 *
 *******************************************************************************
 *  DESCRIPTION:
 *    A PHP Class and MySQL table to find the distance between zip codes and
 *    find all zip codes within a given mileage or kilometer range.
 *
 *******************************************************************************
 */

class Zipcode extends DbAccess {

    const UNIT_MILES         = 'm';
    const UNIT_KILOMETERS    = 'k';
    const SORT_DISTANCE_ASC  = 1;
    const SORT_DISTANCE_DESC = 2;
    const SORT_ZIP_ASC       = 3;
    const SORT_ZIP_DESC      = 4;
    const M2KM_FACTOR        = 1.609344;

    public $units      = self::UNIT_MILES;        // miles or kilometers
    public $decimals   = 2;               // decimal places for returned distance

    // returns the distance between to zip codes.  If there is an error, the
    // function will return false and set the $last_error variable.
    function get_distance($zip1, $zip2) {
        if ($zip1 == $zip2) {
            return 0;
        } // same zip code means 0 miles between. :)

        // get details from database about each zip and exit if there is an error

        $details1 = $this->get_zip_point($zip1);
        $details2 = $this->get_zip_point($zip2);
        if ($details1 == false) {
            $this->last_error = "No details found for zip code: $zip1";
            return false;
        }
        if ($details2 == false) {
            $this->last_error = "No details found for zip code: $zip2";
            return false;
        }

        // calculate the distance between the two points based on the lattitude
        // and longitude pulled out of the database.

        $miles = $this->calculate_mileage($details1[0], $details2[0], $details1[1], $details2[1]);

        if ($this->units == self::UNIT_KILOMETERS) {
            return round($miles * self::M2KM_FACTOR, $this->decimals);
        } else {
            return round($miles, $this->decimals);
        }       // must be miles

    }

    function get_zip_details($zip) {
        $sql = "SELECT lat AS lattitude, lon AS longitude, city, county, state_prefix, 
              state_name, area_code, time_zone
              FROM zip_code 
              WHERE zip_code LIKE '$zip%'";

        return $this->getResults($sql);
    }

    function get_zip_list($zip) {
        $sql = "SELECT city, state_prefix state,zip_code zip
              FROM zip_code 
              WHERE zip_code LIKE '$zip%'
			  OR city LIKE '%".$zip."%'";

        return $this->getResults($sql);
    }

    function get_city_details($city) {
        $sql = "SELECT city, county, state_prefix, 
              state_name, area_code, time_zone
              FROM zip_code 
              WHERE city LIKE '%$city%'";

        return $this->getResults($sql);
    }

    function get_zip_city($zip) {
        $sql = "SELECT city FROM zip_code WHERE zip_code='$zip'";
        return $this->getResults($sql);
    }

    // This function pulls just the lattitude and longitude from the database for a given zip code.
    function get_zip_point($zip) {
        $sql = "SELECT lat, lon from zip_code WHERE zip_code='$zip'";
        return $this->getResults($sql, PDO::FETCH_BOTH);
    }

    /**
     * Actually performs that calculation to determine the mileage between 2 points
     * defined by lattitude and longitude coordinates.
     * @see http://www.cryptnet.net/fsp/zipdy/ Based on the code found there
     * @param float $lat1
     * @param float $lat2
     * @param float $lon1
     * @param float $lon2
     * @return float
     */
    private function calculate_mileage($lat1, $lat2, $lon1, $lon2) {

        // Convert lattitude/longitude (degrees) to radians for calculations
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // Find the deltas
        $delta_lat = $lat2 - $lat1;
        $delta_lon = $lon2 - $lon1;

        // Find the Great Circle distance
        $temp     = pow(sin($delta_lat / 2.0), 2) + cos($lat1) * cos($lat2) * pow(sin($delta_lon / 2.0), 2);
        return 3956 * 2 * atan2(sqrt($temp), sqrt(1 - $temp));
    }

    // returns an array of the zip codes within $range of $zip. Returns
    // an array with keys as zip codes and values as the distance from
    // the zipcode defined in $zip.
    function get_zips_in_range($zip, $range, $sort = 1, $include_base = false) {
        $details = $this->get_zip_point($zip);  // base zip details
        if ($details == false) {
            return false;
        }

        // This portion of the routine  calculates the minimum and maximum lat and
        // long within a given range.  This portion of the code was written
        // by Jeff Bearer (http://www.jeffbearer.com). This significanly decreases
        // the time it takes to execute a query.  My demo took 3.2 seconds in
        // v1.0.0 and now executes in 0.4 seconds!  Greate job Jeff!

        // Find Max - Min Lat / Long for Radius and zero point and query
        // only zips in that range.
        $lat_range = $range / 69.172;
        $lon_range = abs($range / (cos($details[0]) * 69.172));
        $min_lat   = number_format($details[0] - $lat_range, "4", ".", "");
        $max_lat   = number_format($details[0] + $lat_range, "4", ".", "");
        $min_lon   = number_format($details[1] - $lon_range, "4", ".", "");
        $max_lon   = number_format($details[1] + $lon_range, "4", ".", "");

        $return = [];    // declared here for scope

        $sql = "SELECT zip_code, lat, lon FROM zip_code ";
        if (!$include_base) {
            $sql .= "WHERE zip_code <> '$zip' AND ";
        } else {
            $sql .= "WHERE ";
        }
        $sql .= "lat BETWEEN '$min_lat' AND '$max_lat' 
               AND lon BETWEEN '$min_lon' AND '$max_lon'";

        $results = $this->getResults($sql, PDO::FETCH_NUM, true);
        foreach ($results as $row) {

            // loop through all 40 some thousand zip codes and determine whether
            // or not it's within the specified range.

            $dist = $this->calculate_mileage($details[0], $row[1], $details[1], $row[2]);
            if ($this->units == self::UNIT_KILOMETERS) {
                $dist *= self::M2KM_FACTOR;
            }
            if ($dist <= $range) {
                $return[str_pad($row[0], 5, "0", STR_PAD_LEFT)] = round($dist, $this->decimals);
            }
        }

        // sort array
        switch ($sort) {
            case self::SORT_DISTANCE_ASC:
                asort($return);
                break;

            case self::SORT_DISTANCE_DESC:
                arsort($return);
                break;

            case self::SORT_ZIP_ASC:
                ksort($return);
                break;

            case self::SORT_ZIP_DESC:
                krsort($return);
                break;
        }

        if (empty($return)) {
            return false;
        }
        return $return;
    }
}
