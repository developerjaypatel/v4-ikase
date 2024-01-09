function showComplete(somedata) {
	console.log(somedata);
}
//global collections
var kases;
var new_applicant;
var applicants;
var occurences;
var recent_occurences;
var kaseKAIView;
var occurencesView;

//who is logged in
var login_username;

window.Router = Backbone.Router.extend({

    routes: {
        "": "home",
		"kases/:id": "kaseDetails",
		"kases" : "kasesList",
		"kases/events/:id": "kaseEvents",
		"kases/events/edit/:case_id/:event_id/:case_number": "kaseEventDialog",
		"kases/kai/:id": "kaseKAI",
		"events": "eventsList",
		"todo": "eventsListing",
		"upload/:id/:table_name": "documentUpload",
        "logout" : "logout"
	},

    initialize: function () {
		var self = this;
		this.kaseNavBarView = new kaseNavBarView();
        $('.kase_header').html(this.kaseNavBarView.render().el);
		
		this.kaseNavLeftView = new kaseNavLeftView();
		$('.left_sidebar').html(this.kaseNavLeftView.render().el);
		
		readCookie();
		
		//blank kase
		//var new_kase = new Kase({id:-1});
		//new_kase.fetch();
		//blank applicant
		new_applicant = new Applicant({id:-1});
		new_applicant.fetch();
		//get blank applicants collection
		applicants = new ApplicantCollection();
		
		var initial_kases = new KaseCollection();
		//go get the data from server
		initial_kases.fetch({
			success: function (data) {
				// Note that we could also 'recycle' the same instance of EmployeeFullView
				// instead of creating new instances
				//recent kases
				kases = initial_kases;
				self.recentKases();
			}
		});
		
		//recent events
		this.recentOccurences();
		
        // Close the search dropdown on click anywhere in the UI
        $('body').click(function () {
            $('.dropdown').removeClass("open");
        });
		$('.dropdown').click(function () {
			$('.dropdown-toggle').dropdown("toggle");										  
		})
    },
	
    home: function () {
		readCookie();
		
		// Since the home view never changes, we instantiate it and render it only once
        if (!this.kaseHomeView) {
            this.kaseHomeView = new kaseHomeView();
            this.kaseHomeView.render();
        } else {
            this.kaseHomeView.delegateEvents(); // delegate occurences when the view is recycled
        }

		//content
		$("#content").html(this.kaseHomeView.el);
		//nav
		
		this.kaseNavBarView.select_menu('home-menu');
    },
	documentUpload:  function (id, table_name) {
		readCookie();
		if (kases.length == 0) {
			kases.fetch({
				success: function (data) {
					var mymodel = kases.get(id);
					mymodel.set({table_name: table_name});
					// Since the home view never changes, we instantiate it and render it only once
					if (!this.DocumentUploadView) {
						this.DocumentUploadView = new documentUploadView({model:mymodel});
						this.DocumentUploadView.render();
					} else {
						this.DocumentUploadView.delegateEvents(); // delegate occurences when the view is recycled
					}
			
					//content
					$("#content").html(this.DocumentUploadView.el);
				}
			});
		} else {
			var mymodel = kases.get(id);
			mymodel.set({"table_name": table_name});
			// Since the home view never changes, we instantiate it and render it only once
			if (!this.DocumentUploadView) {
				this.DocumentUploadView = new documentUploadView({model:mymodel});
				this.DocumentUploadView.render();
			} else {
				this.DocumentUploadView.delegateEvents(); // delegate occurences when the view is recycled
			}
	
			//content
			$("#content").html(this.DocumentUploadView.el);
		}
	},
	
	kaseDetails: function (id) {
		readCookie();
		var self = this;
		var blnProceed = true;
		if (typeof kases == "undefined") {
			//initial call
			blnProceed = false;
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					blnProceed = true;
					self.kaseDetails(id);
					return;
				}
			});
		}
		if (!blnProceed){
			return;
		}
		if (id > -1) {
			var kase = kases.get(id);
			var applicant_id = kase.get("applicant_id");
			//we have a kase, do we have an applicant yet
			if (applicant_id==null) {
				var applicant_id = -1;
			}
		} else {
			//no kase, no applicant
			var kase = new_kase.clone();
			var applicant_id = -1;
		}
		
		//we need to set up the kase view and subviews
		$('#content').html(new KaseView({model: kase}).render().el);
    },
	
	kasesList: function (id) {
		readCookie();
		
		if (typeof id == "undefined") {
			id = "";
		}
		
		if (typeof kases == "undefined") {
			//initial call
			kases = new KaseCollection();
			//go get the data from server
			kases.fetch({
				success: function (data) {
					// Note that we could also 'recycle' the same instance of EmployeeFullView
					// instead of creating new instances
					$('#content').html(new kaseListingView({model: data}).render().el);
					if (id!="") {
						window.history.pushState(null, null, "#kases/" + id);
					}
				}
			});
		} else {
			$('#content').html(new kaseListingView({model: kases}).render().el);
		}
		
        this.kaseNavBarView.select_menu('kases-list-menu');
    },
	kaseEventDialog: function (id, event_id, case_number) {
		readCookie();
		var self = this;
		var blnProceed = true;
		//get the kase
		
		if (typeof kases == "undefined") {
			//IN CASE THE USER STARTS WITH A LINK DIRECTLY TO THIS URL
			//MUST PRELOAD collection
			blnProceed = false;
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					//go back
					//window.history.pushState(null, null, "#kase/events/" + id);
					var kase = data.get(id);
		
					$('#content').html(new KaseView({model: kase}).render().el);
					self.kaseEvents(id);
					return;
				}
			});
		}
		if (!blnProceed) {
			return;
		}
		occurences = new OccurenceCollection();
		occurences.fetch({
				success: function(data) {
					var occurence = occurences.get(event_id);
					occurence.set("case_number",case_number);
					occurence.set("case_id",id);
					//empty the content holder
					$("#kase_content").html("&nbsp;");
					//then re-assign to calendar
					var occurenceDialogView = new dialogView({el: $("#kase_content"), collection: occurences, model:occurence}).render();
				}
		});
	},
	kaseEvents: function (id) {		
		readCookie();
		var self = this;
		var blnProceed = true;
		//get the kase
		
		if (typeof kases == "undefined") {
			blnProceed = false;
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					//go back
					//window.history.pushState(null, null, "#kase/events/" + id);
					var kase = data.get(id);
		
					$('#content').html(new KaseView({model: kase}).render().el);
					self.kaseEvents(id);
					return;
				}
			});
		}
		if (!blnProceed) {
			return;
		}
		var kase = kases.get(id);
		
		//clean up button
		$("button.calendar").fadeOut(function() {
				$("button.information").fadeIn();
			});
		
		occurences = new OccurenceCollection();
		occurences.fetch({
				success: function(data) {
					//empty the content holder
					$("#kase_content").html("&nbsp;");
					//then re-assign to calendar
					occurencesView = new kaseOccurencesView({el: $("#kase_content"), collection: occurences, model:kase}).render();
					$("#kase_content").toggleClass("glass_header_no_padding");
					//$("#kase_content").prepend('<div id="seperator" class="seperator">&nbsp;</div><div style="height:10px"></div>');
					//prep the assignees
					setTimeout(function () {
						tokenIt('assignee', 'employee');
					}, 100);
				}
			}
		);
	},
	kaseKAI: function (id) {		
		readCookie();
		var self = this;
		var blnProceed = true;
		//get the kase
		
		if (typeof kases == "undefined") {
			blnProceed = false;
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					var kase = data.get(id);
					$('#content').html(new KaseView({model: kase}).render().el);
					self.kaseKAI(id);
					return;
				}
			});
		}
		if (!blnProceed) {
			return;
		}
		var kase = kases.get(id);
		var applicant = new Applicant({id: kase.get("applicant_id")});
		applicant.fetch({
			success: function (data) {
				data.set("case_id", kase.get("id"));
				data.set("case_uuid", kase.get("uuid"));
				
				kaseKAIView = new kaiView({el: $("#kase_content"), model:data}).render();
				$("#kase_content").addClass("glass_header_no_padding");
			}
		});
	},
	eventsList: function (id) {
		readCookie();
		
		if (typeof id == "undefined") {
			id = "";
		}
					
		if (typeof occurences == "undefined") {
			//initial call
			occurences = new OccurenceCollection();
			occurences.fetch({
				success: function (data) {
					occurencesView = new kaseOccurencesView({el: $("#content"), collection: occurences}).render();			
				}
			});			
		} else {
			$('#content').html(new kaseOccurencesView({collection: occurences}).render().el);
		}
		
        this.kaseNavBarView.select_menu('events-list-menu');
    },
	eventsListing: function (id) {
		//alert("here");
		readCookie();
		//the id must be the event owner id
		if (typeof id == "undefined") {
			id = "";
		}
		
		if (typeof occurences == "undefined") {
			//initial call
			occurences = new OccurenceCollection();
			//go get the data from server
			occurences.fetch({
				success: function (data) {
					// Note that we could also 'recycle' the same instance of EmployeeFullView
					// instead of creating new instances
					$('#content').html(new eventListingView({model: data}).render().el);
					
				}
			});
		} else {
			$('#content').html(new eventListingView({model: occurences}).render().el);
		}
		
        this.kaseNavBarView.select_menu('todo-list-menu');
    },
	
	recentKases: function() {
		readCookie();
		$('#kases_recent').html(new kaseListCategoryView({model: kases}).render().el);
	},
	
	recentOccurences: function() {
		readCookie();
		var self = this;
		if (typeof recent_occurences == "undefined") {
			//initial call
			recent_occurences = new RecentOccurenceCollection();
			//go get the data from server
			recent_occurences.fetch({
				success: function (data) {
					// Note that we could also 'recycle' the same instance of EmployeeFullView
					// instead of creating new instances
					$('#occurences_recent').html(new EventListView({model: data}).render().el);
					
					//kalendar
					self.summaryKalendar();
				}
			});
		} else {
			$('#occurences_recent').html(new EventListView({model: recent_occurences}).render().el);
		}
	},
	summaryKalendar:function(event) {
		readCookie();
		var summaryOccurencesView = new kaseOccurencesSummaryView({el: $("#summaryKalendar"), collection: recent_occurences}).render();
	},
    logout: function() {
        $('.alert-error').hide(); // Hide any errors on a new submit
        var url = '../api/logout';
    
        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: "",
            success:function (data) {
				$("#logged_in").val('');
				//clear the cookie
				writeCookie('sess_id', '');
				
                if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
                else { // If not, send them back to the home page
                    document.location.href = "index.html";
                }
            }
        });
    }
});

