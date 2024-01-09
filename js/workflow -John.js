// JavaScript Document
function newKaseWorkFlow(data) {
//alert("hello");
	var new_kase = new Kase({case_id: data.id});
	new_kase.fetch({
		success: function(kase_data) {
			//3 day task - 4600 Letter sent to insurance
			var task_url = 'api/task/add';
			
			var tasksformValues = "table_name=task";
			var task_case_id = kase_data.get("case_id");
			var task_case_number = kase_data.get("case_number");
			if (task_case_number=="") {
				task_case_number = kase_data.get("file_number");
			}
			tasksformValues += "&task_title=New Kase - Work Letter to Insurance - " + task_case_number;
			tasksformValues += "&task_descriptionInput=Send <a href='#letters/" + task_case_id + "' class='white_text'>4600 Letter</a> sent to insurance for<br><a href='#kases/" + task_case_id + "' class='white_text' style='text-decoration:underline'>" + task_case_number + "</a>";
			tasksformValues += "&assignee=IS";
			tasksformValues += "&cc=JP";
			//use firstAvailableDay instead of below
			tasksformValues += "&calctask=3";
			tasksformValues += "&case_id=" + task_case_id;
			
			//return;
			$.ajax({
			url:task_url,
			type:'POST',
			dataType:"json",
			data: tasksformValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						console.log(data);
					}
				}
			});
			
			//second task
			var tasksformValues = "table_name=task";
			tasksformValues += "&task_title=New Kase - Follow up on Authorization - " + task_case_number;
			tasksformValues += "&task_descriptionInput=If none received, send out <a href='#letters/" + task_case_id + "' class='white_text'>Warning Letter</a> prior to filing EH<br><a href='#kases/" + task_case_id + "' class='white_text' style='text-decoration:underline'>" + task_case_number + "</a>";
			tasksformValues += "&assignee=IS";
			tasksformValues += "&cc=JP";
			//use firstAvailableDay instead of below
			tasksformValues += "&calctask=7";
			tasksformValues += "&case_id=" + task_case_id;
			
			//return;
			$.ajax({
			url:task_url,
			type:'POST',
			dataType:"json",
			data: tasksformValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						console.log(data);
					}
				}
			});
			
			if (new_kase.get("worker")=="") {
				new_kase.set("worker", "EC")
			}
			
			//third task
			var tasksformValues = "table_name=task";
			tasksformValues += "&task_title=New Kase - Follow up with Client - 45 Days - " + task_case_number;
			tasksformValues += "&task_descriptionInput=Follow up with Client - 45 Days<br><a href='#kases/" + data.id + "' class='white_text' style='text-decoration:underline'>" + task_case_number + "</a>";
			tasksformValues += "&assignee=" + new_kase.get("worker");
			tasksformValues += "&cc=AP";
			//use firstAvailableDay instead of below
			tasksformValues += "&calctask=45";
			tasksformValues += "&case_id=" + data.id;
			
			//return;
			$.ajax({
			url:task_url,
			type:'POST',
			dataType:"json",
			data: tasksformValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						console.log(data);
					}
				}
			});
			
			//fourth task
			var tasksformValues = "table_name=task";
			tasksformValues += "&task_title=New Kase - Follow up with Client - 90 Days - " + task_case_number;
			tasksformValues += "&task_descriptionInput=Follow up with Client - 90 Days<br><a href='#kases/" + data.id + "' class='white_text' style='text-decoration:underline'>" + task_case_number + "</a>";
			tasksformValues += "&assignee=" + new_kase.get("worker");
			tasksformValues += "&cc=AP";
			//use firstAvailableDay instead of below
			tasksformValues += "&calctask=90";
			tasksformValues += "&case_id=" + data.id;
			
			//return;
			$.ajax({
			url:task_url,
			type:'POST',
			dataType:"json",
			data: tasksformValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						console.log(data);
					}
				}
			});
		}
	});
}
function newKaseWorkFlow1042(data) {
	var new_kase = new Kase({case_id: data.id});
	new_kase.fetch({
		success: function(kase_data) {
			//3 day task - 4600 Letter sent to insurance
			var task_url = 'api/task/add';
			
			var tasksformValues = "table_name=task";
			var task_case_id = kase_data.get("case_id");
			var task_case_number = kase_data.get("case_number");
			if (task_case_number=="") {
				task_case_number = kase_data.get("file_number");
			}
			tasksformValues += "&task_title=New Kase - Work Letter to Insurance - " + task_case_number;
			tasksformValues += "&task_descriptionInput=Send <a href='#letters/" + task_case_id + "' class='white_text'>4600 Letter</a> sent to insurance for<br><a href='#kases/" + task_case_id + "' class='white_text' style='text-decoration:underline'>" + task_case_number + "</a>";
			tasksformValues += "&assignee=IS";
			tasksformValues += "&cc=JP";
			//use firstAvailableDay instead of below
			tasksformValues += "&calctask=3";
			tasksformValues += "&case_id=" + task_case_id;
			
			//return;
			$.ajax({
			url:task_url,
			type:'POST',
			dataType:"json",
			data: tasksformValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						console.log(data);
					}
				}
			});
			
			//second task
			var tasksformValues = "table_name=task";
			tasksformValues += "&task_title=New Kase - Follow up on Authorization - " + task_case_number;
			tasksformValues += "&task_descriptionInput=If none received, send out <a href='#letters/" + task_case_id + "' class='white_text'>Warning Letter</a> prior to filing EH<br><a href='#kases/" + task_case_id + "' class='white_text' style='text-decoration:underline'>" + task_case_number + "</a>";
			tasksformValues += "&assignee=IS";
			tasksformValues += "&cc=JP";
			//use firstAvailableDay instead of below
			tasksformValues += "&calctask=7";
			tasksformValues += "&case_id=" + task_case_id;
			
			//return;
			$.ajax({
			url:task_url,
			type:'POST',
			dataType:"json",
			data: tasksformValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						console.log(data);
					}
				}
			});
			
			if (new_kase.get("worker")=="") {
				new_kase.set("worker", "EC")
			}
			
			//third task
			var tasksformValues = "table_name=task";
			tasksformValues += "&task_title=New Kase - Follow up with Client - 45 Days - " + task_case_number;
			tasksformValues += "&task_descriptionInput=Follow up with Client - 45 Days<br><a href='#kases/" + data.id + "' class='white_text' style='text-decoration:underline'>" + task_case_number + "</a>";
			tasksformValues += "&assignee=" + new_kase.get("worker");
			tasksformValues += "&cc=AP";
			//use firstAvailableDay instead of below
			tasksformValues += "&calctask=45";
			tasksformValues += "&case_id=" + data.id;
			
			//return;
			$.ajax({
			url:task_url,
			type:'POST',
			dataType:"json",
			data: tasksformValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						console.log(data);
					}
				}
			});
			
			//fourth task
			var tasksformValues = "table_name=task";
			tasksformValues += "&task_title=New Kase - Follow up with Client - 90 Days - " + task_case_number;
			tasksformValues += "&task_descriptionInput=Follow up with Client - 90 Days<br><a href='#kases/" + data.id + "' class='white_text' style='text-decoration:underline'>" + task_case_number + "</a>";
			tasksformValues += "&assignee=" + new_kase.get("worker");
			tasksformValues += "&cc=AP";
			//use firstAvailableDay instead of below
			tasksformValues += "&calctask=90";
			tasksformValues += "&case_id=" + data.id;
			
			//return;
			$.ajax({
			url:task_url,
			type:'POST',
			dataType:"json",
			data: tasksformValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						console.log(data);
					}
				}
			});
		}
	});
}
function newKaseWorkFlow1121(injury_id) {
	return;
	
	//1.5 years from sol
	var injury =  new Injury({id: current_case_id});
	injury.fetch({
		success: function(kase_data) {
			var task_url = 'api/task/add';
			
			var tasksformValues = "table_name=task";
			var task_case_id = kase_data.get("case_id");
			var task_case_number = kase_data.get("case_number");
			if (task_case_number=="") {
				task_case_number = kase_data.get("file_number");
			}
			tasksformValues += "&task_title=New Kase - 1.5 YEARS FROM SOL - " + task_case_number;
			tasksformValues += "&task_descriptionInput=Is client done treating?  If not, who is client still treating with and why?";
			
			var arrAssignees = array();
			var assignee = kase_data.get("worker");
			if (isNaN(kase_data.get("worker"))) {
				var theworker = worker_searches.findWhere({"nickname": kase_data.get("worker")});
				if (typeof theworker != "undefined") {
					assignee = theworker.get("worker");
				}
			}
			if (assignee!="") {
				arrAssignees.push(assignee);
			}
			//supervising_attorney
			var assignee = kase_data.get("supervising_attorney");
			if (isNaN(kase_data.get("supervising_attorney"))) {
				var theworker = worker_searches.findWhere({"nickname": kase_data.get("supervising_attorney")});
				if (typeof theworker != "undefined") {
					assignee = theworker.get("worker");
				}
			}
			if (assignee!="") {
				arrAssignees.push(assignee);
			}
			tasksformValues += "&assignee=" + arrAssignees.join(";", arrAssignees);
			//use firstAvailableDay instead of below
			tasksformValues += "&calctask=3";
			tasksformValues += "&case_id=" + task_case_id;
			
			//return;
			$.ajax({
			url:task_url,
			type:'POST',
			dataType:"json",
			data: tasksformValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						console.log(data);
					}
				}
			});
		}
	});
	
}
function newKaseWorkFlow1049() {
	//add task
	var task_url = 'api/task/add';
	
	var tasksformValues = "table_name=task";
	tasksformValues += "&task_title=re: Case review ";
	tasksformValues += "&task_descriptionInput=Case Review";
	tasksformValues += "&assignee=RLG";
	
	var todayDate = new Date();
	var someDate = new Date();
	var numberOfDaysToAdd = 120;
	someDate.setDate(todayDate.getDate() + numberOfDaysToAdd);
	
	while (someDate.getDay() == 0 || someDate.getDay() == 6) {
		someDate.setDate(someDate.getDate()+1)
	}
	
	tasksformValues += "&task_dateandtime=" + moment(someDate).format("MM/DD/YYYY") + " 08:30AM";
	tasksformValues += "&case_id=" + current_case_id;
	
	//return;
	$.ajax({
	url:task_url,
	type:'POST',
	dataType:"json",
	data: tasksformValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//console.log("save_120");
			}
		}
	});
	
	var tasksformValues = "table_name=task";
	tasksformValues += "&task_title=POA task";
	tasksformValues += "&task_descriptionInput=POA task";
	tasksformValues += "&assignee=RLG";
	
	var todayDate = new Date();
	var someDate = new Date();
	var numberOfDaysToAdd = 230;
	someDate.setDate(todayDate.getDate() + numberOfDaysToAdd);
	while (someDate.getDay() == 0 || someDate.getDay() == 6) {
		someDate.setDate(someDate.getDate()+1)
	}
	
	tasksformValues += "&task_dateandtime=" + moment(someDate).format("MM/DD/YYYY") + " 08:30AM";
	tasksformValues += "&case_id=" + current_case_id;
	
	//return;
	$.ajax({
	url:task_url,
	type:'POST',
	dataType:"json",
	data: tasksformValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//console.log("save_230");
			}
		}
	});
	
	if ($("#workerInput").val()!="") {
		var tasksformValues = "table_name=task";
		tasksformValues += "&task_title=introduce and remind of med appt";
		tasksformValues += "&task_descriptionInput=introduce and remind of med appt";
		tasksformValues += "&assignee=" + $("#workerInput").val();
		
		var todayDate = new Date();
		var someDate = new Date();
		var numberOfDaysToAdd = 10;
		someDate.setDate(todayDate.getDate() + numberOfDaysToAdd);
		while (someDate.getDay() == 0 || someDate.getDay() == 6) {
			someDate.setDate(someDate.getDate()+1)
		}
		
		tasksformValues += "&task_dateandtime=" + moment(someDate).format("MM/DD/YYYY") + " 08:30AM";
		tasksformValues += "&case_id=" + current_case_id;
		
		//return;
		$.ajax({
		url:task_url,
		type:'POST',
		dataType:"json",
		data: tasksformValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//console.log("save_10_worker");
				}
			}
		});
	}
}