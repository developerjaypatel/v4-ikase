<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<link rel='stylesheet' type='text/css' href='fullcalendar-1.5.4/fullcalendar/fullcalendar.css' />
<link rel='stylesheet' type='text/css' href='fullcalendar-1.5.4/fullcalendar/fullcalendar.print.css' media='print' />
<script type="text/javascript" src="lib/moment.min.js"></script>
<script type='text/javascript' src='fullcalendar-1.5.4/jquery/jquery-1.8.1.min.js'></script>
<script type='text/javascript' src='fullcalendar-1.5.4/jquery/jquery-ui-1.8.23.custom.min.js'></script>
<script type='text/javascript' src='fullcalendar-1.5.4/fullcalendar/fullcalendar.js'></script>
<script type='text/javascript'>

	$(document).ready(function() {
		var date = new Date();
		var d = date.getDate();
		var m = date.getMonth();
		var y = date.getFullYear();
		
		
		$('#calendar').fullCalendar({
			allDayDefault: false,
			weekends: true,
			header: {
				left: 'prev,next',
				center: 'title',
				right: ''
			},
			viewDisplay: function(view) {
				//alert('The new title of the view is ' + view.title);
				//hidePanel();
			},
			eventClick: function(calEvent, jsEvent, view) {
				
				// change the border color just for fun
				$(this).css('border-color', 'yellow');
				parent.showCustomerKalendar('agendaDay', calEvent.start);
		
			},
			dayClick: function(date, allDay, jsEvent, view) {
				var event_id = "-1";
				var case_id = "";
				var event_id = "-1";
				var start_date = date;
				var day_date_click = new Date(date);
				var day_date = day_date_click.getTime();
				var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
				if (day_date < today_date) {
					return;
				}
				
				var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_"; 
				parent.composeEvent(element_id);
			},
			eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
				//save to database
				ajaxIt(event);
			},
			editable: true,
			aspectRatio: 3,
			defaultView: 'month',
			events: {
				url: 'api/events/count',
				error: function() {
					alert('there was an error while fetching events!');
				},
				color: '#CCFFCC',   // a non-ajax option
				textColor: 'black' // a non-ajax option
			}
		});
	});
</script>
<style type='text/css'>

	body {
		margin-top: 0px;
		margin-left: 0px;
		text-align: left;
		font-size: 14px;
		font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
		color:#FFFFFF;
		}

	#calendar {
		margin-left: 0px;
		width: 100%;
		height: 100%;
		margin: 0 auto;
		}
	#container {
		width:100%;
		align: left;
		margin-left:0px;
		margin: 0 auto;
		}
	.fc-button-content {
	position: relative;
	float: left;
	height: 1em;
	width:.5em;
	text-align:left;
	line-height: 1em;
	padding: 0 .6em;
	white-space: nowrap;
	}
	.fc-day-number{color:#FFFFFF;}
	a{color:#FFFFFF;}
</style>
</head>
<body>
<div id="container">
	<div id='calendar'></div>
</div>
</body>
</html>
