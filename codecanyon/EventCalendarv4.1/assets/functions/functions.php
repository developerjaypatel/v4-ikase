<?php
require("config.php");


/*==========================================================================================================*/
/*===================================== ANTI SQL INJECTION Function ========================================*/
/*==========================================================================================================*/

function antiSQLInjection($texto){
	// Words for search
	$check[1] = chr(34); // simbol "
	$check[2] = chr(39); // simbol '
	$check[3] = chr(92); // simbol /
	$check[4] = chr(96); // simbol `
	$check[5] = "drop table";
	$check[6] = "update";
	$check[7] = "alter table";
	$check[8] = "drop database";
	$check[9] = "drop";
	$check[10] = "select";
	$check[11] = "delete";
	$check[12] = "insert";
	$check[13] = "alter";
	$check[14] = "destroy";
	$check[15] = "table";
	$check[16] = "database";
	$check[17] = "union";
	$check[18] = "TABLE_NAME";
	$check[19] = "1=1";
	$check[20] = 'or 1';
	$check[21] = 'exec';
	$check[22] = 'INFORMATION_SCHEMA';
	$check[23] = 'like';
	$check[24] = 'COLUMNS';
	$check[25] = 'into';
	$check[26] = 'VALUES';

	// Cria se as variáveis $y e $x para controle no WHILE que fará a busca e substituição
	$y = 1;
	$x = sizeof($check);
	// Faz-se o WHILE, procurando alguma das palavras especificadas acima, caso encontre alguma delas, este script substituirá por um espaço em branco " ".
	while($y <= $x){
		   $target = strpos($texto,$check[$y]);
			if($target !== false){
				$texto = str_replace($check[$y], "", $texto);
			}
		$y++;
	}
	// Retorna a variável limpa sem perigos de SQL Injection
	return $texto;
}


/*==========================================================================================================*/
/*========================================= EVENT Functions ================================================*/
/*==========================================================================================================*/

// Write javascript with events listing without the need of getting it from external file
function listEvents()
{
	global $conection;
	$sql = mysqli_query($conection, "select * from events");
    $row = mysqli_num_rows($sql); //changed
		
	echo "
		
		<script>		
		document.addEventListener('DOMContentLoaded', function() {
			 var calendarEl = document.getElementById('events');
        var events = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth'
        });
			$('#events').fullCalendar({
				lang: 'en',
				defaultDate: '".date("Y-m-d")."',
				editable: true,
				eventLimit: true,
				 selectable: true,
            plugins: ['interaction', 'dayGrid'],
				displayEventTime: false,	
				header: {
					left: 'prev,next today',
					center: 'title',
					right: 'month,agendaWeek,agendaDay,listMonth'
				},				
				
				// Modal Box View							
				eventClick:  function(event, jsEvent, view) {					
					$('#modalTitle').html(event.title);
					var imgName=event.image;
					document.getElementById('imageDiv').innerHTML = '<img src='+imgName+' onerror=".'this.style.display="none"'." class=".'img-responsive'." alt=".''." >';
					$('#modalBody').html(event.description);
					$('#modalBodyLoc').html(event.location);
					$('#startTime').html(moment(event.start).format('HH:mm'));
					$('#endTime').html(moment(event.end).format('HH:mm'));
					$('#eventUrl').attr('href',event.url);
					$('#fullCalModal').modal('show');
					
					 return false;
				},
				
				// Dragable Event Update
				eventDrop: function(event, delta) {
				   var start = $.fullCalendar.moment(event.start).format();
				   var end = $.fullCalendar.moment(event.end).format();
				   $.ajax({
				   url: 'events_update.php',
				   data: 'description='+ event.description +'&location='+ event.location +'&title='+ event.title +'&start='+ start +'&end='+ end +'&url='+ event.url +'&color='+ event.color +'&id='+ event.id ,
				   type: 'POST',
				   success: function(json) {
					swal('Good job!', 'Event Updated!', 'success');
						 setTimeout(function () {
							location.reload()
						}, 1000);
					}
				   });
				},
				
				// Popover View				
				eventRender: function(eventObj, element) {
					element.on('click', e => e.preventDefault());
					var imgName = eventObj.image;
					var start = moment(eventObj.start).format('HH:mm');
					var end = moment(eventObj.end).format('HH:mm');					
					  element.popover({	
						html: true,
						title: eventObj.title,
						//Use the folowing line if you want to display the title and date on pophover view title
						/*title: eventObj.title + ' ' + start + ' - ' + end,*/
						content: '<img src='+imgName+' class=".'img-responsive popover'." onerror=".'this.style.display="none"'." alt=".''." >' + '<br/>' + eventObj.description,						
						trigger: 'hover',
						placement: 'bottom',
						container: 'body',					
					  });
				},	

				// Selectable date to create events
				selectHelper: true,
				selectable: true,	
				select: function( start, end, jsEvent, view ) {
					// set values in inputs
					$('#event1').find('input[name=start]').val(
						start.format('YYYY-MM-DD HH:mm')
					);
					$('#event1').find('input[name=end]').val(
						end.format('YYYY-MM-DD HH:mm')
					);					
					// show modal dialog
					$('#event1').modal('show');				   
				},

				events: [
					";
					while ($row = mysqli_fetch_array($sql)) {
				echo "
					{
						id: '".$row['id']."',
						title: '".utf8_decode($row['title'])."',
						image: 'assets/uploads/".$row['image']."',
						description: '".$row['description']."',
						location: '".$row['location']."',					
						start: '".$row['start']."',
						end: '".$row['end']."',
						url: '".$row['url']."',
						color: '".$row['color']."',
						allDay: false
					},"; 	
			} ;
			echo "
				],
						
			});				
		});			
	</script>
	";	
}

