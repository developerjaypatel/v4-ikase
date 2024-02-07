<?php
require("assets/functions/config.php");
/* Values received via ajax */
$id = $_POST['id'];
$title = $_POST['title'];
$description = $_POST['description'];
$location = $_POST['location'];
$start = $_POST['start'];
$end = $_POST['end'];
$url = $_POST['url'];
$color = $_POST['color'];
// update the records
$sql = "UPDATE events SET title=?, description=?, location=?, start=?, end=?, url=?, color=? WHERE id=?";
$q = $db->prepare($sql);
$q->execute(array($title,$description,$location,$start,$end,$url,$color,$id));
?>