//load templates
templateLoader.load(["kaseNavBarView", "kaseNavLeftView", "kaseListCategoryView", "kaseHomeView", "kaseListingView",  "KaseSummaryView", "KaseView", "kaseHeaderView", "kaseApplicantView", "applicantView", "kaiView", "dialogView", "EventListView", "EventListItemView", "documentUploadView"],
    function () {
        app = new Router();
        Backbone.history.start();
	}
);
 // Tell jQuery to watch for any 401 or 403 errors and handle them appropriately
$.ajaxSetup({
    statusCode: {
        401: function(){
            // Redirec the to the login page.
            window.location.replace('#login');
			$('#logoutLink').hide();
         
        },
        403: function() {
            // 403 -- Access denied
            window.location.replace('#denied');
        }
    }
});

function datepickIt (object_id, blnTimepicker) {
	if (typeof blnTimepicker == "undefined") {
		blnTimepicker = true;
	}
	$(object_id).datetimepicker({
		timepicker:blnTimepicker, 
		format:'m/d/Y h:iA',
		mask:false,
		onChangeDateTime:function(dp,$input){
			//alert($input.val());
		}
	});	
}

function tableSortIt (object_id) {
	$("#" + object_id)
		.tablesorter({widthFixed: true, widgets: ['zebra']}) 
	    .tablesorterPager({container: $("#pager")}); 
}