// Display events information inside a modal box
function modalEvents()
{

	echo "	
	
	<div id='fullCalModal' class='modal' tabindex='-1'>
	  <div class='modal-dialog'>
		<div class='modal-content'>
		  <div class='modal-header'>
			<h5 class='modal-title'><i class='fa fa-calendar' aria-hidden='true'></i> EVENT DETAILS</h5>
			<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
		  </div>
		  <div class='modal-body'>
				<div class='table-responsive'>
					<div class='col-md-12'>
						<a id='eventUrl' target='_blank'><h4><i class='fa fa-calendar' aria-hidden='true'></i> <span id='modalTitle'></span></h4></a>
						<!--<p><i class='fa fa-clock-o' aria-hidden='true'></i> <span id='startTime'></span> to <span id='endTime'></span></p> //Enable for displaying time. Use this only for the create event button method --> 
					</div>
					<div class='col-md-12'>	
						<div id='imageDiv'> </div>
						<br/>
						<h4><i class='fa fa-globe'></i> DESCRIPTION:</h4>
						 <p id='modalBody'></p>
						<h4><i class='fa fa-map-marker'></i> LOCATION:</h4>
						 <p id='modalBodyLoc'></p>
					</div>
				</div>
			</div>
		  <div class='modal-footer'>
			<button type='button' class='btn btn-primary' data-bs-dismiss='modal'>Close</button>
		  </div>
		</div>
	  </div>
	</div>
";
}

// Display all events
function listAllEventsDelete()
{
	global $conection;
	$sql = mysqli_query($conection, "select * from events ORDER BY start ASC");
    $row = mysqli_num_rows($sql);
		
		echo "<table class='table table-striped table-bordered table-hover dataTables-example' id='dataTables-example'>";
		echo "  <thead>
                <tr>	
                  <th>TITLE</th>
				  <th>LINK</th>
				  <th>START DATE</th>
				  <th>END DATE</th>
				  <th></th>
                </tr>
              </thead>";
		while ($row = mysqli_fetch_array($sql)) {
			// Print out the contents of each row into a table
			echo "<tr><td>";		
			echo $row['title'];
			echo "</td><td>"; 
			echo $row['url'];
			echo "</td><td>"; 
			echo $row['start'];
			echo "</td><td>";		
			echo $row['end'];
			echo "</td><td class='r'>
			<a href='javascript:EliminaEvento(". $row['id'] . ")'class='btn btn-danger btn-sm' role='button'><i class='fa fa-fw fa-trash'></i> DELETE</a></td>";
			echo "</tr>"; 	
		} 

		echo "</table>";
	
}

