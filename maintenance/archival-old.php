<?php
	/*
		This script is for archival for all systems:
		> ikase.org
		> v2.ikase.org
		> v3.ikase.org
		> v4.ikase.org
		> matrixdocuments.com
		> v2.matrixdocuments.com
		> v4.matrixdocuments.com
	*/

	// credential of local system - for testing
	$host_local = "localhost";
	$user_local = "root";
	$password_local = "";
	
	// credential of ikase.org 
	// for IP as a host VPN must be active. For domain as a host must allow access to local system public IP (where this script will run) 
	// $host = "ikase.org";
	$host_ikase = "25.23.27.161";
	$user_ikase = "root";
	$password_ikase = "admin527#";
	
	// credential of v2.ikase.org - VPN must be active
	$host_v2_ikase = "25.65.124.225";
	$user_v2_ikase = "root";
	$password_v2_ikase = "admin527#";
	
	// credential of v3.ikase.org - VPN must be active
	$host_v3_ikase = "26.235.48.195";
	$user_v3_ikase = "root";
	$password_v3_ikase = "admin527#";
	
	// credential of v4.ikase.org - VPN must be active
	$host_v4_ikase = "26.202.83.151";
	$user_v4_ikase = "mukesh";
	$password_v4_ikase = "admin527#";
	
	// credential of matrixdocuments.com - VPN must be active
	$host_matrix = "26.236.245.53";
	$user_matrix = "Mukesh";
	$password_matrix = "Admin527#";
	
	// credential of v2.matrixdocuments.com - VPN must be active
	$host_v2_matrix = "26.58.139.228";
	$user_v2_matrix = "Mukesh";
	$password_v2_matrix = "Admin527#";
	
	// credential of v4.matrixdocuments.com
	$host_v4_matrix = "v4.matrixdocuments.com";
	$user_v4_matrix = "Mukesh";
	$password_v4_matrix = "Admin527#";

	// code to get large tables in from selected systems & DB
	if(isset($_POST['system']) && isset($_POST['db']))
	{
		$system = $_POST['system'];
		$db = $_POST['db'];
		switch($system)
		{
			case "local":
				// credential of local system - for testing
				$host = $host_local;
				$user = $user_local;
				$password = $password_local;
				break;
			case "ikase.org":
				// credential of ikase.org
				$host = $host_ikase;
				$user = $user_ikase;
				$password = $password_ikase;
				break;
			case "v2.ikase.org":
				// credential of v2.ikase.org - VPN must be active
				$host = $host_v2_ikase;
				$user = $user_v2_ikase;
				$password = $password_v2_ikase;
				break;
			case "v3.ikase.org":
				// credential of v3.ikase.org - VPN must be active
				$host = $host_v3_ikase;
				$user = $user_v3_ikase;
				$password = $password_v3_ikase;
				break;
			case "v4.ikase.org":
				// credential of v4.ikase.org - VPN must be active
				$host = $host_v4_ikase;
				$user = $user_v4_ikase;
				$password = $password_v4_ikase;
				break;
			case "matrixdocuments.com":
				// credential of matrixdocuments.com - VPN must be active
				$host = $host_matrix;
				$user = $user_matrix;
				$password = $password_matrix;
				break;
			case "v2.matrixdocuments.com":
				// credential of v2.matrixdocuments.com - VPN must be active
				$host = $host_v2_matrix;
				$user = $user_v2_matrix;
				$password = $password_v2_matrix;
				break;
			case "v4.matrixdocuments.com":
				// credential of v4.matrixdocuments.com
				$host = $host_v4_matrix;
				$user = $user_v4_matrix;
				$password = $password_v4_matrix;
				break;
			default:
				break;
		}

		// echo $host . " - " . $user . " - " . $password . " - " . $db . " - " . $table . "<br/>";

		try
		{
			// establish connection to selected system DB
			$con = new mysqli($host, $user, $password, $db) or die("Does not connect to $system system DB!");

			$query = "SELECT TABLE_NAME,TABLE_ROWS FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_ROWS >=1000 AND TABLE_SCHEMA = '". $db ."' order by TABLE_ROWS desc";
			$result = $con->query($query);

			if($result->num_rows > 0)
			{
				echo "<div class='table_list'><b>List of large tables (>=1000 rows)</b><ol>";
				while($row = $result->fetch_assoc())
				{
					echo "<li>" . $row["TABLE_NAME"] . "</li>";
				}
				echo "</ol></div>";
			}
			else
			{
				echo "<div class='table_list'>No large table (more than 1000 rows) available in " . $db . " database.</div>";
			}
		}
		catch(Exception $e)
		{
			echo "<div class='table_list'>" . $e->getMessage() . "</div>";
		}
		exit;
	}
