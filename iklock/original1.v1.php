<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	

//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include("api/manage_session.php");
session_write_close();
include("api/connection.php");

include("classes/cls_comm.php");
include("classes/cls_notes.php");
include("classes/cls_user.php");

$blnLoggedIn = false;

if (!$blnLoggedIn) {
	if (isset($_SESSION['password'])) {
		if ($_SESSION['password']!="") {
			$blnLoggedIn = true;
		}
	}
}

if (!$blnLoggedIn) {
	//die(print_r($_SESSION));
	if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
		//die(print_r($_SESSION));
		header("location:index.php?cusid=-1");
		die();
	}
	//owners (and administrators?) are redirected
	if ($_SESSION['user_customer_id']==-1 && $_SESSION['user_role']=="owner") {
		$url = "location:manage/customers/";
		if (isset($_GET["session_id"])) {
			$url .= "index.php?session_id=" . $_GET["session_id"];
		}
		header($url);
		die();
	}
	//die("login failure");
}
//die(print_r($_SESSION));
include("session_check.php");
?>
<!DOCTYPE html>
<html lang="en">
	<head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="shortcut icon" href="images/favicon.png">
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        
        <!-- Optional theme -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
        
        <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:200,400' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" type="text/css" href="css/jquery.datetimepicker.css" />
        <link rel="stylesheet" type="text/css" href="css/jquery.timepicker.css">
        <link rel='stylesheet' type='text/css' href='../../lib/fullcalendar-2.7.1/fullcalendar.css' />
        
        <title>Payroll Master</title>
      
        
        <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
        <link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
        
        <link rel="manifest" href="/manifest.json">
        <meta name="msapplication-TileColor" content="#003">
        <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
        <meta name="theme-color" content="#003">
    
        <style>
            body {
                font-family: 'Source Sans Pro', sans-serif;
            }
            .navbar-inverse {
                background:#003;
            }
            .navbar-inverse .navbar-nav>.active>a, .navbar-inverse .navbar-nav>.active>a:focus, .navbar-inverse .navbar-nav>.active>a:hover {
                color:cyan;
            }
            .navbar-inverse .navbar-nav>li>a {
                color:white;
            }
            .container {
                padding-left:0px;
                padding-right:0px;
                margin-left:0px;
            }
            .content {
                margin-top:10px;
                font-family: 'Source Sans Pro', sans-serif;
            }
            td {
                padding:3px;
            }
            th {
                padding:3px;
            }
            a {
                cursor:pointer;
            }
            .page_title {
                background:#EDEDED; 
                font-size:1.6em;
            }
            .zebra_stripe tbody tr:nth-child(even) td, .zebra_stripe tbody tr.even td {
                background:#e5ecf9;
            }
            .zebra_stripe li:nth-child(odd) {
                background:transparent;
                color:white;
            }
            .zebra_stripe li:nth-child(even) {
                background:black;
                color:white;
            }
            .container {
              width: 99%;
            }
            .black_text {
                color:black;
            }
            .blue_link {
                color:blue;
                text-decoration:underline;
            }
            .calendarTitle {
                font-size: 1.6em;
            }
            .calendarToday {
                background:#FF9;
            }
            .calendarTable td{
                border:1px solid black;
                padding:2px;
                height:100px
                font-size:0.75em;
            }
            .calendarDayHeaders {
                background:#EDEDED;
                width:14%
            }
            .calendarLink {
                color:black;
                text-decoration:underline;
            }
            .go-top {
                position: fixed;
                bottom: 2em;
                right: 2em;
                text-decoration: none;
                color: white;
                background-color: rgba(0, 0, 0, 0.3);
                font-size: 12px;
                padding: 1em;
                display: none;
            }
            
            .go-top:hover {
                background-color: rgba(0, 0, 0, 0.6);
                color: white;
            }
            #logout_link {
                color:red
            }
            .list_highlight {
                background:yellow;
            }
            .letter_click {
                width:3.84%;
                padding:10px;
            }
            .data_card {
                /*
                -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px;
                border:1px solid black;
                */
                margin:5px;
                height:300px;
            }
      </style>
      
      <link href="css/main.css" rel="stylesheet" type="text/css" />
  </head>
  <body style="margin-left:0px; margin-top:0px">
	<!-- Modal -->
