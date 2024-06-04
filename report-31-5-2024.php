<?php
require_once 'shared/legacy_session.php';

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}

include("api/connection.php");
$db = getConnection();
$dbname = "gtg_thecase";
//FIXME: what's this supposed to do? it should always be true, unless it's running from CLI
if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] == "C:\\inetpub\\wwwroot\\iKase.org") {
	$dbname = "ikase";
	if (isset($_SESSION['user_data_source']) && $_SESSION['user_data_source'] != "") {
		$dbname .= "_" . $_SESSION['user_data_source'];
	}
}
//workers/users
$sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, `user`.user_email, `user`.`dateandtime`, `user`.status, `user`.user_type, IF(`user`.user_type='1', 'admin', 'user') `role`, `user`.user_id id, `user`.user_uuid uuid, job.job_id, job.job_uuid, if(job.job IS NULL, '', job.job) job
		FROM `ikase`.`cse_user` user 
		LEFT OUTER JOIN `ikase`.`cse_user_job` cjob
		ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
		LEFT OUTER JOIN `ikase`.`cse_job` job
		ON cjob.job_uuid = job.job_uuid
		WHERE user.deleted = 'N'
		AND user.customer_id = " . $_SESSION['user_customer_id'] . "
		ORDER by user.nickname";
try {
	
	$stmt = $db->query($sql);
	$users = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}

$current_kase_search_terms = "";
$arrSearch = array();
if (isset($_SESSION["current_kase_search_terms"])) {
	if ($_SESSION["current_kase_search_terms"]!="") {
		$current_kase_search_terms = $_SESSION["current_kase_search_terms"];
		$post_terms = json_decode($current_kase_search_terms);
		
		foreach($post_terms as $pindex=>$post_term) {
			if ($post_term!="") {
				$search_term = ucwords(str_replace("_", " ", $pindex));
				//Case Throughdate
				$search_term = str_replace("Case Date", "From", $search_term);
				$search_term = str_replace("Case Throughdate", "Through", $search_term);
				$arrSearch[] = $search_term . ":&nbsp;" . $post_term;
			}
		}
	}
	//reset
	//$_SESSION["current_kase_search_terms"] = "";
	//unset($_SESSION["current_kase_search_terms"]);
}

