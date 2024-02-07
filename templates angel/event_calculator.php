<link rel="stylesheet" type="text/css" href="../css/jquery.datetimepicker.css" />
<div class="event_calculator" style="margin-left:10px">
<!-- <form action="calculator_post.php" enctype="multipart/form-data" method="post"> -->
<table width="550px" border="0" align="left" cellpadding="3" cellspacing="0" class="event_stuff" style="display:" id="event_table_screen">
	<tr id="case_id_row">
        <th align="left" valign="top" scope="row">Date:</th>
        <td colspan="2" valign="top">
        	<input name="dateInput" type="text" id="dateInput" size="30" class="modal_input" value="" />    
        </td>
  </tr>
    <tr>
      <th align="left" valign="top" scope="row">Days:</th>
      <td colspan="2" valign="top">
            <input name="number_of_days" type="text" id="number_of_days" size="30" class="modal_input" value="" />       
        </td>
    </tr>
    <tr>
      <th align="left" valign="top" scope="row">Result:</th>
      <td colspan="2" valign="top">
            <div id="date_result" class="modal_span" value=""></div>       
        </td>
    </tr>
  <tr>
  	<td colspan="2">
    	<input type='submit' id='calculate' name='calculate' onclick="javascript:posting()"/>
  </tr>
</table>
<!-- </form> -->
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script type="text/javascript" src="../lib/jquery.datetimepicker.js"></script>
<script language="javascript">
$("#dateInput").datetimepicker({timepicker: false, format:'m/d/Y'});
/*
function calculate() {
var days_val = $("#number_of_days").val();
var date_val = $("#dateInput").val();

var arrParts = date_val.split("/");

var year_val = arrParts[2];
var month_val = arrParts[0];
var day_val = arrParts[1];

//document.write(date_val_format);
var date_result = Number(day_val) + Number(days_val);

if (month_val == "01" || month_val == "03" || month_val == "05" || month_val == "07" || month_val == "08" || month_val == "10" || month_val == "12") {
	if (date_result > 31 && date_result < 61) {
		date_result = date_result - 31;
		month_val = Number(month_val) + 1;
	}
	if (date_result > 61) {
		date_result = date_result - 61;
		//document.write(date_result);
		month_val = Number(month_val) + 2;
	}
}
if (month_val == "04" || month_val == "06" || month_val == "09" || month_val == "11") {
	if (date_result > 30 && date_result < 59) {
		date_result = date_result - Number("30");
		month_val = Number(month_val) + 1;
	}
	if (date_result > "59") {
		date_result = date_result - 59;
		//document.write(date_result);
		month_val = Number(month_val) + 2;
	}
}
if (month_val == "02") {
	if (date_result > 28 && date_result < 55) {
		date_result = date_result - Number("28");
		month_val = Number(month_val) + 1;
	}
	if (date_result > "55") {
		date_result = date_result - 55;
		//document.write(date_result);
		month_val = Number(month_val) + 2;
	}
}
var date_value = year_val + "/" + month_val + "/" + date_result;
//document.write(date_value);
$("#date_result").val(date_value);
}
*/
function posting() {
	var days_val = $("#number_of_days").val();
	var date_val = $("#dateInput").val();
	
	$.ajax({
	  method: "POST",
	  url: "calculator_post.php",
	  dataType:"json",
	  data: { date: date_val, days: days_val },
	  success:function (data) {
		  if(data.error) {  // If there is an error, show the error tasks
			  alert("error");
		  } else {
			  $("#date_result").html(data.result_date);
		  }
	  }
	});
}
</script>