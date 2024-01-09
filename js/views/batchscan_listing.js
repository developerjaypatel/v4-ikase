window.batchscan_listing_view = Backbone.View.extend({
    initialize:function () {
    },
    render:function () {		
		var self = this;
		var batchscans = this.collection.toJSON();
		_.each( batchscans, function(batchscan) {
			/*
			if (batchscan.id < 0) {
				this.collection.remove(batchscan.id);
			}
			*/
			batchscan.datestamp = "";
			if (batchscan.dateandtime!="") {
				batchscan.datestamp = moment(batchscan.dateandtime).format('MM/DD/YYYY h:mA')
			} 
			
			//preview
			batchscan.preview = "";
			if (batchscan.completion!="") {
				batchscan.preview = "<img id='thumbnail_" + batchscan.id + "' class='batchscan_thumbnail' src='uploads/" + customer_id + "/imports/" + batchscan.time_stamp + "/" + batchscan.completion + "' width='58' height='75' title='Click to review' style='cursor:pointer' />";
			}
		});
		
		try {
			$(this.el).html(this.template({batchscans: batchscans}));
		}
		catch(err) {
			var view = "batchscan_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		return this;
	},
    events:{
		"click .batchscan_file":				"previewBatchscan",
		"click .batchscan_thumbnail":			"previewThumbnail",
		"mouseover .batchscan_thumbnail":		"showLargeImage",
		"mouseout .batchscan_thumbnail":		"hideLargeImage"
	},
	hideLargeImage: function(event) {
		var form_name = "batchscan";
		$("#" + form_name + "_preview_panel").html("");
		$("#" + form_name + "_preview_panel").hide();
	},
	showLargeImage: function(event) {
		var element = event.currentTarget;
		if (element!=null) {
			var panel_html = element.outerHTML;
			panel_html = panel_html.replace('width="58"', 'width="auto"');
			panel_html = panel_html.replace('height="75"', 'height="auto"');
			var rect = element.getBoundingClientRect();
			
			var scrollTop = document.documentElement.scrollTop?
							document.documentElement.scrollTop:document.body.scrollTop;
			var scrollLeft = document.documentElement.scrollLeft?                   
							 document.documentElement.scrollLeft:document.body.scrollLeft;
			elementTop = rect.top+scrollTop - 40;
			//elementTop = scrollTop - 20;
			elementLeft = rect.left+scrollLeft - 10;
			if (elementLeft < 0) {
				elementLeft = 30;
			}
			elementLeft += 100;
			var form_name = "batchscan";
			$("#" + form_name + "_preview_panel").html(panel_html);
			$("#" + form_name + "_preview_panel").css({display: "", top: elementTop, left: elementLeft, position:'absolute'});
			$("#" + form_name + "_preview_panel").show();
		}
	},
	previewThumbnail: function(event) {
		var element = event.currentTarget;
		var element_id = element.id.replace("thumbnail", "batchscan");
		//trigger previewBatchscan
		$("#" + element_id).trigger("click");
	},
	previewBatchscan: function(event) {
		var element = event.currentTarget;
		$("#batchscan_frame").attr("src", "https://ikase.xyz/ikase/ikaseuploads/1033/" + element.innerHTML);
		$("#batchscan_frame").css("height", (window.innerHeight * .8) + "px");
		$("#batchscan_listing").css("width", "45%");
		$("#batchscan_review").fadeIn();
	}
});
window.batchscan_assigned_listing = Backbone.View.extend({
    initialize:function () {
    },
    render:function () {		
		var self = this;
		var activities = this.collection.toJSON();
		
		try {
			if (typeof this.template == "function") {
				 _.each( activities, function(activity) {
					 //console.log(activity.activity);
					 var partial = activity.activity.substr(22 + ("uploads/" + customer_id).length, activity.activity.length);
					 var arrPartial = partial.split("/");
					 var case_id = arrPartial[1];
					 var pdf_path = arrPartial[2];
					 //pdf_path = pdf_path.replace("' target='_blank'>", "");
					 pdf_path = pdf_path.substr(0, pdf_path.indexOf("' target="));
					 
					 //activity.case_id = case_id;
					 activity.path = "<a href='uploads/" + customer_id + "/" + case_id + "/" + pdf_path + "' target='_blank' class='white_text' style='text-decoration:underline'>" + pdf_path + "</a>";
					 
				 
					//let's get the by and any trailing info
					var strpos = activity.activity.indexOf(" by");
					
					if (strpos > -1) {
						var activity_header = activity.activity.substring(0, activity.activity.indexOf(" by"));
						var activity_footer = activity.activity.substring(activity.activity.indexOf(" by") + 3);
						activity_footer = activity_footer.replaceAll("<br />", "\r\n\r\n");
						var arrFooter = activity_footer.split("\r\n\r\n");
						activity_footer = arrFooter[0];
						if (arrFooter.length > 1) {
							arrFooter.splice(0, 1);
							activity_header += "<br><br>" + arrFooter.join("<br>"); 
						}
					} else {
						var activity_header = activity.activity;
						var activity_footer = "";
					}
					activity.activity = activity_header.replaceAll("\r\n", "<br>");
					activity.activity = activity.activity.replaceAll("class='edit_event", "class='white_text edit_event");
					activity.activity = activity.activity.replaceAll("- 00/00/0000", "");
					activity.activity = activity.activity.replaceAll("uploads/uploads/", "uploads/");
					activity.activity = activity.activity.replaceAll("style='cursor:pointer'>", "style='cursor:pointer; background:yellow;color:black'>")
					
					activity.by = activity_footer;
					
					if (activity.case_name!="") {
						activity.name = activity.case_name;
					}
					activity.case_number = "<a href='#kases/" + activity.case_id + "' class='white_text' style='text-decoration:underline'>" + activity.case_number + "</a>";
					
					if (typeof activity.notifieds == "undefined") {
						activity.notifieds = "";
					}
				 });
			}
			
			var mymodel = this.model.toJSON();
			var start_date = mymodel.start_date;
			if (start_date=="0000-00-00") {
				start_date = "";
			} else {
				start_date = moment(start_date).format("MM/DD/YYYY");
			}
			var end_date = mymodel.end_date;
			if (end_date=="0000-00-00") {
				end_date = "";
			} else {
				end_date = moment(end_date).format("MM/DD/YYYY");
			}
			
			$(this.el).html(this.template({activities: activities, start_date: start_date, end_date: end_date}));
		}
		catch(err) {
			var view = "batchscan_assigned_listing";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		setTimeout(function(){
			$('.range_dates').datetimepicker(
				{
					timepicker:false, 
					format:'m/d/Y',
					mask:false,
					onChangeDateTime:function(dp,$input){
						var start_date = $(".stack_activity #start_dateInput").val();
						var end_date = $(".stack_activity #end_dateInput").val();
						var d1 =  new Date(moment(start_date));
						var d2 =  new Date(moment(end_date));
						var diff = d2.getTime() - d1.getTime();
						if (diff < 0) {
							end_date = start_date;
							$(".stack_activity #end_dateInput").val(end_date);
						}
						if (end_date!="") {
							document.location.href = "#batchscans/dateassigned/" + moment(start_date).format("YYYY-MM-DD") + "/" + moment(end_date).format("YYYY-MM-DD");
						}
					}
				}
			);
		}, 1000);
		return this;
	}
});