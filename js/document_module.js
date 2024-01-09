function openDocumentPreviewPanel(document_id) {
	var panel_height = (window.innerHeight  - 115);
	var panel_width = ((window.innerWidth * .6) - 20);
	var yoffset = 725;
	if (document.getElementById("document_info_holder") == null) {
		$.jsPanel({
			id:			"document_info_holder",
			title:    	"Document Preview - " + document_id,
			size:		{ width: panel_width, height: panel_height },
			
			selector: 	"#document_feedback_holder",
			position: 	"bottom right",
			controls: {
				maximize: "disable"
			},
			content: 	"<div id='document_feedback_text' style='margin-left:5px'></div>",
			theme:    	"primary"
		});
	} else {
		//make sure it's positioned correctly
		$("#document_holder.jsPanel").css("top", "65px");
		$("#document_holder.jsPanel").css("left", "0px");
		$("#document_info_holder").fadeIn();
	}
	$("#document_info_holder .jsPanel-content").css("background", "white");
	$("#document_feedback_holder").fadeIn();
	$("#document_feedback_text").html("<div style='float:; margin-right:5px'><a style='color:black; cursor:pointer; font-size:24px' onclick='closeDocumentPreviewPanel()' title='Click to minize this window'>&times;</a></div>");
	/*
	display: block; top: 77px; left: 24px; opacity: 1; z-index: 102; position: absolute; width: 1004px; height: 689px;
	*/
	var src = $("#preview_document_" + document_id).val();
	
	//var panel_width = "100";
	var blnSound = (src.indexOf(".wma") > -1 || src.indexOf(".mp3") > -1);
	if (blnSound) {
		panel_width = "0";
		panel_height = "0";
	}
	
	$("#document_feedback_holder").css("top", "95px");
	$("#document_info_holder").css("left", (window.innerWidth * .4) + "px");
	$("#document_info_holder").css("width", panel_width + "px");
	$("#document_info_holder").css("height", panel_height + "px");
	$("#document_info_holder .jsPanel-title").css("display", "none");
	$("#document_info_holder .jsPanel-hdr").css("display", "none");
	
	$("#document_feedback_text").append("<div id='document_staging'><iframe src='" + src + "' width='" + panel_width + "px' height='" + panel_height + "px' frameborder='0'></iframe></div>"); 
	
	if (blnSound) {
		setTimeout(function() {
			closeDocumentPreviewPanel();
		}, 2000);
	}
}