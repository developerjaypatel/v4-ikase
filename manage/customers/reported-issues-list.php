<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="shortcut icon" href="../../img/favicon.jpg" />
	<title>Reported Issues List</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

	<link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
  <script>
  $( function() {
    $( "#date_from" ).datepicker();
    $( "#date_to" ).datepicker();
  } );
  </script>

	<style type="text/css">
		body{
			background-color: #272727;
			color:#ffffff;	
			font-family: arial;
		}
		tr:nth-child(odd)
		{
  			background-color: #4F5C7F;
  			/*background: url(../../img/glass_row.png);*/
  			color:#ffffff;
		}
		tr:nth-child(even)
		{
  			background-color: #66696D;
  			/*background: url(../../img/glass_row_shade.png);*/
  			color:#ffffff;
		}
		.pagination
		{
			text-decoration: none;
			font-size: 20px;
			color: #0000ff;
			margin: 5px;
		}
		.current_page
		{
			text-decoration: none;
			font-size: 22px;
			color: #000000;
			margin: 5px;
			font-weight: bold;
		}
		a{
			color:#9191ff !important;
		}
		table{
			border:solid 1px #ffffff;
			margin-bottom: 40px;
		}
		p{
			border: solid 1px #fff;
			border-radius: 3px;
			background-color: #585756;
			padding: 5px;
		}
		.btn{
			padding: 3px 5px;
    	background-color: aqua;
		}
	</style>
</head>
<body>
	<center><img height="30px" src="../../img/favicon.jpg"> <span style="font-size: 38px">Reported Issues List</span></center>
<?php
require_once('../../shared/legacy_session.php');
session_write_close();
// include("sec.php");

$r_link = mysqli_connect("localhost","root","admin527#","ikase");

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

$query = "select customer_id,data_source from cse_customer";

$result = mysqli_query($r_link,$query) or die("unable to run query" .  mysqli_error());

if(mysqli_num_rows($result)>0)
{
	$customer_db = array();
	while($row = mysqli_fetch_array($result))
	{
		if($row["data_source"]=="")
		{
			$customer_db[$row["customer_id"]] = "ikase";
		}
		else
		{
			$customer_db[$row["customer_id"]] = "ikase_" . $row["data_source"];
		}
	}
}

if($_SESSION['user_customer_id']==1033 && $_SESSION['user_role']=="masteradmin")
{
	$query = "select com.*,cus.cus_name,cus.cus_email,cus.cus_phone,usr.user_first_name,usr.user_last_name,usr.user_email,usr.user_cell from ikase.cse_complaint com, ikase.cse_customer cus, ikase.cse_user usr where com.user_id = usr.user_id and com.customer_id = cus.customer_id";
	$status_role = 1;
}
else
{
	$query = "select com.*,cus.cus_name,cus.cus_email,cus.cus_phone,usr.user_first_name,usr.user_last_name,usr.user_email,usr.user_cell from ikase.cse_complaint com, ikase.cse_customer cus, ikase.cse_user usr where com.user_id = usr.user_id and com.customer_id = cus.customer_id and com.user_id = " . $_SESSION['user_plain_id'];
	$status_role = 0;
}

$is_search = false;
if(isset($_GET['subject']) && !empty($_GET['subject']))
{
	$query = $query . " and com.subject like '%". $_GET['subject'] ."%'";
	$is_search = true;
}

if(isset($_GET['status']) && !empty($_GET['status']))
{
	$query = $query . " and com.status = '". $_GET['status'] ."'";
	$is_search = true;
}

if(isset($_GET['details']) && !empty($_GET['details']))
{
	$query = $query . " and com.details like '%". $_GET['details'] ."%'";
	$is_search = true;
}

if(isset($_GET['case']) && !empty($_GET['case']))
{
	$query = $query . " and com.case_name like '%". $_GET['case'] . "%'";
	$is_search = true;
}

if(isset($_GET['date_from']) && !empty($_GET['date_from']) && isset($_GET['date_to']) && !empty($_GET['date_to']))
{
	$date_from = $_GET['date_from'];
	$date_from = date("Y-m-d H:i:s", strtotime($date_from));

	$date_to = $_GET['date_to'];
	$date_to = date("Y-m-d H:i:s", strtotime($date_to));
	$date_to = date('Y-m-d H:i:s', strtotime($date_to . ' +1 day'));

	$query = $query . " and com.issue_dateandtime between '" . $date_from . "' and '" . $date_to . "'";
	$is_search = true;
}

$query = $query . " order by com.complaint_id desc";


$result = mysqli_query($r_link, $query) or die("unable to run query" .  mysql_error());

$total_records = mysqli_num_rows($result);

