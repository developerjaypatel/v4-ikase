var search_document_id = false;
window.document_listing_view = Backbone.View.extend({
    initialize:function () {
        //this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
		
		//just in case the ftp update was not done right
		var url = "api/documents/processftp?customer_id=" + customer_id;
		var formValues = "";
		$.ajax({
			url:url,
			type:'POST',
			dataType:"text",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					//console.log("ftp processed");
				}
			}
		});
    },
    events:{
  		"click .save_icon":							"saveDocument",
		"click #document_upload":					"documentUpload",
		"click .delete_icon":						"confirmdeleteDocument",
		"click .delete_yes":						"deleteDocument",
		"click .delete_no":							"canceldeleteDocument",
		"change .document_input":					"releaseSave",
		"keyup .document_input":					"releaseSave",
		"click .send_icon":							"sendDocument",
		"change .filter_select":					"filterDocs",
		"click #select_all_documents":				"selectAllDocuments",
		"change #typeFilter":						"checkManageTypeFilter",
		"change #categoryFilter":					"checkManageCategoriesFilter",
		"change #sub_categoryFilter":				"checkManageSubCategoriesFilter",
		"keyup #document_searchList":				"findIt",
		"click #document_clear_search":				"clearSearch",
		"click .download_document":					"prepDownload",
		"click #label_search_messages":				"Vivify",
		"click #document_searchList":				"Vivify",
		"focus #document_searchList":				"Vivify",
		"change .check_thisone":					"checkThisOne",
		"click .bill_icon":							"billingSingle",
		"change #mass_change":						"massChange",
		"click #send_documents":					"sendDocuments",
		"click #save_billing_modal": 				"addBill",
		"blur #document_searchList":				"unVivify",
		"click .list_link":							"previewDocument",
		"click .window_link":						"openDocumentWindow",
		"click .selectdocument":					"showPencil",
		"click .document_medindex":					"changeNoteLabel",
		"click #document_listing_all_done":			"doTimeouts"
	},
    render:function () {		
		var self = this;
		
		if (typeof this.template != "function") {
			self.model.set("holder", "kase_content");
			var view = "document_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		//this.collection.bind("reset", this.render, this);
		var kase_documents = this.collection.toJSON();
		_.each( kase_documents, function(kase_document) {
			if (kase_document.received_date == "00/00/0000 12:00AM" || kase_document.received_date == "12/31/1969 4:00PM") {
				kase_document.received_date = "";
			}
			
			//docx
			kase_document.download_link = "";
			//download and update
			if (kase_document.document_extension=="") {
				//split it up
				var arrFile = kase_document.document_filename.split(".");
				kase_document.document_extension = arrFile[arrFile.length - 1];
			}
			if (kase_document.document_extension=="docx") {
				if (kase_document.document_filename.indexOf(".docx") < 0) {
					kase_document.document_filename += ".docx";
				}
				kase_document.download_link = "<a id='document_" + kase_document.document_id + "' title='Click to download document to your computer' class='white_text download_document' target='_blank' style='cursor:pointer'><i class='glyphicon glyphicon-save' style='color:#FFFFFF;'>&nbsp;</i></a>";
			}
			
			if (kase_document.document_filename.indexOf("D:/uploads/") > -1) {
				kase_document.document_filename = kase_document.document_filename.replaceAll("D:/uploads/" + customer_id + "/" + kase_document.case_id + "/", "");
				kase_document.document_filename = kase_document.document_filename.replace("../", "");
			}
			
			//obsolete below
			var href = "D:/uploads/" + customer_id + "/" + kase_document.case_id + "/" + kase_document.document_filename.replace("#", "%23");
			if (kase_document.type == "eams_form") {
				href = "D:/uploads/" + customer_id + "/" + kase_document.case_id + "/eams_forms/" + kase_document.document_filename.replace("#", "%23");
			}
			if (!isNaN(kase_document.thumbnail_folder) && kase_document.document_extension!="docx" && kase_document.thumbnail_folder!="") {
				href = "D:/uploads/" + customer_id + "/imports/" + kase_document.document_filename;
			}
			if (kase_document.type == "abacus") {
				href = "https://www.ikase.xyz/ikase/abacus/" + customer_data_source + "/" + kase_document.thumbnail_folder + "/" + kase_document.document_filename;
			}
			if (kase_document.source == "cloud") {
				 kase_document.type = kase_document.source;
			}
			//eams pdfs			
			if (kase_document.type == "jetfiler") {
				href = "D:/uploads/" + customer_id + "/" + kase_document.case_id + "/jetfiler/" + kase_document.document_filename;	
			}
			kase_document.href = href;
			//
			if (kase_document.type=="batchscan3") {
				href = "D:/uploads/" + customer_id + "/imports/" + kase_document.thumbnail_folder + "/" + kase_document.document_filename;
			}
			if (kase_document.source == "cloud") {
				kase_document.preview_href = "javascript:showCloudArchive('" + kase_document.document_filename + "')";
			} else {
			//new link
				kase_document.preview_href = "api/preview.php?case_id=" + kase_document.case_id + "&file=" + encodeURIComponent(kase_document.document_filename) + "&id=" + kase_document.id + "&type=" + kase_document.type + "&thumbnail_folder=" + kase_document.thumbnail_folder;
			}
			if (kase_document.user_name!="") {
				if (typeof kase_document.last_user_attributes == "undefined") {
					kase_document.user_name = "<br /><br />By: " + kase_document.user_name;
				} else {
					if (kase_document.user_name==null) {
						kase_document.user_name = "";
					}
					var arrDocUsers = kase_document.user_name.split("|");
					var arrDocUserAtts = kase_document.last_user_attributes.split("|");
					var arrLength = arrDocUsers.length;
					var arrDocumentCredits = [];
					for(var i =0; i < arrLength; i++) {
						var the_user_name = arrDocUsers[i];
						var the_attribute = arrDocUserAtts[i];
						
						if (typeof arrDocUserAtts[i] == "string") {
							var the_user_attribute = arrDocUserAtts[i].replaceAll("_", " ");
							arrDocumentCredits.push(the_user_attribute.capitalizeWords() + " by " + the_user_name);
						}
					}
					if (arrDocumentCredits.length > 0) {
						kase_document.user_name = "<br /><br />" + arrDocumentCredits.join("<br>");
					}
				}
			}
			var the_type = kase_document.type;
			if (kase_document.source!="") {
				the_type = kase_document.source;
			}
			if (typeof kase_document.preview_path == "undefined") {
				kase_document.preview_path = "";
			}
			if ( kase_document.preview_path=="img/no_preview.gif") {
				kase_document.preview_path = "";
			}
			
			if (kase_document.source != "cloud") {
				if (kase_document.preview_path=="") {
					kase_document.preview = documentThumbnail(kase_document.document_filename, kase_document.customer_id, kase_document.thumbnail_folder, kase_document.case_id, the_type, kase_document.document_date, kase_document.parent_document_uuid);
				} else {
					/*
					if (kase_document.thumbnail_folder!="") {
						kase_document.preview_path = "D:/uploads/" + customer_id + "/" + kase_document.thumbnail_folder.replace("medium", "thumbnail") + "/" +  kase_document.preview_path;
					}
					*/
					kase_document.preview = kase_document.preview_path;
				}
			} else {
				//kase_document.preview = "img/archive_preview.gif";
				kase_document.preview = "merge_documents/default_file_placeholder.jpg";
			}
			
			var blnSound = (kase_document.document_filename.indexOf(".wma") > -1 || kase_document.document_filename.indexOf(".mp3") > -1);
			if (blnSound) {
				kase_document.preview = "img/sound.gif";
			}
				
			if (kase_document.preview!="") {
				if (kase_document.preview.indexOf("/thumbnail/") > -1) {
					kase_document.thumbnail_folder = kase_document.case_id + "/medium";
				}
				
				if(kase_document.preview.includes("D:/uploads/"))
				{
					if(kase_document.preview == "merge_documents/default_file_placeholder.jpg")
					{
						kase_document.preview_img = "merge_documents/default_file_placeholder_main.jpg"
					}else{
						kase_document.preview_img = kase_document.preview;
					}
					if (kase_document.source != "cloud") {
						
						// kase_document.preview = '<img src="' + kase_document.preview + '" width="58" height="75" onmouseover="showPreviewThumbnail(event, \'' + kase_document.preview + '\')" onmouseout="hidePreview()" />';
						kase_document.preview = '<img src="https://v4.ikase.org/document_read.php?file=' + kase_document.preview + '" width="58" height="75" onmouseover="showPreviewThumbnail(event, \'' + kase_document.preview_img + '\')" onmouseout="hidePreview()" />';
					} else {
						// kase_document.preview = '<img src="' + kase_document.preview + '" width="58" height="75" />';
						kase_document.preview = '<img src="' + kase_document.preview + '" width="58" height="75" />';
					}
				}else if(kase_document.preview.includes("pdfimage/"))
				{
					if(kase_document.preview == "merge_documents/default_file_placeholder.jpg")
					{
						kase_document.preview_img = "merge_documents/default_file_placeholder_main.jpg"
					}else{
						kase_document.preview_img = kase_document.preview;
					}
					if (kase_document.source != "cloud") {
						
						// kase_document.preview = '<img src="' + kase_document.preview + '" width="58" height="75" onmouseover="showPreviewThumbnail(event, \'' + kase_document.preview + '\')" onmouseout="hidePreview()" />';
						kase_document.preview = '<img src="' + kase_document.preview + '" width="58" height="75" onmouseover="showPreviewThumbnail(event, \'' + kase_document.preview_img + '\')" onmouseout="hidePreview()" />';
					} else {
						// kase_document.preview = '<img src="' + kase_document.preview + '" width="58" height="75" />';
						kase_document.preview = '<img src="' + kase_document.preview + '" width="58" height="75" />';
					}
				}else{					

					if (kase_document.source != "cloud") {
						
						// kase_document.preview = '<img src="' + kase_document.preview + '" width="58" height="75" onmouseover="showPreviewThumbnail(event, \'' + kase_document.preview + '\')" onmouseout="hidePreview()" />';
						kase_document.preview = '<img src="' + kase_document.preview + '" width="58" height="75" onmouseover="showPreviewThumbnail(event, \'' + kase_document.preview + '\')" onmouseout="hidePreview()" />';
					} else {
						// kase_document.preview = '<img src="' + kase_document.preview + '" width="58" height="75" />';
						kase_document.preview = '<img src="' + kase_document.preview + '" width="58" height="75" />';
					}
				}
				/*
				if (customer_id==1121) {
					kase_document.preview = '<img src="' + kase_document.preview + '" width="58" height="75" onmouseover="showPreviewThumbnail(event, \'' + kase_document.preview + '\')" onmouseout="hidePreview()" />';
				} else {
					kase_document.preview = '<img src="' + kase_document.preview + '" width="58" height="75" onmouseover="documentPreview(event, \'' + kase_document.document_filename + '\', ' + kase_document.customer_id + ', \'' + kase_document.thumbnail_folder + '\', \'' + kase_document.source + '\', \'' + kase_document.document_date + '\', \'' + kase_document.parent_document_uuid + '\')" onmouseout="hidePreview()" />';
				}
				*/
			} 
			
			var note = kase_document.description_html;
			var arrNote = note.split("_");
			//batchscan clean up
			var blnShowNote = true;
			if (arrNote.length == 2) {
				if (!isNaN(arrNote[0]) && !isNaN(arrNote[0])) {
					blnShowNote = false;
				}
			}
			if (!blnShowNote) {
				kase_document.description_html = "";
			}
		});
		
		$(this.el).html(this.template({kase_documents: kase_documents, kase: this.model.toJSON()}));
		
		setTimeout(function() {
			var document_form_title = "Kase Documents";
			if (self.model.get("uuid")=="templates") {
				//change the title
				document_form_title = "Word Templates";
			}
			$("#document_form_title").html(document_form_title);
			self.model.set({table_name: "kase"});
			$('#upload_documents').html(new document_upload_view({model: self.model}).render().el);
			
			$('.document_listing .date_input').datetimepicker({ 
				validateOnBlur:false,  
				timepicker: false,
				format: "m/d/Y",
				step:30,
				onChangeDateTime: function(current_time,$input) {
					//get the current id
					var arrID = $input.prop("id").split("_");
					var theid = arrID[arrID.length - 1];
					
					$("#disabled_save_" + theid).fadeOut(function() {
						$("#document_save_" + theid).fadeIn();
					});
				}
			});
		}, 100);
		
		setTimeout(function() {
			$("#4906_announce").fadeOut();
		}, 6677);
		
		tableSortIt("document_listing");
		
		return this;
    },
	checkThisOne: function(event) {
		//event.preventDefault();
		/*var element = event.currentTarget;
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
		}*/
		this.enableSendDocs();
	},
	sendDocuments: function(event) {
		event.preventDefault();
		
		var check_thisones = $(".check_thisone");
		var arrLength = check_thisones.length;
		var arrCheckeds = [];
		for (var i =0; i < arrLength; i++) {
			var check_thisone = check_thisones[i];
			if (check_thisone.checked) {
				var arrID = check_thisone.id.split("_");
				var check_id = arrID[arrID.length - 1];
				arrCheckeds.push(check_id);
			}
		}
		//console.log(arrCheckeds);
		//now open a new message box and give it the ids
		composeMessage(arrCheckeds);
	},
	massChange: function(event) {
		var element = event.currentTarget;
		
		if (element.value == "send_this") {
			//get all the checked ids
			var check_thisones = $(".check_thisone");
			var arrLength = check_thisones.length;
			var arrCheckeds = [];
			for (var i =0; i < arrLength; i++) {
				var check_thisone = check_thisones[i];
				if (check_thisone.checked) {
					var arrID = check_thisone.id.split("_");
					var check_id = arrID[arrID.length - 1];
					arrCheckeds.push(check_id);
				}
			}
			//console.log(arrCheckeds);
			//now open a new message box and give it the ids
			composeMessage(arrCheckeds);
		}
		
		//reset
		$("#mass_change").val("");
		//alert("Hey, I'm under construction.");
		//return;
		
		//var billing_html = '<div id="billing_holder" style="padding-right:15px"><form id="billing_form" parsley-validate><input type="button" class="btn btn-xs" style="float:right; cursor:pointer; margin-right:-10px" id="save_billing_modal" value="Save" /><input id="table_name" name="table_name" type="hidden" value="billing" /><input id="table_id" name="table_id" type="hidden" value="" /><input id="table_uuid" name="table_uuid" type="hidden" value="" /><input id="billing_id" name="billing_id" type="hidden" value="" /><input id="case_id" name="case_id" type="hidden" value="" /><input id="action_id" name="action_id" type="hidden" class="billing billing_form" value="" /><input id="action_type" name="action_type" type="hidden" class="billing billing_form" value="document" /><table width="600px" border="0" align="left" cellpadding="3" cellspacing="0" style="display:" id="billing_table"><tr id="date_row"><th align="left" valign="top" scope="row" width="100px">Date:</th><td colspan="2" valign="top"><input value="" name="billing_dateInput" id="billing_dateInput" class="billing billing_form" placeholder="" style="width:165px" parsley-error-message="Req" required /><span style="margin-left:15px"><strong>Duration(min):</strong></span>&nbsp;&nbsp;<span><input value="" name="durationInput" id="durationInput" class="billing billing_form" placeholder="" style="width:38px" onkeypress="return event.charCode >= 48 && event.charCode <= 57" /></span></td></tr><tr id="status_row"><th align="left" valign="top" scope="row">Status:</th><td><select name="statusInput" id="statusInput" class="billing billing_form" style="width:310px"><option value="">Select Status..</option><option value="regular_billable">Regular Billable</option><option value="special_billable">Special Billable</option><option value="business_development">Business Development</option><option value="professional_development">Professional Development</option></select></td></tr><tr id="billing_rate_row"><th align="left" valign="top" scope="row">Billing Rate:</th><td><select name="billing_rateInput" id="billing_rateInput" class="billing billing_form" style="width:310px"><option value="">Select Rate..</option><option value="contingency">Contingency</option><option value="discount">Discount</option><option value="normal">Normal</option><option value="premium">Premium</option><option value="fixed_fee">Fixed Fee</option><option value="flat_rate_activity">Flat Rate Activity</option><option value="no_charge">No Charge</option></select></td></tr><tr id="activity_code_row"><th align="left" valign="top" scope="row">Activity Code:</th><td><select name="activity_codeInput" id="activity_codeInput" class="billing billing_form" style="width:310px"><option selected="selected" value="not_specified">Not Specified</option><option value="398087da-44a7-418b-9deb-f39890a2cea9">Consultation with</option><option value="d971bae8-1a3e-471f-b19c-3124db828a7d">Correspondence with</option><option value="9ae7291c-82d0-4fc9-8d56-7fa9abf218d5">Discussion with</option><option value="d18e6b09-a046-46d5-8f5a-2196c1cc0b78">Drafting documents</option><option value="de83a2e2-92f5-4b03-a03f-86ddea0b33dc">Filing Documentation</option><option value="909b7286-fa44-4cca-9f7c-98405c388927">Lunch with</option><option value="bdc32d74-f284-4b8d-9acc-bd82a12461cb">Meeting with</option><option value="60ed1acd-80a6-4ece-ac8f-ed9ea910303b">Negotiations</option><option value="b55efef8-fc31-4149-b9ce-dd6363c39561">Prepare opinion</option><option value="ad262a84-35db-4c8d-a990-2dff9dd47d51">Reporting</option><option value="3133bec0-c0ed-4591-b671-2a6951269a1f">Research</option><option value="4ffd5629-2c25-42c8-8902-98ba768575c3">Reviewing</option><option value="692f128a-6e32-47e0-8558-dcdb47ffe05a">Reviewing Documents</option><option value="1a13cbae-87ad-43a7-8771-8d6f0db57fa7">Telephone Conference with</option><option value="c2375f26-2a45-490c-bad9-860ff04eff5a">Motions</option><option value="77f9a56b-c282-4f8a-8521-7f864d23b1aa">Interview witness</option><option value="ac86a19b-ea3f-475c-95b9-3a2123fde20c">Consultations with expert witness</option><option value="21681748-fe81-49ac-807c-00753442459a">Brief witness</option><option value="74fb9200-e2f5-4291-b9b0-d3453dd97543">Discovery preparations</option><option value="e597434d-aa25-4b0d-a005-f3054a00359b">Attend discovery</option><option value="4d372c88-6540-4af3-ac87-d3320dd96d14">Trial preparations</option><option value="abf10a6f-40ca-4a11-a90c-f71b87998b30">Attend Trial</option><option value="8fa4e8c7-3c6d-4978-8118-6cc045e5e87c">Taxation advice</option><option value="e00753f3-d782-44e7-8195-da0cff9e1fa9">Telephone - exchange of voice mail</option><option value="cbf992ea-6f4d-4510-9472-6c7952faa32c">Telephone conference with client</option><option value="479205f3-3b4e-4184-ba4b-416081435a1c">Telephone conference with other side</option><option value="6f9d8e59-08a2-4462-a726-7a6e1f104d64">Incorporate company</option><option value="8ee8d265-4398-47d0-a710-5d67b1e2de89">Instructing research assistant</option><option value="e39550bb-2ccd-4c45-a99a-974f71af290e">On-line research</option><option value="1f294c00-1e79-43a1-895b-2e52d549f34e">Reviewing case-law</option><option value="b1714f44-34fb-413f-8896-822fa3feec4c">Plan and prepare for</option><option value="8071a61a-d165-4693-ae41-f28c8b4f13ff">Research</option><option value="df6b850e-e413-4a60-a49d-bbdc04ed5cdf">Draft/revise</option><option value="f1c081b8-622d-4b55-a9cf-3078a9c42920">Review/analyze</option><option value="59b39650-512d-4651-a22a-30a1ad50dc16">Communicate (in firm)</option><option value="7663f710-bf40-4206-bed3-5abe3a827a24">Communicate (with client)</option><option value="b696dd9d-fbb2-4c80-b23d-e7b7160ef10e">Communicate (other outside counsel)</option><option value="5ef5a862-7d12-4673-9771-2f97d10439c2">Communicate (other external)</option><option value="3912cf8c-e224-4284-aede-2c59f44eb771">Appear for/attend</option><option value="2b9b471a-5b14-4667-a3b1-11247773a044">Manage data/files</option><option value="de2f61c7-7148-4e82-9378-821cdff90d08">Billable Travel Time</option><option value="e3b5f1af-ceb1-45d2-b2bb-7d7e62231804">Medical Record and Medical Bill Management</option><option value="30f6b6e1-1e9e-43c2-ad44-199117f50221">Training</option><option value="002d905b-7a41-4312-b07d-6d606453ac83">Special Handling Copying/Scanning/Imaging (Internal)</option><option value="84c8facc-cc75-44d2-bd4c-61be744d2be3">Collection-Forensic</option><option value="bd9b4a01-6b95-443b-bfaa-daf776d824d8">Culling &amp; Filtering</option><option value="f8bfd31f-3461-4a52-919b-2fd7d1964a0f">Processing</option><option value="ff6b8f77-c897-4bf8-91b1-cd552bf6449d">Review and Analysis</option><option value="4130d181-fa13-4afd-a3b1-feac770f36b9">Quality Assurance and Control</option><option value="46c58d7d-cab8-4800-a5ed-483d4b31341b">Search Creation and Execution</option><option value="c7c4652d-f737-4571-b0b2-6a0393dde358">Privilege Review Culling and Log Creation</option><option value="37e931e3-2384-49c4-8cbc-20dcbbb6b65d">Document Production Creation and Preparation</option><option value="ab84e885-1c17-4b4c-ad49-1fef8ed953b8">Evidence/Exhibit Creation and Preparation</option><option value="bfa1eeb1-dbb0-41d9-857a-0eea12972d1a">Project Management</option><option value="3dda6652-f5e6-43fb-bac3-abefd4bab11c">Collection Closing Activities</option></select></td></tr><tr id="timekeeper_row"><th align="left" valign="top" scope="row">Timekeeper:</th><td><input type="input" name="timekeeperInput" id="timekeeperInput" class="billing billing_form" value="" style="width:310px" /></td></tr><tr id="description_row"><th align="left" valign="top" scope="row">Description:</th><td><textarea type="input" name="descriptionInput" id="descriptionInput" class="billing billing_form" value="" style="width:310px" rows="5"></textarea></td></tr></table></form></div>';
		
		
		/*
		$("#modal_type").val("billing");
		$("#modal_save_holder").html('<a title="Save" class="billing save" id="save_billing_modal" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
		$("#modal_save_holder").show();
		
		$(".modal-header").css("background-image", "url('img/glass_edit_header_check.png')");
		$("#myModalBody").css("background-image", "url('img/glass_edit_header_check.png')");
		$(".modal-footer").css("background-image", "url('img/glass_edit_header_check.png')");
		$(".modal-body").css("overflow-x", "hidden");
		
		$("#myModal4 .modal-dialog").css("width", "460px");
		$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_check.png')");
		$(".modal-content").css("background-image", "url('img/glass_edit_header_check.png')");
		$("#myModalBody").html(billing_html);
		$("#myModalLabel").html("Bill this");
		$("#timekeeperInput").tokenInput("api/user");
		$(".token-input-list").css("width", "310px");
		$("#myModal4").modal("toggle");
		setTimeout(function() {
			//$('.modal-dialog').animate({width:460, marginLeft:"-250px"}, 700, 'easeInSine');
			$("#billing_holder").fadeIn();
			$('#billing_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
			$("#modal_save_holder").fadeOut();
			
		}, 400);
		//"<div style='color:white'>Billing form here</div>"
		*/
	},
	billingSingle: function(event) {
		//alert("Hey, I'm under construction.");
		//return;
		if (customer_id == "1033") {
			event.preventDefault();
			var element = event.currentTarget;
			var elementArray = element.id.split("_");
			//alert(elementArray[2]);
			
			var action_id = elementArray[1];
			
			var billing_html = '<div id="billing_holder" style="padding-right:15px"><form id="billing_form" parsley-validate><input type="button" class="btn btn-xs" style="float:right; cursor:pointer; margin-right:-10px" id="save_billing_modal" value="Save" /><input id="table_name" name="table_name" type="hidden" value="billing" /><input id="table_id" name="table_id" type="hidden" value="" /><input id="table_uuid" name="table_uuid" type="hidden" value="" /><input id="billing_id" name="billing_id" type="hidden" value="" /><input id="case_id" name="case_id" type="hidden" value="" /><input id="action_id" name="action_id" type="hidden" class="billing billing_form" value="" /><input id="action_type" name="action_type" type="hidden" class="billing billing_form" value="document" /><table width="600px" border="0" align="left" cellpadding="3" cellspacing="0" style="display:" id="billing_table"><tr id="date_row"><th align="left" valign="top" scope="row" width="100px">Date:</th><td colspan="2" valign="top"><input value="" name="billing_dateInput" id="billing_dateInput" class="billing billing_form" placeholder="" style="width:165px" parsley-error-message="Req" required /><span style="margin-left:15px"><strong>Duration(min):</strong></span>&nbsp;&nbsp;<span><input value="" name="durationInput" id="durationInput" class="billing billing_form" placeholder="" style="width:38px" onkeypress="return event.charCode >= 48 && event.charCode <= 57" /></span></td></tr><tr id="status_row"><th align="left" valign="top" scope="row">Status:</th><td><select name="statusInput" id="statusInput" class="billing billing_form" style="width:310px"><option value="">Select Status..</option><option value="regular_billable">Regular Billable</option><option value="special_billable">Special Billable</option><option value="business_development">Business Development</option><option value="professional_development">Professional Development</option></select></td></tr><tr id="billing_rate_row"><th align="left" valign="top" scope="row">Billing Rate:</th><td><select name="billing_rateInput" id="billing_rateInput" class="billing billing_form" style="width:310px"><option value="">Select Rate..</option><option value="contingency">Contingency</option><option value="discount">Discount</option><option value="normal">Normal</option><option value="premium">Premium</option><option value="fixed_fee">Fixed Fee</option><option value="flat_rate_activity">Flat Rate Activity</option><option value="no_charge">No Charge</option></select></td></tr><tr id="activity_code_row"><th align="left" valign="top" scope="row">Activity Code:</th><td><select name="activity_codeInput" id="activity_codeInput" class="billing billing_form" style="width:310px"><option selected="selected" value="not_specified">Not Specified</option><option value="398087da-44a7-418b-9deb-f39890a2cea9">Consultation with</option><option value="d971bae8-1a3e-471f-b19c-3124db828a7d">Correspondence with</option><option value="9ae7291c-82d0-4fc9-8d56-7fa9abf218d5">Discussion with</option><option value="d18e6b09-a046-46d5-8f5a-2196c1cc0b78">Drafting documents</option><option value="de83a2e2-92f5-4b03-a03f-86ddea0b33dc">Filing Documentation</option><option value="909b7286-fa44-4cca-9f7c-98405c388927">Lunch with</option><option value="bdc32d74-f284-4b8d-9acc-bd82a12461cb">Meeting with</option><option value="60ed1acd-80a6-4ece-ac8f-ed9ea910303b">Negotiations</option><option value="b55efef8-fc31-4149-b9ce-dd6363c39561">Prepare opinion</option><option value="ad262a84-35db-4c8d-a990-2dff9dd47d51">Reporting</option><option value="3133bec0-c0ed-4591-b671-2a6951269a1f">Research</option><option value="4ffd5629-2c25-42c8-8902-98ba768575c3">Reviewing</option><option value="692f128a-6e32-47e0-8558-dcdb47ffe05a">Reviewing Documents</option><option value="1a13cbae-87ad-43a7-8771-8d6f0db57fa7">Telephone Conference with</option><option value="c2375f26-2a45-490c-bad9-860ff04eff5a">Motions</option><option value="77f9a56b-c282-4f8a-8521-7f864d23b1aa">Interview witness</option><option value="ac86a19b-ea3f-475c-95b9-3a2123fde20c">Consultations with expert witness</option><option value="21681748-fe81-49ac-807c-00753442459a">Brief witness</option><option value="74fb9200-e2f5-4291-b9b0-d3453dd97543">Discovery preparations</option><option value="e597434d-aa25-4b0d-a005-f3054a00359b">Attend discovery</option><option value="4d372c88-6540-4af3-ac87-d3320dd96d14">Trial preparations</option><option value="abf10a6f-40ca-4a11-a90c-f71b87998b30">Attend Trial</option><option value="8fa4e8c7-3c6d-4978-8118-6cc045e5e87c">Taxation advice</option><option value="e00753f3-d782-44e7-8195-da0cff9e1fa9">Telephone - exchange of voice mail</option><option value="cbf992ea-6f4d-4510-9472-6c7952faa32c">Telephone conference with client</option><option value="479205f3-3b4e-4184-ba4b-416081435a1c">Telephone conference with other side</option><option value="6f9d8e59-08a2-4462-a726-7a6e1f104d64">Incorporate company</option><option value="8ee8d265-4398-47d0-a710-5d67b1e2de89">Instructing research assistant</option><option value="e39550bb-2ccd-4c45-a99a-974f71af290e">On-line research</option><option value="1f294c00-1e79-43a1-895b-2e52d549f34e">Reviewing case-law</option><option value="b1714f44-34fb-413f-8896-822fa3feec4c">Plan and prepare for</option><option value="8071a61a-d165-4693-ae41-f28c8b4f13ff">Research</option><option value="df6b850e-e413-4a60-a49d-bbdc04ed5cdf">Draft/revise</option><option value="f1c081b8-622d-4b55-a9cf-3078a9c42920">Review/analyze</option><option value="59b39650-512d-4651-a22a-30a1ad50dc16">Communicate (in firm)</option><option value="7663f710-bf40-4206-bed3-5abe3a827a24">Communicate (with client)</option><option value="b696dd9d-fbb2-4c80-b23d-e7b7160ef10e">Communicate (other outside counsel)</option><option value="5ef5a862-7d12-4673-9771-2f97d10439c2">Communicate (other external)</option><option value="3912cf8c-e224-4284-aede-2c59f44eb771">Appear for/attend</option><option value="2b9b471a-5b14-4667-a3b1-11247773a044">Manage data/files</option><option value="de2f61c7-7148-4e82-9378-821cdff90d08">Billable Travel Time</option><option value="e3b5f1af-ceb1-45d2-b2bb-7d7e62231804">Medical Record and Medical Bill Management</option><option value="30f6b6e1-1e9e-43c2-ad44-199117f50221">Training</option><option value="002d905b-7a41-4312-b07d-6d606453ac83">Special Handling Copying/Scanning/Imaging (Internal)</option><option value="84c8facc-cc75-44d2-bd4c-61be744d2be3">Collection-Forensic</option><option value="bd9b4a01-6b95-443b-bfaa-daf776d824d8">Culling &amp; Filtering</option><option value="f8bfd31f-3461-4a52-919b-2fd7d1964a0f">Processing</option><option value="ff6b8f77-c897-4bf8-91b1-cd552bf6449d">Review and Analysis</option><option value="4130d181-fa13-4afd-a3b1-feac770f36b9">Quality Assurance and Control</option><option value="46c58d7d-cab8-4800-a5ed-483d4b31341b">Search Creation and Execution</option><option value="c7c4652d-f737-4571-b0b2-6a0393dde358">Privilege Review Culling and Log Creation</option><option value="37e931e3-2384-49c4-8cbc-20dcbbb6b65d">Document Production Creation and Preparation</option><option value="ab84e885-1c17-4b4c-ad49-1fef8ed953b8">Evidence/Exhibit Creation and Preparation</option><option value="bfa1eeb1-dbb0-41d9-857a-0eea12972d1a">Project Management</option><option value="3dda6652-f5e6-43fb-bac3-abefd4bab11c">Collection Closing Activities</option></select></td></tr><tr id="timekeeper_row"><th align="left" valign="top" scope="row">Timekeeper:</th><td><input type="input" name="timekeeperInput" id="timekeeperInput" class="billing billing_form" value="" style="width:310px" /></td></tr><tr id="description_row"><th align="left" valign="top" scope="row">Description:</th><td><textarea type="input" name="descriptionInput" id="descriptionInput" class="billing billing_form" value="" style="width:310px" rows="5"></textarea></td></tr></table></form></div>';
			
			var bill = new BillingMain({ case_id: current_case_id, action_id: action_id, action_type: "document"});
			bill.fetch({
				success: function(bill) {
					bill = bill.toJSON();
					bill.billing_date = moment(bill.billing_date).format("MM/DD/YYYY hh:mma");
					$("#billing_dateInput").val(bill.billing_date);
					//return;
					$("#durationInput").val(bill.duration);
					$("#billing_form #statusInput").val(bill.billing_status);
					$("#billing_rateInput").val(bill.billing_rate);
					$("#activity_codeInput").val(bill.activity_code);
					$("#billing_form #descriptionInput").val(bill.description);
					$("#billing_id").val(bill.billing_id);
					$("#billing_form #table_id").val(bill.billing_id);
					$("#timekeeperInput").tokenInput("api/user");
					$(".token-input-list").css("width", "310px");
					if (bill.billing_id != "") {
						//setTimeout(function() {
							$("#timekeeperInput").tokenInput("add", {id: bill.timekeeper, name: bill.user_name});
							$(".token-input-list").css("width", "310px");
						//}, 2000);
					}
				}
			});
				
			$("#modal_type").val("billing");
			$("#modal_save_holder").html('<a title="Save" class="billing save" id="save_billing_modal" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
			$("#modal_save_holder").show();
			
			$(".modal-header").css("background-image", "url('img/glass_edit_header_check.png')");
			$("#myModalBody").css("background-image", "url('img/glass_edit_header_check.png')");
			$(".modal-footer").css("background-image", "url('img/glass_edit_header_check.png')");
			$(".modal-body").css("overflow-x", "hidden");
			
			$("#myModal4 .modal-dialog").css("width", "460px");
			$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_check.png')");
			$(".modal-content").css("background-image", "url('img/glass_edit_header_check.png')");
			$("#myModalBody").html(billing_html);
			$("#myModalLabel").html("Bill this");
			//$("#timekeeperInput").tokenInput("api/user");
			$(".token-input-list").css("width", "310px");
			$("#myModal4").modal("toggle");
			setTimeout(function() {
				//$('.modal-dialog').animate({width:460, marginLeft:"-250px"}, 700, 'easeInSine');
				$("#billing_holder").fadeIn();
				$('#billing_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
				$("#modal_save_holder").fadeOut();
				
			}, 400);
		}
	},
	addBill: function(event) {
		var self = this;
		var element = event.currentTarget;
		
		if (customer_id == 1033) {
			
			var billing_date = $("#billing_dateInput").val();
			var duration = $("#durationInput").val();
			var status = $("#billing_form #statusInput").val();
			var billing_rate = $("#billing_rateInput").val();
			var activity_code = $("#activity_codeInput").val();
			var timekeeper = $("#timekeeperInput").val();
			var description = $("#billing_form #descriptionInput").val();
			var table_id = $("#billing_form #table_id").val();
			var action_id = $("#action_id").val();
			var action_type = $("#action_type").val();
				
			var formValues = "case_id=" + current_case_id + "&billing_date=" + billing_date + "&table_id=" + table_id + "&status=" + status;
				formValues += "&duration=" + duration + "&billing_rate=" + billing_rate + "&activity_code=" + activity_code + "&timekeeper=" + timekeeper + "&description=" + description + "&action_id=" + action_id + "&action_type=" + action_type;
			
			var modal_bg = $(".modal-dialog").css('background-image');
			modal_bg = modal_bg.replace('"', "'");
			modal_bg = modal_bg.replace('"', "'");
			//console.log(modal_bg);
			//alert(modal_bg);
			//return;
			$.ajax({
			  method: "POST",
			  url: "api/billing/add",
			  dataType:"json",
			  data: formValues,
			  success:function (data) {
				  if(data.error) {  // If there is an error, show the error tasks
					  alert("error");
				  } else {
					  $("#myModalBody").css('background', "#0C3");
					  //rgb(255, 255, 255)
					  setTimeout(function() {
						  $("#myModalBody").css('background-color', '');
						  $("#myModalBody").css('background-image', modal_bg);
						  setTimeout(function() {
							  //self.displayEvent();
						  }, 700);
					  }, 2000);
				  }
			  }
			});
		}
	},
	changeNoteLabel: function(event) {
		return;
		//obsolete below
		
		var element = event.currentTarget;
		var element_id = element.id;
		var arrID = element_id.split("_");
		var theid = arrID[2];
		
		if (element.checked) {
			$("#document_note_label_" + theid).html("Record Type");
		} else {
			$("#document_note_label_" + theid).html("Note");
		}
	},
	doTimeouts: function(event) {
		var self = this;
		
		$('#kase_loading').html("");
		$('#ikase_loading').html("");
		$("#document_list_content").show();
		//console.log(user_data_path);
		if (user_data_path == "A1" || user_data_path == "perfect" || user_data_path == "tritek" || user_data_path == "ecand") {
			//let's get the archive
			if (user_data_path == "A1" || user_data_path == "perfect" || user_data_path == "tritek") {
				if (user_data_path == "A1" || user_data_path == "perfect") {
					var kase_archives = new LegacyArchiveCollection([], { case_id: current_case_id });
					
					//specific to dholakia, a1 archives on separate folders
					if (customer_id == 1117) {
						var a1_folders = new LegacyA1Folders({case_id: current_case_id});
						
						a1_folders.fetch({
							success: function(data) {
								//could be empty
								var length = data.length;
								if (length==1) {
									if (data.toJSON()[0].name=="") {
										length = 0;
									}
								}
								if (length>0) {
									$("#a1_archive_count").html("(" + length + ")");		
									$("#archive_a1_legacy_holder").show();					
								} else {
									$("#archive_a1_legacy_holder").hide();
								}
							}
						});
					}
					
				} else {
					var kase_archives = new ArchiveCollection([], { case_id: current_case_id });
				}
			}
			if (user_data_path=="ecand") {
				var kase_archives = new EcandArchiveCollection([], { case_id: current_case_id });
			}
			kase_archives.fetch({
				success: function(data) {
					if (data.length>0) {
						if (data.toJSON()[0].document_name!="" || data.toJSON()[0].path!="" ) {
							$("#archive_count").html("(" + data.length + ")");
						}
					} else {
						$("#archive_legacy_holder").hide();
					}
				}
			});
		}
		var kase_documents = this.collection.toJSON();
		_.each( kase_documents, function(kase_document) {
			
			var document_type = $("#document_type_" + kase_document.id);
			if (document_type.html().indexOf(kase_document.type) < 0) {
				document_type.val("");
			} else {
				document_type.val(kase_document.type);
			}
			
			var document_category = $("#document_category_" + kase_document.id);
			if (document_category.html().indexOf(kase_document.document_extension) < 0) {
				document_category.val("");
			} else {
				document_category.val(kase_document.document_extension);
			}
			
			var document_subcategory = $("#document_subcategory_" + kase_document.id);
			if (document_subcategory.html().indexOf(kase_document.description) < 0) {
				document_subcategory.val("");
			} else {
				document_subcategory.val(kase_document.description);
			}
		});
		
		$("#document_listing_holder").css("height", (window.innerHeight - 375) + "px");
		$("#document_preview_holder").css("height", (window.innerHeight - 375) + "px");
		
		$("#document_listing th").css("font-size", "1.2em");
		$("#document_listing td").css("font-size", "1.1em");
		
		self.model.set("hide_upload", true);
		showKaseAbstract(self.model);
		
		var kase_dois = new KaseInjuryCollection({case_id: current_case_id});
		kase_dois.fetch({
			success: function(kase_dois) {
				var dois = kase_dois.toJSON();
				
				var arrID = [];
				var arrOptions = [];
				var blnSelected = false;
				_.each( dois, function(doi) {
					if (arrID.indexOf(doi.injury_id) < 0) {
						arrID.push(doi.injury_id);
						var doi_date = moment(doi.start_date).format("MM/DD/YYYY");
						if (doi.end_date!="0000-00-00") {
							doi_date += "-" +  moment(doi.end_date).format("MM/DD/YYYY") + " CT";
						}
						var selected = "";
						
						var option = "<option value='" + doi.injury_id + "'" + selected + ">" + doi_date + "</option>";
						arrOptions.push(option);	
					}
				});
				
				if (arrOptions.length > 1) {
					var selected = "";
					if (!blnSelected) {
						selected = " selected";
					}
					var option = "<option value='' " + selected + ">Select DOI from List - optional</option>";
					arrOptions.unshift(option);
					
					$(".doi_id").html(arrOptions.join(""));
					
					self.setDOI()
				}
			}
		});
	},
	setDOI: function() {
			var kase_documents = this.collection.toJSON();
			_.each( kase_documents, function(kase_document) {			
				if (kase_document.doi_id!="") {
					//set it
					$("#doi_id_" + kase_document.id).val(kase_document.doi_id);
				}
			});
	},
	openDocumentWindow: function(event) {
		var element = event.currentTarget;
		var element_id = element.id.replace("window_thumbnail_", "");
		
		var url = $("#preview_document_" + element_id).val();
		
		window.open(url);
	},
	previewDocument: function(event) {
		var list_links = $(".list_link");
		var arrLength = list_links.length;
		for(var i = 0; i < arrLength; i++) {
			var thelink_id = list_links[i].id;
			$("#" + thelink_id).children()[0].style.border = "none";
		}
		var element = event.currentTarget;
		var element_id = element.id;
		$("#" + element_id).children()[0].style.border = "4px solid #00fff3";
		element_id = element.id.replace("thumbnail_", "");
		
		if ($("#preview_document_" + element_id).val().indexOf("showCloud") > -1) {
			//it's a cloud docu archive
			var path = $("#document_filename_" + element_id).val();
			showCloudArchive(path);
			return;
		}
		/*	
		if (customer_id != 1121) {
			openDocumentPreviewPanel(element_id);
			return;
		} else {
		*/
			$("#document_preview_holder").attr("src", $("#preview_document_" + element_id).val());
			
			if ($("#document_right_pane").css("display")!="block") {
				$("#document_listing_holder").css("width", "1115px");
				$("#document_preview_holder").css("width", (window.innerWidth - 1165) + "px");
				$("#document_preview_holder").css("height", (window.innerHeight - 445) + "px");
				$("#document_right_pane").fadeIn();
			}
			
			$("#window_link_" + element_id).fadeIn();
		//}
	},
	unVivify: function(event) {
		var textbox = $("#document_searchList");
		var label = $("#label_search_docs");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#document_searchList");
		var label = $("#label_search_docs");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	findIt: function(event) {
		var element = event.currentTarget;
		
		if (customer_id != 1104) {
			findIt(element, 'document_listing', 'kase_document');
		} else {
			var self = this;
			clearTimeout(search_document_id);
			search_document_id = setTimeout(function() {
				self.scheduledSearchDocument(element.value);
			}, 1000);
		}
	},
	scheduledSearchDocument: function(search_term) {
		var self = this;

		var kase_documents = new DocumentCaseSearch([], { case_id: current_case_id, search_term: search_term });
		kase_documents.fetch({
			success: function(data) {
				$('#kase_content').html(new document_listing_view({collection: data, model: self.model}).render().el);
			}
		});
	},
	selectAllDocuments:function(event) {
		//event.preventDefault();
		var element = event.currentTarget;
		var blnChecked = element.checked;
		
		var select_docs = $(".check_thisone");
		var arrLength = select_docs.length;
		for(var i = 0; i < arrLength; i++) {
			var select_doc = select_docs[i];
			var select_id = select_doc.id;
			
			document.getElementById(select_id).checked = blnChecked;
		}
		
		this.enableSendDocs();
	},
	enableSendDocs: function() {
		var select_docs = $(".check_thisone");
		var arrLength = select_docs.length;
		var blnChecked = false;
		for(var i = 0; i < arrLength; i++) {
			if (select_docs[i].checked) {
				blnChecked = true;
				break;
			}
		}
		
		if (blnChecked) {
			document.getElementById("send_documents").disabled = false;
		} else {
			document.getElementById("send_documents").disabled = true;
		}
	},
	filterDocs: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		if (element.value!="new_filter") {
			filterIt(element, "document_listing", "kase_document");
		}
	},
	checkManageCategoriesFilter: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		
		if (element.value=="new_filter") {
			composeEditDocumentTypes("categories");
		}
	},
	checkManageSubCategoriesFilter: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		
		if (element.value=="new_filter") {
			composeEditDocumentTypes("subcategories");
		}
	},
	checkManageTypeFilter: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		
		if (element.value=="new_filter") {
			composeEditDocumentTypes("types");
		}
	},
	documentUpload: function(event) {
		document.location = "#upload/" + this.model.id + "/kase";
	},
	releaseSave: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		
		//get the current id
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		$("#disabled_save_" + theid).fadeOut(function() {
			$("#document_save_" + theid).fadeIn();
		});
	},
	sendDocument: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		
		//get the current id
		//var arrID = current_input.id.split("_");
		//var theid = arrID[arrID.length - 1];
		
		composeMessage(current_input.id);
	},
	saveDocument: function(event) {
		//we're making changes, clear the cache
		resetCurrentContent("document");
		
		var current_input = event.currentTarget;
		event.preventDefault();
		
		//get the current id
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		var name = $("#document_name_" + theid).val();
		var source = $("#document_source_" + theid).val();
		var received_date = $("#document_received_" + theid).val();
		
		//show the two drop downs and the check box
		var type = $("#document_type_" + theid).val();
		var category = $("#document_category_" + theid).val();
		var subcategory = $("#document_subcategory_" + theid).val();
		var note = $("#document_note_" + theid).val();
		var document_id = $("#document_id_" + theid).val();
		
		doi_id = "";
		if (document.getElementById("doi_id_" + theid)!=null) {
			if ($("#doi_id_" + theid).val()!="") {
				doi_id = $("#doi_id_" + theid).val();
			}
		}
		
		var formValues = { 
			document_id: document_id,
			document_name: name, 
			source: source, 
			received_date: received_date, 
			type: type, 
			document_extension: category, 
			description: subcategory, 
			description_html: note,
			doi_id: doi_id
		};
		var url = "api/documents/categorize";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					//hide the save button
					//$("#document_save_" + theid).hide();
					$("#document_save_" + theid).fadeOut(function() {
						$("#disabled_save_" + theid).fadeIn();
					});
					//get the color
					var back_color = $(".kase_document_row_" + theid).css("background");
					//mark it all green
					$(".kase_document_row_" + theid).css("background", "green");
					setTimeout(function() {
						//hide the processed row, no longer a batch scan
						//$(".document_row_" + theid).fadeOut();
						$(".kase_document_row_" + theid).css("background", back_color);
					}, 2500);
					
					//med index
					if (document.getElementById("document_medindex_" + theid).checked) {
						
						var doc = new Document({id: theid});
						doc.fetch({
							success:function (data) {
								var json = data.toJSON();
								
								//bring up new medindex
								var corp_id = "-1";
								var object_id;
								
								if (current_case_id == -1) {
									current_case_id = document.location.hash.split("/")[1];
								}
								//make sure the kase is in kases
								var kase = kases.findWhere({case_id: current_case_id});
								if (typeof kase == "undefined") {
									var kase =  new Kase({id: current_case_id});
									kase.fetch({
										success: function (kase) {
											kases.add(kase);
											
											composeExam(object_id, corp_id, current_case_id, theid, json);
										}
									});
									return;
								}
								//we're good
								composeExam(object_id, corp_id, current_case_id, theid, json);
							}
						});
					}
				}
			}
		});
	},
	confirmdeleteDocument: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "document");
		
	},
	canceldeleteDocument: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteDocument: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "document");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteDocument(event);
			$(".kase_document_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				//$(".document_row_" + theid).fadeOut();
				$(".kase_document_row_" + id).fadeOut();
			}, 2500);
		}
	},
	prepDownload: function(event) {
		var self = this;
		var element = event.currentTarget;
		event.preventDefault();
		//do we have the case id in the name? we need it
		var document_id = element.id.split("_")[1];
		var document_filename = $("#document_filename_" + document_id).val();
		var strpos = document_filename.indexOf("_" + current_case_id + "_");
		if (strpos < 0) {
			//we need to rename the file so that edits can be made
			var url = "api/documents/download";
			var formValues = "case_id=" + current_case_id + "&document_id=" + document_id;
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						console.log(data.error.text);
					} else { 
						$("#document_filename_" + document_id).val(data.destination);
						self.downloadDocument(document_id, data.destination);
						
						//change the thumbnail href
						var arrHref = $("#thumbnail_" + document_id).prop("href").split("/");
						arrHref[arrHref.length - 1] = data.destination;
						var href = arrHref.join("/");
						$("thumbnail_" + document_id).prop("href", href);
					}
				}
			});
		} else {
			//start the download right away
			self.downloadDocument(document_id, document_filename);
		}
	},
	downloadDocument: function(document_id, document_filename) {
		var url = "api/download.php?file=uploads/" + customer_id + "/" + current_case_id + "/" + document_filename.replace("#", "%23");
		
		window.open(url);
	},
	clearSearch: function() {
		$("#document_searchList").val("");
		$( "#document_searchList" ).trigger( "keyup" );
		
		$(".filter_select").val();
		$( "#typeFilter" ).trigger( "click" );
	}
});

