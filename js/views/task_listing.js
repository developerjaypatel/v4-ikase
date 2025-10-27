window.task_listing = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
    },
	events: {
		"click #compose_task": 					"composeTasks",
		"click .expand_task": 					"expandTask",
		"click .shrink_task": 					"shrinkTask",
		"click .task_action": 					"reactTask",
		"click .delete_task": 					"confirmdeleteTask",
		"click .delete_yes":					"deleteTask",
		"click .close_task":					"closeTask",
		"click .delete_no":						"canceldeleteTask",
		"click .task_listing .read_holders":	"readTask",
		"mouseover #task_preview_panel": 		"freezePreview",
		"mouseover .task_preview_link": 		"freezePreview",
		"click .edit_task":						"editTask",
		"keyup #taskdayview_start_date":		"scheduleResetDay",
		"click .backtotop":						"gotoTop",
		"click .check_all":						"checkAll",
		"click #cc_tasks_filter":				"ccTasks",
		"change #mass_change":					"massChange",
		"click #update_date_range":				"updateRange",	
		"change .check_thisone":				"checkThisOne",	
		"change #assigneeFilter":				"filterByAssignee",
		"change #task_print_options":			"printTasks",
		"click #task_listing_all_done":			"doTimeouts"
	},
    render:function () {		
		var self = this;
		
		this.collection.bind("reset", this.render, this);
		
		if (typeof this.model.get("homepage") == "undefined") {
			this.model.set("homepage", false);
		}
		if (typeof this.model.get("case_id") == "undefined") {
			this.model.set("case_id", "");
		}
		if (typeof this.model.get("user_id") == "undefined") {
			this.model.set("user_id", "");
		}
		/*
		var arrToUserNames = [];
		var task = this.collection.toJSON()[0];
		var arrToUsers = task.assignee.split(";");
		arrayLength = arrToUsers.length;
		for (var i = 0; i < arrayLength; i++) {
			var theworker = worker_searches.findWhere({"nickname": arrToUsers[i]});
			if (typeof theworker != "undefined") {
				if (this.model.get("homepage") == true) {
					arrToUserNames[arrToUserNames.length] = theworker.get("nickname");
				} else {
					arrToUserNames[arrToUserNames.length] = theworker.get("user_name");
				}
			} else {
				arrToUserNames[arrToUserNames.length] = arrToUsers[i];
			}
		}
		*/
		//we might have a new day
        var blnToday = false;
		var tasks = this.collection.toJSON();
		var arrDayCount = [];
		var low_date = "";
		var high_date = "";
		var blnCc = false;
		_.each( tasks, function(task) {
			if (task.cc!="") {
				if (!blnCc) {
					blnCc = true;
				}
			}
			if (task.task_dateandtime=="0000-00-00 00:00:00" || task.task_date!="") {
				task.task_dateandtime = task.task_date;
			}
			if (moment(task.task_dateandtime).format()=="Invalid date") {
				if (task.task_dateandtime.indexOf("02-29") > -1) {
					task.task_dateandtime = task.task_dateandtime.replace("02-29", "02-28");
				}
			}
			if (low_date=="") {
				low_date = moment(task.task_dateandtime);
			}
			if (moment(task.task_dateandtime) < low_date) {
				low_date = moment(task.task_dateandtime);
			}
			
			if (high_date=="") {
				high_date = moment(task.task_dateandtime);
			}
			if (moment(task.task_dateandtime) > high_date) {
				high_date = moment(task.task_dateandtime);
			}
			
			if (moment(task.task_dateandtime).format('h:mm a')=="12:00 am") {
				task.time = "";
			} else {
				task.time = moment(task.task_dateandtime).format('h:mm a');
			}
			
			if (task.end_date=="0000-00-00 00:00:00" || task.end_date=="") {
				task.end_date = "";
			} else {
				task.end_date = moment(task.end_date).format("MM/DD/YY");
			}
			if (task.task_name != "") {
				task.task_title = task.task_name;
			}
			if (task.task_name != "" && task.task_description == "") {
				task.task_description = task.task_name;
			}
			if (task.case_stored_name != "") {
				task.case_name = task.case_stored_name;
			}
			var the_day = moment(task.task_dateandtime).format("MMDDYY");
			if (typeof arrDayCount[the_day] == "undefined") {
				arrDayCount[the_day] = 1;
			} else {
				arrDayCount[the_day]++;
			}
			
			//from
			var theassigner = worker_searches.findWhere({"user_name": task.from});
			if (typeof theassigner != "undefined") {
				task.from = theassigner.get("nickname").toUpperCase();
			}
			if (task.from!="") {
				task.originator = task.from;
			}
			
			var arrToUserNames = [];
            //lookup all the user_name
            var arrToUsers = task.assignee.split(";");
            arrayLength = arrToUsers.length;
            for (var i = 0; i < arrayLength; i++) {
                var theworker = worker_searches.findWhere({"nickname": arrToUsers[i]});
                if (typeof theworker != "undefined") {
                	arrToUserNames.push(theworker.get("nickname").toUpperCase());
                } else {
                    arrToUserNames.push(arrToUsers[i]);
                }
            }
            task.arrToUserNames = arrToUserNames;
			
            var arrCcUserNames = [];
            //lookup all the user_name
            var arrCcUsers = task.cc.split(";");
            arrayLength = arrCcUsers.length;
            for (var i = 0; i < arrayLength; i++) {
            	//only my cc are allowed
                //if (arrCcUsers[i]==login_nickname) {
                    var theworker = worker_searches.findWhere({"nickname": arrCcUsers[i]});
                    if (typeof theworker != "undefined") {
                        arrCcUserNames.push(theworker.get("nickname").toUpperCase());
                    } else {
                        arrCcUserNames.push(arrCcUsers[i]);
                    }
                //}
            }
			task.arrCcUserNames = arrCcUserNames;
			
			//var the_day = moment(task.task_dateandtime).format("MMDDYY");
            var the_week = moment(task.task_dateandtime).format("MMYY") + moment(task.task_dateandtime, "YYYY-MM-DD").week();
			var this_week = moment().format("MMYY") + moment().week();
            task.anchor_link = "";
            if (the_week == this_week) {
	            if (!blnToday) {
                	blnToday = true;
                    task.anchor_link = "<a name='tasks_today' id='tasks_today'></a>";
                }
            }
			if (user_data_path == 'A1') {
				task.case_number = task.cpointer;
			}
			
			//related cases tasks
			var arrTaskDOIS = [];
			if (typeof task.injury_dates != "undefined") {
				if (task.injury_dates!="") {
					var arrDates = task.injury_dates.split(",");
					arrDates.forEach(function(injury_dates, index, array) {
						var arrDateArray = injury_dates.split("|");
						
						var doi_dates = moment(arrDateArray[1]).format("MM/DD/YYYY");
						if (arrDateArray[2]!="0000-00-00") {
							doi_dates += " - " + moment(arrDateArray[1]).format("MM/DD/YYYY") + " CT";
						}
						var injury_details = "<a href='#injury/" + current_case_id + "/" + arrDateArray[0] + "' class='white_text'>" + doi_dates + "</a>";
						arrTaskDOIS.push("<a href='#kases/" + task.main_case_id + "' class='white_text'>" + arrDateArray[0] + "</a> - DOI:" + injury_details);
					
					});
				}
			}
			task.subject = "";
			if (arrTaskDOIS.length > 0) {
				task.injury_dates = arrTaskDOIS.join("<br>");
				
				var new_subject = "<span style='font-size:0.7em;'><span style='background:orange;color:black;padding:2px;font-weight:bold'>From Related Cases:</span><br>" + task.injury_dates + "</span>";
				if (task.task_title!="") {
					task.subject = "<br><br>";
				}
				task.subject += new_subject;
			}
		});
		
		if (low_date == "") {
			low_date = moment(low_date).format("MM/DD/YYYY");
			high_date = moment(high_date).format("MM/DD/YYYY");
		} else {
			low_date = moment(low_date).format("MM/DD/YYYY");
			high_date = moment(high_date).format("MM/DD/YYYY");
		}
		var blnMyTasks = false;
		var listing_type = "out";
		var title = this.model.get("title");
		//
        if (title.indexOf("Task Inbox")!==false || title=="My Tasks" || title=="Tasks for Me" || title=="Daily Tasks" || title=="Upcoming Tasks") {
            blnMyTasks = true;
			listing_type = "in";
        }
		var pagetype = "full";
		if (this.model.get("homepage")) {
			pagetype = "home";
		}
		
		if (typeof this.model.get("start") == "undefined") {
			if (low_date=="Invalid date") {
				low_date = "";
			}
			if (high_date=="Invalid date") {
				high_date = "";
			}
			this.model.set("start", low_date);
			this.model.set("end", high_date);
		}
		$(this.el).html(this.template({tasks: tasks, case_id: this.model.get("case_id"), user_id: this.model.get("user_id"), title: this.model.get("title"), receive_label: this.model.get("receive_label"), homepage: this.model.get("homepage"), blnMyTasks: blnMyTasks, listing_type: listing_type, pagetype: pagetype, arrDayCount: arrDayCount, start: this.model.get("start"), end: this.model.get("end"), blnCc: blnCc}));
				
		setTimeout(function() {
			tableSortIt();
		}, 100);
		
		return this;
    },
	filterByAssignee: function(event) {
		var element = event.currentTarget;
		var $rows = $('.task_listing .task_data_row');
		var the_kind = element.id.replace("Filter", "");
		var theobj = $("#" + element.id);
		var val = $.trim($(theobj).val()).replace(/ +/g, ' ').toLowerCase();
		//$(".date_row").hide();
		//$(".letter_row").hide();
		$rows.show().filter(function() {
			//var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
			var text = $( '.assignee_values', $( this ) ).text ().replace(/\s+/g, ' ').toLowerCase();
			if (text.indexOf(val) > -1) {
				if (this.classList.length==2) {
					if (this.classList[1].indexOf("row") > -1) {
						var row_id = this.classList[1];
						$("." + row_id).show();
					}
				}
			}
			
			return !~text.indexOf(val);
		}).hide();
	},
	ccTasks: function(event) {
		//tomorrow
	},
	massChange: function(event) {
		//alert("Hey, I'm working.");
		//return;
		var dropdown = event.currentTarget;
		var arrCheckBoxes = $('.check_thisone');
		var arrChecked = [];
		var arrLength = arrCheckBoxes.length;
		
		for(var i =0; i < arrLength; i++) {
			var element = arrCheckBoxes[i];
			//var elementsArray = elements.id.split("_");
			if (element.checked) {
				var checkbox_element = element.id;
				var arrCheckbox =  checkbox_element.split("_");
				var task_id = arrCheckbox[2];
				arrChecked.push(task_id);
			}
		}
		
		if (arrChecked.length==0) {
			document.getElementById(dropdown.id).selectedIndex = 0;
			return;
		}
		this.model.set("checked_boxes", arrChecked);
		var ids = arrChecked.join(", ");
		var action = dropdown.value;
		if (action != "" || action != "undefined") {
			if (action == "change_date") {
				composeDateChange(ids, "task");
				dropdown.selectedIndex = 0;
			}
			if (action == "close_task") {
				var arrIDs = ids.split(", ");
				for (var int = 0; int < arrIDs.length; int++) {
				//$.each( ids, function( key, value ) {
					//alert( arrIDs[int] );
					closeElement(event, arrIDs[int], "task");
					dropdown.selectedIndex = 0;
				//});
				}
			}
			if (action == "delete_task") {
				var arrIDs = ids.split(", ");
				for (var int = 0; int < arrIDs.length; int++) {
				//$.each( ids, function( key, value ) {
					//alert( arrIDs[int] );
					deleteElement(event, arrIDs[int], "tasking");
					dropdown.selectedIndex = 0;
				//});
				}
			}
			if (action == "transfer_task") {
				composeTransferTask(ids, "task");
				dropdown.selectedIndex = 0;
			}
		} else { 
			console.log("no action");
		}
		//$("#deleteModal").modal('toggle');
	},
	updateRange: function() {
		var start_date_range = $('#start_dateInput').val();
		var format_start_date_range = moment(start_date_range).format("YYYY-MM-DD");
		var end_date_range = $('#end_dateInput').val();
		var format_end_date_range = moment(end_date_range).format("YYYY-MM-DD");
		document.location.href = "#taskbydates/" + format_start_date_range + "/" + format_end_date_range + "";
	},
	checkThisOne: function(event) {
		//event.preventDefault();
		var element = event.currentTarget;
		var arrThisOne = $(".check_thisone");
		var arrLength = arrThisOne.length;
		
		if ($('#mass_change').css("display")=="none") {	
			for(var i = 0; i < arrLength; i++) {
				var element = arrThisOne[i];
				if (element.checked) {
					$('#mass_change').fadeIn();
					break;
				}
			}
		} else {
			for(var i = 0; i < arrLength; i++) {
				var element = arrThisOne[i];
				if (element.checked) {
					//something is checked get out
					return;
				}
			}
			//this will hide the drop down
			$('#mass_change').fadeOut();
		}
	},
	checkAll: function(event) {
		//event.preventDefault();
		var element = event.currentTarget;
		var element_id = $(element).attr('id');
		var arrElement = element_id.split("_");
		var date_day = arrElement[3];
		var task_id = arrElement[2];
		//date_day = date_day.replace(" ", ".");
		//date_day = date_day.replace(" ", ".");
		//date_day = date_day.replace(" ", ".");
		
		if (task_id=="0" && date_day=="overall") {
			$('.check_thisone').prop('checked', element.checked);
		} else {
			$('.check_thisone_' + date_day).prop('checked', element.checked);
		}
		if($('#mass_change').is(":visible")) {
			if (element.checked) { // check select status
				$('#mass_change').show();
			} else {
				$('#mass_change').hide();
			}
		} else {
			if (element.checked) { // check select status
				$('#mass_change').show();
			} else {
				$('#mass_change').hide();
			}
		}
		return;
		
		if ($('.check_thisone_' + date_day).prop('checked') == "false") {
			if (element.checked) { // check select status
				$('.check_thisone_' + date_day).prop('checked', true);
			} else {
				$('.check_thisone').prop('checked', false);         
			}
		} else {
			if (element.checked) { // check select status
				
				$('.check_thisone_' + date_day).prop('checked', true);
				$(element).attr('checked', 'checked');
				
			} else {
				$('.check_thisone').prop('checked', false);
				$('.check_thisone').prop('checked', false);         
			}
		}
	},
	scheduleResetDay:function() {
		var self = this;
		clearTimeout(reset_timeout);
		setTimeout(function() {
			self.resetDayStartDate();
		}, 1000);
	},
	gotoTop: function() {
		var target_offset = $("#tasks_top").offset();
		var target_top = target_offset.top;
		target_top = target_top - 105;
		//goto that anchor by setting the body scroll top to anchor top
		$('html, body').animate({scrollTop:target_top}, 1100, 'easeInSine');
	},
	gotoToday: function() {
		if ($("#tasks_today").length > 0) {
			//get the top offset of the target anchor
			var target_offset = $("#tasks_today").offset();
			var target_top = target_offset.top;
			target_top = target_top - 55;
			//goto that anchor by setting the body scroll top to anchor top
			$('html, body').animate({scrollTop:target_top}, 1100, 'easeInSine');
		}
	},
	resetDayStartDate: function() {
		var start_date = $("#taskdayview_start_date").val();
		if (typeof start_date == "undefined") {
			start_date = "";
		}
		if (start_date.length==10) {
			if (isDate(start_date)) {
				var arrURL = document.location.href.split("#")[1].split("/");
				current_start_date = arrURL[3];
				start_date = moment(start_date).format("YYYY-MM-DD");
				if (current_start_date != start_date) {
					$("#content").html(loading_image);
					var current_hash = document.location.hash.split("/")[0];
					document.location.href = current_hash + "/" + start_date;
				}
			}
		}
	},
	composeTasks: function(event) {
		event.preventDefault();
		//composeTask();
		
		if (current_case_id > 0) {
			document.location.href = "#tasks/" + current_case_id;
		} else {
			document.location.href = "#taskoutbox";
		}
		setTimeout(function() {
			$("#task_manage_holder #compose_task").trigger("click");
		}, 1111);
	},
	expandTask: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		$("#expand_" + id).hide();
		$("#shrink_" + id).show();
		$("#description_" + id).fadeIn();
		
	},
	shrinkTask: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		$("#shrink_" + id).hide();
		$("#expand_" + id).show();
		$("#description_" + id).fadeOut();
		
	},
	editTask: function(event) {
		/*
		var element = event.currentTarget;
		if (element.id != "new_task") {
			this.readTask(event);
		}
		event.preventDefault();
		composeTask(element.id);
		*/
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[2];
		
		if (current_case_id > 0) {
			document.location.href = "#tasks/" + current_case_id;
		} else {
			document.location.href = "#taskoutbox";
		}
		setTimeout(function() {
			$("#task_listing #open_task_" + id + "_" + current_case_id).trigger("click");
		}, 1111);
	} ,
	reactTask: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeTask(element.id);
	},
	readTask: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var id = element.id.split("_")[2];
		if ($(".task_listing #read_holder_" + id).css("display")!="none") {
			$(".task_listing #read_holder_" + id).fadeOut(
				function() {
					//$(".task_row_" + id).fadeIn();
					$(".task_listing #action_holder_" + id).fadeIn();
					//mark the row as read
					var url = 'api/task/read';
					formValues = "id=" + id;
			
					$.ajax({
						url:url,
						type:'POST',
						dataType:"json",
						data: formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error tasks
								saveFailed(data.error.text);
							} else {
								//console.log(data);
								//refresh the new task indicator
								checkTaskInbox();
							}
						}
					});
				}
			);
		}
	},
	confirmdeleteTask: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		if (element.id=="") {
			element = element.parentElement;
		}
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "task");
		/*
		var arrPosition = showDeleteConfirm(element);
		if ($("." + boxtype + "#confirm_delete_task").hasClass("home")) {	
			$("." + boxtype + "#confirm_delete_task").css({display: "none", top: arrPosition[0], left: arrPosition[1], position:'absolute'});
		} else {
			$("." + boxtype + "#confirm_delete_task").css({display: "none", top: arrPosition[0], left: arrPosition[1], position:'absolute'});
		}
		$("." + boxtype + "#confirm_delete_task").fadeIn();
		$("." + boxtype + " #confirm_delete_id").val(id);
		*/
	},
	closeTask: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		closeElement(event, id, "task");
		
	},
	canceldeleteTask: function(event) {
		event.preventDefault();
		$("#confirm_delete_task").fadeOut();
	},
	deleteTask: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "task");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteTask(event);
			$(".task_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".task_row_" + id).fadeOut();
			}, 2500);
		}
	},
	printTasks: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var selected = element.selectedIndex;
		
		var url = "";
		switch(selected) {
			case 1:
				url = $("#print_today_link").attr("href");
				break;
			case 2:
				url = $("#print_thisweek_link").attr("href");
				break;
			case 3:
				url = $("#print_nextweek_link").attr("href");
				break;
			case 4:
				url = $("#print_all_link").attr("href");
				break;
		}
		
		if (url!="") {
			window.open(url);
		}
		
		element.selectedIndex = 0;
	},
	freezePreview: function() {
		 freezeTaskPreview();
	},
	doTimeouts: function() {
		if ($("#preview_pane").length > 0) {
			$("#task_links").hide();	
			$("#print_task_links").hide();
			$("#print_today_link").text("Print")
			var href = "report.php#taskuserinbox/" + this.model.get("user_id") + "/" + this.model.get("user_name").replaceAll(" ", "_");
			if (document.location.hash=="#tasksummary") {
				href +=  "/" + moment().format("YYYY-MM-DD");	
				
				//change all the weeks links
				var print_weeks = $("#print_weeks").html();
				print_weeks = print_weeks.replaceAll('report.php#taskbydates', 'report.php#taskuserinbox/' + this.model.get("user_id") + "/" + this.model.get("user_name").replaceAll(" ", "_"));
				$("#print_weeks").html(print_weeks);
			}
			$("#print_today_link").prop("href", href)	;
			$(".print_this_day").hide();
			$("#preview_pane_holder").css("margin-top", "-30px");
		}
		if (document.location.hash.indexOf("all/")===false) {
			if ($(".dashboard_home").length == 0) {
				this.gotoToday();
			}
		}
		
		//kase task listing?
		var blnKaseListing = false;
		if (document.location.hash.indexOf("#tasks/") == 0 || document.location.hash == "" || document.location.hash.indexOf("#kase/" + current_case_id) == 0) {
			/*
			$("#print_today_link").html("Print");
			$("#print_today_link").attr("href", "report.php#kasetasks/" + current_case_id);	
			// + "/" + moment().format("YYYY-MM-DD"));
			$("#print_today_link").attr("title", "Click to print this Kase's Tasks");
			$("#print_weeks").hide();
			*/
			$("#print_today_link").html("Print");
			if (current_case_id > -1) {
				$("#print_today_link").attr("href", "report.php#kasetasksbyday/" + current_case_id + "/" + moment().format("YYYY-MM-DD"));	
			}
			// + "/" + moment().format("YYYY-MM-DD"));
			$("#print_today_link").attr("title", "Click to print this Kase's Tasks");
			$("#print_weeks").hide();
			
			var blnUpcomingTasks = (this.model.get("title") == "Upcoming Tasks");
			
			var newhref = $("#print_all_link").attr("href");
			if (current_case_id > -1) {
				newhref = newhref.replace("taskbydates", "kasetaskbydates");
				newhref += "/" + current_case_id;
			}
			$("#print_all_link").attr("href", newhref);
			
			var prints = document.getElementById("task_print_options").options;
			for (var i = 0; i < 5; i++) {
				var printlink = prints[i];
				var printid = printlink.id;
				if (blnUpcomingTasks) {
					if (printid!="" && printid!="today_option") {
						printlink.style.display = "none";
					}
					if (printid=="today_option") {
						printlink.text = "All";
					}
				} else {
					if (printid!="" && printid!="today_option" && printid!="all_option") {
						printlink.style.display = "none";
					}
				}
			}
			blnKaseListing = true;
		}
		if (document.location.hash.indexOf("all/") > 0) {
			$("#date_range_holder").hide();
			$("#print_weeks").hide();
			if (document.location.hash.indexOf("dailytaskall/") > 0) {
				$("#print_today_link").attr("href", "report.php#taskdayinboxall/" + moment().format("YYYY-MM-DD"));
			}
			if (document.location.hash.indexOf("taskcompletedall/") > 0) {
				$("#print_today_link").attr("href", "report.php#taskdaycompletedall/" + moment().format("YYYY-MM-DD"));
			}
		} else {
			$("#date_range_holder").show();
			$("#print_weeks").show();
			rangeDates();
		}
		if (document.location.hash.indexOf("#tasksummary")!=0) {
			//massChange transfer task only for summary screen
			if (typeof document.getElementById("mass_change").options[4] != "undefined") {
				document.getElementById("mass_change").options[4].remove();
			}
		}
	}
});


