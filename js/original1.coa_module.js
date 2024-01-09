function composeNewCOA(element_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	
	var coa_id = -1;
	
	var kaseArray = element_id.split("_");
	var case_id = kaseArray[1];
	var new_legal_id = kaseArray[2];
	if (kaseArray.length > 3) {
		coa_id = kaseArray[3];
	}
	var kase = kases.findWhere({case_id: case_id});
	if (typeof kase == "undefined") {
		//get it
		var kase =  new Kase({id: case_id});
		kase.fetch({
			success: function (kase) {
				if (kase.toJSON().uuid!="") {
					kases.add(kase);
					composeNewCOA(element_id);
				}
				return;		
			}
		});
		return;
	}
	var coa = new AccidentCause({case_id: case_id, coa_id: coa_id});
	coa.set("new_legal_id", new_legal_id);
	if (coa_id > 0) {
		coa.fetch({
			success: function (coa) {
				$("#myModalLabel").html("Edit COA");
				$("#myModalBody").html(new coa_view({model: coa}).render().el);		
			}
		});
	} else {
		$("#myModalLabel").html("New COA");
		$("#myModalBody").html(new coa_view({model: coa}).render().el);
	}
	
	$("#modal_save_holder").html('<a title="Save COA" class="save" onClick="saveEverythingCOA(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#input_for_checkbox").hide();
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	
	$(".modal-dialog").css("width", "600px");
	$(".modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");

	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
	$('#myModal4').modal('show');		
}

 function saveEverythingCOA(event) {
	event.preventDefault();
	var self = this;
	var url = "api/coa/add";
	
	var inputArr = $("#coa_panel .input_class").serializeArray();
	
	var coa_new_legal_id = $("#coa_form #new_legal_id").val();
	
	//var coa_date = $("#coa_dateInput").val();
	formValues = "case_id=" + current_case_id + "&coa_date=";
	
	var coa_id = $("#coa_form #table_id").val();
	formValues += "&table_id=" + coa_id;
	formValues += "&coa_info=" + JSON.stringify(inputArr);
	//formValues += "&coa_details=" + JSON.stringify(inputSlipAndFallArr);
	formValues += "&coa_new_legal_id=" + coa_new_legal_id;
	
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//indicate success
				$(".modal #gifsave").hide();
				$("#modal_save_holder").show();
				
				setTimeout(function() {
					$('#myModal4').modal('toggle');	
				}, 700);
				
				//refresh the listing
				var new_legal_id = $("#new_legal_form #table_id").val();
				var coas = new AccidentCauseCollection({case_id: current_case_id, new_legal_id: new_legal_id});
				coas.fetch({
					success: function(data) {
						var kase = kases.findWhere({case_id: current_case_id});		
						var kase_json = kase.toJSON();
						var file_number = kase_json.file_number;
						
						var case_date_law = $("#filing_dateSpan").html();
						var overide = $("#overideInput").val();
			
						var empty_model = new Backbone.Model;
						empty_model.set("file_number", file_number);
						empty_model.set("filing_date", case_date_law);
						empty_model.set("overide", overide);
						//empty_model.set("statute", statute);
						
						empty_model.set("case_id", current_case_id);
						empty_model.set("new_legal_id", new_legal_id);
						empty_model.set("holder", "coa_listing_holder");
						$('#coa_listing_holder').html(new coa_listing_view({collection: data, model: empty_model}).render().el);
						$("#coa_listing_holder").removeClass("glass_header_no_padding");
						//hideEditRow();
					}
				});
			}
		}
	});
}