$filename = ROOT_PATH.'sessions\search_terms_' . $_SESSION["user_plain_id"] . ".txt";
if (file_exists($filename)) {
	$arrSearch = array();
	$handle = fopen($filename, "r");
	$current_kase_search_terms = fread($handle, filesize($filename));
	fclose($handle);
	
	$post_terms = json_decode($current_kase_search_terms);
		
	foreach($post_terms as $pindex=>$post_term) {
		if ($post_term!="") {
			$search_term = ucwords(str_replace("_", " ", $pindex));
			//Case Throughdate
			$search_term = str_replace("Case Date", "From", $search_term);
			$search_term = str_replace("Case Throughdate", "Through", $search_term);
			$arrSearch[] = $search_term . ":&nbsp;" . $post_term;
		}
	}
	//die(print_r($arrSearch));
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="robots" content="noindex,nofollow">
    <link rel="shortcut icon" href="img/favicon.png">
    <link rel="stylesheet" type="text/css" href="css/jquery.datetimepicker.css" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-codes.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-embedded.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-ie7-codes.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-ie7.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/animation.css" rel="stylesheet" />
    <link rel='stylesheet' type='text/css' href='lib/fullcalendar-2.7.1/fullcalendar.css' />
    <title>iKase Reports - Legal Case Management System. Fast. Mobile</title>
    
  </head>
  <body style="">

    <!-- Wrap all page content here -->
    <div id="content"></div>

	<!--main dependencies-->
	<script type="text/javascript" src="lib/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="lib/underscore-min.js"></script>
    <script type="text/javascript" src="lib/backbone.js"></script>
    <script type="text/javascript" src="lib/backbone.localStorage.js"></script>
    <script async type="text/javascript" src="lib/jquery.datetimepicker.js"></script>
    <script type="text/javascript" src="lib/moment.min.js"></script>
    
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Latest compiled and minified JavaScript 
	<script type="text/javascript" src="lib/offline.min.js"></script>-->
    <script type="text/javascript" src="lib/moment.min.js"></script> 
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    
    
    <!--cookies-->
    <script src="js/cookies.js"></script>
    <script src="js/md5.js"></script>
    
    <!--load templates-->
    <script src="js/utilities.js?version=<?php echo $version; ?>"></script>
    <script src="lib/fullcalendar-2.7.1/fullcalendar.js?version=<?php echo $version; ?>"></script>
    
    <!--models for data access-->
	<script src="js/models/billingmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/checkmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/corporationmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/exammodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/eventmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/kasemodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/activitymodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/messagesmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/notemodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/tasksmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/worker_search_model.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/usermodel.js?version=<?php echo $version; ?>"></script>
    
    <!--views-->
    <script src="js/views/activity_listing.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/billing_listing.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/check_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/event_listing.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/exam_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/kase_listing.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/kase_occurences.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/messages_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/message_listing.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/notes_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/partie_listing.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/tasks_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/task_listing.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/user_details.js?version=<?php echo $version; ?>"></script>
    
    <script language="javascript">
	//workers
	var worker_searches = new WorkerSearchCollection();
	worker_searches.reset(<?php echo json_encode($users); ?>);
	
	//event assignee
	var arrEmployeeOptions = [];
	arrEmployeeOptions.push("<option value=''>Filter By Assignee</option>");
	worker_searches.forEach(function(element, index, array) { 
		var user_name = element.toJSON().user_name;
		var user_nickname = element.toJSON().nickname;
		if (user_name!="Matrix Admin" && user_name!="") {
			arrEmployeeOptions.push("<option value='" + user_nickname + "'>" + user_nickname + " - " + user_name.toLowerCase().capitalizeWords() + "</option>");
		}
	});
	var blnAdmin = false;
		
	<?php if ($_SESSION['user_role']=="admin" || $_SESSION['user_role']=="masteradmin") { ?>
		blnAdmin = true;
	<?php } ?>
	
	var blnPatient = false;
	<?php if ($_SESSION["user_customer_type"]=="Medical Office") { ?>
	blnPatient = true;
	<?php } ?>
	
	var user_data_path = '';
	<?php if (isset($_SESSION['user_data_path'])) { ?>
	var user_data_path = '<?php echo $_SESSION['user_data_path']; ?>';
	<?php } ?>
	var customer_name = '<?php echo $_SESSION['user_customer_name']; ?>';
	var login_user_id = '<?php echo $_SESSION['user_plain_id']; ?>';
	var login_username = '<?php echo addslashes($_SESSION['user_name']); ?>';
	var login_nickname = '<?php echo $_SESSION['user_nickname']; ?>';
	var customer_id = '<?php echo $_SESSION['user_customer_id']; ?>';
	var user_customer_name = '<?php echo $_SESSION['user_customer_name']; ?>';
	
	<?php 
	if (!isset($_SESSION["filter_attorney"])) {
		$_SESSION["filter_attorney"] = "";
	}
	if (!isset($_SESSION["filter_worker"])) {
		$_SESSION["filter_worker"] = "";
	}
	?>
	
	var filter_attorney = "<?php echo $_SESSION["filter_attorney"]; ?>";
	var filter_worker = "<?php echo $_SESSION["filter_worker"]; ?>";
	
	var assignee_filter = "<select id='assigneeFilter' class='modalInput event input_class' style='height:25px; width:210px;'>" + arrEmployeeOptions.join("") + "</select>";
	
	var arrSearch = [];
	
	<?php if (count($arrSearch) > 0) { ?>
	arrSearch = [<?php echo '"' . implode('","', $arrSearch) . '"'; ?>];
	<?php } ?>
	
	</script>
    <!--main app -->
	<script src="js/app_report.js?version=<?php echo $version; ?>"></script>
  </body>
</html>