window.task_listing_pane = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
    },
	events: {
		"click #compose_task": 					"expandTask",
		"click .open_task": 					"expandTask",
		"click .shrink_task": 					"shrinkTask",
		"click #hide_preview_pane":				"shrinkTask",
		"click .task_action": 					"reactTask",
		"click .delete_task": 					"confirmdeleteTask",
		"click .delete_yes":					"deleteTask",
		"click .restore_task":					"restoreTask",
		"click .close_task":					"closeTask",
		"click .delete_no":						"canceldeleteTask",
		"click .task_listing .read_holders":	"readTask",
		"mouseover #task_preview_panel": 		"freezePreview",
		"mouseover .task_preview_link": 		"freezePreview",
		"click .edit_task":						"editTask",
		"keyup #taskdayview_start_date":		"scheduleResetDay",
		"click .backtotop":						"gotoTop",
		"click .check_all":						"checkAll",
		"click #cc_tasks_filter":				"ccTasks",
		"change #mass_change":					"massChange",
		"click #update_date_range":				"updateRange",	
		"change .check_thisone":				"checkThisOne",	
		"change #assigneeFilter":				"filterByAssignee",
		"click .assignee_listing":				"filterByAssigneeNickname",
		"click #show_all_link":					"showAllTasks",
		"click #task_completed":				"tasksCompleted",
		"click #task_overdue":					"tasksOverdue",
		"click #closed_tasks":					"tasksClosed",
		"click #deleted_tasks":					"tasksDeleted",
		"click #open_tasks":					"tasksOpen",
		"change #task_print_options":			"printTasks",
		"click #task_listing_all_done":			"doTimeouts"
	},
    render:function () {
		if (typeof this.template != "function") {
			var view = "task_listing_pane";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   		
		var self = this;
		
		this.collection.bind("reset", this.render, this);
		
		if (typeof this.model.get("homepage") == "undefined") {
			this.model.set("homepage", false);
		}
		if (typeof this.model.get("case_id") == "undefined") {
			this.model.set("case_id", "");
		}
		/*
		var arrToUserNames = [];
		var task = this.collection.toJSON()[0];
		var arrToUsers = task.assignee.split(";");
		arrayLength = arrToUsers.length;
		for (var i = 0; i < arrayLength; i++) {
			var theworker = worker_searches.findWhere({"nickname": arrToUsers[i]});
			if (typeof theworker != "undefined") {
				if (this.model.get("homepage") == true) {
					arrToUserNames[arrToUserNames.length] = theworker.get("nickname");
				} else {
					arrToUserNames[arrToUserNames.length] = theworker.get("user_name");
				}
			} else {
				arrToUserNames[arrToUserNames.length] = arrToUsers[i];
			}
		}
		*/
		//we might have a new day
        var blnToday = false;
		var tasks = this.collection.toJSON();
		//alert(tasks);
		var arrDayCount = [];
		var low_date = "";
		var high_date = "";
		var today = moment(login_today);
		var closest_date = today;
		var blnCc = false;
		var blnBadDates = false;	//dates saved as 0000-00-00
		var blnFoundClosest = false;
		var arrCounts = [];
		_.each( tasks, function(task) {
			//count tasks by user
			var arrAssigned = task.assignee.split(";");
			for(var i = 0; i < arrAssigned.length; i++) {
				var assignee = arrAssigned[i];
				if (typeof arrCounts[assignee] == "undefined") {
					arrCounts[assignee] = 1;
				} else {					
					arrCounts[assignee]++; 
				}
			}
			if (task.cc!="") {
				if (!blnCc) {
					blnCc = true;
				}
			}
			task.bad_date_indicator = false;
			if (task.task_dateandtime!="0000-00-00 00:00:00") {
				var moment_task_date = moment(task.task_dateandtime);
			} else {
				var moment_task_date = moment();
				task.task_dateandtime = moment().format("YYYY-MM-DD");
				task.bad_date_indicator = true;
				blnBadDates = true;
			}
			if (low_date=="") {
				low_date = moment_task_date;
			}
			if (moment_task_date < low_date) {
				low_date = moment_task_date;
			}
			
			if (high_date=="") {
				high_date = moment_task_date;
			}
			if (moment_task_date > high_date) {
				high_date = moment_task_date;
			}
			if (today._i == moment_task_date._i) {
				blnFoundClosest = true;
			}
			if (!blnFoundClosest) {
				//closest to today
				if (today <= moment_task_date) {
					//console.log(closest_date._i, moment_task_date._i);
					//if (closest_date <= moment_task_date || typeof closest_date._i == "undefined") {
						closest_date = moment_task_date;
						blnFoundClosest = true;
					//}
				}
			}
			
			if (moment_task_date.format('h:mm a')=="12:00 am") {
				task.time = "";
			} else {
				task.time = moment_task_date.format('h:mm a');
			}
			
			if (task.end_date=="0000-00-00 00:00:00" || task.end_date=="") {
				task.end_date = "";
			} else {
				task.end_date = moment(task.end_date).format("MM/DD/YY");
			}
			if (task.task_name != "") {
				task.task_title = task.task_name;
			}
			if (task.task_name != "" && task.task_description == "") {
				task.task_description = task.task_name;
			}
			if (task.case_stored_name != "") {
				task.case_name = task.case_stored_name;
			}
			var the_day = moment_task_date.format("MMDDYY");
			if (typeof arrDayCount[the_day] == "undefined") {
				arrDayCount[the_day] = 1;
			} else {
				arrDayCount[the_day]++;
			}
			
			task.doi = "";
			if (task.doi_start!="" && task.doi_start!="0000-00-00") {
				task.doi = moment(task.doi_start).format("MM/DD/YYYY");
				if (task.doi_end!="" && task.doi_end!="0000-00-00") {
					task.doi += "-" + moment(task.doi_end).format("MM/DD/YYYY") + " CT";
				}
			}
			//from
			var theassigner = worker_searches.findWhere({"user_name": task.from});
			if (typeof theassigner != "undefined") {
				task.from = theassigner.get("nickname").toUpperCase();
			}
			if (task.from!="") {
				task.originator = task.from;
			}
			
			//var the_day = moment_task_date.format("MMDDYY");
            var the_week = moment_task_date.format("MMYY") + moment(task.task_dateandtime, "YYYY-MM-DD").week();
			var this_week = moment().format("MMYY") + moment().week();
            task.anchor_link = "";
            if (the_week == this_week) {
	            if (!blnToday) {
                	blnToday = true;
                    task.anchor_link = "<a name='tasks_today' id='tasks_today'></a>";
                }
            }
			if (user_data_path == 'A1') {
				task.case_number = task.cpointer;
			}
			if (task.case_number == task.file_number) {
				task.file_number = "";
			}
			task.case_number_link = "";
			if (task.case_number!="") {
				task.case_number_link = '<a href="?n=#kase/' + task.case_id + '" class="list-item_kase" style="color:white; font-weight:bold; padding:2px; background:darkblue" target="_blank">' + task.case_number + '</a>';
			}
			
			task.file_number_link = "";
			if (task.file_number!="") {
				task.file_number_link = '<a href="?n=#kase/' + task.case_id + '" class="list-item_kase" style="color:white; font-weight:bold; padding:2px; background:darkblue" target="_blank">' + task.file_number + '</a>';
			}
			//related cases tasks
			var arrTaskDOIS = [];
			if (typeof task.injury_dates != "undefined") {
				if (task.injury_dates!="") {
					var arrDates = task.injury_dates.split(",");
					arrDates.forEach(function(injury_dates, index, array) {
						var arrDateArray = injury_dates.split("|");
						
						var doi_dates = moment(arrDateArray[1]).format("MM/DD/YYYY");
						if (arrDateArray[2]!="0000-00-00") {
							doi_dates += " - " + moment(arrDateArray[1]).format("MM/DD/YYYY") + " CT";
						}
						var injury_details = "<a href='#injury/" + current_case_id + "/" + arrDateArray[0] + "' class='white_text'>" + doi_dates + "</a>";
						arrTaskDOIS.push("<a href='#kases/" + task.main_case_id + "' class='white_text'>" + arrDateArray[0] + "</a> - DOI:" + injury_details);
					
					});
				}
			}
			task.subject = "";
			if (arrTaskDOIS.length > 0) {
				task.injury_dates = arrTaskDOIS.join("<br>");
				
				var new_subject = "<span style='font-size:0.7em;'><span style='background:orange;color:black;padding:2px;font-weight:bold'>From Related Cases:</span><br>" + task.injury_dates + "</span>";
				if (task.task_title!="") {
					task.subject = "<br><br>";
				}
				task.subject += new_subject;
			}
		});
		
		this.model.set("closest_date", closest_date);
		if (low_date == "") {
			low_date = moment(low_date).format("MM/DD/YYYY");
			high_date = moment(high_date).format("MM/DD/YYYY");
		} else {
			low_date = moment(low_date).format("MM/DD/YYYY");
			high_date = moment(high_date).format("MM/DD/YYYY");
		}
		var blnMyTasks = false;
		var listing_type = "out";
		var title = this.model.get("title");
		//
        if (title.indexOf("Task Inbox")!==false || title=="My Tasks" || title=="Tasks for Me" || title=="Daily Tasks" || title=="Upcoming Tasks") {
            blnMyTasks = true;
			listing_type = "in";
        }
		var pagetype = "full";
		if (this.model.get("homepage")) {
			pagetype = "home";
		}
		
		if (typeof this.model.get("start") == "undefined") {
			if (low_date=="Invalid date") {
				low_date = "";
			}
			if (high_date=="Invalid date") {
				high_date = "";
			}
			this.model.set("start", low_date);
			this.model.set("end", high_date);
		}
		
		var day_print_url = "taskdayoutbox";
        if (blnMyTasks) {
            day_print_url = "taskdayinbox";
        }
        if (document.location.hash.indexOf("#tasks/") == 0) {
	        day_print_url = "kasetasksbyday/" + current_case_id;
        }
        if (document.location.hash.indexOf("outbox") > 0) {
	        day_print_url = day_print_url.replace("inbox", "outbox");
        }
		if (document.location.hash.indexOf("dailytaskall/") > 0) {
	        day_print_url = day_print_url.replace("box", "boxall");
        }
		
		//console.log(arrCounts);
		//var counts = Object.assign({}, arrCounts);
		var count_report = "";
		if (document.location.hash.indexOf("#taskfirmoverdue")==0) {
			var arrCountSorted = [];
			for (var assignee in arrCounts) {
				arrCountSorted.push([assignee, arrCounts[assignee]]);
			}
			
			arrCountSorted.sort(function(a, b) {
				if(a[0] < b[0]) {
					return -1;
				} else {
					return 1;
				}
			});
			
			var arrCountReport = [];
			arrCountSorted.forEach(function(item, index) {
				if (item.indexOf("unique") < 0 && item.indexOf("insert") < 0) {
					item[0] = "<span class='assignee_listing' style='cursor:pointer' title='Click to Filter listings for " + item[0] + "'>" + item[0] + "</span>";
					arrCountReport.push(item.join("&nbsp;(") + ")");
				}
			});
			count_report = arrCountReport.join("&nbsp;|&nbsp;");
		}
		//console.log(tasks);
		$(this.el).html(this.template({tasks: tasks, case_id: this.model.get("case_id"), title: this.model.get("title"), receive_label: this.model.get("receive_label"), homepage: this.model.get("homepage"), blnMyTasks: blnMyTasks, day_print_url: day_print_url, listing_type: listing_type, pagetype: pagetype, arrDayCount: arrDayCount, start: this.model.get("start"), end: this.model.get("end"), blnCc: blnCc, blnBadDates: blnBadDates, count_report: count_report}));
				
		setTimeout(function() {
			tableSortIt();
		}, 100);
		
		return this;
    },
	tasksDeleted: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		document.location.href = "#tasksdeleted/" + current_case_id;
	},
	tasksClosed: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		document.location.href = "#tasksclosed/" + current_case_id;
	},
	tasksOpen: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		
		var href = "";
		if (document.location.hash.indexOf("#tasksclosed/") > -1) {
			href = document.location.hash.replace("tasksclosed", "tasks");
		}
		if (document.location.hash.indexOf("#tasksdeleted/") > -1) {
			href = document.location.hash.replace("tasksdeleted", "tasks");
		}
		if (href != "") {
			document.location.href = href;
		}
	},
	tasksCompleted: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		document.location.href = "#taskcompleted";
	},
	tasksOverdue: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		document.location.href = "#taskoverdue";
	},
	printTasks: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var selected = element.selectedIndex;
		
		var url = "";
		switch(selected) {
			case 1:
				url = $("#print_today_link").attr("href");
				break;
			case 2:
				url = $("#print_thisweek_link").attr("href");
				break;
			case 3:
				url = $("#print_nextweek_link").attr("href");
				break;
			case 4:
				//overdue?
				if (document.location.hash=="#taskfirmoverdue") {
					url = "report.php#taskfirmoverdue";
				} else {
					url = $("#print_all_link").attr("href");
					var arrURL = url.split("/");
					
					if ($("#start_dateInput").length > 0) {
						var arrDate = $("#start_dateInput").val().split("/");
						
						arrURL[1] = arrDate[2] + "-" + arrDate[0] + "-" + arrDate[1];
					}
					if ($("#end_dateInput").length > 0) {
						var arrDate = $("#end_dateInput").val().split("/");
						
						arrURL[2] = arrDate[2] + "-" + arrDate[0] + "-" + arrDate[1];
					}
					
					url = arrURL.join("/");
				}
				break;
			case 5:
				var nickname = element.value;
				var the_worker = worker_searches.findWhere({nickname:nickname});
				
				var url = "report.php#taskoverdues/" + nickname + "/" + the_worker.get("user_id") + "/" + the_worker.get("user_name").replaceTout(" ", "_");
				
				break;
		}
		
		if (url!="") {
			window.open(url);
		}
		
		element.selectedIndex = 0;
	},
	showAllTasks: function() {
		$(".assignee_listing").css("background", "none");
		$("#show_all_tasks").hide();
		$(".task_date").show();
		$('.task_listing .task_data_row').show();
		$("#filtered_option").val("");
		$("#filtered_option").html("");
		$("#filtered_option").hide();
		$("#all_option").show();
	},
	filterByAssigneeNickname: function(event) {
		$("#show_all_tasks").css("display", "inline-block");
		
		var element = event.currentTarget;
		$(".assignee_listing").css("background", "none");
		element.style.background = "black";
		element.style.padding = "2px";
		
		var $rows = $('.task_listing .task_data_row');
		var val = element.innerText.toLowerCase();
		$("#filtered_option").val(element.innerText);
		$("#filtered_option").html(element.innerText + " Tasks");
		$("#all_option").hide();
		$("#filtered_option").show();
		//$(".date_row").hide();
		//$(".letter_row").hide();
		$rows.show().filter(function() {
			var text = $( '.assignee_values', $( this ) ).text ().replace(/\s+/g, ' ').toLowerCase();
			if (text.indexOf(val) > -1) {
				if (this.classList.length==2) {
					if (this.classList[1].indexOf("row") > -1) {
						var row_id = this.classList[1];
						$("." + row_id).show();
					}
				}
			}
			
			return !~text.indexOf(val);
		}).hide();
		
		$(".task_date").hide();
		
		var rows = $(".task_data_row");
		var rowsLength = rows.length;
		for(var j = 0; j < rowsLength; j++) {
			var row = rows[j];
			if (row.style.display != "none") {
				var class_date = row.classList[2];
				if ($(".task_date." + class_date).css("display")=="none") {
					$(".task_date." + class_date).show();
				}
			}
		}
		
		var rows = $(".task_row");
		var arrLength = rows.length;
		
		var arrVisibleRows = [];
		for(var i = 0; i < arrLength; i++) {
			var row = rows[i];
			if (row.style.display != "none") {
				arrVisibleRows.push(row.id);
			}
		}
		
		var arrLength = arrVisibleRows.length
		//go one less, so that the last one has a row after it
		for(var i = 0; i < arrLength - 1; i++) {
			var row = $("#" + arrVisibleRows[i]);
			var next_row = $("#" + arrVisibleRows[i+1]);
			var class_date = row[0].classList[0];
			var next_class_date = next_row[0].classList[0];
			
			if (class_date=="task_date" && next_class_date=="task_date") {
				row[0].style.display = "none";
			}
		}
		/*
		var task_dates = $(".task_date");
		var arrLength = task_dates.length;
		for(var i = 0; i < arrLength; i++) {
			var task_date = task_dates[i];
			var class_date = task_date.classList[1];
			
			var rows = $("." + class_date);
			var rowsLength = rows.length;
			var display_rows = 0;
			for(var j = 0; j < rowsLength; j++) {
				var row = rows[j];
				if (row.style.display != "none") {
					display_rows++;
				}
			}
			if (display_rows > 0) {
				$(".task_date." + class_date).show();
			}
		}
		*/
	},
	filterByAssignee: function(event) {
		var element = event.currentTarget;
		var $rows = $('.task_listing .task_data_row');
		var the_kind = element.id.replace("Filter", "");
		var theobj = $("#" + element.id);
		var val = $.trim($(theobj).val()).replace(/ +/g, ' ').toLowerCase();
		//$(".date_row").hide();
		//$(".letter_row").hide();
		$rows.show().filter(function() {
			//var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
			var text = $( '.assignee_values', $( this ) ).text ().replace(/\s+/g, ' ').toLowerCase();
			if (text.indexOf(val) > -1) {
				if (this.classList.length==2) {
					if (this.classList[1].indexOf("row") > -1) {
						var row_id = this.classList[1];
						$("." + row_id).show();
					}
				}
			}
			
			return !~text.indexOf(val);
		}).hide();
	},
	ccTasks: function(event) {
		//tomorrow
	},
	massChange: function(event) {
		//alert("Hey, I'm working.");
		//return;
		var dropdown = event.currentTarget;
		var arrCheckBoxes = $('.check_thisone');
		var arrChecked = [];
		var arrLength = arrCheckBoxes.length;
		
		for(var i =0; i < arrLength; i++) {
			var element = arrCheckBoxes[i];
			//var elementsArray = elements.id.split("_");
			if (element.checked) {
				var checkbox_element = element.id;
				var arrCheckbox =  checkbox_element.split("_");
				var task_id = arrCheckbox[2];
				arrChecked.push(task_id);
			}
		}
		
		if (arrChecked.length==0) {
			document.getElementById(dropdown.id).selectedIndex = 0;
			return;
		}
		this.model.set("checked_boxes", arrChecked);
		var ids = arrChecked.join(", ");
		var action = dropdown.value;
		if (action != "" || action != "undefined") {
			console.log(action);
			if (action == "change_date") {
				composeDateChange(ids, "task");
			}
			if (action == "close_task") {
				var arrIDs = ids.split(", ");
				for (var int = 0; int < arrIDs.length; int++) {
				//$.each( ids, function( key, value ) {
					//alert( arrIDs[int] );
					closeElement(event, arrIDs[int], "task");
				//});
				}
			}
			if (action == "delete_task") {
				var arrIDs = ids.split(", ");
				for (var int = 0; int < arrIDs.length; int++) {
				//$.each( ids, function( key, value ) {
					//alert( arrIDs[int] );
					deleteElement(event, arrIDs[int], "tasking");
				//});
				}
			}
		} else { 
			console.log("no action");
		}
		//$("#deleteModal").modal('toggle');
	},
	updateRange: function(event) {
		event.preventDefault();
		var start_date_range = $('#start_dateInput').val();
		var format_start_date_range = moment(start_date_range).format("YYYY-MM-DD");
		var end_date_range = $('#end_dateInput').val();
		var format_end_date_range = moment(end_date_range).format("YYYY-MM-DD");
		var hash = "#taskbydates/";
		if (document.location.hash.indexOf("taskoutbox") > 0 || document.location.hash.indexOf("taskbydatesout") > 0) {
			hash = "#taskbydatesout/";
		}
		document.location.href = hash + format_start_date_range + "/" + format_end_date_range + "";
	},
	checkThisOne: function(event) {
		//event.preventDefault();
		var element = event.currentTarget;
		var arrThisOne = $(".check_thisone");
		var arrLength = arrThisOne.length;
		
		if ($('#mass_change').css("display")=="none") {	
			for(var i = 0; i < arrLength; i++) {
				var element = arrThisOne[i];
				if (element.checked) {
					$('#mass_change').fadeIn();
					break;
				}
			}
		} else {
			for(var i = 0; i < arrLength; i++) {
				var element = arrThisOne[i];
				if (element.checked) {
					//something is checked get out
					return;
				}
			}
			//this will hide the drop down
			$('#mass_change').fadeOut();
		}
	},
	checkAll: function(event) {
		//event.preventDefault();
		var element = event.currentTarget;
		var element_id = $(element).attr('id');
		var arrElement = element_id.split("_");
		var date_day = arrElement[3];
		var task_id = arrElement[2];
		//date_day = date_day.replace(" ", ".");
		//date_day = date_day.replace(" ", ".");
		//date_day = date_day.replace(" ", ".");
		
		$('.check_thisone_' + date_day).prop('checked', element.checked);
		if($('#mass_change').is(":visible")) {
			if (element.checked) { // check select status
				$('#mass_change').show();
			} else {
				$('#mass_change').hide();
			}
		} else {
			if (element.checked) { // check select status
				$('#mass_change').show();
			} else {
				$('#mass_change').hide();
			}
		}
		return;
		
		if ($('.check_thisone_' + date_day).prop('checked') == "false") {
			if (element.checked) { // check select status
				$('.check_thisone_' + date_day).prop('checked', true);
			} else {
				$('.check_thisone').prop('checked', false);         
			}
		} else {
			if (element.checked) { // check select status
				
				$('.check_thisone_' + date_day).prop('checked', true);
				$(element).attr('checked', 'checked');
				
			} else {
				$('.check_thisone').prop('checked', false);
				$('.check_thisone').prop('checked', false);         
			}
		}
	},
	scheduleResetDay:function() {
		var self = this;
		clearTimeout(reset_timeout);
		setTimeout(function() {
			self.resetDayStartDate();
		}, 1000);
	},
	gotoTop: function() {
		var target_offset = $("#tasks_top").offset();
		var target_top = target_offset.top;
		target_top = target_top - 105;
		//goto that anchor by setting the body scroll top to anchor top
		$('html, body').animate({scrollTop:target_top}, 1100, 'easeInSine');
	},
	gotoToday: function() {
		/*
		if ($("#tasks_today").length > 0) {
			//get the top offset of the target anchor
			var target_offset = $("#tasks_today").offset();
			var target_top = target_offset.top;
			target_top = target_top - 55;
			//goto that anchor by setting the body scroll top to anchor top
			$('html, body').animate({scrollTop:target_top}, 1100, 'easeInSine');
		}
		*/
		var closest_date = this.model.get("closest_date");
		if ($(".task_date_" + closest_date).length > 0) {
			var top = parseInt($(".task_date_" + closest_date).position().top)
			$("#task_list_outer_div").scrollTop(top);
		}
	},
	resetDayStartDate: function() {
		var start_date = $("#taskdayview_start_date").val();
		if (typeof start_date == "undefined") {
			start_date = "";
		}
		if (start_date.length==10) {
			if (isDate(start_date)) {
				var arrURL = document.location.href.split("#")[1].split("/");
				current_start_date = arrURL[3];
				start_date = moment(start_date).format("YYYY-MM-DD");
				if (current_start_date != start_date) {
					$("#content").html(loading_image);
					var current_hash = document.location.hash.split("/")[0];
					document.location.href = current_hash + "/" + start_date;
				}
			}
		}
	},
	composeTasks: function(event) {
		event.preventDefault();
		composeTask();
	},
	expandTask: function(event) {
		event.preventDefault();
		
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[2];
		if (typeof id == "undefined") {
			id = 0;
		}
		
		$(".task_row_" + this.model.get("current_task_id")).css("background", "");
		this.model.set("current_task_id", -1);
		
		hidePreview();
		if (this.model.get("message_link_clicked")) {
			this.model.set("message_link_clicked", false);
			return;
		}
		var self = this;
		
		
		this.model.set("current_task_id", id);
		
		this.model.set("current_background", $(".task_row_" + id).css("background"));
		$(".task_row_" + id).css("background", "#F90");		
		$("#task_floats").hide();
		/*
		var new_outer_width = "60%";
		var new_pane_width = "40%";
		if (screen.width < 1300) {
			//var new_outer_width = "50%";
			//var new_pane_width = "50%";
		}
		$("#task_list_outer_div").css("width", new_outer_width);
		$("#preview_pane_holder").css("width", new_pane_width);
		*/
		$("#preview_pane_holder").css("width", "550px");
		var new_pane_width = window.innerWidth - 600;
		$("#task_list_outer_div").css("width", new_pane_width + "px");
		
		//is there an id already
		if (id > 0) {
			//we need a little more height for the assignee box
			$("#preview_pane_holder").css("height", "620px");
		} else {
			$("#preview_pane_holder").css("height", "600px");
		}
		
		$("#preview_pane_holder").fadeIn(function() {
			$("#preview_pane").html(loading_image);
			//load the note into the pane
			composeTaskPane(element.id);
		});
		
	},
	shrinkTask: function(event) {
		event.preventDefault();
		$("#preview_pane_holder").fadeOut(function() {
			$("#preview_pane").html("");
			$("#task_list_outer_div").css("width", "100%");		
		});
		$(".task_row_" + this.model.get("current_task_id")).css("background", "");
		this.model.set("current_task_id", -1);
		$("#task_floats").show();
	},
	editTask: function(event) {
		var element = event.currentTarget;
		if (element.id != "new_task") {
			this.readTask(event);
		}
		event.preventDefault();
		composeTask(element.id);
	} ,
	reactTask: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeTask(element.id);
	},
	readTask: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var id = element.id.split("_")[2];
		if ($(".task_listing #read_holder_" + id).css("display")!="none") {
			$(".task_listing #read_holder_" + id).fadeOut(
				function() {
					//$(".task_row_" + id).fadeIn();
					$(".task_listing #action_holder_" + id).fadeIn();
					//mark the row as read
					var url = 'api/task/read';
					formValues = "id=" + id;
			
					$.ajax({
						url:url,
						type:'POST',
						dataType:"json",
						data: formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error tasks
								saveFailed(data.error.text);
							} else {
								//console.log(data);
								//refresh the new task indicator
								checkTaskInbox();
							}
						}
					});
				}
			);
		}
	},
	confirmdeleteTask: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		if (element.id=="") {
			element = element.parentElement;
		}
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "task");
		/*
		var arrPosition = showDeleteConfirm(element);
		if ($("." + boxtype + "#confirm_delete_task").hasClass("home")) {	
			$("." + boxtype + "#confirm_delete_task").css({display: "none", top: arrPosition[0], left: arrPosition[1], position:'absolute'});
		} else {
			$("." + boxtype + "#confirm_delete_task").css({display: "none", top: arrPosition[0], left: arrPosition[1], position:'absolute'});
		}
		$("." + boxtype + "#confirm_delete_task").fadeIn();
		$("." + boxtype + " #confirm_delete_id").val(id);
		*/
	},
	restoreTask: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		element.style.display = "none";
		
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		var url = 'api/task/restore';
		formValues = "id=" + id;

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error tasks
					saveFailed(data.error.text);
				} else {
					$(".task_row_" + id).css("background", "lime");
					setTimeout(function() {
						$(".task_row_" + id).fadeOut();
					}, 2500);
				}
			}
		});
		
	},
	closeTask: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		closeElement(event, id, "task");
		
	},
	canceldeleteTask: function(event) {
		event.preventDefault();
		$("#confirm_delete_task").fadeOut();
	},
	deleteTask: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "task");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteTask(event);
			$(".task_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".task_row_" + id).fadeOut();
			}, 2500);
		}
	},
	freezePreview: function() {
		 freezeTaskPreview();
	},
	doTimeouts: function() {
		var self = this;
		if (document.location.hash.indexOf("all/")==-1) {
			//if ($(".dashboard_home").length == 0) {
				this.gotoToday();
			//}
		}
		//kase task listing?
		var blnKaseListing = false;
		if (document.location.hash.indexOf("#tasks/") == 0 || document.location.hash.indexOf("#tasksclosed/") == 0 || document.location.hash.indexOf("#tasksdeleted/") == 0) {
			$("#print_today_link").html("Print");
			$("#print_today_link").attr("href", "report.php#kasetasksbyday/" + current_case_id + "/" + moment().format("YYYY-MM-DD"));	
			// + "/" + moment().format("YYYY-MM-DD"));
			$("#print_today_link").attr("title", "Click to print this Kase's Tasks");
			$("#print_weeks").hide();
			
			var newhref = $("#print_all_link").attr("href");
			newhref = newhref.replace("taskbydates", "kasetaskbydates");
			newhref += "/" + current_case_id;
			$("#print_all_link").attr("href", newhref);
			
			var prints = document.getElementById("task_print_options").options;
			for (var i = 0; i < 5; i++) {
				var printlink = prints[i];
				var printid = printlink.id;
				if (printid!="" && printid!="today_option" && printid!="all_option") {
					printlink.style.display = "none";
				}
			}
			
			blnKaseListing = true;
		}
		if (document.location.hash.indexOf("all/") > 0) {
			$("#date_range_holder").hide();
			$("#print_weeks").hide();
			if (document.location.hash.indexOf("dailytaskall/") > 0) {
				$("#print_today_link").attr("href", "report.php#taskdayinboxall/" + moment().format("YYYY-MM-DD"));
				//trim the drop down
				if ($("#task_print_options").length > 0) {
					var prints = document.getElementById("task_print_options").options;
					for (var i = 0; i < 5; i++) {
						var printlink = prints[i];
						var printid = printlink.id;
						if (printid!="" && printid!="today_option") {
							printlink.style.display = "none";
						}
						if (printid=="today_option") {
							printlink.text = "All";
						}
					}
				}
			}
			if (document.location.hash.indexOf("taskcompletedall/") > 0) {
				$("#print_today_link").attr("href", "report.php#taskdaycompletedall/" + moment().format("YYYY-MM-DD"));
				
				//trim the drop down
				if ($("#task_print_options").length > 0) {
					var prints = document.getElementById("task_print_options").options;
					for (var i = 0; i < 5; i++) {
						var printlink = prints[i];
						var printid = printlink.id;
						if (printid!="" && printid!="today_option" && printid!="all_option") {
							printlink.style.display = "none";
						}
					}
				}
				
				$("#task_manage_holder").css({
				   'margin-top' : "-22px",
				   'left' : "605px"
				 });
				 $("#task_form_count").css({
				   'margin-left' : "145px",
				   'position' : ""
				 });
			}
		} else if (document.location.hash.indexOf("taskcompleted") > 0) {
			var newhref = $("#print_today_link").attr("href").replace("taskdayinbox", "taskdaycompleted");
			var title = $("#print_today_link").attr("title");
			$("#print_today_link").attr("title", title.replace("Click to print today&rsquo;s tasks", "Click to print today&rsquo;s completed tasks"));
			$("#print_today_link").attr("href", newhref);
			
			newhref = $("#print_thisweek_link").attr("href");
			newhref = newhref.replace("taskbydates", "taskbydatescompleted");
			$("#print_thisweek_link").attr("href", newhref);
			
			newhref = $("#print_nextweek_link").attr("href");
			newhref = newhref.replace("taskbydates", "taskbydatescompleted");
			$("#print_nextweek_link").attr("href", newhref);
			
			if ($("#print_all_link").length > 0) {
				newhref = $("#print_all_link").attr("href");
				newhref = newhref.replace("taskbydates", "taskbydatescompleted");
				$("#print_all_link").attr("href", newhref);
			}
			
			//trim the drop down
			if ($("#task_print_options").length > 0) {
				var prints = document.getElementById("task_print_options").options;
				for (var i = 0; i < 5; i++) {
					var printlink = prints[i];
					var printid = printlink.id;
					if (printid!="" && printid!="today_option" && printid!="all_option") {
						printlink.style.display = "none";
					}
				}
			}
			
			$("#task_manage_holder").css({
			   'margin-top' : "-16px",
			   'left' : "205px"
			 });
			 $("#task_form_count").css({
			   'margin-left' : "145px",
			   'position' : ""
			 });
		} else if (document.location.hash.indexOf("taskoverdue") > 0) {
			$("#date_range_holder").hide();
			$("#print_weeks").hide();
			$("#print_today_link").html("Print");
			var newhref = $("#print_today_link").attr("href").replace("taskdayinbox", "taskoverdue");
			var title = $("#print_today_link").attr("title");
			$("#print_today_link").attr("title", title.replace("Click to print today&rsquo;s tasks", "Click to print overdue tasks"));
			var arrHref = newhref.split("/");
			newhref = arrHref[0];
			$("#print_today_link").attr("href", newhref);
			$("#task_manage_holder").hide();
			
			//trim the drop down
			if ($("#task_print_options").length > 0) {
				var prints = document.getElementById("task_print_options").options;
				for (var i = 0; i < 5; i++) {
					var printlink = prints[i];
					var printid = printlink.id;
					if (printid!="" && printid!="today_option") {
						printlink.style.display = "none";
					}
					if (printid=="today_option") {
						printlink.text = "All";
					}
				}
			}
			
			$("#task_form_count").css({
			 'margin-top':'-20px',
			   'left' : "125px"
			 });
		} else if (document.location.hash.indexOf("#taskfirmoverdue")==0) {
			var prints = document.getElementById("task_print_options").options;
			for (var i = 0; i < 5; i++) {
				var printlink = prints[i];
				var printid = printlink.id;
				
				if (printid!="" && printid!="all_option" && printid!="filtered_option") {
					printlink.style.display = "none";
				} else {
					printlink.style.display = "";
				}
				if (printid=="all_option") {
					printlink.text = "All";
				}
			}
		} else if (document.location.hash.indexOf("taskbydates") > 0) {
			$("#print_weeks").hide();
			$("#print_today_link").html("Print");
			$("#print_today_link").attr("href", "report.php" + document.location.hash);
			$("#task_manage_holder").hide();
			$("#date_range_holder").show();
			rangeDates();
		}else {
			if (!blnKaseListing) {
				$("#date_range_holder").show();
				$("#print_weeks").show();
				rangeDates();
			}
		}
		if (document.location.hash.indexOf("#tasksupcoming") == 0) {
			$("#task_manage_holder").css({
			   'margin-top' : "-16px",
			   'left' : "205px"
			 });
			 $("#task_form_count").css({
			   'margin-left' : "145px",
			   'position' : ""
			 });
		}
		if (document.location.hash.indexOf("#dailytaskall") == 0) {
			$("#task_manage_holder").css({
			   'margin-top' : "-52px",
			   'left' : "455px"
			 });
			 var the_count = $("#task_form_count").html();
			 $("#task_form_count").hide();
			 var new_html = $("#task_form_title").html().replace("All Employees Tasks", "All Employees Tasks " + the_count);
			 $("#task_form_title").html(new_html);
		}
		//out?
		if (document.location.hash.indexOf("outbox") > 0) {
			newhref = $("#print_today_link").attr("href");
			newhref = newhref.replace("inbox", "outbox");
			$("#print_today_link").attr("href", newhref);
			
			newhref = $("#print_thisweek_link").attr("href");
			newhref = newhref.replace("taskbydates", "taskbydatesout");
			$("#print_thisweek_link").attr("href", newhref);
			
			newhref = $("#print_nextweek_link").attr("href");
			newhref = newhref.replace("taskbydates", "taskbydatesout");
			$("#print_nextweek_link").attr("href", newhref);
			
			if ($("#print_all_link").length > 0) {
				newhref = $("#print_all_link").attr("href");
				newhref = newhref.replace("taskbydates", "taskbydatesout");
				$("#print_all_link").attr("href", newhref);
			}
		}
		
		if (blnKaseListing) {
			//get the count of others
			var blnShowClosed = true;
			if (document.location.hash.indexOf("tasksclosed") > 0) {
				blnShowClosed = false;
			}
			var blnShowDeleted = true;
			if (document.location.hash.indexOf("tasksdeleted") > 0) {
				blnShowDeleted = false;
			}
			
			if (document.location.hash.indexOf("#tasks/") < 0) {
				//fetch kase tasks
				var kase_tasks = new TaskInboxCollection({case_id: current_case_id, blnClosed: false, blnDeleted: false});
				kase_tasks.fetch({
					success: function (data) {
						$("#open_tasks").html("Open Tasks (" + data.length + ")");
						$("#open_tasks").show();
					}
				});	
			}
			
			if (document.location.hash.indexOf("#tasksclosed/") < 0) {
				//any closeds
				//fetch kase tasks
				var closed_tasks = new TaskInboxCollection({case_id: current_case_id, blnClosed: true});
				closed_tasks.fetch({
					success: function (data) {
						$("#closed_tasks").html("Closed Tasks (" + data.length + ")");
						$("#closed_tasks").show();
					}
				});	
			}
			if (document.location.hash.indexOf("#tasksdeleted/") < 0) {
				//any deletes
				var deleted_tasks = new TaskInboxCollection({case_id: current_case_id, blnDeleted: true});
				deleted_tasks.fetch({
					success: function (data) {
						if (data.length > 0) {
							$("#deleted_tasks").html("Deleted Tasks (" + data.length + ")");
							$("#deleted_tasks_holder").css("display", "inline-block");
						}
					}
				});	
			}
		}
		
		if (document.location.hash.indexOf("dailytaskall") < 0) {
			var pane_height = (window.innerHeight - 80) + "px";
			$("#task_list_outer_div").css("height", pane_height);
		} else {
			//adjust
			$("#task_list_outer_div").css({
			 'overflow-y':'',
			   'height' : "60px"
			 });
		}
		self.model.set("hide_upload", true);
		showKaseAbstract(self.model);
	}
});