// Display all events
function listAllEventsEdit()
{
	global $conection;
	$sql = mysqli_query($conection, "select * from events ORDER BY start ASC");
    $row = mysqli_num_rows($sql);
		
		echo "<table class='table table-striped table-bordered table-hover dataTables-example' id='dataTables-example'>";
		echo "  <thead>
                <tr>	
                  <th>TITLE</th>
				  <th>LINK</th>
				  <th>START DATE</th>
				  <th>END DATE</th>
				  <th></th>
                </tr>
              </thead>";
		while ($row = mysqli_fetch_array($sql)) {
			// Print out the contents of each row into a table
			echo "<tr><td>";		
			echo $row['title'];
			echo "</td><td>"; 
			echo $row['url'];
			echo "</td><td>"; 
			echo $row['start'];
			echo "</td><td>";		
			echo $row['end'];
			echo "</td><td class='r'>
			<a href='events_edit.php?id=". $row['id'] . "' class='btn btn-primary btn-sm' role='button'><i class='fa fa-fw fa-edit'></i> EDIT</a></td>";
			echo "</tr>"; 	
		} 

		echo "</table>";
	
}

// Display all Types (sort of category for Events)
function listAllTypes()
{
	global $conection;
	$sql = mysqli_query($conection, "select * from type ORDER BY id ASC");
    $row = mysqli_num_rows($sql);
		
		echo "<table class='table table-striped table-bordered table-hover dataTables-example' id='dataTables-example'>";
		echo "  <thead>
                <tr>
					<th>ID</th>				
					<th>TITLE</th>					
					<th></th>
                </tr>
              </thead>";
		while ($row = mysqli_fetch_array($sql)) {
			// Print out the contents of each row into a table
			echo "<tr><td>";		
			echo $row['id'];
			echo "</td>";
			echo "<td>";		
			echo $row['title'];
			echo "</td>"; 			
			echo "<td class='r'>			
			<a href='javascript:EliminaTipo(". $row['id'] . ")'class='btn btn-danger btn-sm' role='button'><i class='fa fa-fw fa-trash'></i> DELETE</a></td>";
			echo "</tr>"; 	
		} 

		echo "</table>";
	
}

function getTitle($id)
{
	global $conection;
	$sql = mysqli_query($conection, "select title from events WHERE id='".$id."'");
	$row = mysqli_fetch_assoc($sql);
	
	echo "<option value='".$row['title']."' required>".$row['title']."</option>";
}

