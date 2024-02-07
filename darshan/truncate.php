<?php
$servername = "localhost";
$username = "NewAdmin";
$password = "NewAccess527!";
$con = mysql_connect($servername,$username,$password);

if($con)
{
   	$db=mysql_select_db("ikase_darshan");
    if($db)
    {
      $truncate_sql ="TRUNCATE TABLE  cse_case";
      $truncate_sql1 ="TRUNCATE TABLE  cse_injury";
      $truncate_sql2 ="TRUNCATE TABLE  cse_case_injury";
      $truncate_sql3 ="TRUNCATE TABLE  cse_corporation";
      $truncate_sql4 ="TRUNCATE TABLE  cse_case_corporation";
      $truncate_sql5 ="TRUNCATE TABLE  cse_partie_type";
      $truncate_sql6 ="TRUNCATE TABLE  cse_corporation_adhoc";

      
  
      $truncate_result=mysql_query($truncate_sql);
      if($truncate_result){
              echo " success truncate cse_case";
      }else{
             echo " failed truncate cse_case".mysql_error();
      }

      $truncate_result1=mysql_query($truncate_sql1);
      if($truncate_result1){
        echo " success truncate cse_injury";
      }else{
             echo " failed truncate cse_injury".mysql_error();
        
      }

      $truncate_result2=mysql_query($truncate_sql2);
      if($truncate_result2){
        echo " success truncate cse_case_injury";
      }else{
             echo " failed truncate cse_case_injury".mysql_error();
        
      }

      $truncate_result3=mysql_query($truncate_sql3);
      if($truncate_result3){

       echo " success truncate cse_corporation";
      }else{
             echo " failed truncate cse_corporation".mysql_error();
        
      }

      $truncate_result4=mysql_query($truncate_sql4);
      if($truncate_result4){

       echo " success truncate cse_case_corporation";
      }else{
             echo " failed truncate cse_case_corporation".mysql_error(); 
        
      }

      $truncate_result5=mysql_query($truncate_sql5);
      if($truncate_result5){

       echo " success truncate cse_partie_type";
      }else{
             echo " failed truncate cse_partie_type".mysql_error(); 
        
      }

      $truncate_result6=mysql_query($truncate_sql6);
      if($truncate_result6){

       echo " success truncate cse_corporation_adhoc";
      }else{
             echo " failed truncate cse_corporation_adhoc".mysql_error(); 
        
      }


    	} else{
        echo "soething wrong in db connection ".mysql_error();

      }
     } else {
	die("connection die");
}

?>