function findIt(obj, namespace, page) {
	var $rows = $('.' + namespace + ' .' + page + '_data_row');
	var theobj = $("#" + obj.id);
	var val = $.trim($(theobj).val()).replace(/ +/g, ' ').toLowerCase();

	$rows.show().filter(function() {
		var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
		return !~text.indexOf(val);
	}).hide();
}

function tokenIt(object_id, model_name) {	
	//"../api/php-example.php"
	var search_collection;
	switch(model_name) {
		case "employee":
			search_collection = new EmployeeTypeCollection();
			break;
	}

	//go get the data from server
	search_collection.fetch({
		success: function (data) {
				//console.log(data);
				$("#" + object_id).tokenInput(data.toJSON(), {
					theme: "facebook"
				});
		}
	});
}

	function gridsterIt (gridster_index) {
	if (typeof gridster_index == "undefined") {
		gridster_index = -1;
	}
	var gridster = [];
	$(function () {
		//kompany applicant gridster
		if (gridster_index < 0 || gridster_index==0) {
			gridster[0] = $("#gridster_tall ul").gridster({
				namespace: '#gridster_tall',
				widget_margins: [2, 2],
				widget_base_dimensions: [262, 55],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[0].serialize();
						console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_tall").fadeIn();
		}
		//kase gridster header
		if (gridster_index < 0 || gridster_index==1) {
			gridster[1] = $("#gridster_flat ul").gridster({
				namespace: '#gridster_flat',
				widget_margins: [2, 2],
				widget_base_dimensions: [170, 45],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[1].serialize();
						console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			
			gridster[1].options.max_rows = 1;
			//$("#gridster_flat ul").css("z-index", "9");
			$("#gridster_flat").fadeIn();
		}
		//kai gridster
		if (gridster_index < 0 || gridster_index==2) {
			gridster[2] = $("#gridster_kai ul").gridster({
				namespace: '#gridster_kai',
				widget_margins: [5, 5],
				widget_base_dimensions: [260, 55],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[2].serialize();
						console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_kai").fadeIn();
		}
		//dialog gridster
		if (gridster_index < 0 || gridster_index==3) {
			gridster[3] = $("#gridster_dialog ul").gridster({
				namespace: '#gridster_dialog',
				widget_margins: [5, 5],
				widget_base_dimensions: [260, 55],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[3].serialize();
						console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_dialog").fadeIn();
		}
	});
	$('#content').fadeIn();
}

function toggleFormEdit(master_class) {
	//get all the editing fields, and toggle them back
	$("." + master_class + " .editing").toggleClass("hidden");
	$("." + master_class + " .span_class").removeClass("editing");
	$("." + master_class + " .input_class").removeClass("editing");
	
	$("." + master_class + " .span_class").toggleClass("hidden");
	$("." + master_class + " .input_class").toggleClass("hidden");
	$("." + master_class + " .input_holder").toggleClass("hidden");
	$(".button_row." + master_class).toggleClass("hidden");
	$(".edit_row." + master_class).toggleClass("hidden");
}
function isTouchDevice() {
	var ua = navigator.userAgent;
	var isTouchDevice = (
		ua.match(/iPad/i) ||
		ua.match(/iPhone/i) ||
		ua.match(/iPod/i) ||
		ua.match(/Android/i)
	);

	return isTouchDevice;
}

function editField(field_object, master_class) {
	var theclass = "";
	if (typeof master_class == "undefined") {
		master_class = "";
	}
	//if element dblclicked is a group holder (ie:Email group)
	if (master_class!="") {
		//get the name from the object
		if (typeof field_object.id == "undefined") {
			var field_name = field_object.attr("id");
			field_name = field_name.replace("Grid", "");
			//let's get the class
			theclass = field_object.parent().parent().attr("class");
		} else {
			var field_name = field_object.id;
			field_name = field_name.replace("Grid", "");
			//let's get the class
			theclass = field_object.parentElement.parentElement.className;
		}
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + field_name;
		//edit the field, only if it is not already in editing mode
		//show the input, hide the span
		$(".span_class" + master_class).toggleClass("hidden");
		$(".input_class" + master_class).toggleClass("hidden");
		if (master_class!="") {
			$(field_name + "Save").toggleClass("hidden");
		}
		$(".span_class" + master_class).addClass("editing");
		$(".input_class" + master_class).addClass("editing");
		$(field_name + "Save").addClass("editing");
		
		//get out, no more after this for masters
		return;
	}
	if (master_class=="") {
		if (typeof field_object != "undefined") {
			//get the name from the object
			if (typeof field_object.id == "undefined") {
				var field_name = field_object.attr("id");
				field_name = field_name.replace("Grid", "");
				//let's get the class
				theclass = field_object.parent().parent().attr("class");
			} else {
				var field_name = field_object.id;
				field_name = field_name.replace("Grid", "");
				//let's get the class
				theclass = field_object.parentElement.parentElement.className;
			}
			var arrClass = theclass.split(" ");
			theclass = "." + arrClass[0] + "." + arrClass[1];
			field_name = theclass + " #" + field_name;
		} else {
			//field_name = "#" + field_name;
			return;
		}
		
		//edit the field, only if it is not already in editing mode
		if ($(field_name + "Span").hasClass("editing")) {
			//turn it all off
			$(field_name + "Span").toggleClass("hidden");
			$(field_name + "Input").toggleClass("hidden");
			$(field_name + "Save").toggleClass("hidden");
			
			$(field_name + "Span").removeClass("editing");
			$(field_name + "Input").removeClass("editing");
			$(field_name + "Save").removeClass("editing");
			return;
		}
		//show the input, hide the span
		$(field_name + "Span").toggleClass("hidden");
		$(field_name + "Input").toggleClass("hidden");
		$(field_name + "Save").toggleClass("hidden");
		
		$(field_name + "Span").addClass("editing");
		$(field_name + "Input").addClass("editing");
		$(field_name + "Save").addClass("editing");
	}
}