if(isset($_GET['page']) && is_numeric($_GET['page']))
{
	$page = $_GET['page'];
}
else
{
	$page = 1;
}

$page_size = 10;

if(isset($_GET['total_records']) && is_numeric($_GET['total_records']))
{
	$page_size = $_GET['total_records'];
}

$total_pages = ceil($total_records / $page_size);

$offset = ($page - 1) * $page_size;

$query .= " limit " . $page_size . " offset " . $offset;

$result = mysqli_query($r_link,$query) or die("unable to run query" .  mysqli_error());

if(mysqli_num_rows($result)>0)
{
	echo '<p align="center"> <big>Page:</big>';
	for($i=1;$i<=$total_pages;$i++)
	{
		if($i == $page)
		{
			echo '<a class="current_page">'. $i .'</a>';
		}
		else
		{
			if($is_search)
			{
				echo '<a class="pagination" href="https://'. $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] .'&page='. $i .'">' . $i . '</a>';
			}
			else
			{
				echo '<a class="pagination" href="?page='. $i .'">' . $i . '</a>';
			}
		}
	}

	echo '</p>';
	
	echo '<p>Search: <input type="text" name="subject" id="subject" placeholder="Subject" /> <input type="text" name="details" id="details" placeholder="Details" /> <input type="text" name="case" id="case" placeholder="Case Name" /> <select name="status" id="status"><option value="">Status</option><option value="open">Open</option><option value="progress">Progress</option><option value="close">Close</option></select> <input placeholder="Issue Date From" type="text" name="date_from" id="date_from" readonly="readonly" /> <input placeholder="Issue Date To" type="text" name="date_to" id="date_to" readonly="readonly" /> <select name="total_records" id="total_records"><option value="">Total Records</option><option value="100">100</option><option value="500">500</option><option value="100000">All</option></select> <input class="btn" type="button" value="Search" onclick="search()" /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Current Page Filter: <input type="text" id="myInput" style="" /> <input class="btn" style="display: none;" type="button" value="Print" onclick="getPrint()" /></p>';
	echo '<table align="center" border="1px" cellspacing="0px" cellpadding="5px" width="100%" bgcolor="#fff">';
	echo '<thead>';
	echo '<tr bgcolor="#66696D"><th>ID</th>';
	if($status_role == 1)
	{
		echo '<th align="left">Customer Details</th><th align="left">User Details</th><th align="left">Issue Details</th>';
		//echo '<th>Status</th>';
	}
	else
	{
		echo '<th>Case ID</th><th align="left">Subject</th><th>Date</th><th align="left">Issue Details</th><th>Issue By</th>';
	}

	echo '<th>Priority</th><th>Reported On</th><th>Status</th></tr>';
	echo '</thead>';
	echo '<tbody id="myTable">';
	while($row = mysqli_fetch_array($result))
	{

		if($row['is_read']=="N" && $status_role == 0)
		{
			echo '<tr id="row'. $row['complaint_id'] .'" valign="top" onmousemove="changeReadFlag('. $row['complaint_id'] .')">';
		}
		else
		{
			echo '<tr id="row'. $row['complaint_id'] .'" valign="top">';
		}
		echo '<td align="center">' . $row["complaint_id"] . '</td>';

		if($row["case_id"]=="0")
		{
			$case_id = "";
		}
		else
		{
			$case_id = $row["case_id"];
		}
		
		if($status_role == 1)
		{
			echo '<td><b>ID:</b>' . $row["customer_id"] . '<br><b>Name:</b>' . $row["cus_name"] . '<br><b>Email:</b>' . $row["cus_email"] . '<br><b>Phone:</b>' . $row['cus_phone'] . '</td>';
			
			echo '<td><b>ID:</b>' . $row["user_id"] . '<br><b>Logon:</b>' .  $row["user_logon"] . '<br><b>Name:</b>' . $row['user_first_name'] . '&nbsp;' . $row['user_last_name'] . '<br><b>Email:</b>' . $row['user_email'] . '<br><b>Phone:</b>' . $row['user_cell'] . '</td>';
		
		
			echo '<td><b>Case ID:</b>' . $case_id . ' - ' . $row["case_name"] . '<hr>';
			echo '<b>Subject:</b>' . $row["subject"] . '<hr>';
			echo '<b>Date:</b>' . date("m/d/Y h:i A", strtotime($row["issue_dateandtime"])) . '<hr>';

			if(isset($row["attachment"]) && !empty($row["attachment"]))
			{
				echo '<b>Attachment:</b> <a target="_blank" href="https://'. $_SERVER['SERVER_NAME'] .'/' . $row["attachment"] . '">Click Here</a><hr>';
			}

			echo '<b>Details:</b>' . $row["details"] . '<hr>';
			echo '<b>By:</b>&nbsp;&nbsp;' . $row["entered_by"] . '</td>';
			//echo '<td align="center">' . strtoupper($row["status"]) . '</td>';
		}
		else
		{
			echo '<td align="center">' . $case_id . ' - ' . $row["case_name"] . '</td>';
			echo '<td>'. $row["subject"] .'</td>';
			echo '<td align="center">'. date("m/d/Y h:i A", strtotime($row["issue_dateandtime"])) .'</td>';
			
			echo '<td>'. $row["details"];
			if(isset($row["attachment"]) && !empty($row["attachment"]))
			{
				echo '<hr/><b>Attachment:</b> <a target="_blank" href="https://'. $_SERVER['SERVER_NAME'] .'/' . $row["attachment"] . '">Click Here</a>';
			}
			echo '</td>';

			echo '<td align="center">'. $row["entered_by"] .'</td>';
		}
		echo '<td align="center">' . strtoupper($row["priority"]) . '</td>';
		echo '<td align="center">' . date("m/d/Y h:i A", strtotime($row["dateandtime"])) . '</td>';

		echo '<td align="center">';
		if($status_role==1)
		{
			echo '<select title="Change Status" id="status'. $row['complaint_id'] .'" onchange="changeStatus('. $row['complaint_id'] .')">';
			echo '<option value="open">Open</option>';
			echo '<option value="progress">Progress</option>';
			echo '<option value="close">Close</option>';
			echo '</select>';
			echo '<script>$("#status'. $row['complaint_id'] .'").val("'. $row["status"] .'")</script>';
			echo '<div style="margin-top: 5px" id="status_msg'. $row['complaint_id'] .'"></div>';
		} 
		else
		{
			echo strtoupper($row["status"]);
			if($row['is_read']=="N")
			{
				echo '<sup id="moveupdate" style="background-color:#ff0000;color:#ffffff;margin:5px;padding:0px 3px;">Update</sup>';
			}
		}
		echo '</td>';
		echo '</tr>';
	}
	echo '</tbody><tr style="display:none" id="filter_msg"><td align="center" colspan="100">Data may be available in other page!</td></tr></table>';
}
else
{
	echo '<p align="center">No issue reported! <br/><br/> <a href="reported-issues-list.php">Click Here For All Data</a></p>';
}
mysqli_close($r_link);
if($status_role==1)
{
?>
<script type="text/javascript">
	var total_page = <?= $page; ?>;
	function changeStatus(complaint_id)
	{
		var url = "https://<?= $_SERVER['SERVER_NAME'] ?>/api/complaint/status_update";
		var status = $("#status"+complaint_id).val();

		$.post(url,{complaint_id:complaint_id,status:status},function(data){
			$("#status_msg"+complaint_id).html(data);
			setTimeout(function () {
				   $("#status_msg"+complaint_id).html("");
			}, 4000);
		});
	}
</script>
<?php
}

