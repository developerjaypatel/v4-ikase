//global collections
var kases;
var eams_carriers;
var attorney_searches;
var blnKasesFetched = false;
var search_kases;
var notes;
var kaseNotes;
var noteKaseView;
var new_kase;
var new_applicant;
var new_partie;
var applicants;
var occurences;
var corporations;
var parties;
var recent_occurences;
var kaseKAIView;
var partyKaseView;
var occurencesView;
var case_id;

window.Router = Backbone.Router.extend({
    routes: {
        "": "home",
		"applicant/:id": "kaseApplicant",
		"events": "eventsList",
		"kases/:id": "kaseDetails",
		"kases" : "kasesList",
		"kases/events/:id": "kaseEvents",
		"kases/events/edit/:case_id/:event_id": "kaseEventDialog",
        "kases/kai/:id": "kaseKAI",
		"logout" : "logout",
		"injury/:case_id" : "kaseInjury",
		"import" : "documentImport",
		"notes/:case_id" : "kaseNotes",
		"notes/:case_id/:id/:type" : "kaseNote",
		"parties/:id" : "kaseParties",
		"parties/:case_id/:id/:type" : "kasePartie",
		"todo": "eventsListing",
		"upload/:id/:table_name": "documentUpload",
		"users" : "kaseUsers",
		"users/:id" : "kaseUser",
		"users/email/:user_id" : "userEmail"
	},

    initialize: function () {
		var self = this;
		readCookie();
		kases = new KaseCollection();
		
		// Close the search dropdown on click anywhere in the UI
		$('body').click(function () {
			$('.dropdown').removeClass("open");
		});
		$('.dropdown').click(function () {
			$('.dropdown-toggle').dropdown("toggle");										  
		});
		
		//navigation
		search_kases = new KaseCollection();
		search_kases.fetch({
			success:function(data){
				$('.kase_header').html(new kase_nav_bar_view({model: search_kases}).render().el);								
			}
		});
		
		//left column navigation
		self.kaseNavLeftView = new kase_nav_left_view();
		$('.left_sidebar').html(self.kaseNavLeftView.render().el);
		
		//recent events
		setTimeout(function() {
			self.recentOccurences();
		}, 500);
		
		//recent kases
		setTimeout(function() {
			self.recentKases();
		}, 600);

		//go get the data from server
		kases.fetch({
			success: function (data) {
				//recent kases
				blnKasesFetched = true;
			}
		});
		
		//eams carriers
		eams_carriers = new EamsCarrierCollection();
		eams_carriers.fetch({
			success:function(data){
				//console.log("carriers:" + data.length);
			}
		});
		//eams carriers
		/*
		attorney_searches = new AttorneySearchCollection();
		attorney_searches.fetch({
			success:function(data){
				//console.log("carriers:" + data.length);
			}
		});
		*/
    },
	
    home: function () {
		readCookie();
		
		// Since the home view never changes, we instantiate it and render it only once
        if (!this.kaseHomeView) {
            this.kaseHomeView = new kase_home_view();
            this.kaseHomeView.render();
        } else {
            this.kaseHomeView.delegateEvents(); // delegate occurences when the view is recycled
        }

		//content
		$("#content").html(this.kaseHomeView.el);
		//nav
		setTimeout(function() {
			this.kaseHomeView.select_menu('home-menu');
		}, 300);
    },
	documentImport: function() {
		$('#content').html(new import_view().render().el);
		$("#content").addClass("glass_header_no_padding");
	},
	documentUpload:  function (id, table_name) {
		readCookie();
		var self = this;
		var blnProceed = true;
		//get the kase
		if (typeof kases == "undefined" || !blnKasesFetched) {
			blnProceed = false;
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					var kase = data.get(id);
					kase.set("header_only", true);
					$('#content').html(new kase_view({model: kase}).render().el);
					self.documentUpload(id, table_name);
					return;
				}
			});
		}
		if (!blnProceed) {
			return;
		}
		var kase = kases.get(id);
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		var mymodel = kases.get(id);
		mymodel.set({table_name: table_name});
		// Since the home view never changes, we instantiate it and render it only once
		this.DocumentUploadView = new document_upload_view({model:mymodel});
		this.DocumentUploadView.render();
		
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);			
		//content
		$("#kase_content").html(this.DocumentUploadView.el);
	},
	
	kaseDetails: function (id) {
		readCookie();
		var self = this;
		var blnProceed = true;
		if (typeof kases == "undefined" || !blnKasesFetched) {
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
			//perform an ajax call to track views by current user
			var url = '../api/kase/view';
			formValues = "id=" + id;
	
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						self.recentKases();
					}
				}
			});
			
			var kase = kases.get(id);
			$(document).attr('title', kase.get("case_number") + " :: " + kase.get("full_name") + " vs " + kase.get("employer") + " :: iKase");
			if (typeof kase == "undefined") {
				//case does not exist, get out
				document.location.href = "#";
				return;
			}
			//var kase =  new Kase({id: id});
			var applicant_id = kase.get("applicant_id");
			//we have a kase, do we have an applicant yet
			if (applicant_id==null) {
				var applicant_id = -1;
			}
		} else {
			//no kase, no applicant
			var kase = new Kase();
			case_number_next = String(customer_settings.case_number_next);
			if (customer_settings.case_number_next < 10) {
				case_number_next = "000" + case_number_next;
			}
			if (customer_settings.case_number_next < 100) {
				case_number_next = "00" + case_number_next;
			}
			if (customer_settings.case_number_next < 1000) {
				case_number_next = "0" + case_number_next;
			}
			kase.set("case_number", customer_settings.case_number_prefix + case_number_next);
			var applicant_id = -1;
		}
		kase.set("applicant_id", applicant_id);
		//we need to set up the kase view and subviews
		
		var type = "";
		//parties = new Corporations([], { case_id: id });
		parties = new Parties([], { case_id: id, case_uuid: kase.get("uuid"), panel_title: "Dashboard" });
		parties.fetch({
			success: function(parties) {
				//if we have no parties and no applicant
				if (parties.length == 0 && id > 0) {
					kase.set("header_only", true);
					$('#content').html(new kase_view({model: kase}).render().el);
					self.kaseApplicant(id);
					return;
				}
				if (parties.length == 1 && id > 0) {
					//is that one partie the applicant
					if (parties.toJSON()[0].type=="applicant") {
						//we must have the employer next, at least for WCAB
						//self.kasePartie(id, -1, "employer");
						$("#content").removeClass("glass_header_no_padding");
						$('#content').html(new kase_view({model: kase}).render().el);
						hideEditRow();
						return;
					}
				}
				if (parties.length > 1 && id > 0) {
					//we have applicant and 1 partie, we need employer
					employer_partie = parties.findWhere({"type": "employer"});
					
					if (typeof employer_partie == "undefined") {
						$("#content").removeClass("glass_header_no_padding");
						$('#content').html(new kase_view({model: kase}).render().el);
						hideEditRow();
						return;
					}
				}
				if (parties.length > 0 || id==-1) {
					kase.set("header_only", true);
				}
				$("#content").removeClass("glass_header_no_padding");
				$('#content').html(new kase_view({model: kase}).render().el);
				if (parties.length == 0) {
					//we don't want the dashboard for a new kase without parties
					return;
				}
				$('#kase_content').html(new partie_cards_view({model: parties}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});	
    },
	newKase: function () {
		readCookie();
		var self = this;
		var blnProceed = true;
		if (typeof kases == "undefined" || !blnKasesFetched) {
			//initial call
			blnProceed = false;
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					blnProceed = true;
					self.newKase();
					return;
				}
			});
		}
		if (!blnProceed){
			return;
		}

		var kase = new Kase();
		kase.set("header_only",true);
		//we need to set up the kase view and subviews
		$("#content").removeClass("glass_header_no_padding");
		$('#content').html(new kase_view({model: kase}).render().el);
    },
	kasesList: function (id) {
		readCookie();
		
		if (typeof id == "undefined") {
			id = "";
		}
		
		if (typeof kases == "undefined" || !blnKasesFetched) {
			//initial call
			search_kases = new KaseCollection();
			//go get the data from server
			search_kases.fetch({
				success: function (data) {
					// Note that we could also 'recycle' the same instance of EmployeeFullView
					// instead of creating new instances
					$('#content').html(new kase_listing_view({collection: data, model: ""}).render().el);
				}
			});
		} else {
			$('#content').html(new kase_listing_view({model: kases}).render().el);
		}
		
        this.kaseNavBarView.select_menu('kases-list-menu');
    },
	kaseEventDialog: function (id, event_id) {
		readCookie();
		var self = this;
		var blnProceed = true;
		//get the kase
		
		if (typeof kases == "undefined" || !blnKasesFetched) {
			//IN CASE THE USER STARTS WITH A LINK DIRECTLY TO THIS URL
			//MUST PRELOAD collection
			blnProceed = false;
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					//go back
					//window.history.pushState(null, null, "#kase/events/" + id);
					var kase = data.get(id);
					kase.set("header_only", true);
					$('#content').html(new kase_view({model: kase}).render().el);
					self.kaseEventDialog(id, event_id);
					return;
				}
			});
		}
		if (!blnProceed) {
			return;
		}
		var kase = kases.get(id);
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		occurences = new OccurenceCollection({case_id: id});
		occurences.fetch({
				success: function(data) {
					if (event_id > -1) {
						var occurence = occurences.get(event_id);
					} else {
						//no kase, no applicant
						var occurence = new Occurence({case_id: id});
					}
					
					occurence.set("case_number", kase.get("case_number"));
					occurence.set("case_id",id);
					occurence.set("case_uuid", kase.get("uuid"));
					occurence.set("gridster_me",true);
					//empty the content holder
					$("#kase_content").html("&nbsp;");
					//then re-assign to calendar
					var occurenceDialogView = new dialog_view({el: $("#kase_content"), collection: occurences, model:occurence}).render();
				}
		});
	},
	kaseEvents: function (id) {		
		readCookie();
		var self = this;
		var blnProceed = true;
		//get the kase
		
		if (typeof kases == "undefined" || !blnKasesFetched) {
			blnProceed = false;
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					//go back
					//window.history.pushState(null, null, "#kase/events/" + id);
					var kase = data.get(id);
					kase.set("header_only", true);
					$('#content').html(new kase_view({model: kase}).render().el);
					self.kaseEvents(id);
					return;
				}
			});
		}
		if (!blnProceed) {
			return;
		}
		var kase = kases.get(id);
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		//clean up button
		$("button.calendar").fadeOut(function() {
				$("button.information").fadeIn();
			});
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);
		
		occurences = new OccurenceCollection({case_id: id});
		occurences.fetch({
				success: function(data) {
					//empty the content holder
					$("#kase_content").html("&nbsp;");
					//then re-assign to calendar
					occurencesView = new kase_occurences_view({el: $("#kase_content"), collection: occurences, model:kase}).render();
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
		
		if (typeof kases == "undefined" || !blnKasesFetched) {
			blnProceed = false;
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					var kase = data.get(id);
					kase.set("header_only", true);
					$('#content').html(new kase_view({model: kase}).render().el);
					self.kaseKAI(id);
					return;
				}
			});
		}
		if (!blnProceed) {
			return;
		}
		var kase = kases.get(id);
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		var applicant = new Applicant({id: kase.get("applicant_id")});
		applicant.fetch({
			success: function (data) {
				data.set("case_id", kase.get("id"));
				data.set("case_uuid", kase.get("uuid"));
				
				kaseKAIView = new kai_view({el: $("#kase_content"), model:data}).render();
				$("#kase_content").addClass("glass_header_no_padding");
			}
		});
	},
	kaseApplicant: function (id) {		
		readCookie();
		var self = this;
		var blnProceed = true;
		//get the kase
		
		if (typeof kases == "undefined" || !blnKasesFetched) {
			blnProceed = false;
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					var kase = data.get(id);
					kase.set("header_only", true);
					$('#content').html(new kase_view({model: kase}).render().el);
					self.kaseApplicant(id);
					return;
				}
			});
		}
		if (!blnProceed) {
			return;
		}
		var kase = kases.get(id);
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		var applicant = new Person({id: kase.get("applicant_id")});
		applicant.fetch({
			success: function (data) {
				data.set("case_id", kase.get("id"));
				data.set("case_uuid", kase.get("uuid"));
				data.set("gridster_me", true);
				
				the_applicant = new dashboard_person_view({el: $("#kase_content"), model:data}).render();
				//$("#kase_content").addClass("glass_header_no_padding");
			}
		});
	},
	kaseInjury: function (id) {		
		readCookie();
		var self = this;
		var blnProceed = true;
		//get the kase
		
		if (typeof kases == "undefined" || !blnKasesFetched) {
			blnProceed = false;
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					self.kaseInjury(id);
					return;
				}
			});
		}
		if (!blnProceed) {
			return;
		}
		
		var kase = kases.get(id);
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);
		$('#kase_content').html(new dashboard_injury_view({model: kase}).render().el);
	},
	kaseParties: function (id) {		
		readCookie();
		var self = this;
		var blnProceed = true;
		//get the kase
		if (typeof kases == "undefined" || !blnKasesFetched) {
			blnProceed = false;
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					var kase = data.get(id);
					kase.set("header_only", true);
					$('#content').html(new kase_view({model: kase}).render().el);
					self.kaseParties(id);
					return;
				}
			});
		}
		if (!blnProceed) {
			return;
		}
		var kase = kases.get(id);
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		var type = "";
		//parties = new Corporations([], { case_id: id });
		parties = new Parties([], { case_id: id, panel_title: "Parties" });
		parties.fetch({
			success: function(data) {
				kase.set("header_only", true);
				$('#content').html(new kase_view({model: kase}).render().el);
				if (parties.length == 0) {
					self.kasePartie(id, -1, "new");
					return;
				}
				$('#kase_content').html(new partie_cards_view({model: data}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				
				hideEditRow();
			}
		});	
	},
	kaseNotes: function (case_id) {		
		readCookie();
		var self = this;
		var blnProceed = true;
		//get the kase
		
		if (typeof kases == "undefined" || !blnKasesFetched) {
			blnProceed = false;
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					var kase = data.get(case_id);
					if (typeof kase == "undefined") {
						//case does not exist, get out
						document.location.href = "#";
						return;
					}
					kase.set("header_only", true);
					$('#content').html(new kase_view({model: kase}).render().el);
					self.kaseNotes(case_id);
					return;
				}
			});
		}
		if (!blnProceed) {
			return;
		}
		var kase = kases.get(case_id);
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		var type = "";
		notes = new NoteCollection([], { case_id: case_id });
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);
		notes.fetch({
			success: function(data) {
				$('#kase_content').html(new note_listing_view({model: data}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});	
			
	},
	kaseNote: function (case_id, note_id) {		
		readCookie();
		var self = this;
		var blnProceed = true;
		//get the kase if url direct referrer
		if (typeof kases == "undefined" || !blnKasesFetched) {
			blnProceed = false;
			
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					var kase = data.get(case_id);
					if (typeof kase == "undefined") {
						//case does not exist, get out
						document.location.href = "#";
						return;
					}
					kase.set("header_only", true);
					$('#content').html(new kase_view({model: kase}).render().el);
					self.kaseNote(case_id, note_id);
					return;
				}
			});
		}
		if (!blnProceed) {
			return;
		}
		var kase = kases.get(case_id);
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		//initial call
		var type = "";
		if (note_id=="") {
			note_id = -1;
		} 
		var note = new Note({id: note_id});
		note.fetch({
			success: function (data) {
				//alert(note_id);
				data.set("case_id", case_id);
				data.set("case_uuid", kase.get("uuid"));
				noteKaseView = new notes_view({el: $("#kase_content"), model:data}).render();
				$("#kase_content").addClass("glass_header_no_padding");
				showEditRow();
			}
		});
			
	},
	kaseUsers: function () {		
		readCookie();
		var self = this;
		
		//initial call
		var type = "";
		users = new UserCollection([]);
		users.fetch({
			success: function(data) {
				$('#content').html(new user_listing_view({model: data}).render().el);
				$("#content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});				
	},
	kaseUser: function (user_id) {		
		readCookie();
		var self = this;
		
		if (user_id=="") {
			user_id = -1;
		}
		$('#content').html(new dashboard_user_view({model: {user_id: user_id}}).render().el);
	},
	userEmail: function (user_id) {		
		readCookie();
		var self = this;
		
		if (user_id=="") {
			user_id = -1;
		} 
				//let's get the specific corporation by id.  if id < 0, we will get an empty corporation
		var email = new Email({user_id: user_id});
		email.fetch({
			success: function (email) {
				email.set("gridster_me", true);
				$('#user_panel').html(new email_view({model: email}).render().el);				
				$("#user_panel").addClass("glass_header_no_padding");
			}
		});	
	},
	kasePartie: function (case_id, corporation_id, corporation_type) {		
		readCookie();
		var self = this;
		if (corporation_id < 0 && corporation_type=="new") {
			self.kaseNewPartie(case_id);
			return;
		}
		var blnProceed = true;
		//get the kase
		
		if (typeof kases == "undefined" || !blnKasesFetched) {
			blnProceed = false;
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					var kase = data.get(case_id);
					if (typeof kase == "undefined") {
						//case does not exist, get out
						document.location.href = "#";
						return;
					}
					kase.set("header_only", true);
					$('#content').html(new kase_view({model: kase}).render().el);
					self.kasePartie(case_id, corporation_id, corporation_type);
					return;
				}
			});
		}
		if (!blnProceed) {
			return;
		}
		var kase = kases.get(case_id);
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
		}
		//let's get the specific corporation by id.  if id < 0, we will get an empty corporation
		var corporation = new Corporation({id: corporation_id, case_id: case_id, type:corporation_type});
		corporation.fetch({
			success: function (corp) {
				//make sure the data is there, if not there, redirect as new
				if (corp.get("company_name")=="" && corporation_id > 0) {
					self.kasePartie(case_id, -1, corporation_type);
					return;
				}
				//if this is a new partie, the query will return partie_type values
				corp.set("partie", corporation_type.capitalize());
				corp.set("case_id", kase.get("id"));
				corp.set("case_uuid", kase.get("uuid"));
				corp.set("gridster_me", true);
				corp.set("show_buttons", true);
				corp.adhocs.fetch({
					success:function (data) {
						$('#kase_content').html(new partie_view({model: corp, collection: data}).render().el);				
						$("#kase_content").addClass("glass_header_no_padding");
						showEditRow();
					}
				});
				
			}
		});
	},
	kaseNewPartie: function (case_id) {		
		readCookie();
		var self = this;
		var blnProceed = true;
		//get the kase
		
		if (typeof kases == "undefined" || !blnKasesFetched) {
			blnProceed = false;
			kases = new KaseCollection();
			kases.fetch({
				success: function(data) {
					var kase = data.get(case_id);
					if (typeof kase == "undefined") {
						//case does not exist, get out
						document.location.href = "#";
						return;
					}
					self.kaseNewPartie(case_id);
					return;
				}
			});
		}
		if (!blnProceed) {
			return;
		}
		var kase = kases.get(case_id);
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		
		//get the parties for the case, and then 
		parties = new Parties([], { case_id: case_id, case_uuid: kase.get("uuid") });
		parties.fetch({
			success: function(parties) {
				kase.set("header_only", true);
				$('#content').html(new kase_view({model: kase}).render().el);
					
				//$('#kase_content').html(new partie_view({model:kase, parties: parties}).render().el);
				newPartyKaseView = new parties_new_view({el: $("#kase_content"), model:kase, parties: parties}).render();
				$("#kase_content").addClass("glass_header_no_padding");		
				hideEditRow();
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
			occurences = new OccurenceCollection({case_id: id});
			occurences.fetch({
				success: function (data) {
					occurencesView = new kaseOccurencesView({el: $("#content"), collection: occurences}).render();			
				}
			});			
		} else {
			$('#content').html(new kase_occurences_view({collection: occurences}).render().el);
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
			occurences = new OccurenceCollection({case_id: id});
			//go get the data from server
			occurences.fetch({
				success: function (data) {
					// Note that we could also 'recycle' the same instance of EmployeeFullView
					// instead of creating new instances
					$('#content').html(new event_listing_view({model: data}).render().el);
					
				}
			});
		} else {
			$('#content').html(new event_listing_view({model: occurences}).render().el);
		}
		
        this.kaseNavBarView.select_menu('todo-list-menu');
    },
	
	recentKases: function() {
		var recent_kases = new KaseRecentCollection();
		recent_kases.fetch({
			success: function(recent_kases) {
				$('#kases_recent').html(new kase_list_category_view({model: recent_kases}).render().el);
				return;
			}
		});
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
					$('#occurences_recent').html(new event_list_view({model: data}).render().el);
					
					//kalendar
					self.summaryKalendar();
				}
			});
		} else {
			$('#occurences_recent').html(new event_list_view({model: recent_occurences}).render().el);
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
templateLoader.load(["dashboard_view", "import_view", "kase_nav_bar_view", "kase_nav_left_view", "kase_list_category_view", "kase_home_view", "kase_listing_view",  "kase_summary_view", "kase_view", "kase_header_view", "applicant_view", "person_view", "kai_view", "dialog_view", "event_list_view", "event_list_item_view", "document_upload_view", "dashboard_injury_view", "injury_view", "bodyparts_view", "note_listing_view", "notes_view", "parties_view", "partie_listing_view", "parties_new_view", "partie_view", "partie_cards_view", "user_listing_view", "dashboard_user_view", "user_view", "email_view", "signature_view", "dashboard_person_view"],
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
            window.location.replace('#logout');
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
function gridsterById (gridster_id) {
	var gridster = [];
	$(function () {
		gridster[0] = $("#" + gridster_id + " ul").gridster({
			namespace: "#" + gridster_id,
			widget_margins: [2, 2],
			widget_base_dimensions: [230, 44],
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
					//console.log(JSON.stringify(serial));
				}
			}
			}).data('gridster');
		$("#" + gridster_id).fadeIn();
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
				widget_base_dimensions: [230, 40],
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
						//console.log(JSON.stringify(serial));
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
				widget_base_dimensions: [126.5, 45],
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
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			
			gridster[1].options.max_rows = 1;
			//$("#gridster_flat ul").css("z-index", "9");
			$("#gridster_flat").fadeIn();
		}
		//kai gridster
		if (gridster_index==2) {
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
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_kai").fadeIn();
		}
		//dialog gridster
		if (gridster_index==3) {
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
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_dialog").fadeIn();
		}
		//parties gridster
		if (gridster_index==4) {
			gridster[4] = $("#gridster_parties ul").gridster({
				namespace: '#gridster_parties',
				widget_margins: [5, 5],
				widget_base_dimensions: [230, 35],
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
						var serial = gridster[4].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_parties").fadeIn();
		}
	
		//notes gridster
		if (gridster_index==5) {
			gridster[5] = $("#gridster_notes ul").gridster({
				namespace: '#gridster_notes',
				widget_margins: [7, 5],
				widget_base_dimensions: [340, 55],
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
						var serial = gridster[5].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_notes").fadeIn();
		}
		
		//parties_new gridster
		if (gridster_index==6) {
			gridster[6] = $("#gridster_parties_new ul").gridster({
				namespace: '#gridster_parties_new',
				widget_margins: [5, 5],
				widget_base_dimensions: [175, 35],
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
						var serial = gridster[6].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_parties_new").fadeIn();
		}
	
		//parties_cards gridster
		if (gridster_index==7) {
			gridster[7] = $("#gridster_parties_cards ul").gridster({
				namespace: '#gridster_parties_cards',
				widget_margins: [5, 5],
				widget_base_dimensions: [275, 180],
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
						var serial = gridster[7].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_parties_cards").fadeIn();
		}
		//notes gridster
		if (gridster_index==8) {
			gridster[8] = $("#gridster_user ul").gridster({
				namespace: '#gridster_user',
				widget_margins: [2, 2],
				widget_base_dimensions: [230, 40],
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
						var serial = gridster[8].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_user").fadeIn();
		}
		//person gridster
		if (gridster_index==9) {
			gridster[9] = $("#gridster_person ul").gridster({
				namespace: '#gridster_person',
				widget_margins: [2, 2],
				widget_base_dimensions: [55, 44],
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
						var serial = gridster[8].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_person").fadeIn();
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
function hideEditRow() {
	$(".edit_row .kase").fadeOut();
}
function showEditRow() {
	$(".edit_row .kase").fadeIn();
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
function updateNote(notes_value) {
	$("#noteInput").val(notes_value);
}
function deleteForm(event, subform) {
	var self = this;
	event.preventDefault(); // Don't let this button submit the form
	
	if (typeof subform == "undefined") {
		var form_name = $("#sub_category_holder").attr("class");
	} else {
		form_name = subform;
	}
	
	var id = $('.' + form_name + ' #table_id').val();
	$('.' + form_name + ' .alert-error').hide(); // Hide any errors on a new submit
        var url = '../api/' + form_name + '/delete';
		formValues = "id=" + id + "&table_name=" + form_name;

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
                if(data.error) {  // If there is an error, show the error messages
                    saveFailed(data.error.text);
                }
                else { // If not, send them back to the home page
					kases.remove(kases.get(id));
					document.location.href = "#kases";
                }
            }
        });
}
function deleteElement(event, id, form_name) {
	//empty the cache, content is going to need to be fetched
	resetCurrentContent(form_name);
	
	var self = this;
	event.preventDefault(); // Don't let this button submit the form
	var url = '../api/' + form_name + '/delete';
	formValues = "id=" + id + "&table_name=" + form_name;

	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not
				//hide the grid containing the element
				$("#partie_nameGrid_" + id).fadeOut();
			}
		}
	});
}
function addForm(event, subform, api_url) {
	var self = this;
	event.preventDefault(); // Don't let this button submit the form
	
	if (typeof subform == "undefined") {
		var form_name = $("#sub_category_holder").attr("class");
	} else {
		form_name = subform;
	}
	
	if (typeof api_url == "undefined") {
		api_url = form_name.toLowerCase();
	}
	
	//toggle the input/span
	
	
	$blnValid = $("#" + form_name + "_form").parsley('validate');
	//for now
	//$blnValid = true;
	var id = $("." + form_name + " #table_id").val();
	if (id < 0) {
		//special case for person
		if (form_name=="person") {
			if ($blnValid) {
				//we need to save the additional info as well
				$blnValid = $("#kai_form").parsley('validate');
			}
		}
	}
	//get out if invalid			
	if (!$blnValid) {
		$("." + form_name + " .alert-warning").show();
		//$("." + form_name + " .alert-text").html("Please fill in the required fields in the correct format.");
		$("." + form_name + " .alert-warning").html("*");
		return;
	}
	
	$( "." + form_name + " .reset" ).trigger( "click" );
	
	$("." + form_name + " .alert-warning").hide();
	$("." + form_name + " .alert-error").hide(); // Hide any errors on a new submit
	
	var id = $("." + form_name + " #table_id").val();
	if (id > 0) {
		updateForm(event, subform, api_url);
		return;
	}
	
	$("." + form_name + " #gifsave").show();
	var url = "../api/" + api_url + "/add";
	var formValues = $("#" + form_name + "_form").serialize();
	var find = "Input";
	var regEx = new RegExp(find, 'g');
	formValues = formValues.replace(regEx, '');
	
	//special case for person
	if (form_name=="person") {
		//we need to save the additional info as well
		var additionalFormValues = $("#kai_form").serialize();
		additionalFormValues = additionalFormValues.substr(additionalFormValues.indexOf("titleInput"));
		additionalFormValues = additionalFormValues.replace(regEx, '');
		formValues += "&" + additionalFormValues;
	}
	//return;
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				console.log(data.error.text);
				saveFormFailed(data.error.text);
			}
			else { 
				// redraw the screen
				var arrValues = formValues.split("&");
				for(var i =0, len = arrValues.length; i < len; i++) {
					var thevalue = arrValues[i];
					var arrValuePair = thevalue.split("=");
					field_name = arrValuePair[0];
					fieldvalue = arrValuePair[1];
					//self.model.set(field_name, fieldvalue);
					
					if ($("." + form_name + " #" + field_name + "Input").length != 0) {
						if (field_name!="password") {
							//treat text fields one way
							if ($("." + form_name + " #" + field_name + "Input").prop("type")!= "select-one") {
								$("." + form_name + " #" + field_name + "Span").html(escapeHtml($("." + form_name + " #" + field_name + "Input").val()));
							} else {
								//text value of the drop down
								var dropdown_text = $("." + form_name + " #" + field_name + "Input :selected").text();
								dropdown_text = dropdown_text.split(" - ")[0];
								if (dropdown_text=="Select from List") {
									dropdown_text = "";
								}
								$("." + form_name + " #" + field_name + "Span").html(escapeHtml(dropdown_text));
							}
						}
					}
					//toggleFormEdit(form_name);
				}
				
				//insert the id, so we can update right away
				$("." + form_name + " #table_id").val(data.id);
				$("." + form_name + " #" + api_url + "_uuid").val(data.uuid);
				
				if (form_name == "kase") {
					//redirect to new applicant
					var new_kase = new Kase();
					$(".kase #table_id").val(data.id);
					//update the next case number
					customer_settings.case_number_next = data.case_number_next;
					
					new_kase.set("id", $(".kase #table_id").val());
					new_kase.fetch({
						success: function(kase) {
							kases.add(kase);
							if (!$(".kase .save").hasClass("hidden")) {
								$('#content').html(new kase_view({model: kase}).render().el);
							}
							document.location.href="#applicant/" + new_kase.id;
						}
					});
					return;
				}
				if (form_name == "person") {
					//check for an employer?
					var corporations = new Corporations([], { case_id: $(".person #case_id").val() });
					corporations.fetch({
						success: function(corporations) {
							//if we have no parties and no applicant
							employer_partie = corporations.findWhere({"type": "employer"});
				
							if (typeof employer_partie == "undefined") {
								//document.location.href="#parties/" + $(".person #case_id").val() + "/-1/employer";
								document.location.href="#kases/" + $(".person #case_id").val();
								
								return;
							}
						}
					});
					
				}
				//hide animation
				saveFormSuccessful(form_name);
			}
		}
	});
}
	
function updateForm(event, subform, api_url) {
	event.preventDefault(); // Don't let this button submit the form
	var self = this;
	
	if (typeof subform == "undefined") {
		var form_name = $("#sub_category_holder").attr("class");
	} else {
		form_name = subform;
	}
	if (typeof api_url == "undefined") {
		api_url = form_name.toLowerCase();
	}
	
	$('.' + form_name + ' #gifsave').show();
	var url = "../api/" + api_url + "/update";
	
	var formValues = $("#" + form_name + "_form").serialize();
	var find = "Input";
	var regEx = new RegExp(find, "g");
	formValues = formValues.replace(regEx, "");
	
	$.ajax({
		url:url,
		type:"POST",
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				console.log("error" + data.error.text);
				saveFormFailed(data.error.text);
			}
			else { // If not, go  back to read mode
				var arrValues = formValues.split("&");
				var ssn1 = "";
				var ssn2 = "";
				var ssn3 = "";
				for(var i =0, len = arrValues.length; i < len; i++) {
					var thevalue = arrValues[i];
					var arrValuePair = thevalue.split("=");
					field_name = arrValuePair[0];
					fieldvalue = arrValuePair[1];
					
					//ssn is triple set
					if (field_name.indexOf("ssn")==0) {
						if (field_name=="ssn1") {
							var ssn1 = String(fieldvalue);
						}
						if (field_name=="ssn2") {
							var ssn2 = String(fieldvalue);
						}
						if (field_name=="ssn3") {
							var ssn3 = String(fieldvalue);
						}
						continue;
					}
					
					if ($("." + form_name + " #" + field_name + "Input").length != 0) {
						//never show password
						if (field_name!="password") {
							//treat text fields one way
							if ($("." + form_name + " #" + field_name + "Input").prop("type")!= "select-one") {
								if (field_name!="note") {
									$("." + form_name + " #" + field_name + "Span").html(escapeHtml($("." + form_name + " #" + field_name + "Input").val()));
								} else {
									//the note is not escaped, except for script
									var note_value = $("." + form_name + " #" + field_name + "Input").val();
									var stringOfHtml = note_value;
									var html = $(stringOfHtml);
									html.find('script').remove();
									
									note_value = html.wrap("<div>").parent().html(); // have to wrap for html to get the outer element

									$("." + form_name + " #" + field_name + "Span").html(note_value);
								}
							} else {
								//text value of the drop down
								var dropdown_text = $("." + form_name + " #" + field_name + "Input :selected").text();
								dropdown_text = dropdown_text.split(" - ")[0];
								if (dropdown_text=="Select from List") {
									dropdown_text = "";
								}
								$("." + form_name + " #" + field_name + "Span").html(escapeHtml(dropdown_text));
							}
						}
					}
					//toggleFormEdit(form_name);
				}
				//ssn special case
				if (form_name=="person") {
					$("." + form_name + " #ssnSpan").html(ssn1 + ssn2 + ssn3);
				}
				saveFormSuccessful(form_name);
			}
		}
	});
}
function saveFormSuccessful(form_name) {
	$("." + form_name + " #gifsave").hide();
	$("." + form_name + " .alert-success").fadeIn(function() { 
		setTimeout(function() {
				$("." + form_name + " .alert-success").fadeOut();
			},1500);
	});
	
	//need to refresh recent kases
	//if (form_name == "Employer" || form_name == "person" || form_name == "kase") {
		//refresh recent kases
		kases.fetch({
			success: function (data) {
				//refresh recent kases
				$('#kases_recent').html(new kase_list_category_view({model: data}).render().el);
			}
		});
		if (form_name == "Employer"){
			if ($(".Employer")[0].parentElement.parentElement.id=="employer_holder") {
				//now go to partie screen
				var id = $("." + form_name + " #case_id").val();
				document.location.href = "#parties/" + id;
			}
		}
	//}
	
}
function saveFailed(text) {
	$('.alert-error').text(text)
	$(".alert-error").fadeIn(function() { 
		setTimeout(function() {
				$(".alert-error").fadeOut();
			},1500);
	});
	
}
function showAuto() {
	$("#autocomplete_completed").show();
}
function eamsComplete(form_name, obj_selector) {
	new AutoCompleteView({
		input: $("." + form_name + " #" + obj_selector),
		form_name:form_name,
		model: eams_carriers,
		onSelect: function (model) {
			//clean display in the search box
			$("." + form_name + " #" + obj_selector).val(model.display());
			//eams number - THERE MUST BE AN ADHOC eams_id FIELD ON THE FORM
			$("#" + form_name.toLowerCase() + "_eams_ref_numberInput").val(model.get("eams_ref_number"));
			//address
			var the_street = model.get("street_1");
			if (model.get("street_2")!="") {
				the_street += ", " + model.get("street_2");
			}
			$("." + form_name + " #full_addressInput").val(the_street + ", " + model.get("city") + ", " + model.get("state") + " " + model.get("zip_code"));
		}
	}).render();
}
function attorneyComplete(form_name, obj_selector) {
	new AutoCompleteView({
		input: $("." + form_name + " #" + obj_selector),
		form_name:form_name,
		model: attorney_searches,
		onSelect: function (model) {
			//clean display in the search box
			$("." + form_name + " #" + obj_selector).val(model.display());
			//eams number - THERE MUST BE AN ADHOC eams_id FIELD ON THE FORM
			$("#attorneyInput").val(model.get("attorney"));
			//address
		}
	}).render();
}
function zipLookup(event) {
	event.preventDefault();
	var element = event.currentTarget;
	var form_name = $("#sub_category_holder").attr("class");
	
	$.zipLookup(                                            
		$("." + form_name + " #" + element.id).val(),                                      
		function(cityName, stateName, stateShortName){     
			$("." + form_name + " #cityInput").val(cityName);            
			$("." + form_name + " #stateInput").val(stateName);  
			$("." + form_name + " #stateshortInput").val(stateShortName);
		},
		function(errMsg){                                   
			$('.message').html("Error: " + escapeHtml(errMsg));         
	});
}

var placeSearch, autocompleteEmployer, autocompleteCarrier, autocompleteDefense;
var componentForm = {
	street_number: 'short_name',
	route: 'long_name',
	locality: 'long_name',
	neighborhood: 'long_name',
	sublocality: 'long_name',
	administrative_area_level_1: 'short_name',
	country: 'long_name',
	postal_code: 'short_name'
};
function initializeGoogleAutocomplete(className) {
	var text_box = document.querySelectorAll("." + className + " #full_addressInput")[0];
	window["autocomplete" + className] = new google.maps.places.Autocomplete(text_box, { types: ['geocode'] });
  // Create the autocomplete object, restricting the search
  // to geographical location types.
	
  // populate the address fields in the form.
  google.maps.event.addListener(window["autocomplete" + className], 'place_changed', function() {
    setTimeout("fillInAddress('" + className + "')", 200);
	
	
  });
}

// [START region_fillform]
function fillInAddress(className) {
  // Get the place details from the autocomplete object.
	var place = window["autocomplete" + className].getPlace();
	
	/*
	var full_address = place.address_components[0].long_name + " " + place.address_components[1].long_name + ", " + place.address_components[2].long_name + ", " + place.address_components[5].short_name + " " + place.address_components[7].long_name;
	
	var text_box = document.querySelectorAll("." + className + " #full_addressInput")[0];
	text_box.value = full_address;
	console.log("address filled in");
  */
	for (var component in componentForm) {
		document.getElementById(component + "_" + className).value = '';
		document.getElementById(component + "_" + className).disabled = false;
	}
	
	// Get each component of the address from the place details
	// and fill the corresponding field on the form.
	var sublocality = "";
	for (var i = 0; i < place.address_components.length; i++) {
		var addressType = place.address_components[i].types[0];
		if (componentForm[addressType]) {
		  var val = place.address_components[i][componentForm[addressType]];
		  document.getElementById(addressType + "_" + className).value = val;
		  
		  if (addressType=="neighborhood") {
			//sublocality = val;  
		  }	  
		  if (addressType=="sublocality") {
			sublocality = val;  
		  }
		}
	}
	if (sublocality=="") {
		sublocality = document.getElementById("locality_" + className).value;
	}
	var text_box = document.querySelectorAll("." + className + " #full_addressInput")[0];
	var full_address = document.getElementById("street_number_" + className).value + " " + document.getElementById("route_" + className).value + ", " + sublocality + ", " + document.getElementById("administrative_area_level_1_" + className).value + " " + document.getElementById("postal_code_" + className).value;
	text_box.value = full_address;
	
	document.getElementById("city_" + className).value = sublocality;
	document.getElementById("street_" + className).value = document.getElementById("street_number_" + className).value + " " + document.getElementById("route_" + className).value;
	
	document.querySelectorAll("." + className + " #suiteInput")[0].focus();
}
// [END region_fillform]

// [START region_geolocation]
// Bias the autocomplete object to the user's geographical location,
// as supplied by the browser's 'navigator.geolocation' object.
function geolocate() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      var geolocation = new google.maps.LatLng(
          position.coords.latitude, position.coords.longitude);
      autocomplete.setBounds(new google.maps.LatLngBounds(geolocation,
          geolocation));
    });
  }
}