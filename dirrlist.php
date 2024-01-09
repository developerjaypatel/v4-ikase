<?php
//This folders allow to commit/push in git 
$Allow_for_git = array("js", "api", "template");

$dir    = '../ikase.org';
$files1 = scandir($dir);
echo "<pre>";
foreach ($files1 as $key => $value) {
    if(!in_array($value, $Allow_for_git) && $value != "." && $value != ".." && $value != ".git" ){
        if(is_dir($value)){
            echo $value."/<br>";
        }else{
            echo $value."<br>";
        }
    }
}
// print_r($files1);
?>