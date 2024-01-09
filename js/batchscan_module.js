var current_pages;
var current_uploaded;
var current_date;
var current_batchscan_id;
var crop_counter = 0;
var blnCropping = false;

function openBatchScanPanel(batchscan_id) {
	var yoffset = Number($(".navbar").css("height").replace("px", "")) + 15;
	//var panel_height = window.innerHeight - yoffset - 20;
	var panel_height = 500;
	if (document.getElementById("batchscan_info_holder") == null) {
		$.jsPanel({
			id:			"batchscan_info_holder",
			title:    	"Batchscan Processing - " + batchscan_id,
			size:		{ width: "350", height: panel_height },
			
			selector: 	"#batchscan_feedback_holder",
			position: 	"bottom right",
			controls: {
				maximize: "disable"
			},
			content: 	"<div id='batchscan_feedback_text' style='margin-left:5px'></div>",
			theme:    	"primary"
		});
	} else {
		//make sure it's positioned correctly
		$("#batchscan_holder.jsPanel").css("top", "0px");
		$("#batchscan_holder.jsPanel").css("left", "0px");
		$("#batchscan_info_holder").fadeIn();
	}
	$("#batchscan_info_holder .jsPanel-content").css("background", "white");
	$("#batchscan_feedback_holder").fadeIn();
	$("#batchscan_feedback_text").html("<div style='float:right; margin-right:5px'><i class='glyphicon glyphicon-chevron-down' style'color:black; cursor:pointer' onclick='minimizeBatchscanPanel()' title='Click to minize this window'></i></div><div>Processing Scan ...</div>");
	/*
	display: block; top: 77px; left: 24px; opacity: 1; z-index: 102; position: absolute; width: 1004px; height: 689px;
	*/
	$("#batchscan_info_holder").css("top",  yoffset + "px");
	$("#batchscan_info_holder").css("left", "12px");
	$("#batchscan_info_holder").css("width", "350px");
	$("#batchscan_info_holder").css("height", panel_height + "px");
	$("#batchscan_info_holder .jsPanel-title").css("display", "none");
	$("#batchscan_info_holder .jsPanel-hdr").css("display", "none");
}
function currentBatchscanID(batchscan_id) {
	if (login_user_id != 2) {
		//alert("under repairs");
		//return;
	}
	current_batchscan_thumbnail = 0;
	if (batchscan_id!="") {
		if (!isNaN(Number(batchscan_id))) {
			//start the reading process
			current_pages = "";
			current_uploaded = "";
			current_date = "";
			current_batchscan_id = batchscan_id;
			blnStitching = false;
			//bring up a jspanel
			openBatchScanPanel(batchscan_id);
			var suffix = "_ikase";

			//03/7/2018
			suffix = "_edges";
			$("#batchscan_feedback_text").append("<div id='batchscan_staging'><iframe src='../readcode" + suffix + ".php?batchscan_id=" + batchscan_id + "' width='100%' height='700px' frameborder='0' scrolling='no'></iframe></div>"); 
			
			/*//put up the thumbnails
			//perform an ajax call to track views by current user
			var url = 'api/batchscan/explode';
			formValues = "id=" + batchscan_id;
	
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						if (data.success) {
							current_pages = data.pages;
							current_uploaded = data.uploaded;
							current_date = data.date;
							
							//stop checking, we're done
							clearTimeout(preptimeout);
							$("#batchscan_feedback_text").append("<div>PDF Processed...</div>");
							//$("#batchscan_feedback_text").append("<div>Extracted Pages:" + data.pages + "</div>");
							
							//crop
							//cropBatchscan(batchscan_id);
							
							checkBatchscanPNGs(batchscan_id, data.pages, data.uploaded, data.date);
						}
					}
				}
			});
			//check on progress by looking at the scans folder
			
			preptimeout = setTimeout(function() {
				checkOnPrep(batchscan_id);
			}, 2000);
			*/
		}
	}
}
var preptimeout = false;
function checkOnPrep(batchscan_id) {
	var url = 'api/batchscan/checkprep';
	formValues = "id=" + batchscan_id;

	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				if (data.success) {
					$("#batchscan_feedback_text").html("<div>Preparing Scan ...</div>");
					var progress = 0;
					
					//break early
					if (data.pages > 0) {
						//pages have been recorded in db
						if (data.found > data.pages - 3) {
							setTimeout(function() {
								checkBatchscanPNGs(current_batchscan_id, current_pages, current_uploaded, current_date);
							}, 3000);
							console.log("done checking");
							return;
						}
					}
					if (data.found > 0 && data.pages > 0) {
						progress = Number(data.found) / Number(data.pages) * 240;
					}
					$("#batchscan_feedback_text").append("<div style='background:green; width:" + progress + "px; height:10px; color:white'></div>");
					
					if (data.pages > data.found || data.pages==0) {
						console.log("keep checking");
						
						preptimeout = setTimeout(function() {
							checkOnPrep(batchscan_id);
						}, 1500);
						
					}
				}
			}
		}
	});
}
function checkOnStitch(batchscan_id) {
	if (!blnStitching) {
		clearTimeout(preptimeout);
		return;
	}
	console.log("check on stitch");
	var url = 'api/batchscan/checkstitch';
	formValues = "id=" + batchscan_id;

	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				if (data.success) {
					if (blnStitched) {
						return;
					}
					$("#batchscan_feedback_text").html("<div>Stitching ...</div>");
					var progress = 0;
					
					//break early
					if (data.documents > 0) {
						//pages have been recorded in db
						if (data.found > data.documents - 1) {
							return;
						}
					}
					if (data.found > 0 && data.documents > 0) {
						progress = Number(data.found) / Number(data.documents) * 240;
					}
					$("#batchscan_feedback_text").append("<div style='background:green; width:" + progress + "px; height:10px; color:white'></div>");
					
					if (data.documents > data.found || data.documents==0) {
						preptimeout = setTimeout(function() {
							checkOnStitch(batchscan_id);
						}, 1500);
					}
				}
			}
		}
	});
}
var current_batchscan_thumbnail = -1;
var blnStitching = false;
var blnStitched = false;
function stitchBatchscan(batchscan_id) {
	blnStitching = true;
	blnStitched = false;
	feedback = "<div>Stitching...<div>";
	$("#batchscan_feedback_text").html(feedback);
	var url = "api/batchscan/stitchstack";
	formValues = "id=" + batchscan_id;
	
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				if (data.success) {
					blnStitching = false;
					blnStitched = true;
					//stop checking, we're done
					clearTimeout(preptimeout);
					brightBatchscanFeedback(batchscan_id)
					var feedback = "<div>Batchscan Completed&nbsp;&#10003;</div>";
					$("#batchscan_feedback_text").html(feedback);
					$("#batchscan_info_holder .jsPanel-content").css("background", "lime");
					setTimeout(function() {
						$("#batchscan_info_holder").fadeOut();
					}, 2500);
					checkImports();
				}
			}
		}
	});
	
	preptimeout = setTimeout(function() {
		checkOnStitch(batchscan_id);
	}, 2500);
}
var blnCheckingBatchPNGs = false;
function checkBatchscanPNGs(batchscan_id, pages, uploaded, date, last_page) {
	blnCheckingBatchPNGs = true;
	if (typeof current_batchscan_thumbnail == "undefined") {
		current_batchscan_thumbnail = 0;
	}
	current_batchscan_thumbnail++;
	if (current_batchscan_thumbnail == pages) {
		blnCheckingBatchPNGs = false;
		stitchBatchscan(batchscan_id);
		return;
	}
	var feedback = "";
	if (typeof last_page != "undefined") {
		//feedback = "<div>Found Separator:" + last_page + "</div>"
	}
	feedback += "<div>Checking Page:" + current_batchscan_thumbnail + " of " + pages + "</div>"
	//progress bar, 240 pixels wide
	var progress = Number(current_batchscan_thumbnail) / Number(pages) * 240;
	feedback += "<div style='background:green; width:" + progress + "px; height:10px;'></div>"
	
	$("#batchscan_feedback_text").html(feedback);
	
	var filepath = uploaded.replace(".pdf", "-" + current_batchscan_thumbnail + ".jpg");
	
	var thumb_path = "../scans/" + customer_id + "/" + date;
	var arrLength = Number(pages);

	if (document.getElementById("batchscan_staging")== null) {
		$("#batchscan_feedback_text").append("<div id='batchscan_staging'></div>");
	} 
	//$("#batchscan_staging").append("<img id='barcode_preview' src='" + thumb_path + "/" + filepath + "' width='150px' height='auto'>");
	var crop_path = filepath.replace(".jpg", "_crop.jpg");
	var suffix = "_ikase";
	$("#batchscan_staging").html("<iframe src='../readcode" + suffix + ".php?batchscan_id=" + batchscan_id + "&uploaded=" + encodeURIComponent(uploaded) + "&pages=" + pages + "&counter=" + current_batchscan_thumbnail+ "&date=" + date + "&path=" + encodeURIComponent(crop_path) + "' width='100%' height='400px' frameborder='0'></iframe>");
}

