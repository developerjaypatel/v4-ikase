window.dashboard_home_view_mobile = Backbone.View.extend({
    initialize:function () {

    },
    events:{
	//"click #show_assigned"	:	"showAssigned",
	//"click #hide_assigned"	:	"hideAssigned"
	"click #home_view_all_done_mobile":	"doTimeouts",
	"click #label_search":				"Vivify",
	"click #srch-term":					"Vivify",
	"focus #srch-term":					"Vivify",
	"blur #srch-term":					"unVivify",
	"keyup #srch-term": 				"scheduleSearch",
    "keypress #srch-term": 				"onkeypress"
  	
    },
	scheduleSearch: function(event) {
		var self = this;
		clearTimeout(search_timeout_id);
		
		if (kase_searching == true) {
			return;
		}
		
		search_timeout_id = setTimeout(function() {
			self.searchKases(event);
		}, 700);
	},
	searchKases: function (event) {
		kase_searching = true;
		blnSearched = true;
		$('#ikase_loading').html(loading_image);
		var self = this;
		var key = $('#srch-term').val();
		
		if (typeof key =="undefined") {
			return;
		}
		
		var search_started = false;
		
		if (search_started) {
			return;
		}
		if (this.collection.length == 0) {
			this.collection = kases.clone();
		}
		
		if (event.keyCode === 8) { // backspace key pressed
			if ($('#srch-term').val()=="") {
				$('#ikase_loading').html('');
				if ($("#list_kases_header").length==0 || $("#list_kases_header").parent().parent()[0].id=="search_results") {
					$("#search_results").html("");
				} else {
					
					$('#content').html(new kase_listing_view_mobile({collection: kases, model: ""}).render().el);
				}
				kase_searching = false;
				return;
			}
        }
		event.preventDefault();
        var key = $('#srch-term').val();
		key = key.toString().cleanString();
		$('#srch-term').val(key);
		if (key.length > 0) {
			if (key.length > 2) {
				var my_kases = new KaseCollection(this.collection.searchCollection(key));
								
				//may not have found anything				
				//let's do an actual search, it might be an old or closed record
				blnSearchingKases = true;
				search_kases = my_kases.searchDB(key);
				//I NEED A PROMISE HERE!!
				//if data is found, it will tack-on later on, messy for now
				$('#ikase_loading').html('');
				var mymodel = new Backbone.Model();
				mymodel.set("key", key);
				if ($("#list_kases_header").length==0 || $("#list_kases_header").parent().parent()[0].id=="search_results") {
					
					$('#search_results').html(new kase_listing_view_mobile({collection: my_kases, model: mymodel}).render().el);
					//$("#search_results").css("margin-top", "55px");
					$("#search_results").show();
					
				} else {
					$('#content').html(new kase_listing_view_mobile({collection: my_kases, model: mymodel}).render().el);
				}
			}
			
			setTimeout(function() {
				$('#ikase_loading').html('');
				$("#kase_status_title").html("Found");
			}, 700);
			kase_searching = false;
			return;
			//give them a chance to type, so search only when they stopped typing
			/*
			CHECK LATER IF THIS IS ACTUALLY FASTER, THOUGH I DOUBT IT
			clearTimeout(search_timeout_id);
			search_timeout_id = setTimeout(function() {
				self.model.findByName(key);
				$('#content').html(new kase_listing_view({model: self.model}).render().el);
			}, 500);
			*/
			
			//let's use underscore to find the right records
			/*
			var my_kases = kases.filter(function(kase){ 
				var blnReturn;
				var first_name = kase.get('first_name').toLowerCase();
				blnReturn = (first_name.indexOf(key) > -1);
				return blnReturn;
			});
			
			var key = key.toLowerCase();
			blnFound = false;
			var my_kases = new KaseCollection();
			_.each( kases.toJSON(), function(kase) {
				//if (kase.first_name.toLowerCase().indexOf(key) > -1 || kase.last_name.toLowerCase().indexOf(key) > -1 || kase.employer.toLowerCase().indexOf(key) > -1) {
				if (_.values(kase).toString().toLowerCase().indexOf(key) > -1) {
					my_kases.add(kase);
				}
			});
			
			$('#content').html(new kase_listing_view({model: my_kases}).render().el);
			*/
		} else {
			kase_searching = false;
			$('#ikase_loading').html('');
			var mymodel = new Backbone.Model();
			mymodel.set("key", "");
			$('#content').html(new kase_listing_view_mobile({collection: kases, model: mymodel}).render().el);
		}
    },
    onkeypress: function (event) {
        if (event.keyCode === 13) { // enter key pressed
            event.preventDefault();
        }
    },
	unVivify: function(event) {
		var textbox = $("#srch-term");
		var label = $("#label_search");
		
		if (textbox.val() == "") {
			label.animate({color: "#CCC", top: "60px", fontSize: "1.3em", left: "5px", fontWeight: "300"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#srch-term");
		var label = $("#label_search");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "29px", left: "-60px", fontSize: ".68em"}, 250);
			//$('#notes_searchList').focus();
		}
	},
    render:function () {
		var self = this;
		$(this.el).html(this.template());
		var mymodel = new Backbone.Model();
		mymodel.set("key", "");
		
		
        return this;
    },
	doTimeouts:function() {

	},
	showAssigned:function() {
		$('#show_assigned').hide();
		$('#row_2_col_2').show();
		$('#hide_assigned').show();
		
	},
	hideAssigned:function() {
		$('#hide_assigned').hide();
		$('#row_2_col_2').hide();
		$('#show_assigned').show();
		
	}
});
