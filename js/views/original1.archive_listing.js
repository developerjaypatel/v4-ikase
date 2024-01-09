window.archive_listing_view = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
    },
    events:{
		"keyup #archive_searchList":				"findIt",
		"click #archive_clear_search":				"clearSearch"
	},
    render:function () {		
		var self = this;
		
		//this.collection.bind("reset", this.render, this);
		var kase_archives = this.collection.toJSON();
		/*
		_.each( kase_archives, function(kase_archive) {
		});
		*/
		try {
			$(this.el).html(this.template({kase_archives: kase_archives, kase: this.model}));
		}
		catch(err) {
			var view = "archive_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		setTimeout(function() {
			$("#archive_listing th").css("font-size", "1em");
			$("#archive_listing").css("font-size", "1.1em");
		}, 600);
		
		return this;
	},
	findIt: function(event) {
		var element = event.currentTarget;
		findIt(element, 'archive_listing', 'kase_archive');
	},
	clearSearch: function() {
		$("#archive_searchList").val("");
		$( "#archive_searchList" ).trigger( "keyup" );
		
		$(".filter_select").val();
		$( "#typeFilter" ).trigger( "click" );
	}
});
window.ecand_archive_listing_view = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
    },
    events:{
		"keyup #archive_searchList":				"findIt",
		"click #archive_clear_search":				"clearSearch"
	},
    render:function () {		
		var self = this;
		
		if (typeof this.template != "function") {
			var view = "ecand_archive_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		//this.collection.bind("reset", this.render, this);
		var kase_archives = this.collection.toJSON();
		/*
		_.each( kase_archives, function(kase_archive) {
		});
		*/
		try {
			$(this.el).html(this.template({kase_archives: kase_archives, kase: this.model}));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		setTimeout(function() {
			$("#ecand_archive_listing th").css("font-size", "1em");
			$("#ecand_archive_listing").css("font-size", "1.1em");
		}, 600);
		
		return this;
	},
	findIt: function(event) {
		var element = event.currentTarget;
		findIt(element, 'ecand_archive_listing', 'kase_archive');
	},
	clearSearch: function() {
		$("#archive_searchList").val("");
		$("#archive_searchList" ).trigger( "keyup" );
		
		$(".filter_select").val();
		$( "#typeFilter" ).trigger( "click" );
	}
});
window.archive_legacy_listing_view = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
    },
    events:{
		"keyup #archive_searchList":				"findIt",
		"click #archive_clear_search":				"clearSearch",
		"change .filter_select":					"filterArchives",
		"click .expand_remainder":					"expandRemainder",
		"click #archive_listing_all_done":			"doTimeouts"
	},
    render:function () {		
		var self = this;
		
		//this.collection.bind("reset", this.render, this);
		var kase_archives = this.collection.toJSON();
		
		_.each( kase_archives, function(kase_archive) {
			var description = kase_archive.description;
			description = description.replaceAll("&#;160", "");
			description = description.replaceAll("&#;254", "");
			description = description.replaceAll("<br><br>", "<br>");
			description = description.replaceAll("<BR><BR>", "<BR>");
			
			
			//are we dealing with a msg
			if (kase_archive.path.indexOf(".msg") > -1) {
				var arrDescr = description.split("<BR>");
				description = arrDescr[0];
				//remove the first element
				arrDescr.splice(0, 1);
				
				var description_remainder = arrDescr.join("<BR>");
				description_remainder = "<div><a class='expand_remainder' id='expand_remainder_" + kase_archive.id + "' style='cursor:pointer; background:white; color:black; padding:1px'>...</a></div><div style='display:none' id='remainder_" + kase_archive.id + "'></div>";
				description += description_remainder;
			}
			
			kase_archive.description = description;
		});
		
		try {
			$(this.el).html(this.template({kase_archives: kase_archives, kase: this.model}));
		}
		catch(err) {
			var view = "archive_legacy_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		return this;
	},
	expandRemainder: function(event) {
		var element = event.currentTarget;
		var archive_id = element.id.split("_")[2];
		var remainder = $("#remainder_" + archive_id);
		
		if (remainder.html()!="" || remainder.html().indexOf("<iframe") > -1) {
			remainder.html("");
			remainder.css("background", "none");
			remainder.fadeOut();
			$("#expand_remainder_" + archive_id).html("...");
			return;
		}
		$("#expand_remainder_" + archive_id).html("");
		remainder.html('<i class="icon-spin4 animate-spin" style="font-size:2em; color:white"></i>');
		remainder.show();
		//get the msg in html format
		var src = $(".kase_archive_row_" + archive_id + " a")[0].href;
		//perform an ajax call to track views by current user
		var url = 'api/archivemsg';
		formValues = "filename=" + encodeURIComponent(src);

		$.ajax({
			url:url,
			type:'POST',
			dataType:"text",
			data: formValues,
			success:function (data) {
				$("#expand_remainder_" + archive_id).html("close");
				remainder.html("<iframe id='remainder_frame_" + archive_id + "' width='100%' height='500px' style='overflow-y:scroll; background:#FFFFFF'></iframe>");
				
				var doc = document.getElementById("remainder_frame_" + archive_id).contentWindow.document;
				doc.open();
				var content = data.substring(data.indexOf("<head>"));
				doc.write(content);
				doc.close();
				//remainder.css("background", "white");
			}
		});
		
	},
	doTimeouts: function(event) {
		var arrOptions = [];
		arrOptions.push("<option value=''>Filter By</option>");
		var archive_categories = this.model.get("archive_categories");
		archive_categories.forEach(function(element, index, array) {
			if (element!="") {
				arrOptions.push("<option value='" + element + "'>" + element + "</option>");
			}
		});
		
		$("#archiveFilter").html(arrOptions.join("\r\n"));
		
		setTimeout(function() {
			$("#archive_listing th").css("font-size", "1em");
			$("#archive_listing").css("font-size", "1.1em");
		}, 600);
	},
	findIt: function(event) {
		var element = event.currentTarget;
		findIt(element, 'archive_listing', 'kase_archive');
	},
	clearSearch: function() {
		$("#archive_searchList").val("");
		$( "#archive_searchList" ).trigger( "keyup" );
		
		$(".filter_select").val();
		$( "#typeFilter" ).trigger( "click" );
	},
	filterArchives: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		filterIt(element, "archive_listing", "kase_archive");
	}
});
window.legacy_a1_list_folders_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
		"click .list_files":				"listFiles"
	},
    render:function () {		
		if (typeof this.template != "function") {
			this.model.set("holder", "kase_content");
			var view = "legacy_a1_list_folders_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		
		try {
			$(this.el).html(this.template({folders: this.collection.toJSON()}));
		}
		catch(err) {
			alert(err);
			return "";
		}
		
		return this;
	},
	listFiles: function(event) {
		event.preventDefault();
		var element= event.currentTarget;
		var folder = element.innerHTML;
		//get the folder name
		
		var url = "api/legacya1_list/" + current_case_id + "/" + encodeURIComponent(folder.replaceTout(" ", "~~"));
		//pass it to xyx
		
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				//get a list of files
				var mymodel = new Backbone.Model();
				mymodel.set("holder", "kase_content"); 
				mymodel.set("folder", folder); 
				$('#kase_content').html(new a1_archive_listing_view({collection: data, model: mymodel}).render().el);
			}
		});
	}
});
window.a1_archive_listing_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
		"click .show_a1_archive":				"openFile"
	},
    render:function () {		
		if (typeof this.template != "function") {
			this.model.set("holder", "kase_content");
			var view = "a1_archive_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		
		try {
			$(this.el).html(this.template({archives: this.collection, folder: this.model.get("folder")}));
		}
		catch(err) {
			alert(err);
			return "";
		}
		
		return this;
	},
	openFile: function(event) {
		event.preventDefault();
		var element= event.currentTarget;
		var file = element.innerHTML;
		var folder = this.model.get("folder");
		var url = "https://kustomweb.xyz/a1_archive/legacy_read.php?case_id=" + current_case_id + "&customer=" + customer_data_source + "&folder=" + encodeURIComponent(folder.replaceTout(" ", "~~")) + "&file=" + encodeURIComponent(file.replaceTout(" ", "~~"));
		//$('#kase_content').html("<iframe src='" + url + "' width='1000px' height='500px'></iframe>");
		window.open(url);
	}
});