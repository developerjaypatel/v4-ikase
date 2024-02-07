<?php
$sql = "CREATE TABLE `docslib` (
  `date` date DEFAULT NULL,
  `form_desc` varchar(120) CHARACTER SET latin1 DEFAULT NULL,
  `cpointer` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `recno` float DEFAULT NULL,
  `author` varchar(10) CHARACTER SET latin1 DEFAULT NULL,
  `document` longblob,
  `locator` float DEFAULT NULL,
  `pldmaster` tinyint(1) DEFAULT NULL,
  `masterpld` tinyint(1) DEFAULT NULL,
  `wptype` varchar(45) CHARACTER SET latin1 DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$sql = "ALTER TABLE `goldberg2_docs`.`docsXX` 
ADD INDEX `cpointer` (`cpointer` ASC),
ADD INDEX `recno` (`recno` ASC);
;
";
for($int=2; $int < 37; $int++) {
	$sql_create = str_replace("XX", $int, $sql);
	
	echo $sql_create . "<br /><br />";
}
?>