?>
<!DOCTYPE html>
<head>
	<title>Archival script for all system</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
	<script>
		function loadTable()
		{
			if($("#database").val()!="")
			{
				var system = $("#system").val();
				var database = $("#database").val();
				$("#table_list").html("<i>Loading tables, please wait...</i>");
				$.post("archival-script-new.php",{system:system,db:database}, function(data){
					$("#table_list").html(data);
				});
			}
		}
	</script>
	<style type="text/css">
		.msg{
			font-size:20px;
			border:solid 1px;
			padding:5px;
			background-color: #f1f1f1;
			margin: 5px;
		}
		.table_list{
			margin: 5px;
			background-color: #f1f1f1;
			padding: 7px;
		}
	</style>
</head>
<body>
<h1>Archival script for all system (ikase/matrix)</h1>
<small style="color:#f00"><i>Before running script please check all parameters/options carefully. Don't run script for live system before checking!</i></small>
<form method="get">
	<table border="0" cellpadding="2">
		<tr>
			<td>Select System:</td>
			<td>
				<select name="system" id="system" onchange = "loadTable()">
					<option value="local">Test in Local System</option>
					<option value="ikase.org">ikase.org</option>
					<option value="v2.ikase.org">v2.ikase.org</option>
					<option value="v3.ikase.org">v3.ikase.org</option>
					<option value="v4.ikase.org">v4.ikase.org</option>
					<option value="matrixdocuments.com">matrixdocuments.com</option>
					<option value="v2.matrixdocuments.com">v2.matrixdocuments.com</option>
					<option value="v4.matrixdocuments.com">v4.matrixdocuments.com</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Enter Database Name:</td>
			<td>
				<input type="text" name="database" id="database" onblur="loadTable()" required />
			</td>
		</tr>
		<tr>
			<td>Enter Table Name:</td>
			<td>
				<input type="text" name="table" id="table" required />
			</td>
		</tr>
		<tr>
			<td></td><td><input name="archival" type="submit" value="OK" /></td>
		</tr>
	</table>