if($status_role==0)
{
?>
<script type="text/javascript">
	function changeReadFlag(complaint_id)
	{
		var url = "https://<?= $_SERVER['SERVER_NAME'] ?>/api/complaint/read_flag";
		$.post(url,{complaint_id:complaint_id},function(data){
			//alert(data);
		});
		
		$("#row"+complaint_id).removeAttr("onmousemove");
	}
</script>
<?php
}
?>
<script>
$(document).ready(function(){
  $("#myInput").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    var count = 0;
    $("#myTable tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
      if($(this).text().toLowerCase().indexOf(value) > -1)
      {
      	count++;
      }
      if(count>0)
      {
      	$("#filter_msg").hide();
      }
      else
      {
      	$("#filter_msg").show();
      }
    });
  });
});

function getPrint()
{
	var win = window.open('', '', 'height=700,width=700');
	win.document.write($("body").html());
	win.document.close();
	win.print();    
}

function search()
{
	var url = "https://<?= $_SERVER['SERVER_NAME'] ?>/manage/customers/reported-issues-list.php?";
	
	if($("#subject").val()!="")
	{
		url = url + "&subject=" + $("#subject").val();
	}

	if($("#details").val()!="")
	{
		url = url + "&details=" + $("#details").val();
	}

	if($("#case").val()!="")
	{
		url = url + "&case=" + $("#case").val();
	}

	if($("#status").val()!="")
	{
		url = url + "&status=" + $("#status").val();
	}

	if($("#total_records").val()!="")
	{
		url = url + "&total_records=" + $("#total_records").val();
	}

	if($("#date_from").val()!="" && $("#date_to").val()!="")
	{
		url = url + "&date_from=" + $("#date_from").val() + "&date_to=" + $("#date_to").val();
	}

	window.location.href = url;
}
</script>
</body>
</html>