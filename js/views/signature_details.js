var signature_timeout_id;
var inside;
window.signature_view = Backbone.View.extend({
	render: function () {
		var self = this;
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "signature_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		//we are not in editing mode initially
		this.model.set("editing", false);
		
		if (this.model.id<0) {
			$( ".signature .edit" ).trigger( "click" );
		}
        return this;
	},
	
	events:{
		"dblclick .signature .gridster_border": 	"editSignaturesField",
		"click .signature .save":					"scheduleAddSignature",
		"click .signature .save_field":				"saveSignaturesField",
		"click .signature .edit": 					"scheduleSignaturesEdit",
		"click .signature .reset": 					"scheduleSignaturesReset",
		"click #signature_view_all_done":			"doTimeouts"		
    },
	doTimeouts:function() {
		var self = this;
		gridsterById('gridster_signature');
		/*
		$(".signature #signatureInput").cleditor({
			width:895,
			height: 320,
			controls:     // controls to add to the toolbar
					  "bold italic underline | font size " +
					  "style | color highlight"
		});
		
		$(".signature .cleditorMain").hide();
		
		setTimeout(function() {
			//new editor
			inside = iframeRef( document.getElementById('tiny_holder') );
			inside.getElementById("editor").innerHTML = self.model.get("signature");
		}, 2000);
		*/
	},
	editSignaturesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".signature_" + field_name;
		}
		editField(element, master_class);
	},
	editdialogField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".signature_" + field_name;
		}
		editField(element, master_class);
	},
	scheduleAddSignature:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(signature_timeout_id);
		signature_timeout_id = setTimeout(function() {
			self.addSignature(event);
		}, 200);
	},
	addSignature:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		//turn off editing to toggle
		this.model.set("editing", false);
		addForm(event,"signature");
		//turn off editing altogether
		this.model.set("editing", false);
		return;
    },
	saveSignaturesField: function (event) {
		console.log("save_function_start");
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		console.log("save_function_next");
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		console.log("save_function_after");
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		console.log("save_function_model_start");
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		
		if (typeof kases != "undefined") {
			if (this.model.id!="") {
				kases.get(this.model.id).set(this.model.toJSON());
			}
		}
		//this should not redraw, it will update if no id
		addForm(event);
	},
	scheduleSignaturesEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(signature_timeout_id);
		signature_timeout_id = setTimeout(function() {
			self.toggleSignaturesEdit(event);
		}, 200);
	},
	toggleSignaturesEdit: function (event) {
		event.preventDefault();
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		//going forward we are in editing mode until reset or save
		this.model.set("editing", true);
		toggleFormEdit("signature");
		if ($(".signature #signatureInput").css("display").indexOf("block") > -1) {
			$(".signature #signatureInput").fadeOut(function() {
				$(".signature #signatureSpan").fadeIn();
			});
		} else {
			$(".signature #signatureSpan").fadeOut(function() {
				$(".signature #signatureInput").fadeIn();
			});
		}
		/*
		if ($(".signature .cleditorMain").css("display")=="block") {
			$(".signature .cleditorMain").fadeOut(function() {
				$("#signatureSpan").fadeIn();
			});
		} else {
			$("#signatureSpan").fadeOut(function() {
				$(".signature .cleditorMain").fadeIn();
			});
		}
		*/
		/*
		if ($(".signature #tiny_holder").css("display")=="block" || $(".signature #tiny_holder").css("display")=="inline") {
			$(".signature #tiny_holder").fadeOut(function() {
				$("#signatureSpan").fadeIn();
			});
		} else {
			$("#signatureSpan").fadeOut(function() {
				$(".signature #tiny_holder").fadeIn();
			});
		}
		*/
	},
	scheduleSignaturesReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(signature_timeout_id);
		signature_timeout_id = setTimeout(function() {
			self.resetSignaturesForm(event);
		}, 200);
	},
	
	resetSignaturesForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.toggleSignaturesEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	}
});

function initToolbarBootstrapBindings() {
  var fonts = ['Serif', 'Sans', 'Arial', 'Arial Black', 'Courier', 
		'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande', 'Lucida Sans', 'Tahoma', 'Times',
		'Times New Roman', 'Verdana'],
		fontTarget = $('[title=Font]').siblings('.dropdown-menu');
  $.each(fonts, function (idx, fontName) {
	  fontTarget.append($('<li><a data-edit="fontName ' + fontName +'" style="font-family:\''+ fontName +'\'">'+fontName + '</a></li>'));
  });
  $('a[title]').tooltip({container:'body'});
	$('.dropdown-menu input').click(function() {return false;})
		.change(function () {$(this).parent('.dropdown-menu').siblings('.dropdown-toggle').dropdown('toggle');})
	.keydown('esc', function () {this.value='';$(this).change();});

  $('[data-role=magic-overlay]').each(function () { 
	var overlay = $(this), target = $(overlay.data('target')); 
	overlay.css('opacity', 0).css('position', 'absolute').offset(target.offset()).width(target.outerWidth()).height(target.outerHeight());
  });
  if ("onwebkitspeechchange"  in document.createElement("input")) {
	var editorOffset = $('#editor').offset();
	$('#voiceBtn').css('position','absolute').offset({top: editorOffset.top, left: editorOffset.left+$('#editor').innerWidth()-35});
  } else {
	$('#voiceBtn').hide();
  }
};
function showErrorAlert (reason, detail) {
	var msg='';
	if (reason==='unsupported-file-type') { msg = "Unsupported format " +detail; }
	else {
		console.log("error uploading file", reason, detail);
	}
	$('<div class="alert"> <button type="button" class="close" data-dismiss="alert">&times;</button>'+ 
	 '<strong>File upload error</strong> '+msg+' </div>').prependTo('#alerts');
};