</form>
<div id="table_list"></div>
<?php
	ini_set("max_execution_time", 0);

	if(isset($_GET['archival']))
	{
		// checks system parameter
		if(!isset($_GET['system']))
		{
			echo "Please select system!";
			exit;
		}
		else
		{
			$system = $_GET['system'];
			echo '<script>$("#system").val("'. $system .'");</script>';
		}

		// checks database parameter
		if(!isset($_GET['database']))
		{
			echo "Please provide database name!";
			exit;
		}
		else
		{
			$db = $_GET['database'];
			echo '<script>$("#database").val("'. $db .'");</script>';
		}

		// checks table parameter
		if(!isset($_GET['table']) || empty($_GET['table']))
		{
			echo "Please provide table name!";
			exit;
		}
		else
		{
			$table = $_GET['table'];
			echo '<script>$("#table").val("'. $table .'");</script>';
		}
		
		// based on system name will set appropriate connection parameters
		switch($system)
		{
			case "local":
				// credential of local system - for testing
				$host = $host_local;
				$user = $user_local;
				$password = $password_local;
				break;
			case "ikase.org":
				// credential of ikase.org	
				$host = $host_ikase;
				$user = $user_ikase;
				$password = $password_ikase;
				break;
			case "v2.ikase.org":
				// credential of v2.ikase.org - VPN must be active
				$host = $host_v2_ikase;
				$user = $user_v2_ikase;
				$password = $password_v2_ikase;
				break;
			case "v3.ikase.org":
				// credential of v3.ikase.org - VPN must be active
				$host = $host_v3_ikase;
				$user = $user_v3_ikase;
				$password = $password_v3_ikase;
				break;
			case "v4.ikase.org":
				// credential of v4.ikase.org - VPN must be active
				$host = $host_v4_ikase;
				$user = $user_v4_ikase;
				$password = $password_v4_ikase;
				break;
			case "matrixdocuments.com":
				// credential of matrixdocuments.com - VPN must be active
				$host = $host_matrix;
				$user = $user_matrix;
				$password = $password_matrix;
				break;
			case "v2.matrixdocuments.com":
				// credential of v2.matrixdocuments.com - VPN must be active
				$host = $host_v2_matrix;
				$user = $user_v2_matrix;
				$password = $password_v2_matrix;
				break;
			case "v4.matrixdocuments.com":
				// credential of v4.matrixdocuments.com
				$host = $host_v4_matrix;
				$user = $user_v4_matrix;
				$password = $password_v4_matrix;
				break;
			default:
				break;
		}

		// echo $host . " - " . $user . " - " . $password . " - " . $db . " - " . $table . "<br/>";

		try
		{
			// establish connection to selected system DB
			$con = new mysqli($host, $user, $password, $db) or die("Does not connect to $system system DB!");
				
			$msg = "";
			$field = "";

			// check table available or not
			$query = "SHOW TABLES LIKE '". $table ."'";
			$result = $con->query($query);
			if($result->num_rows <= 0)
			{
				echo "<div class='msg'>" . $table . " table does not exists.";
				exit;
			}

			// check table_next already exists
			$query = "SHOW TABLES LIKE '". $table ."_next'";
			$result = $con->query($query);
			if($result->num_rows > 0)
			{
				echo "<div class='msg'>" . $table . "_next table already exists.</div>";
				exit;
			}

			// check table_archive already exists
			$query = "SHOW TABLES LIKE '". $table ."_archive'";
			$result = $con->query($query);
			if($result->num_rows > 0)
			{
				echo "<div class='msg'>" . $table . "_archive table already exists.</div>";
				exit;
			}

			// find primary key field which will use for recent rows
			$result = $con->query("desc $table");
			if($result->num_rows > 0)
			{
				while($row = $result->fetch_assoc())
				{
					if($row["Key"]=="PRI")
					{
						// set primary key field
						$field = $row["Field"];
					}
				}
				
				// create tablename_next table from tablename table
				$query = "create table " . $table . "_next like " . $table;
				if($con->query($query))
				{
					$msg .= $table . "_next table created.<br/>";
				}

				// insert most recent 1000 rows from tablename table to tablename_next table
				$query = "insert into " . $table . "_next select * from " . $table . " order by " . $field . " desc limit 1000";
				if($con->query($query))
				{
					$msg .= "Most recent rows taken from " . $table . " to " . $table . "_next.<br/>";
				}

				// rename the tablename table to tablename_archive
				$query = "rename table " . $table . " TO " . $table . "_archive";
				if($con->query($query))
				{
					$msg .= $table . " table rename to " . $table . "_archive.<br/>";
				}

				// rename the tablename_next table to tablename
				$query = "rename table " . $table . "_next to " . $table;
				if($con->query($query))
				{
					$msg .= $table . "_next table rename to " . $table . "<br/>";
				}
			}
			else
			{
				$msg .= "Sorry, please try again!<br/>";
			}

			echo '<div class="msg">' . $msg . '</div>';	
		}
		catch(Exception $e)
		{
			echo '<div class="msg">' . $e->getMessage() . '</div>';		
		}
	}
?>
</body>
</html>