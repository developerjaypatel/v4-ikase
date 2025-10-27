<!DOCTYPE html>
<head>
	<title>Archival script for all system UI</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
	<script>
		var link_local = "http://localhost/practice/archival-script/archival-script-api.php";
		var link_ikase = "https://ikase.org/archival-script-api.php";
		var link_ikase_v2 = "https://v2.ikase.org/archival-script-api.php";
		var link_ikase_v3 = "https://v3.ikase.org/archival-script-api.php";
		var link_ikase_v4 = "https://v4.ikase.org/archival-script-api.php";
		var link_matrix = "https://matrixdocuments.com/archival-script-api.php";
		var link_matrix_v2 = "https://v2.matrixdocuments.com/archival-script-api.php";
		var link_matrix_v4 = "https://v4.matrixdocuments.com/archival-script-api.php";

		function loadTable()
		{
			if($("#database").val()!="")
			{
				var system = $("#system").val();
				var database = $("#database").val();
				var link = "";
				switch(system)
				{
					case "local":
						link = link_local;
						break;
					case "ikase.org":
						link = link_ikase;
						break;
					case "v2.ikase.org":
						link = link_ikase_v2;
						break;
					case "v3.ikase.org":
						link = link_ikase_v3;
						break;
					case "v4.ikase.org":
						link = link_ikase_v4;
						break;
					case "matrixdocuments.com":
						link = link_matrix;
						break;
					case "v2.matrixdocuments.com":
						link = link_matrix_v2;
						break;
					case "v4.matrixdocuments.com":
						link = link_matrix_v4;
						break;	

				}
				$("#table_list").html("<i>Loading tables, please wait...</i>");
				$.get(link,{system:system,db:database}, function(data){
					if(data)
					{
						$("#table_list").html(data);
					}
					else
					{
						$("#table_list").html("Can't access the link...");
					}
				});
			}
		}

		function runArchival()
		{
			var system = $("#system").val();
			if($("#database").val()==""){
				alert("Please enter database name...");
				$("#database").focus();
				return false;
			}
			else
			{
				var database = $("#database").val();
			}

			if($("#table").val()==""){
				alert("Please enter table name...");
				$("#table").focus();
				return false;
			}
			else
			{
				var table = $("#table").val();
			}

			var link = "";
			switch(system)
			{
				case "local":
					link = link_local;
					break;
				case "ikase.org":
					link = link_ikase;
					break;
				case "v2.ikase.org":
					link = link_ikase_v2;
					break;
				case "v3.ikase.org":
					link = link_ikase_v3;
					break;
				case "v4.ikase.org":
					link = link_ikase_v4;
					break;
				case "matrixdocuments.com":
					link = link_matrix;
					break;
				case "v2.matrixdocuments.com":
					link = link_matrix_v2;
					break;
				case "v4.matrixdocuments.com":
					link = link_matrix_v4;
					break;
			}

			if(confirm("Are you sure to run archival script?")==true)
			{
				$("#table_list").html("<i>Processing, please wait...</i>");
				$.post(link,{system:system,db:database,table:table,archival: "ok"}, function(data){
					if(data)
					{
						$("#table_list").html(data);
					}
					else
					{
						$("#table_list").html("Can't access the link...");
					}
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
					<!-- <option value="local">Test in Local System</option> -->
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
			<td></td><td><input name="archival" type="button" value="OK" onclick="runArchival()" /></td>
		</tr>
	</table>
</form>
<div id="table_list"></div>
</body>
</html>