window.document_listing_view_mobile = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
    },
    events:{
  		"click .save_icon":							"saveDocument",
		"click #document_upload":					"documentUpload",
		"click .delete_icon":						"confirmdeleteDocument",
		"click .delete_yes":						"deleteDocument",
		"click .delete_no":							"canceldeleteDocument",
		"change .document_input":					"releaseSave",
		"keyup .document_input":					"releaseSave",
		"click .send_icon":							"sendDocument",
		"change .filter_select":					"filterDocs",
		"keyup #document_searchList":				"findIt",
		"click #document_clear_search":				"clearSearch",
		"click #document_listing_all_done_mobile":	"doTimeouts",
		"click #label_search_messages":				"Vivify",
		"click #document_searchList":				"Vivify",
		"focus #document_searchList":				"Vivify",
		"blur #document_searchList":				"unVivify"
	},
	unVivify: function(event) {
		var textbox = $("#document_searchList");
		var label = $("#label_search_docs");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#document_searchList");
		var label = $("#label_search_docs");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
    render:function () {		
		var self = this;
		
		//this.collection.bind("reset", this.render, this);
		var kase_documents = this.collection.toJSON();
		_.each( kase_documents, function(kase_document) {
			if (kase_document.received_date == "00/00/0000 12:00AM" || kase_document.received_date == "12/31/1969 4:00PM") {
				kase_document.received_date = "";
			}
			
			//docx
			if (kase_document.document_extension=="docx") {
				if (kase_document.document_filename.indexOf(".docx") < 0) {
					kase_document.document_filename += ".docx";
				}
			}
			if (kase_document.document_filename.indexOf("D:/uploads/") > -1) {
				kase_document.document_filename = kase_document.document_filename.replaceAll("D:/uploads/" + customer_id + "/" + kase_document.case_id + "/", "");
				kase_document.document_filename = kase_document.document_filename.replace("../", "");
			}
			
			var href = "D:/uploads/" + customer_id + "/" + kase_document.case_id + "/" + kase_document.document_filename.replace("#", "%23");
			if (kase_document.type == "eams_form") {
				href = "D:/uploads/" + customer_id + "/" + kase_document.case_id + "/eams_forms/" + kase_document.document_filename.replace("#", "%23");
			}
			if (!isNaN(kase_document.thumbnail_folder) && kase_document.document_extension!="docx") {
				href = "D:/uploads/" + customer_id + "/imports/" + kase_document.document_filename;
			}
			kase_document.href = href;
			
			if (kase_document.user_name!="") {
				if (typeof kase_document.last_user_attributes == "undefined") {
					kase_document.user_name = "<br /><br />By: " + kase_document.user_name;
				} else {
					var arrDocUsers = kase_document.user_name.split("|");
					var arrDocUserAtts = kase_document.last_user_attributes.split("|");
					var arrLength = arrDocUsers.length;
					var arrDocumentCredits = [];
					for(var i =0; i < arrLength; i++) {
						var the_user_name = arrDocUsers[i];
						var the_attribute = arrDocUserAtts[i];
						
						var the_user_attribute = arrDocUserAtts[i].replaceAll("_", " ");
						arrDocumentCredits.push(the_user_attribute + " by " + the_user_name);
					}
					if (arrDocumentCredits.length > 0) {
						kase_document.user_name = "<br /><br />By: " + arrDocumentCredits.join("<br>");
					}
				}
			}
			var the_type = kase_document.type;
			if (kase_document.source!="") {
				the_type = kase_document.source;
			}
			kase_document.preview = documentThumbnail(kase_document.document_filename, kase_document.customer_id, kase_document.thumbnail_folder, kase_document.case_id, the_type, kase_document.document_date, kase_document.parent_document_uuid);
			if (kase_document.preview!="") {
				// kase_document.preview = '<img src="' + kase_document.preview + '" width="58" height="75" onmouseover="documentPreview(event, \'' + kase_document.document_filename + '\', ' + kase_document.customer_id + ', \'' + kase_document.thumbnail_folder + '\', \'' + kase_document.source + '\', \'' + kase_document.document_date + '\', \'' + kase_document.parent_document_uuid + '\')" onmouseout="hidePreview()" />';
				kase_document.preview = '<img src="https://v4.ikase.org/document_read.php?file=' + kase_document.preview + '" width="58" height="75" onmouseover="documentPreview(event, \'' + kase_document.document_filename + '\', ' + kase_document.customer_id + ', \'' + kase_document.thumbnail_folder + '\', \'' + kase_document.source + '\', \'' + kase_document.document_date + '\', \'' + kase_document.parent_document_uuid + '\')" onmouseout="hidePreview()" />';
			} 
			
		});
		
		$(this.el).html(this.template({kase_documents: kase_documents, kase: this.model.toJSON()}));
		
		setTimeout(function() {
			var document_form_title = "Kase Documents";
			if (self.model.get("uuid")=="templates") {
				//change the title
				document_form_title = "Word Templates";
			}
			$("#document_form_title").html(document_form_title);
			self.model.set({table_name: "kase"});
			$('#upload_documents').html(new document_upload_view({model: self.model}).render().el);
			
			$('.document_listing .date_input').datetimepicker({ 
				validateOnBlur:false, 
				minDate: 0, 
				defaultTime:'8:00',  
				step:30,
				onChangeDateTime: function(current_time,$input) {
					//get the current id
					var arrID = $input.prop("id").split("_");
					var theid = arrID[arrID.length - 1];
					
					$("#disabled_save_" + theid).fadeOut(function() {
						$("#document_save_" + theid).fadeIn();
					});
				}
			});
		}, 100);
		
		tableSortIt("document_listing");
		
		return this;
    },
	doTimeouts: function(event) {
		$('#kase_loading').html("");
		$('#ikase_loading').html("");
		$("#document_list_content").show();
		
		//let's get the archive
		if (user_data_path == "A1") {
			var kase_archives = new LegacyArchiveCollection([], { case_id: current_case_id });
		} else {
			var kase_archives = new ArchiveCollection([], { case_id: current_case_id });
		}
		kase_archives.fetch({
			success: function(data) {
				if (data.length>0) {
					if (data.toJSON()[0].document_name!="" || data.toJSON()[0].path!="" ) {
						$("#archive_count").html("(" + data.length + ")");
					}
				}
			}
		});
	},
	findIt: function(event) {
		var element = event.currentTarget;
		findIt(element, 'document_listing', 'kase_document');
	},
	filterDocs: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		filterIt(element, "document_listing", "kase_document");
	},
	documentUpload: function(event) {
		document.location = "#upload/" + this.model.id + "/kase";
	},
	releaseSave: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		
		//get the current id
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		$("#disabled_save_" + theid).fadeOut(function() {
			$("#document_save_" + theid).fadeIn();
		});
	},
	sendDocument: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		
		//get the current id
		//var arrID = current_input.id.split("_");
		//var theid = arrID[arrID.length - 1];
		
		composeMessage(current_input.id);
	},
	saveDocument: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		
		//get the current id
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		var name = $("#document_name_" + theid).val();
		var source = $("#document_source_" + theid).val();
		var received_date = $("#document_received_" + theid).val();
		
		//show the two drop downs and the check box
		var type = $("#document_type_" + theid).val();
		var category = $("#document_category_" + theid).val();
		var subcategory = $("#document_subcategory_" + theid).val();
		var note = $("#document_note_" + theid).val();
		var document_id = $("#document_id_" + theid).val();
		var formValues = { 
			document_id: document_id,
			document_name: name, 
			source: source, 
			received_date: received_date, 
			type: type, 
			document_extension: category, 
			description: subcategory, 
			description_html: note
		};
		var url = "api/documents/categorize";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					//hide the save button
					//$("#document_save_" + theid).hide();
					$("#document_save_" + theid).fadeOut(function() {
						$("#disabled_save_" + theid).fadeIn();
					});
					//get the color
					var back_color = $(".kase_document_row_" + theid).css("background");
					//mark it all green
					$(".kase_document_row_" + theid).css("background", "green");
					setTimeout(function() {
						//hide the processed row, no longer a batch scan
						//$(".document_row_" + theid).fadeOut();
						$(".kase_document_row_" + theid).css("background", back_color);
					}, 2500);
				}
			}
		});
	},
	confirmdeleteDocument: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "document");
		
	},
	canceldeleteDocument: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteDocument: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "document");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteDocument(event);
			$(".kase_document_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				//$(".document_row_" + theid).fadeOut();
				$(".kase_document_row_" + id).fadeOut();
			}, 2500);
		}
	},
	clearSearch: function() {
		$("#document_searchList").val("");
		$( "#document_searchList" ).trigger( "keyup" );
		
		$(".filter_select").val();
		$( "#typeFilter" ).trigger( "click" );
	}
});