// Edit Themes Information
function editEvent($id)
{
	global $conection;
	$sql = mysqli_query($conection, "select * from events WHERE id='".$id."'");
    $row = mysqli_fetch_assoc($sql);
	$image = $row['image'];
	

    echo "
				<fieldset>		

					<!-- Text input-->
					<div class='form-group'>
						<label class='col-md-3 control-label' for='color'>Color</label>
						<div class='col-md-4'>
							<div id='cp1' class='input-group colorpicker-component'>
								<input id='cp1' type='text' class='form-control' name='color' value='".$row['color']."' required/>
								<span class='input-group-addon'><i></i></span>
							</div>
						</div>
					</div>

					<div class='form-group col-md-4'>
						<label class='col-md-3 control-label' for='start'>Start Date</label>
						<div class='input-group date form_date col-md-3' data-date='' data-date-format='yyyy-mm-dd hh:ii' data-link-field='start' data-link-format='yyyy-mm-dd hh:ii'>
							<span class='input-group-addon'><span class='glyphicon glyphicon-calendar'></span></span><input class='form-control' size='4' type='text' value='".$row['start']."' readonly>
						</div>
						<input id='start' name='start' type='hidden' value='".$row['start']."' required>

					</div>

					<div class='form-group col-md-4'>
						<label class='col-md-3 control-label' for='end'>End Date</label>
						<div class='input-group date form_date col-md-3' data-date='' data-date-format='yyyy-mm-dd hh:ii' data-link-field='end' data-link-format='yyyy-mm-dd hh:ii'>
							<span class='input-group-addon'><span class='glyphicon glyphicon-calendar'></span></span><input class='form-control' size='4' type='text' value='".$row['end']."' readonly>
						</div>
						<input id='end' name='end' type='hidden' value='".$row['end']."' required>

					</div>
					
					<!-- Text input-->
					<div class='form-group'>
						<label class='col-md-3 control-label' for='url'>Link</label>
						<div class='col-md-6'>
							 <textarea rows ='1' cols='10' id='url' name='url' type='text' class='form-control input-md'>".$row['url']."</textarea>

						</div>
					</div>

					<!-- Text input-->
					<div class='form-group'>
						<label class='col-md-3 control-label' for='description'>Description</label>
						<div class='col-md-6'>
							<textarea class='form-control' rows='5' name='description' id='description'>".$row['description']."</textarea>
						</div>
					</div>
					
					<!-- Text input-->
					<div class='form-group'>
						<label class='col-md-3 control-label' for='location'>Location</label>
						<div class='col-md-6'>
							<textarea class='form-control' rows='1' name='location' id='location'>".$row['location']."</textarea>
						</div>
					</div>
	
				";

}


// Update Themes Information
function updateEvent($id,$title,$description,$location,$start,$end,$url,$color)
{
	global $conection;
	$query = mysqli_query($conection,"UPDATE events SET title = '$title', description = '$description', location = '$location', start = '$start', end = '$end', url = '$url', color = '$color' WHERE id = '$id'");
	if (!$query) {
		echo ("No data was inserted!: " . mysqli_error());
		return false;
	} else {
			echo "<script type='text/javascript'>swal('Good job!', 'Event Updated!', 'success');</script>";
			echo '<meta http-equiv="refresh" content="1; ./">'; 
			die();
			}				
			return true;
}


// Display all events
function listAllEvents()
{
	global $conection;
	$sql = mysqli_query($conection, "select * from events ORDER BY start ASC");
    $row = mysqli_num_rows($sql);
	
		while ($row = mysqli_fetch_array($sql)) {
			//define date and time
			$starttime = $row['start'];
			$start = date('H:i',strtotime($starttime));
			$endtime = $row['end'];
			$end = date('H:i',strtotime($endtime));
			$data = date('d F Y',strtotime($starttime));
						
			echo "
			<div class='element-item transition ".$row['title']."' data-category='transition'>
				<article class='card fl-left'>
				  <section class='dates'>
					 <time datetime='".$data."'>
					 <span>".$data."</span>
					 </time>
				  </section>
				  <section class='card-cont'>
					 <small><i class='fa fa-map-marker'></i> ".$row['location']."</small>
					 <h3>".$row['title']."</h3>
					 <div class='even-date'>
						<i class='fa fa-clock-o'></i>
						
					";
						
						$s = date('H:i',strtotime($starttime));
						$e = date('H:i',strtotime($endtime));
						
						if ($s == $e)
						{
							echo "<span> All Day </span>";

						}
						if ($s != $e)
						{
							echo "<span> ".$start." to ".$end."</span>";
						}
					
				echo "
						
					 </div>
					 </br>
					 <div class='even-info'>
						<p>
						   ".$row['url']."
						   <a href='".$row['url']."' target='_blank'>Visit</a>
						</p>
						
					 </div>
					 
				  </section>
			   </article>
			</div>
			";	
		} 
			
}
