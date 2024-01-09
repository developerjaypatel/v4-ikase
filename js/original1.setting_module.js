function saveSettingModal(event) {
	addForm(event, "setting");
}
function composeNewCategorySetting(element_id, type) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	if (typeof type == "undefined") {
		type = "customer";
	}
	var setting_id = -1;
	var category_id = element_id.replace("new_setting_", "");
	
	
	var new_setting = new Backbone.Model({new_setting_id: setting_id});
	new_setting.set("type", type);
	new_setting.set("setting_id", setting_id);
	new_setting.set("category", category_id);
	new_setting.set("setting", "");
	new_setting.set("setting_value", "");
	new_setting.set("default_value", "");
	new_setting.set("holder", "myModalBody");
	//new_setting.set("dateandtime", moment().format('MM/DD/YYYY h:mm a'));
	$("#myModalBody").html(new setting_view({model: new_setting}).render().el);
	$("#myModalLabel").html("New Setting");
	
	$("#apply_settings").hide();
	
	$("#modal_save_holder").html('<a title="Save Setting" class="setting save" onClick="saveSettingModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("setting");
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_task.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$(".modal-dialog").css("width", "600px");
	$(".modal-dialog").css("background-image", "url('img/glass_edit_header_task.png')");
	/*
	setTimeout(function() {
		//$('.modal-dialog').css('top', '0px');
		$('.modal-dialog').css('top', '20%');
		$('.modal-dialog').css('margin-top', '50px')
	}, 700);
	*/
	$(".modal-content").css("background-image", "url('img/glass_edit_header_task.png')");
}
function composeNewSetting(element_id, type) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	if (typeof type == "undefined") {
		type = "customer";
	}
	if (element_id != "new_setting") {
		var partieArray = element_id.split("_");
	}
	var setting_id = -1;
	if (element_id != "new_setting") {
		if (partieArray.length > 2) {
			setting_id = partieArray[2];
		}
	}
	
	if (setting_id < 0) {
		var new_setting = new Backbone.Model({new_setting_id: setting_id});
		new_setting.set("type", type);
		new_setting.set("setting_id", setting_id);
		new_setting.set("category", "");
		new_setting.set("setting", "");
		new_setting.set("setting_value", "");
		new_setting.set("default_value", "");
		new_setting.set("holder", "myModalBody");
		//new_setting.set("dateandtime", moment().format('MM/DD/YYYY h:mm a'));
		$("#myModalBody").html(new setting_view({model: new_setting}).render().el);
		$("#myModalLabel").html("New Setting");
	} else {
		var new_setting = new CustomerSetting({setting_uuid: setting_id});
		new_setting.fetch({
			success: function (data) {
				data.set("new_setting_id", data.id);
				data.set("type", type);
				data.set("holder", "myModalBody");
				$("#myModalBody").html(new setting_view({model: data}).render().el);
				$("#myModalLabel").html("Edit Setting");
			}
		});
	}
	$("#apply_settings").hide();
	
	$("#modal_save_holder").html('<a title="Save Setting" class="setting save" onClick="saveSettingModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("setting");
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_task.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$(".modal-dialog").css("width", "600px");
	$(".modal-dialog").css("background-image", "url('img/glass_edit_header_task.png')");
	/*
	setTimeout(function() {
		//$('.modal-dialog').css('top', '0px');
		$('.modal-dialog').css('top', '20%');
		$('.modal-dialog').css('margin-top', '50px')
	}, 700);
	*/
	$(".modal-content").css("background-image", "url('img/glass_edit_header_task.png')");
}
function composeUserSetting(element_id, type) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	type = "user";
	
	if (element_id != "new_setting") {
		var partieArray = element_id.split("_");
	}
	var setting_id = -1;
	if (element_id != "new_setting") {
		if (partieArray.length > 2) {
			setting_id = partieArray[2];
		}
	}
	
	if (setting_id < 0) {
		var new_setting = new Backbone.Model({new_setting_id: setting_id});
		new_setting.set("type", type);
		new_setting.set("setting_id", setting_id);
		new_setting.set("category", "");
		new_setting.set("setting", "");
		new_setting.set("setting_value", "");
		new_setting.set("default_value", "");
		new_setting.set("holder", "myModalBody");
		//new_setting.set("dateandtime", moment().format('MM/DD/YYYY h:mm a'));
		$("#myModalBody").html(new setting_view({model: new_setting}).render().el);
		$("#myModalLabel").html("New Setting");
	} else {
		var new_setting = new UserSetting({setting_id: setting_id});
		new_setting.fetch({
			success: function (data) {
				data.set("new_setting_id", data.id);
				data.set("type", type);
				data.set("holder", "myModalBody");
				$("#myModalBody").html(new setting_view({model: data}).render().el);
				$("#myModalLabel").html("Edit Setting");
			}
		});
	}
	$("#apply_settings").hide();
	
	$("#modal_save_holder").html('<a title="Save User Setting" class="setting save" onClick="saveSettingModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("setting");
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_task.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$(".modal-dialog").css("width", "600px");
	$(".modal-dialog").css("background-image", "url('img/glass_edit_header_task.png')");
	/*
	setTimeout(function() {
		$('.modal-dialog').css('top', '0px');
		$('.modal-dialog').css('margin-top', '50px')
	}, 700);
	*/
	$(".modal-content").css("background-image", "url('img/glass_edit_header_task.png')");
}