window.document_filters_listing = Backbone.View.extend({
	initialize:function () {
    },
	events: {
		"click #select_all_filters":		"selectAll"
	},
    render:function () {		
		var self = this;
		
		//what are we looking for?
		
		//let's cycle through the types
		var mymodel = this.model.toJSON();

		var filter_type = mymodel.filter_type;
		var document_filters = JSON.parse(mymodel.document_filters);
		switch(filter_type) {
			case "types":
				var arrFilters = document_filters.types;
				break;
			case "categories":
				var arrFilters = document_filters.categories;
				break;
			case "subcategories":
				var arrFilters = document_filters.subcategories;
				break;
		}
		var arrRows = [];
		arrFilters.forEach(function(element, index, array) {
			var checked = " checked";
			var row_display = "";
			var row_class = "active_filter";
			if (element.indexOf("|deleted") > -1) {
				checked = "";
				row_display = "none";
				row_class = "deleted_filter";
			}
			var therow = "<tr style='display:" + row_display + "' class='" + row_class + "'><td class='document_type'><input type='checkbox' class='document_filter_checkbox' value='Y' id='document_type_" + index + "' name='document_type_" + index + "'" + checked + "></td><td class='document_type' style='color:black'>" + element.replace("|deleted", "") + "</td></tr>";
			arrRows.push(therow);
		});
		var html = "<table id='document_filters_table'>" + arrRows.join("") + "</table>";
		try {
			$(this.el).html(this.template({html: html, filter_type: filter_type}));
		}
		catch(err) {
			var view = "document_filters_listing";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		return this;
	},
	selectAll:function(event) {
		var element = event.currentTarget;
		$(".document_filter_checkbox").prop("checked", element.checked);
	}
});

window.document_listing_message = Backbone.View.extend({
    initialize:function () {
    },
    render:function () {		
		var self = this;
		var kase_documents = this.collection.toJSON();
		$(this.el).html(this.template({kase_documents: kase_documents}));
		
		return this;
	},
    events:{
  		"click #message_documents":			"selectAll",
		"click .message_document":			"selectDocument"
	},
	selectDocument: function (event) {
		/*
		var element = event.currentTarget;
		var theid = element.id.split("_")[2];
		*/
		//go through the list, find the checked ones
		var checked_documents = "";
		var documents = $(".message_document");
		var array_length = documents.length;
		var arrDocuments = [];
		for(var i = 0; i < array_length; i++) {
			var message_document = documents[i];
			var id = message_document.id.split("_")[2];
			var check = document.getElementById("message_document_" + id);
			if (check.checked) {
				var message_document_name = document.getElementById("message_document_name_" + id).innerHTML;
				arrDocuments.push(message_document_name);
			}
		}
		//$("#documents_list").html(arrDocuments.join("<br />"));
	},
	selectAll:function(event) {
		var element = event.currentTarget;
		$(".message_document").prop("checked", element.checked);
	}
});
function kaseAutoComplete(form_name, obj_selector) {
	new AutoCompleteKaseView({
		input: $("." + form_name + " ." + obj_selector),
		form_name:form_name,
		model: kases,
		onSelect: function (model) {
			//clean display in the search box
		}
	}).render();
}
