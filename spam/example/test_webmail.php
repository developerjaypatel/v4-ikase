<?php
//die(print_r($_SERVER));
/*
***************************************************************************
*   Copyright (C) 2007 by Cesar D. Rodas                                  *
*   cesar@sixdegrees.com.br                                               *
*                                                                         *
*   Permission is hereby granted, free of charge, to any person obtaining *
*   a copy of this software and associated documentation files (the       *
*   "Software"), to deal in the Software without restriction, including   *
*   without limitation the rights to use, copy, modify, merge, publish,   *
*   distribute, sublicense, and/or sell copies of the Software, and to    *
*   permit persons to whom the Software is furnished to do so, subject to *
*   the following conditions:                                             *
*                                                                         *
*   The above copyright notice and this permission notice shall be        *
*   included in all copies or substantial portions of the Software.       *
*                                                                         *
*   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,       *
*   EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF    *
*   MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.*
*   IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR     *
*   OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, *
*   ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR *
*   OTHER DEALINGS IN THE SOFTWARE.                                       *
***************************************************************************
*/ 
require("../spam.php");
$dbhost = "52.24.207.176";
$dbuser="root";
$dbpass="admin527#";
$dbname="ikase";
$db = mysql_connect($dbhost,$dbuser,$dbpass);
mysql_select_db($dbname,$db);
/**
 *
 *    Because the system do not manage a method where you 
 *    can save the data, you must define a function which recives
 *    the wanted "n-grams" and return and array which is 
 *    "n-grams" and percent of accuracy (what its learn with example_trainer).
 *    In this example those datas are loaded from mysql.
 *
 */
$spam = new spam("handler");
/**/
$sql=mysql_query("select message text, '1', `from` from cse_webmail ORDER BY webmail_id ASC LIMIT 0, 5",$db);
echo "<h1>Spam test</h1>";
print '<table cellpadding="5" cellspacing="0" width="100%">';
echo "<tr align='center'>";
echo "<td><h1>From</h1></td>";
echo "<td><h1>Spam</h1></td>";
echo "<td><h1>Keywords</h1></td>";
echo "</tr>";
$i=0;

require_once '../spamfilter.php';

while ($text=mysql_fetch_array($sql)){
	/*
    $score1 = number_format($spam->isItSpam_v2($text[0],'spam'),2);
    $score2 = number_format( $spam->isItSpam($text[0],'spam'),2);
	*/
	// Search in all available blacklists
	$filter = new SpamFilter();

	$result = $filter->check_text($text[0]);
	$spam = "ok";
	if ($result) {
		$spam = "spam (" . $result . ")";
	}
	
	$stopwords = file('C:\\inetpub\\wwwroot\\iKase.org\\spam\\stop_words.txt');
	//$text = "Requirements - Working knowledge, on LAMP Environment using Linux, Apache 2, MySQL 5 and PHP 5, - Knowledge of Web 2.0 Standards - Comfortable with JSON - Hands on Experience on working with Frameworks, Zend, OOPs - Cross Browser Javascripting, JQuery etc. - Knowledge of Version Control Software such as sub-version will be preferable.";

	$keywords = stopWords($text[0], $stopwords);
	$final_array = array_count_values($keywords);
	//die(print_r($final_array));
	arsort($final_array);
	$final_array = array_slice($final_array, 0, 3);
	$final_array = array_keys($final_array);
	//die(print_r($final_array));

    echo "<tr bgcolor='".(++$i%2 == 0 ? 'white' : '#c0c0c0')."'>";
    echo "<td valign='top' width=50%>$text[2]</td>";
    echo "<td valign='top' width=10%>". $spam . "</td>";
	echo "<td valign='top'>". implode("<br />", $final_array) . "</td>";
    echo "</tr>";
}
print "</table>";

/**
 *  Callback function
 *
 *  This is function is called by the classifier class, and it must 
 *  return all the n-grams.
 *  
 *  @param Array $ngrams N-grams.
 *  @param String $type Type of set to compare
 */
function handler($ngrams,$type) {
    global $db;
    
    $info = array_keys($ngrams);
    
    $sql = "select ngram,percent from knowledge_base where belongs = '$type' && ngram in ('".implode("','",$info)."')";
    $r = mysql_query($sql,$db);
    
    while ( $row = mysql_fetch_array($r) ) {
        $t[ $row['ngram'] ]  = $row['percent'];     
    }

    return $t;
}
?>