window.task_listing_mobile = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
    },
	events: {
		"click #compose_task": 					"composeTasks",
		"click .expand_task": 					"expandTask",
		"click .shrink_task": 					"shrinkTask",
		"click .task_action": 					"reactTask",
		"click .delete_task": 					"confirmdeleteTask",
		"click .delete_yes":					"deleteTask",
		"click .close_task":					"closeTask",
		"click .delete_no":						"canceldeleteTask",
		"click .task_listing .read_holders":	"readTask",
		"mouseover #task_preview_panel": 		"freezePreview",
		"mouseover .task_preview_link": 		"freezePreview",
		"click .edit_task":						"editTask",
		"keyup #taskdayview_start_date":		"scheduleResetDay",
		"click .backtotop":						"gotoTop",
		"click .check_all":						"checkAll",
		"change #mass_change":					"massChange",
		"click #update_date_range":				"updateRange",	
		"change .check_thisone":				"checkThisOne",	
		"change #assigneeFilter":				"filterByAssignee",
		"click #task_listing_mobile_all_done":	"doTimeouts"
	},
    render:function () {		
		var self = this;
		
		this.collection.bind("reset", this.render, this);
		
		if (typeof this.model.get("homepage") == "undefined") {
			this.model.set("homepage", false);
		}
		if (typeof this.model.get("case_id") == "undefined") {
			this.model.set("case_id", "");
		}
		/*
		var arrToUserNames = [];
		var task = this.collection.toJSON()[0];
		var arrToUsers = task.assignee.split(";");
		arrayLength = arrToUsers.length;
		for (var i = 0; i < arrayLength; i++) {
			var theworker = worker_searches.findWhere({"nickname": arrToUsers[i]});
			if (typeof theworker != "undefined") {
				if (this.model.get("homepage") == true) {
					arrToUserNames[arrToUserNames.length] = theworker.get("nickname");
				} else {
					arrToUserNames[arrToUserNames.length] = theworker.get("user_name");
				}
			} else {
				arrToUserNames[arrToUserNames.length] = arrToUsers[i];
			}
		}
		*/
		//we might have a new day
        var blnToday = false;
		var tasks = this.collection.toJSON();
		var arrDayCount = [];
		var low_date = "";
		var high_date = "";
		var blnCc = false;
		_.each( tasks, function(task) {
			if (low_date=="") {
				low_date = moment(task.task_dateandtime);
			}
			if (moment(task.task_dateandtime) < low_date) {
				low_date = moment(task.task_dateandtime);
			}
			
			if (high_date=="") {
				high_date = moment(task.task_dateandtime);
			}
			if (moment(task.task_dateandtime) > high_date) {
				high_date = moment(task.task_dateandtime);
			}
			
			if (moment(task.task_dateandtime).format('h:mm a')=="12:00 am") {
				task.time = "";
			} else {
				task.time = moment(task.task_dateandtime).format('h:mm a');
			}
			
			if (task.end_date=="0000-00-00 00:00:00" || task.end_date=="") {
				task.end_date = "";
			} else {
				task.end_date = moment(task.end_date).format("MM/DD/YY");
			}
			if (task.task_name != "") {
				task.task_title = task.task_name;
			}
			if (task.case_stored_name != "") {
				task.case_name = task.case_stored_name;
			}
			if (task.task_name != "" && task.task_description == "") {
				task.task_description = task.task_name;
			}
			if (task.cc!="") {
				if (!blnCc) {
					blnCc = true;
				}
			}
			var the_day = moment(task.task_dateandtime).format("MMDDYY");
			if (typeof arrDayCount[the_day] == "undefined") {
				arrDayCount[the_day] = 1;
			} else {
				arrDayCount[the_day]++;
			}
			
			//from
			var theassigner = worker_searches.findWhere({"user_name": task.from});
			if (typeof theassigner != "undefined") {
				task.from = theassigner.get("nickname").toUpperCase();
			}
			if (task.from!="") {
				task.originator = task.from;
			}
			
			//var the_day = moment(task.task_dateandtime).format("MMDDYY");
            var the_week = moment(task.task_dateandtime).format("MMYY") + moment(task.task_dateandtime, "YYYY-MM-DD").week();
			var this_week = moment().format("MMYY") + moment().week();
            task.anchor_link = "";
            if (the_week == this_week) {
	            if (!blnToday) {
                	blnToday = true;
                    task.anchor_link = "<a name='tasks_today' id='tasks_today'></a>";
                }
            }
			if (user_data_path == 'A1') {
				task.case_number = task.cpointer;
			}
			
			//related cases tasks
			var arrTaskDOIS = [];
			if (typeof task.injury_dates != "undefined") {
				if (task.injury_dates!="") {
					var arrDates = task.injury_dates.split(",");
					arrDates.forEach(function(injury_dates, index, array) {
						var arrDateArray = injury_dates.split("|");
						
						var doi_dates = moment(arrDateArray[1]).format("MM/DD/YYYY");
						if (arrDateArray[2]!="0000-00-00") {
							doi_dates += " - " + moment(arrDateArray[1]).format("MM/DD/YYYY") + " CT";
						}
						var injury_details = "<a href='#injury/" + current_case_id + "/" + arrDateArray[0] + "' class='white_text'>" + doi_dates + "</a>";
						arrTaskDOIS.push("<a href='#kases/" + task.main_case_id + "' class='white_text'>" + arrDateArray[0] + "</a> - DOI:" + injury_details);
					
					});
				}
			}
			task.subject = "";
			if (arrTaskDOIS.length > 0) {
				task.injury_dates = arrTaskDOIS.join("<br>");
				
				var new_subject = "<span style='font-size:0.7em;'><span style='background:orange;color:black;padding:2px;font-weight:bold'>From Related Cases:</span><br>" + task.injury_dates + "</span>";
				if (task.task_title!="") {
					task.subject = "<br><br>";
				}
				task.subject += new_subject;
			}
		});
		
		if (low_date == "") {
			low_date = moment(low_date).format("MM/DD/YYYY");
			high_date = moment(high_date).format("MM/DD/YYYY");
		} else {
			low_date = moment(low_date).format("MM/DD/YYYY");
			high_date = moment(high_date).format("MM/DD/YYYY");
		}
		var blnMyTasks = false;
		var listing_type = "out";
		var title = this.model.get("title");
		//
        if (title.indexOf("Task Inbox")!==false || title=="My Tasks" || title=="Tasks for Me" || title=="Daily Tasks" || title=="Upcoming Tasks") {
            blnMyTasks = true;
			listing_type = "in";
        }
		var pagetype = "full";
		if (this.model.get("homepage")) {
			pagetype = "home";
		}
		
		if (typeof this.model.get("start") == "undefined") {
			if (low_date=="Invalid date") {
				low_date = "";
			}
			if (high_date=="Invalid date") {
				high_date = "";
			}
			this.model.set("start", low_date);
			this.model.set("end", high_date);
		}
		
		try {
			$(this.el).html(this.template({tasks: tasks, case_id: this.model.get("case_id"), title: this.model.get("title"), receive_label: this.model.get("receive_label"), homepage: this.model.get("homepage"), blnMyTasks: blnMyTasks, listing_type: listing_type, pagetype: pagetype, arrDayCount: arrDayCount, start: this.model.get("start"), end: this.model.get("end"), blnCc: blnCc}));
		}
		catch(err) {
			console.log(err);
			
			return "";
		}
				
		setTimeout(function() {
			tableSortIt();
		}, 100);
		
		return this;
    },
	filterByAssignee: function(event) {
		var element = event.currentTarget;
		var $rows = $('.task_listing .task_data_row');
		var the_kind = element.id.replace("Filter", "");
		var theobj = $("#" + element.id);
		var val = $.trim($(theobj).val()).replace(/ +/g, ' ').toLowerCase();
		//$(".date_row").hide();
		//$(".letter_row").hide();
		$rows.show().filter(function() {
			//var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
			var text = $( '.assignee_values', $( this ) ).text ().replace(/\s+/g, ' ').toLowerCase();
			if (text.indexOf(val) > -1) {
				if (this.classList.length==2) {
					if (this.classList[1].indexOf("row") > -1) {
						var row_id = this.classList[1];
						$("." + row_id).show();
					}
				}
			}
			
			return !~text.indexOf(val);
		}).hide();
	},
	massChange: function(event) {
		//alert("Hey, I'm working.");
		//return;
		var dropdown = event.currentTarget;
		var arrCheckBoxes = $('.check_thisone');
		var arrChecked = [];
		var arrLength = arrCheckBoxes.length;
		
		for(var i =0; i < arrLength; i++) {
			var element = arrCheckBoxes[i];
			//var elementsArray = elements.id.split("_");
			if (element.checked) {
				var checkbox_element = element.id;
				var arrCheckbox =  checkbox_element.split("_");
				var task_id = arrCheckbox[2];
				arrChecked.push(task_id);
			}
		}
		
		if (arrChecked.length==0) {
			document.getElementById(dropdown.id).selectedIndex = 0;
			return;
		}
		this.model.set("checked_boxes", arrChecked);
		var ids = arrChecked.join(", ");
		var action = dropdown.value;
		if (action != "" || action != "undefined") {
			console.log(action);
			if (action == "change_date") {
				composeDateChange(ids, "task");
			}
			if (action == "close_task") {
				var arrIDs = ids.split(", ");
				for (var int = 0; int < arrIDs.length; int++) {
				//$.each( ids, function( key, value ) {
					//alert( arrIDs[int] );
					closeElement(event, arrIDs[int], "task");
				//});
				}
			}
			if (action == "delete_task") {
				var arrIDs = ids.split(", ");
				for (var int = 0; int < arrIDs.length; int++) {
				//$.each( ids, function( key, value ) {
					//alert( arrIDs[int] );
					deleteElement(event, arrIDs[int], "tasking");
				//});
				}
			}
		} else { 
			console.log("no action");
		}
		//$("#deleteModal").modal('toggle');
	},
	updateRange: function() {
		var start_date_range = $('#start_dateInput').val();
		var format_start_date_range = moment(start_date_range).format("YYYY-MM-DD");
		var end_date_range = $('#end_dateInput').val();
		var format_end_date_range = moment(end_date_range).format("YYYY-MM-DD");
		document.location.href = "#taskbydates/" + format_start_date_range + "/" + format_end_date_range + "";
	},
	checkThisOne: function(event) {
		//event.preventDefault();
		var element = event.currentTarget;
		var arrThisOne = $(".check_thisone");
		var arrLength = arrThisOne.length;
		
		if ($('#mass_change').css("display")=="none") {	
			for(var i = 0; i < arrLength; i++) {
				var element = arrThisOne[i];
				if (element.checked) {
					$('#mass_change').fadeIn();
					break;
				}
			}
		} else {
			for(var i = 0; i < arrLength; i++) {
				var element = arrThisOne[i];
				if (element.checked) {
					//something is checked get out
					return;
				}
			}
			//this will hide the drop down
			$('#mass_change').fadeOut();
		}
	},
	checkAll: function(event) {
		//event.preventDefault();
		var element = event.currentTarget;
		var element_id = $(element).attr('id');
		var arrElement = element_id.split("_");
		var date_day = arrElement[3];
		var task_id = arrElement[2];
		//date_day = date_day.replace(" ", ".");
		//date_day = date_day.replace(" ", ".");
		//date_day = date_day.replace(" ", ".");
		
		$('.check_thisone_' + date_day).prop('checked', element.checked);
		if($('#mass_change').is(":visible")) {
			if (element.checked) { // check select status
				$('#mass_change').show();
			} else {
				$('#mass_change').hide();
			}
		} else {
			if (element.checked) { // check select status
				$('#mass_change').show();
			} else {
				$('#mass_change').hide();
			}
		}
		return;
		
		if ($('.check_thisone_' + date_day).prop('checked') == "false") {
			if (element.checked) { // check select status
				$('.check_thisone_' + date_day).prop('checked', true);
			} else {
				$('.check_thisone').prop('checked', false);         
			}
		} else {
			if (element.checked) { // check select status
				
				$('.check_thisone_' + date_day).prop('checked', true);
				$(element).attr('checked', 'checked');
				
			} else {
				$('.check_thisone').prop('checked', false);
				$('.check_thisone').prop('checked', false);         
			}
		}
	},
	scheduleResetDay:function() {
		var self = this;
		clearTimeout(reset_timeout);
		setTimeout(function() {
			self.resetDayStartDate();
		}, 1000);
	},
	gotoTop: function() {
		var target_offset = $("#tasks_top").offset();
		var target_top = target_offset.top;
		target_top = target_top - 105;
		//goto that anchor by setting the body scroll top to anchor top
		$('html, body').animate({scrollTop:target_top}, 1100, 'easeInSine');
	},
	gotoToday: function() {
		if ($("#tasks_today").length > 0) {
			//get the top offset of the target anchor
			var target_offset = $("#tasks_today").offset();
			var target_top = target_offset.top;
			target_top = target_top - 55;
			//goto that anchor by setting the body scroll top to anchor top
			$('html, body').animate({scrollTop:target_top}, 1100, 'easeInSine');
		}
	},
	resetDayStartDate: function() {
		var start_date = $("#taskdayview_start_date").val();
		if (typeof start_date == "undefined") {
			start_date = "";
		}
		if (start_date.length==10) {
			if (isDate(start_date)) {
				var arrURL = document.location.href.split("#")[1].split("/");
				current_start_date = arrURL[3];
				start_date = moment(start_date).format("YYYY-MM-DD");
				if (current_start_date != start_date) {
					$("#content").html(loading_image);
					var current_hash = document.location.hash.split("/")[0];
					document.location.href = current_hash + "/" + start_date;
				}
			}
		}
	},
	composeTasks: function(event) {
		event.preventDefault();
		composeTask();
	} ,
	expandTask: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		$("#expand_" + id).hide();
		$("#shrink_" + id).show();
		$("#description_" + id).fadeIn();
		
	},
	shrinkTask: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		$("#shrink_" + id).hide();
		$("#expand_" + id).show();
		$("#description_" + id).fadeOut();
		
	},
	editTask: function(event) {
		var element = event.currentTarget;
		if (element.id != "new_task") {
			this.readTask(event);
		}
		event.preventDefault();
		composeTask(element.id);
	} ,
	reactTask: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeTask(element.id);
	},
	readTask: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var id = element.id.split("_")[2];
		if ($(".task_listing #read_holder_" + id).css("display")!="none") {
			$(".task_listing #read_holder_" + id).fadeOut(
				function() {
					//$(".task_row_" + id).fadeIn();
					$(".task_listing #action_holder_" + id).fadeIn();
					//mark the row as read
					var url = 'api/task/read';
					formValues = "id=" + id;
			
					$.ajax({
						url:url,
						type:'POST',
						dataType:"json",
						data: formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error tasks
								saveFailed(data.error.text);
							} else {
								//console.log(data);
								//refresh the new task indicator
								checkTaskInbox();
							}
						}
					});
				}
			);
		}
	},
	confirmdeleteTask: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		if (element.id=="") {
			element = element.parentElement;
		}
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "task");
		/*
		var arrPosition = showDeleteConfirm(element);
		if ($("." + boxtype + "#confirm_delete_task").hasClass("home")) {	
			$("." + boxtype + "#confirm_delete_task").css({display: "none", top: arrPosition[0], left: arrPosition[1], position:'absolute'});
		} else {
			$("." + boxtype + "#confirm_delete_task").css({display: "none", top: arrPosition[0], left: arrPosition[1], position:'absolute'});
		}
		$("." + boxtype + "#confirm_delete_task").fadeIn();
		$("." + boxtype + " #confirm_delete_id").val(id);
		*/
	},
	closeTask: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		closeElement(event, id, "task");
		
	},
	canceldeleteTask: function(event) {
		event.preventDefault();
		$("#confirm_delete_task").fadeOut();
	},
	deleteTask: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "task");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteTask(event);
			$(".task_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".task_row_" + id).fadeOut();
			}, 2500);
		}
	},
	freezePreview: function() {
		 freezeTaskPreview();
	},
	doTimeouts: function() {
		if (document.location.hash.indexOf("all/")===false) {
			if ($(".dashboard_home").length == 0) {
				this.gotoToday();
			}
		}
		//kase task listing?
		var blnKaseListing = false;
		if (document.location.hash.indexOf("#tasks/") == 0) {
			$("#print_today_link").html("Print");
			$("#print_today_link").attr("href", "report.php#kasetasks/" + current_case_id);	
			// + "/" + moment().format("YYYY-MM-DD"));
			$("#print_today_link").attr("title", "Click to print this Kase's Tasks");
			$("#print_weeks").hide();
			blnKaseListing = true;
		}
		if (document.location.hash.indexOf("all/") > 0) {
			$("#date_range_holder").hide();
			$("#print_weeks").hide();
			if (document.location.hash.indexOf("dailytaskall/") > 0) {
				$("#print_today_link").attr("href", "report.php#taskdayinboxall/" + moment().format("YYYY-MM-DD"));
			}
			if (document.location.hash.indexOf("taskcompletedall/") > 0) {
				$("#print_today_link").attr("href", "report.php#taskdaycompletedall/" + moment().format("YYYY-MM-DD"));
			}
		} else {
			$("#date_range_holder").show();
			$("#print_weeks").show();
			rangeDates();
		}
	}

});
window.task_track_listing = Backbone.View.extend({
	render: function() {
		var self = this;
		var mymodel = this.model.toJSON();
		var arrDayCount = [];
		var tasks = this.collection.toJSON();
		_.each( tasks, function(task) {
			
			if (task.task_dateandtime=="0000-00-00 00:00:00" || task.dateandtime=="") {
				task.task_dateandtime = "";
			} else {
				task.task_dateandtime = moment(task.task_dateandtime).format("MM/DD/YY");
			}
			if (task.time_stamp=="0000-00-00 00:00:00" || task.time_stamp=="") {
				task.time_stamp = "";
			} else {
				task.time_stamp = moment(task.time_stamp).format("MM/DD/YY h:mm a");
			}
			
		});
		
		try {
			$(this.el).html(this.template({tasks: tasks}));
		}
		catch(err) {
			var view = "task_track_listing";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		return this;
	}
});
window.task_print_listing = Backbone.View.extend({
	initialize:function () {
        //this.model.on("change", this.render, this);
    },
	events: {
		"dblclick #date_range_area":						"dateRangeShow",
		"click #close_date_range":							"dateRangeHide",
		"click #update_date_range":							"updateRange",
		"click .range_dates":								"calendarFix"
	},
	render: function(){
		var mymodel = this.model.toJSON();
		var arrDayCount = [];
		var tasks = this.collection.toJSON();
		_.each( tasks, function(task) {
			var the_day = moment(task.task_dateandtime).format("MMDDYY");
			if (typeof arrDayCount[the_day] == "undefined") {
				arrDayCount[the_day] = 1;
			} else {
				arrDayCount[the_day]++;
			}
			
			if (task.task_dateandtime=="0000-00-00 00:00:00" || task.dateandtime=="") {
				task.task_dateandtime = "";
			} else {
				task.task_dateandtime = moment(task.task_dateandtime).format("MM/DD/YY");
			}
			if (task.date_assigned=="0000-00-00 00:00:00" || task.date_assigned=="") {
				task.date_assigned = "";
			} else {
				task.date_assigned = moment(task.date_assigned).format("MM/DD/YY");
			}
		});
		if (document.location.hash.indexOf("all/") < 0) {
			this.model.set("title", login_username);
		} else {
			this.model.set("title", "All Employees");
		}
		$(this.el).html(this.template({tasks: tasks, title: this.model.get("title"), start: this.model.get("start"), end: this.model.get("end"), inout: this.model.get("inout"), receive_label: this.model.get("receive_label"), arrDayCount: arrDayCount}));
		
		return this;
	},
	dateRangeShow: function() {
		$("#date_range_area").fadeOut(100, function() {
			$("#date_range_area_input").fadeIn(200);
			rangeDates();
		});
	},
	dateRangeHide: function() {
		$("#date_range_area_input").fadeOut(100, function() {
			$("#date_range_area").fadeIn(200);
		});
	},
	calendarFix: function() {
		setTimeout(function() {
			$('.xdsoft_datetimepicker').css("top", "50px");
		}, 90);
	},
	updateRange: function() {
		var start_date_range = $('#start_dateInput').val();
		var format_start_date_range = moment(start_date_range).format("YYYY-MM-DD");
		var end_date_range = $('#end_dateInput').val();
		var format_end_date_range = moment(end_date_range).format("YYYY-MM-DD");
		document.location.href = "#taskbydates/" + format_start_date_range + "/" + format_end_date_range + "";
	}
});
window.task_print_stack_listing = Backbone.View.extend({
	events: {
		"change #assigneeFilter":				"filterByAssignee",
	},
	render: function(){
		var mymodel = this.model.toJSON();
		var arrDayCount = [];
		var tasks = this.collection.toJSON();
		var blnFirmOverdue = (document.location.hash=="#taskfirmoverdue");
		var current_employee = "";
		_.each( tasks, function(task) {
			var the_day = moment(task.task_dateandtime).format("MMDDYY");
			if (typeof arrDayCount[the_day] == "undefined") {
				arrDayCount[the_day] = 1;
			} else {
				arrDayCount[the_day]++;
			}
			
			if (task.task_dateandtime=="0000-00-00 00:00:00" || task.dateandtime=="") {
				task.task_dateandtime = "";
			} else {
				task.task_dateandtime = moment(task.task_dateandtime).format("MM/DD/YY");
			}
			if (task.date_assigned=="0000-00-00 00:00:00" || task.date_assigned=="") {
				task.date_assigned = "";
			} else {
				task.date_assigned = moment(task.date_assigned).format("MM/DD/YY");
			}
			
			if (task.task_type == "case_type_pi") {
				task.task_type = "PI";
			}
			
			task.user_name = "";
			//assignee name
			if (blnFirmOverdue) {
				if (task.assignee!="") {
					if (current_employee!=task.assignee) {
						current_employee = task.assignee;
						var arrEmployee = current_employee.split(";");
						var arrUserNames = [];
						for (var i = 0; i < arrEmployee.length; i++) {
							var employee = worker_searches.findWhere({"nickname": arrEmployee[i]});
							if (typeof employee != "undefined") {
								arrUserNames.push(employee.get("user_name"));
							}
						}
						task.user_name = arrUserNames.join("; ");
					}
				}
			}
			//console.log(task.user_name);
		});
		
		if (this.model.get("title").indexOf("Overdue Tasks List") < 0) {
			if (document.location.hash.indexOf("all/") < 0) {
				if (document.location.hash.indexOf("taskuserinbox") < 0) {
					this.model.set("title", login_username + " - " + this.model.get("title"));
				}
			} else {
				if (typeof this.model.get("user_id") == "undefined") {
					if (this.model.get("title").indexOf("Kase Tasks List") < 0) {
						this.model.set("title", "All Employees - " + this.model.get("title"));
					}
				}
			}
		}
		$(this.el).html(this.template({tasks: tasks, title: this.model.get("title"), inout: this.model.get("inout"), receive_label: this.model.get("receive_label"), arrDayCount: arrDayCount, blnFirmOverdue: blnFirmOverdue}));
		
		return this;
	},
	filterByAssignee: function(event) {
		var element = event.currentTarget;
		var $rows = $('.task_listing .task_data_row');
		var the_kind = element.id.replace("Filter", "");
		var theobj = $("#" + element.id);
		var val = $.trim($(theobj).val()).replace(/ +/g, ' ').toLowerCase();
		//$(".date_row").hide();
		//$(".letter_row").hide();
		$rows.show().filter(function() {
			//var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
			var text = $( '.assignee_values', $( this ) ).text ().replace(/\s+/g, ' ').toLowerCase();
			if (text.indexOf(val) > -1) {
				if (this.classList.length==2) {
					if (this.classList[1].indexOf("row") > -1) {
						var row_id = this.classList[1];
						setTimeout(function() {
							$("." + row_id).show();
						}, 100);
					}
				}
			}
			
			return !~text.indexOf(val);
		}).hide();
	}
});
window.task_summary_listing_print= Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .task_user": 					"printUserTasks"
	},
	render:function () {		
		if (typeof this.template != "function") {
			this.model.set("holder", "content");
			var view = "task_summary_listing_print";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}
			
		var self = this;
		var tasks = this.collection.toJSON();
		this.model.set("current_user_id", "-1");
		$(this.el).html(this.template({tasks: tasks}));
		
		return this;
	},
	printUserTasks:function(event) {
		event.preventDefault();
		
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var user_id = elementArray[elementArray.length - 1];
		var user_name = element.innerHTML.trim();
		var url = "report.php#taskuserinbox/" + user_id + "/" + encodeURIComponent(user_name);
		
		window.open(url);
	}
});
window.task_summary_listing = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .task_user": 					"expandSummary",
		"click #print_task_summary":			"printSummary"
	},
    render:function () {		
		if (typeof this.template != "function") {
			this.model.set("holder", "content");
			var view = "task_summary_listing";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}
			
		var self = this;
		var tasks = this.collection.toJSON();
		this.model.set("current_user_id", "-1");
		$(this.el).html(this.template({tasks: tasks}));
		
		setTimeout(function() {
			$("#task_summary_list_outer_div").css("height", (window.innerHeight - 80) + "px");
			$("#preview_block_holder").css("height", (window.innerHeight - 55) + "px");
			
			$("#task_summary_listing th, td").css("font-size", "1.6em");
		}, 1000);
		
		return this;
	},
	expandSummary: function(event) {
		event.preventDefault();
		
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		
		$(".user_task_row_" + this.model.get("current_user_id")).css("background", "");
		this.model.set("current_user_id", -1);
		
		hidePreview();
		
		var self = this;
		
		
		this.model.set("current_user_id", id);
		
		this.model.set("current_background", $(".user_task_row_" + id).css("background"));
		$(".user_task_row_" + id).css("background", "#F90");
		
		if (typeof cookie_left_width == "undefined") {
			$("#task_summary_list_outer_div").css("width", "30%");
			$("#preview_pane_holder").css("width", "70%");
		} else {
			if (cookie_left_width > 70) {
				cookie_left_width = 70;
				cookie_right_width = 30;
				
				writeCookie(cookie_left_width, 70, 24*60*60*1000);
				writeCookie(cookie_right_width, 30, 24*60*60*1000);
			}
			$("#task_summary_list_outer_div").css("width", cookie_left_width + "%");
			$("#preview_pane_holder").css("width", cookie_right_width + "%");
		}
		
		$("#preview_pane_holder").fadeIn(function() {
			$("#preview_pane").html(loading_image);
			//load the tasks into the pane
			var tasks = new TaskInboxCollection({ user_id: id });
			tasks.fetch({
				success: function (data) {
					if (data.length > 0) {
						var task_listing_info = new Backbone.Model;
						task_listing_info.set("title", element.innerText + " Tasks");
						task_listing_info.set("receive_label", "Due");
						task_listing_info.set("homepage", true);
						task_listing_info.set("user_id", id);
						task_listing_info.set("user_name", element.innerText);
						$('#preview_pane').html(new task_listing({collection: data, model: task_listing_info}).render().el);
						$("#preview_pane").removeClass("glass_header_no_padding");
						
					} else {
						$('#preview_pane').html("<span class='large_white_text'>No Scheduled Tasks</span>");
					}
				}
			});
		});
		
	},
	shrinkSummary: function(event) {
		event.preventDefault();
		$("#preview_pane_holder").fadeOut(function() {
			$("#preview_pane").html("");
			$("#task_summary_list_outer_div").css("width", "100%");		
		});
		$(".user_task_row_" + this.model.get("current_user_id")).css("background", "");
		this.model.set("current_user_id", -1);
	},
	printSummary: function() {
		var url = "report.php#tasksummary";
		window.open(url);
	}
});