function composeCustomersupport() {
	var case_id = -1;
	$("#myModalLabel").html("Report Issue");
		occurence = new Occurence({event_id: -1, event_kind: "customer_support"});
		occurence.set("case_id", case_id);
		occurence.set("event_kind", "customer_support");
		occurence.set("event_type", "customer_support");
		occurence.set("event_title", "Customer Support @ " + moment().format('MM/DD/YY h:mm a'));
		occurence.set("event_dateandtime", new Date());
		occurence.set("event_from", login_username);
		
		$("#modal_save_holder").html('<a title="Send Issue Details" class="event_dialog save" onClick="saveReportIssueModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
		//$("#input_for_checkbox").html('&nbsp;');
		occurence.set("holder", "myModalBody");
		$("#myModalBody").html(new customer_support_view({model: occurence}).render().el);
		
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
}

function saveReportIssueModal(event) {
	// var form = "report_issue_form";
	// $("." + form_name + " .custom-error-message").css('display', 'none');
	// $("." + form_name + " .token-input-list-person").css('border', 'solid 1px red');
	// addForm(event, "event");
	//alert("This is underdevelopment...");
	var complaint_url = 'api/complaint/add';
	var tasksformValues = new FormData($("#report_issue_form")[0]);
	
	var valid = true;
	
	if($("#event_titleInput").val()=="")
	{
		$("#msg_subject_error").text("Please enter subject...");
		valid = false;
		$("#event_titleInput").focus();
		return false;		
	}
	
	if($("#event_descriptionInput").val()=="")
	{
		$("#msg_details_error").text("Please enter details...");
		valid = false;
		$("#event_descriptionInput").focus();
		return false;		
	}	
	
	if(valid)
	{
		$("#upload_data_message").text("Please wait, uploading details...");
		$("#modal_save_holder").hide();
		$.ajax({
	 		url:complaint_url,
			type:'POST',
	 		//dataType:"json",
	 		data: tasksformValues,
			processData: false,
			contentType: false,
 			success:function (data) {	 				
				$("#report_issue_form").hide();
 				$("#complaint_message_div").show();			
				$("#complaint_message").text(data);
				
				// setTimeout(function () {
				//    $(".close").trigger("click");
				// }, 4000);				
 			}
		});
	}	
}

var seconds = 5000;
function checkComplaintAttention() {
	$.post("/api/complaint/check_issue_attention",{},function(data){
		if(data)
		{
			if(parseInt(data)>0)
			{
				$("#reported_issue_indicator").html("<a style='color:#fff' target='_blank' href='/manage/customers/reported-issues-list.php#moveupdate'>" + data + "</a>");
				$("#reported_issue_indicator").show();
			}
			else
			{
				$("#reported_issue_indicator").hide();
			}
		}
		else
		{
			seconds = seconds * 3;
		}
	});
  	timeout = setTimeout(checkComplaintAttention, seconds);
}
checkComplaintAttention();