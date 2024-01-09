window.workflow_sheet_view = Backbone.View.extend({
    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		
    },
	events:{
		"change #case_typeInput":							"setWorkflowNumber",
		"click .action_button":								"addAction",
		"click #reset_action_button":						"resetAction",
		"click .save_trigger":								"saveWorkflow",
		"click .delete_trigger":							"deleteTrigger",
		"keyup .workflow_value":							"setChanged",
		"change .workflow_value":							"setChanged",
		"change .trigger_time":								"changeTriggerTime",
		"click .assign_other":								"showNotification",
		"click #workflow_sheet_all_done":					"doTimeouts"
    },
    render: function () {
		var self = this;
		if (typeof this.template != "function") {
			var view = "workflow_sheet_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		$(document).attr('title', "iKase Workflows");
		
		this.model.set("blnChanged", false);
		
		var select_options = setting_options;
		select_options = select_options.replace("<option value=''>Filter By Type</option>", "<option value=''>Select Trigger Event from List</option>");
		select_options = select_options.replace("<option style='font-size: 1pt; background-color: #999999;' disabled>", "");
		select_options = select_options.replace("<option style='font-size: 1pt; background-color: #000000;' disabled>&nbsp;</option>", "");
		select_options = select_options.replace("<option value='case_type_wc'>WC</option><option value='case_type_pi'>PI</option><option value='new_filter'>Manage List</option>", "");
		
		this.model.set("select_options", select_options);
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
        return this;
	},
	setChanged: function() {
		this.model.set("blnChanged", true);
	},
	setWorkflowNumber:function() {
		var case_type = $("#case_typeInput").val();
		var prefix = "";
		
		switch(case_type) {
			case "WCAB":
				prefix = "WC";
				break;
			case "NewPI":
				prefix = "PI";
				break;
			case "social_security":
				prefix = "SSN";
				break;
			case "civil":
				prefix = "CV";
				break;
			case "class_action":
				prefix = "CA";
				break;
			case "class_action":
				prefix = "CA";
				break;
			case "employment_law":
				prefix = "EM";
				break;
			case "immigration":
				prefix = "IM";
				break;
			case "WCAB_Defense":
				prefix = "WCD";
				break;
		}
		
		//get the next id
		var url = "api/workflownext";
						
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			data: "",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//$("#workflow_prefix").html(prefix + "&nbsp;-&nbsp;");
					$("#workflow_number_holder").fadeIn();
					$("#workflow_buttons_holder").fadeIn();
					$("#workflow_number").val(prefix + "-" + data.max_id);
				}
			}
		});
	},
	changeTriggerTime: function(event) {
		var self = this;
		var element =  event.currentTarget;
		var arrID = element.id.split("_");
		var flow = arrID[arrID.length - 1];
		var action = arrID[arrID.length - 2];
		
		switch(element.value) {
			case "-7":
			case "-1":
				$("#trigger_interval_" + action + "_" + flow).val("days");		
				break;
			case "-31":
				$("#trigger_interval_" + action + "_" + flow).val("months");		
				break;
			case "-365":
				$("#trigger_interval_" + action + "_" + flow).val("years");		
				break;
		}
		if (Number(element.value) <  0) {
			//hide the rest
			$("#trigger_" + action + "_" + flow).css("visibility", "hidden");
			//$("#trigger_actual_" + action + "_" + flow).css("visibility", "hidden");
		} else {
			$("#trigger_" + action + "_" + flow).css("visibility", "visible");
			//$("#trigger_actual_" + action + "_" + flow).css("visibility", "visible");
		}
	},
	showNotification: function(event) {
		var self = this;
		
		var arrID = event.currentTarget.id.split("_");
		var flow = arrID[arrID.length - 1];
		var action = arrID[arrID.length - 3];
		
		$("#notify_holder_" + action + "_" + flow).fadeIn();
	},
	resetAction: function() {
		$(".trigger_row_date_active").show();
		$(".trigger_row_date_active").show();
		
		$(".action_button").show();
		$("#reset_action_button").hide();
	},
	addAction: function(event) {
		var self = this;
		event.preventDefault();
		$("#triggers_headers").show();
		
		var arrID = event.currentTarget.id.split("_");
		var action = arrID[arrID.length - 1];
		
		var nextflow = $(".trigger_row_" + action).length;
		$(".trigger_row_" + action).hide();
		if ($("#trigger_row_" + action + "_" + nextflow).length == 0) {
			var the_row = document.getElementById("trigger_row_" + action + "_0").outerHTML;
			var new_row = the_row.replaceAll("_0", "_" + nextflow);
			new_row = new_row.replace('class="trigger_row_status"', 'class="trigger_row_status trigger_row_status_active"');
			new_row = new_row.replace('class="trigger_row_date"', 'class="trigger_row_date trigger_row_date_active"');
			new_row = new_row.replace('class="trigger_row_task"', 'class="trigger_row_task trigger_row_task_active"');
			$("#triggers_table").append(new_row);
		}
		$(".trigger_row_date_active").hide();
		$(".trigger_row_status_active").hide();
		$(".trigger_row_task_active").hide();
		
		$(".action_button").hide();
		$("#reset_action_button").show();
		
		$("#trigger_row_" + action + "_" + nextflow).show();
		
		var theme_3 = {
			theme: "kase",
			onAdd: function(item) {
				var theid = this[0].id;
				theid = theid.split("_")[2];		
			}
		};
		$("#trigger_notify_" + action + "_" + nextflow).tokenInput("api/user", theme_3);
		$(".token-input-list-kase").css("width", "252px");
		$(".token-input-dropdown-kase").css("width", "252px");
	},
	deleteTrigger: function(event) {
		var self = this;
		if (!confirm("Press OK to confirm this delete request")) {
			return;
		}
		event.preventDefault();
		var arrID = event.currentTarget.id.split("_");
		var action = arrID[arrID.length - 2];
		var flow = arrID[arrID.length - 1];
		var trigger_id = $("#trigger_id_" + action + "_" + flow).val();
		
		var url = 'api/trigger/delete';
		formValues = "id=" + trigger_id;

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#trigger_row_" + action + "_" + flow).css("background", "red");
					setTimeout(function() {
						$("#trigger_row_" + action + "_" + flow).remove();
					}, 2500);
				}
			}
		});
	},
	saveWorkflow: function(event) {
		var self = this;
		
		event.preventDefault();
		var arrID = event.currentTarget.id.split("_");
		var action = arrID[arrID.length - 2];
		var flow = arrID[arrID.length - 1];
		
		if (!this.model.get("blnChanged")) {
			self.saveTrigger(flow, action);
			return;
		}
		//validate first
		if ($("#workflow_number").val()=="") {
			$("#workflow_number").css("border", "2px solid red");
			return;
		}
		$("#workflow_number").css("border", "1px solid rgb(169, 169, 169)");
		
		if ($("#trigger_interval_" + action + "_" + flow).val()=="") {
			$("#trigger_interval_" + action + "_" + flow).css("border", "2px solid red");
			return;
		}
		$("#trigger_interval_" + action + "_" + flow).css("border", "1px solid rgb(169, 169, 169)");
		
		if ($("#trigger_datetype_" + action + "_" + flow).val()=="") {
			$("#trigger_datetype_" + action + "_" + flow).css("border", "2px solid red");
			return;
		}
		$("#trigger_datetype_" + action + "_" + flow).css("border", "1px solid rgb(169, 169, 169)");
		
		if ($("#trigger_" + action + "_" + flow).val()=="") {
			$("#trigger_" + action + "_" + flow).css("border", "2px solid red");
			return;
		}
		$("#trigger_" + action + "_" + flow).css("border", "1px solid rgb(169, 169, 169)");
		
		if ($("#trigger_date_" + action + "_" + flow).val()=="") {
			$("#trigger_date_" + action + "_" + flow).css("border", "2px solid red");
			return;
		}
		$("#trigger_date_" + action + "_" + flow).css("border", "1px solid rgb(169, 169, 169)");
		
		if ($("#trigger_description_" + action + "_" + flow).val()=="") {
			$("#trigger_description_" + action + "_" + flow).css("border", "2px solid red");
			return;
		}
		$("#trigger_description_" + action + "_" + flow).css("border", "1px solid rgb(169, 169, 169)");
		
		/*
		var nextflow = $(".trigger_row_" + action).length + 1;
		
		if ($("#trigger_row_date_" + nextflow).length == 0) {
			var the_row = document.getElementById("trigger_row_0").outerHTML;
			var new_row = the_row.replaceAll("_0", "_" + nextflow);
			$("#workflow_table").append(new_row);
			$("#save_trigger_" + nextflow).html("Save Workflow Task #" + nextflow)
			$("#trigger_row_date_" + nextflow).show();
		}
		*/
		
		var url = "api/workflow/save";
		var formValues = "table_id=" + $("#workflow_id").val();
		formValues += "&workflow_number=" + encodeURIComponent($("#workflow_number").val());
		formValues += "&description=" + encodeURIComponent($("#workflow_description").val());
		formValues += "&case_type=" + encodeURIComponent($("#case_typeInput").val());
		$.ajax(
			{
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						//console.log("save_10_worker");
						$("#workflow_id").val(data.id);
						self.saveTrigger(flow, action);
						$(".trigger_row_date_active").show();
						$(".trigger_row_date_active").show();
						
						$(".action_button").show();
						$("#reset_action_button").hide();
					}
				}
			}
		);
	},
	saveTrigger: function(flow, action) {
		$("#save_trigger_" + action + "_" + flow).hide();
		var url = "api/trigger/save";
		var formValues = "workflow_id=" + $("#workflow_id").val();
		formValues += "&action=" + action;
		formValues += "&table_id=" + $("#trigger_id_" + action + "_" + flow).val();
		formValues += "&trigger_time=" + $("#trigger_time_" + action + "_" + flow).val();
		formValues += "&operation=" + $("#operation_" + action + "_" + flow).val();
		formValues += "&trigger_interval=" + $("#trigger_interval_" + action + "_" + flow).val();
		formValues += "&trigger=" + $("#trigger_" + action + "_" + flow).val();
		formValues += "&trigger_actual=" + $("#trigger_actual_" + action + "_" + flow).val();
		//formValues += "&trigger_date=" + $("#trigger_date_" + action + "_" + flow).val();
		formValues += "&trigger_description=" + encodeURIComponent($("#trigger_description_" + action + "_" + flow).val());
		
		//assignees
		var arrAssign = [];
		var notify = $("#trigger_notify_" + action + "_" + flow).val();
		if (notify!="") {
			arrAssign = notify.split(",");
		}
		if (document.getElementById("trigger_assign_" + action + "_satty_" + flow).checked) {
			arrAssign.push("KASE_SATTY");
		}
		if (document.getElementById("trigger_assign_" + action + "_atty_" + flow).checked) {
			arrAssign.push("KASE_ATTY");
		}
		if (document.getElementById("trigger_assign_" + action + "_coord_" + flow).checked) {
			arrAssign.push("KASE_COORD");
		}
		var assignee = arrAssign.join(",");
		formValues += "&assignee=" + encodeURIComponent(assignee);
		
		$.ajax(
			{
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						$("#trigger_id_" + action + "_" + flow).val(data.id);
						$("#trigger_action_" + action + "_" + flow).css("color", "lime");
						setTimeout(function() {
							$(".trigger_row_date_active").show();
							$(".trigger_row_status_active").show();
							
							$(".action_button").show();
							$("#reset_action_button").hide();
						
							$("#trigger_action_" + action + "_" + flow).css("color", "white");
							$("#save_trigger_" + action + "_" + flow).show();
						}, 2500);
					}
				}
			}
		);
	},
	doTimeouts: function() {
		$("#workflow_table td").css("padding-bottom", "15px");
		
		if (this.model.id > 0) {
			
			$("#workflow_title").html("Edit Workflow " + this.model.id);
			$("#case_typeInput").val(this.model.get("case_type"));
			$("#workflow_number_holder").show();
			$("#workflow_buttons_holder").fadeIn();
			
			var case_type = $( "#case_typeInput option:selected" ).text();
			$("#case_typeSpan").html(case_type);
			$("#case_typeInput").hide();
			var theme_3 = {
				theme: "kase",
				onAdd: function(item) {
					var theid = this[0].id;
					theid = theid.split("_")[2];		
				}
			};
					
			//lookup triggers
			var triggers = new TriggerCollection({workflow_id: this.model.id});
			triggers.fetch({
				success: function(data) {
					var trigs = data.toJSON();
					if (trigs.length > 0) {
						$("#triggers_headers").show();
					}
					var trig_counter = 1;
					_.each( trigs, function(trigger) {
						var action = trigger.action;
						var nextflow = $(".trigger_row_" + action).length;
						var the_row = document.getElementById("trigger_row_" + action + "_0").outerHTML;
						var new_row = the_row.replaceAll("_0", "_" + nextflow);
						new_row = new_row.replace('class="trigger_row_' + action + '"', 'class="trigger_row_' + action + ' trigger_row_' + action + '_active"');
						
						$("#triggers_table").append(new_row);
						
						//now update the values
						$("#trigger_action_" + action + "_" + nextflow).html(action.capitalize() + " " + trig_counter );
						trig_counter++;
						$("#trigger_id_" + action + "_" + nextflow).val(trigger.id);
						$("#operation_" + action + "_" + nextflow).val(trigger.operation);
						if (Number(trigger.trigger_time) - parseInt(trigger.trigger_time)==0) {
							trigger.trigger_time = parseInt(trigger.trigger_time);
						}
						$("#trigger_time_" + action + "_" + nextflow).val(trigger.trigger_time);
						$("#trigger_interval_" + action + "_" + nextflow).val(trigger.trigger_interval);
						$("#trigger_" + action + "_" + nextflow).val(trigger.trigger);
						$("#trigger_actual_" + action + "_" + nextflow).val(trigger.trigger_actual);
						$("#trigger_description_" + action + "_" + nextflow).val(trigger.trigger_description);
						
						if (trigger.id > 0) {
							$("#delete_trigger_" + action + "_" + nextflow).show();
						}
						//checkboxes
						var arrAssign = trigger.assignee.split(",");
						document.getElementById("trigger_assign_" + action + "_satty_" + nextflow).checked = (arrAssign.indexOf("KASE_SATTY") > -1);
						document.getElementById("trigger_assign_" + action + "_atty_" + nextflow).checked = (arrAssign.indexOf("KASE_ATTY") > -1);
						
						$("#trigger_row_" + action + "_" + nextflow).show();
						$("#trigger_notify_" + action + "_" + nextflow).tokenInput("api/user", theme_3);
						
						//gotta add to the notification
						arrAssign.forEach(function(currentValue, index, array) {
							if (!isNaN(currentValue) && currentValue!="") {
								//other
								if ($("#notify_holder_" + action + "_" + nextflow).css("display")=="none") {
									document.getElementById("trigger_assign_" + action + "_other_" + nextflow).checked = true;
									$("#notify_holder_" + action + "_" + nextflow).show();
								}
								//we have a number
								var theworker = worker_searches.findWhere({"user_id": currentValue});
								$("#trigger_notify_" + action + "_" + nextflow).tokenInput("add", {
									id: currentValue, 
									name: theworker.get("user_name")
								});
							}
						});
						if (arrAssign.length < 3) {
							$(".token-input-list-kase").css("height","27px");
						}
						
						$(".token-input-list-kase").css("width", "252px");
						$(".token-input-dropdown-kase").css("width", "252px");
					});
				}
			});
		}
	}
})
var blnNewWorkflow = false;
window.workflow_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .workflow_category":					"filterByCategory",
		"click #workflows_clear_search":			"clearSearch",
		"click #new_workflow":						"newWorkflow",
		"click .edit_workflow":						"editWorkflow",
		"click .delete_workflow":					"confirmdeleteWorkflow",	//not on right now
		"click .workflow_deactivate":				"deactivateWorkflow",
		"click .workflow_activate":					"activateWorkflow",
		"click .list_kases":						"reviewKases",
		"click #workflow_listing_all_done":			"doTimeouts"		
	},
    render:function () {		
		var self = this;
		if (typeof this.template != "function") {
			var view = "workflow_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   
		var workflows = this.collection.toJSON();
		var mymodel = this.model.toJSON();

		var page_title = this.model.get("page_title");
		var embedded = this.model.get("embedded");
		
		if (typeof mymodel.embedded == "undefined") {
			this.model.set("embedded", false);
		}
		var case_id = mymodel.case_id;
		
		try {
			$(this.el).html(this.template({
				workflows: workflows,
				page_title: page_title, 
				embedded: embedded
			}));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		blnNewWorkflow = false;
		
		return this;
    },
	doTimeouts: function(event) {
		if (this.collection.length==0) {
			var page_title = this.model.get("page_title");
			$(".workflow_listing_" + page_title).hide();
			$("#" + page_title.toLowerCase() + "_holder").css("padding-bottom", "15px");
			
		}
		$(".workflow_listing th").css("font-size", "1em");
		$(".workflow_listing").css("font-size", "1.1em");
		
		$(".tablesorter").css("background", "url(../img/glass_dark.png)");
		
		setTimeout(function() {
			$("#workflow_feedback").fadeOut();
		}, 5500);
	},
	deactivateWorkflow: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		var workflow = new Workflow({id: id});
		
		workflow.activateFlow("N");
	},
	activateWorkflow: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		var workflow = new Workflow({id: id});
		
		workflow.activateFlow("Y");
	},
	confirmdeleteWorkflow: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "workflow");
	},
	newWorkflow:function (l) {
		document.location.href = "#workflow/new";
    },
	reviewKases: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var workflow_id = element.id.split("_")[2];
		var case_type = $("#worflow_case_type_" + workflow_id).html().trim();
		
		var row = "workflow_kases_row_" + workflow_id;
		var holder = "workflow_kases_holder_" + workflow_id;
		
		//hide any checks lookup
		
		var url = "api/workflow/cases/" + workflow_id;
		
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				var mymodel = new Backbone.Model({
					"holder": holder, 
					"workflow_id": workflow_id,
					"case_type": case_type,
					"embedded": true,
					"page_title": "Workflow Kases"
				});
	
				$('#' + holder).html(new account_kases_listing_view({collection: data, model: mymodel}).render().el);
				$('.' + row).fadeIn();
			}
		});
	},
	editWorkflow:function (event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		document.location.href = "#workflow/edit/" + id;
    },
	clearSearch: function() {
		$("#workflows_searchList").val("");
		$( "#workflows_searchList" ).trigger( "keyup" );
		$("#workflows_searchList").focus();
	},
});