function brightBatchscanFeedback(batchscan_id) {
	$("#batchscan_info_holder").css("opacity", "1");
	
	setTimeout(function() {
		//clean up after yourself
		var url = "api/batchscan/cleanafter";
		formValues = "id=" + batchscan_id;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					if (data.success) {
						console.log(batchscan_id + " clean");
					}
				}
			}
		});
	}, 700);
}
function cropBatchscan(batchscan_id) {
	if (batchscan_id!="") {
		if (!isNaN(Number(batchscan_id))) {
			blnCropping = true;
			var url = 'api/batchscan/crop';
			formValues = "id=" + batchscan_id;
			formValues += "&page_number=" + crop_counter;
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						if (data.success) {
							current_pages = data.pages;
							current_uploaded = data.uploaded;
							current_date = data.date;
							
							//stop checking, we're done
							$("#batchscan_feedback_text").append("<div>Scan Prepared...</div>");
							//checkBatchscanPNGs(batchscan_id, data.pages, data.uploaded, data.date);
							if (data.page_number==data.pages - 1) {
								blnCropping = false;
								var arrUploaded = data.uploaded.split("\\");
								var uploaded = arrUploaded[arrUploaded.length - 1];
								checkBatchscanPNGs(batchscan_id, data.pages, uploaded, data.date);
							} else {
								//nextone
								crop_counter++;
								//setTimeout(function() {
									cropBatchscan(batchscan_id);
								//}, 500);
							}
						}
					}
				}
			});
		}
	}
}
