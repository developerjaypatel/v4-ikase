function composePhone(element_id) {
	$("#gifsave").hide();
	var case_id = -1;
	if (typeof element_id != "undefined") {
		var partieArray = element_id.split("_");
		case_id = partieArray[2];
	} else {
		case_id = current_case_id;
	}
	
	//if (customer_id != "1033") {
		$("#input_for_checkbox").hide();
		$("#myModalLabel").html("Phone Call");
		occurence = new Occurence({event_id: -1, event_kind: "phone_call"});
		occurence.set("case_id", case_id);
		occurence.set("event_kind", "phone_call");
		occurence.set("event_type", "phone_call");
		occurence.set("event_title", "Phone Call @ " + moment().format('MM/DD/YY h:mm a'));
		occurence.set("event_dateandtime", new Date());
		occurence.set("event_from", login_username);
		
		$("#modal_save_holder").html('<a title="Save Phone" class="event_dialog save" onClick="savePhoneModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
		//$("#input_for_checkbox").html('&nbsp;');
		occurence.set("holder", "myModalBody");
		$("#myModalBody").html(new event_view({model: occurence}).render().el);
		
		$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
		$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
		$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
		$(".modal-body").css("overflow-x", "hidden");
		$(".modal-dialog").css("width", "600px");
		$(".modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
		/*
		setTimeout(function() {
			$('.modal-dialog').css('top', '0px');
			$('.modal-dialog').css('margin-top', '50px')
		}, 700);
		*/
		$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
		
		$('#myModal4').modal('show');
		/*
	} else {
		$("#input_for_checkbox").hide();
		occurence = new Occurence({event_id: -1, event_kind: "phone_call"});
		occurence.set("case_id", case_id);
		occurence.set("event_kind", "phone_call");
		occurence.set("event_type", "phone_call");
		occurence.set("event_title", "Phone Call @ " + moment().format('MM/DD/YY h:mm a'));
		occurence.set("event_dateandtime", new Date());
		occurence.set("event_from", login_username);
		occurence.set("holder", "dialog_content");
		//var new_event_view_load = new event_view({model: occurence}).render().el;
		$("#dialog_content").html(new event_view({model: occurence}).render().el);
		
		setTimeout(function(){
			renderPhoneDialog(case_id);
		}, 100);
		/*
		$("#modal_save_holder").html('<a title="Save Phone" class="event_dialog save" onClick="savePhoneModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
		//$("#input_for_checkbox").html('&nbsp;');
		occurence.set("holder", "myModalBody");
		//$("#myModalBody").html(new event_view({model: occurence}).render().el);
		
		$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
		$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
		$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
		$(".modal-body").css("overflow-x", "hidden");
		$(".modal-dialog").css("width", "600px");
		$(".modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
		/*
		setTimeout(function() {
			$('.modal-dialog').css('top', '0px');
			$('.modal-dialog').css('margin-top', '50px')
		}, 700);
		
		$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
		
		//$('#myModal4').modal('show');
	}
	*/
}
function savePhoneModal(event) {
	addForm(event, "event");
	
	if (document.getElementById("apply_tasks")!=null) {
		if (document.getElementById("apply_tasks").checked) {		
			var tasks_url = 'api/task/add';
			var tasksformValues = $("#event_form").serialize();
			tasksformValues = tasksformValues.replaceAll("event", "task");
			tasksformValues = tasksformValues.replace("task_dateandtimeInput=", "ignore_me=");
			tasksformValues = tasksformValues.replace("callback_dateInput=", "task_dateandtimeInput=");
			
			//return;
			$.ajax({
			url:tasks_url,
			type:'POST',
			dataType:"json",
			data: tasksformValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					}
					resetCurrentContent();
				}
			});	
		}
	}
}
function renderPhoneDialog(case_id) {
	var dialog_width = "610px";
	//alert(case_id);
	if (case_id != "" && case_id != "undefined" && case_id != "-1") { 
		dialog_width = "1050px";
	}
	$(".dialog_content")
	//$("<div id='dialog_content' style='background:url(\"img/glass_edit_header_new_solid.png\"); color:white'>" + new_event_view_load + "</div>")
			  .dialog({
				"title" : "Phone Message",
				"width" : dialog_width,
				"position": ['center',100],
				"buttons" : { "Save" : function(){ savePhoneModal(event); $(this).dialog("close"); }, "Close" : function(){ $(this).dialog("close"); } } 
			   })
			  .dialogExtend({
				"closable" : true,
				"maximizable" : false,
				"minimizable" : true,
				"collapsable" : false,
				"dblclick" : "collapse",
				"titlebar" : "transparent",
				"minimizeLocation" : "left",
				"icons" : {
				  "close" : "ui-icon-close",
				  "maximize" : "ui-icon-circle-plus",
				  "minimize" : "ui-icon-circle-minus",
				  "collapse" : "ui-icon-triangle-1-s",
				  "restore" : "ui-icon-bullet"
				},
				"load" : function(evt, dlg){ $('#event_dateandtimeInput').css("display", ""); },
				"beforeCollapse" : function(evt, dlg){  },
				"beforeMaximize" : function(evt, dlg){  },
				"beforeMinimize" : function(evt, dlg){  },
				"beforeRestore" : function(evt, dlg){  },
				"collapse" : function(evt, dlg){  },
				"maximize" : function(evt, dlg){  },
				"minimize" : function(evt, dlg){ 
					$(".ui-dialog-titlebar").css("font-size", "0.65em"); 
				},
				"restore" : function(evt, dlg){ 
					$(".ui-dialog-titlebar").css("font-size", "1em"); 
				}
			  });
			  $(".dialog_content").css("background", "url('img/glass_edit_header_new_solid.png')");
			  $(".ui-dialog").css("background", "url('img/glass_edit_header_new_solid.png')");
			  $(".ui-dialog-buttonpane").css("background", "url('img/glass_edit_header_new_solid.png')");
			  $(".ui-dialog-titlebar").css("background", "url('img/glass_edit_header_new_solid.png')");
			  $(".ui-dialog-titlebar").css("color", "#FFFFFF");
			  $(".ui-dialog-titlebar").css("font-size", "1.2em");
			  $(".ui-dialog-titlebar").css("font-weight", "thin");
			  $(".ui-icon-close").css("top", "0px");
			  $(".ui-icon-close").css("left", "0px");
			  $(".dialog_content").css("color", "white");
			  setTimeout(function() { 
			  	$('#event_dateandtimeInput').css("display", "");
			  }, 500);
			  
}