<?php
	header("Access-Control-Allow-Origin: *");
	/*
		This script is for archival for all systems:
		> ikase.org
		> v2.ikase.org
		> v3.ikase.org
		> v4.ikase.org
		> matrixdocuments.com
		> v2.matrixdocuments.com
		> v4.matrixdocuments.com
		Must host on each system with link should be like - https://domain/archival-script-api.php
	*/

	// credential of local system - for testing
	$host_local = "localhost";
	$user_local = "root";
	$password_local = "";
	
	// credential of ikase.org 
	$host_ikase = "localhost";
	$user_ikase = "root";
	$password_ikase = "admin527#";
	
	// credential of v2.ikase.org
	$host_v2_ikase = "localhost";
	$user_v2_ikase = "root";
	$password_v2_ikase = "admin527#";
	
	// credential of v3.ikase.org
	$host_v3_ikase = "localhost";
	$user_v3_ikase = "root";
	$password_v3_ikase = "admin527#";
	
	// credential of v4.ikase.org
	$host_v4_ikase = "localhost";
	$user_v4_ikase = "mukesh";
	$password_v4_ikase = "admin527#";
	
	// credential of matrixdocuments.com
	$host_matrix = "localhost";
	$user_matrix = "Mukesh";
	$password_matrix = "Admin527#";
	
	// credential of v2.matrixdocuments.com
	$host_v2_matrix = "localhost";
	$user_v2_matrix = "Mukesh";
	$password_v2_matrix = "Admin527#";
	
	// credential of v4.matrixdocuments.com
	$host_v4_matrix = "localhost";
	$user_v4_matrix = "Mukesh";
	$password_v4_matrix = "Admin527#";

	// code to get large tables in from selected systems & DB
	if(isset($_GET['system']) && isset($_GET['db']))
	{
		$system = $_GET['system'];
		$db = $_GET['db'];
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

		// echo $host . " - " . $user . " - " . $password . " - " . $db ;

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

	// code start for table archival
	if(isset($_POST['archival']))
	{
		// checks system parameter
		if(!isset($_POST['system']))
		{
			echo "Please select system!";
			exit;
		}
		else
		{
			$system = $_POST['system'];			
		}

		// checks database parameter
		if(!isset($_POST['db']))
		{
			echo "Please provide database name!";
			exit;
		}
		else
		{
			$db = $_POST['db'];
		}

		// checks table parameter
		if(!isset($_POST['table']) || empty($_POST['table']))
		{
			echo "Please provide table name!";
			exit;
		}
		else
		{
			$table = $_POST['table'];
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
		// exit;
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