<div class="modal fade" id="myModal4" tabindex="-1" role="dialog" aria-labelledby="myModal4Label" aria-hidden="true" style="">
        <div class="modal-dialog" style="opacity:1">
            <div class="modal-content">
              <div class="modal-header">
                <input type="hidden" id="modal_type" value="">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <div id="modal_save_holder" style="float:right"></div>
                <div id="gifsave" style="float:right; display:none">
                    <i class="icon-spin4 animate-spin" style="font-size:1.5em"></i>
                </div>
                <h4 class="modal-title" id="myModalLabel" style="color:#FFFFFF;">Modal title</h4>
              </div>
              <div class="modal-body" id="myModalBody" style="color:#FFFFFF;">
              <i class="icon-spin4 animate-spin"></i></div>
              <div class="modal-footer" style="color:#FFFFFF; display:none">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="interoffice btn btn-primary save" onClick="saveModal()">Save changes</button>
              </div>
            </div>
          </div>
        <!-- /.modal-dialog -->
      </div>
    <!-- Wrap all page content here -->
    <div id="wrap">    
        <!-- Fixed navbar -->
        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container app_header">
            </div>
        </div>
        <!-- Begin page content -->
        <div id="serial_holder" style="float:right"></div>
        <div class="container content" id="content_top" style="margin-top:30px"></div>
        <div class="container secondary_content" id="content_bottom"></div>
    </div>
    
	<!--main dependencies-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script type="text/javascript" src="../../lib/underscore-min.js"></script>
    <script type="text/javascript" src="../../lib/backbone.js"></script>
    <script type="text/javascript" src="../../lib/backbone.localStorage.js"></script>
    <!-- libraries -->
    <script type="text/javascript" src="../../lib/moment.min.js"></script> 
	<script src="../../lib/fullcalendar-2.7.1/fullcalendar.js"></script>

    <!--color -->
    <!--<script async type="text/javascript" src="jscolor/jscolor.js"></script>-->
    
    <!--load templates-->
    <script src="js/utilities.js"></script>
    <script async src="js/mask_phone.js"></script>
    <script async src="js/cookies.js"></script>
   
   	<!--models-->
    
   	<!--views-->
    <script src="js/views/home_details.js"></script>
    <script src="js/views/navigation_view.js"></script>
    <script src="js/views/payschedule_views.js"></script>
    
	<script async type="text/javascript" src="../../lib/jquery.datetimepicker.js"></script>
    <script async type="text/javascript" src="../../lib/jquery.timepicker.js"></script>
    
    <script type="text/javascript">
	<?php
	$next_day = mktime(0, 0, 0, date("m"),   date("d") + 1,   date("Y"));
	
	$arrDay = firstAvailableDay( date("Y-m-d", $next_day));
	$next_day = $arrDay["linux_date"];
	?>
	var next_available_day = "<?php echo $next_day; ?>";
	
	<?php
	$tomorrow = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") + 1, date("Y")));
	?>
	var tomorrow = '<?php echo $tomorrow; ?>';
	var customer_id = '<?php echo $_SESSION["user_customer_id"]; ?>';
	var customer_name = '<?php echo $_SESSION["user_customer_name"]; ?>';
	var current_session_id = '<?php echo $_SESSION['user']; ?>';
	var login_name = '<?php echo $_SESSION["user_name"]; ?>';
	var customer_type = '<?php echo $_SESSION['user_customer_type']; ?>';
	<?php if (isset($_GET["masterlogin"]) || isset($_GET["session_id"])) { ?>
	window.history.pushState('v1', 'iKlock Corporate Timeclock', 'v1.php');
	<?php } ?>
	
	<?php if ($_SESSION['user_role']=="masteradmin") { ?>
	<?php } ?>
	</script>
    <!--main app -->
	<script src="js/app.js"></script>
  </body>
</html>