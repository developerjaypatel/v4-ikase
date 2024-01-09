window.account_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	events:{
        "click .account .delete":					"deleteAccountView",
		"click .account .save":						"saveAccount",
		"click .account .save_field":				"saveAccountViewField",
		"click .account .edit": 					"toggleEditViewAccount",
		"click .account .reset": 					"resetAccountForm",
		"keyup .account .input_class": 				"valueAccountViewChanged",
		"dblclick .account .gridster_border": 		"editAccountViewField",
		"click #return_summary":					"returnListView",
		"click #account_all_done":					"doTimeouts"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "account_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		
		var mymodel = this.model.toJSON();
		
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		return this;
    },
	doTimeouts: function() {
		var self = this;
		
		gridsterById("gridster_account");
		if(this.model.id=="" || this.model.id==-1){
			//editing mode right away
			this.model.set("editing", false);
 
			$(".account .edit").trigger("click"); 
			$(".account .delete").hide();
			$(".account .reset").hide();
			$(".account #table_id").val("");

		}
		$("#panel_title").html(this.model.get("page_title"));

		//get data from account_info, use .each to populate fields
		var mymodel = this.model.toJSON();
		if (mymodel.account_info!="") {
			var account_info = JSON.parse(mymodel.account_info);
			_.each( account_info, function(the_info) {
				if ($("#" + the_info.name).length == 1) {
					$("#" + the_info.name).val(the_info.value);
					the_info.name = the_info.name.replace("Input", "Span");
					$("#" + the_info.name).html(String(the_info.value).replaceTout("\r\n", "<br>"));
				}
			});
		}
		if (this.model.id < 0) {
			$("#account_bankInput").focus();
		}
		
		$(".form_label_vert").css("font-size", "1.1em");
		$(".form_label_vert").css("color", "white");
	},
	returnListView: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		document.location.href = "#bankaccount/list/" + this.model.get("account_type");
	},
	editAccountViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".account_" + field_name;
		}
		editField(element, master_class);
	},
	saveAccountViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".account_" + field_name;
		}
		element_id = element_id.replace("SaveLink", "");
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		if (master_class != "") {
			//hide all the subs
			$(".span_class" + master_class).toggleClass("hidden");
			$(".input_class" + master_class).toggleClass("hidden");
			//$(field_name + "Save").toggleClass("hidden");
			
			$(".span_class" + master_class).addClass("editing");
			$(".input_class" + master_class).addClass("editing");
			$(field_name + "Save").addClass("editing");
		} else {
			//get the parent to get the class
			var theclass = element.parentElement.parentElement.parentElement.parentElement.parentElement.className;
			var arrClass = theclass.split(" ");
			theclass = "." + arrClass[0] + "." + arrClass[1];
			field_name = theclass + " #" + element_id;
			
			//restore the read look
			editField($(field_name + "Grid"));
			
			var element_value = $(field_name + "Input").val();
			$(field_name + "Span").html(escapeHtml(element_value));
		}			
		
		//this should not redraw, it will update if no id
		this.addAccountView(event);
	
	},
	saveAccount:function (event) {
		event.preventDefault();
		
		//require at least a name
		if ($("#account_bankInput").val()=="") {
			$("#account_bankInput").css("border", "2px solid red");
			$("#account_bankInput").focus();
			return;
		}
		/*
		if ($("#account_numberInput").val()=="") {
			$("#account_numberInput").css("border", "2px solid red");
			$("#account_numberInput").focus();
			return;
		}
		*/
		var self = this;
		var url = "api/account/add";
		
		var arrAccount = $("#account_panel .input_class").serializeArray();
		//var arrForms = arrPlaintiffFinancial + arrDefendantFinancial;
		var account_type = self.model.get("account_type");
		formValues = "account_type=" + account_type;
		var account_id = "";
		if ($("#table_id").val() != "") {
			account_id = $("#table_id").val();
		} else {
			account_id = -1;
		}
		formValues += "&table_id=" + account_id;
		formValues += "&account_name=" + $("#account_bankInput").val();
		formValues += "&account_info=" + JSON.stringify(arrAccount);
		
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
					$(".button_row").html("<span style='background:green; color:white; padding:2px'>Saved &#10003;</span>");
					//toggleFormEdit("financial");
					setTimeout(function(){ 
						
						//$("#panel_title").css("color", "white");
						window.Router.prototype.listBankAccounts(account_type);
						window.history.replaceState(null, null, "#bankaccount/list/" + account_type);
						app.navigate("bankaccount/list/" + account_type, {trigger: false});
						
					}, 2000);
				}
			}
		});
	},
	addAccountView:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		if ($(".account #table_id").val()=="") {
			$(".account #table_id").val("-1")
		}
		addForm(event, "account");
		return;
    },
	deleteAccountView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "account");
		return;
    },
	toggleEditViewAccount: function(event) {
		event.preventDefault();
		if ($(".account #table_id").val()=="") {
			return;
		}
		
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		//$("#address").show();
		//get all the editing fields, and toggle them back
		$(".account .editing").toggleClass("hidden");
		$(".account .span_class").removeClass("editing");
		$(".account .input_class").removeClass("editing");

		$(".account .span_class").toggleClass("hidden");
		$(".account .input_class").toggleClass("hidden");
		$(".account .input_holder").toggleClass("hidden");
		$(".button_row.account").toggleClass("hidden");
		$(".edit_row.account").toggleClass("hidden");
		
	},
	resetAccountForm: function(event) {
		event.preventDefault();
		this.toggleEditViewAccount(event);
		//this.render();
		//$("#address").hide();
	},
	valueAccountViewChanged: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		element.style.border = "1px inset rgb(0, 0, 0)";
	}
})
window.account_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click #new_account_button":				"newAccount",
		"click .review_transactions":				"reviewTransactions",
		"click .review_kases":						"reviewKases",
		"click .review_adjustments":				"reviewAdjustments",
		"click .delete_icon":						"confirmdeleteAccount",
		"click .delete_yes":						"deleteAccount",
		"click .delete_no":							"canceldeleteAccount",
		"click .add_check":							"newPayment",
		"click .account_display":					"displayAccountBalance",
		"click .account_pending":					"listAccountPendingRequests",
		"click .add_adjustment":					"newAdjustment"
	},
    render:function () {		
		if (typeof this.template != "function") {
			var view = "account_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}
		var self = this;
		
		var mymodel = this.model.toJSON();
		var accounts = this.collection.toJSON();
		_.each( accounts, function(account) {
			if (account.account_info!="") {
				var jdata = JSON.parse(account.account_info);
				account.bank = jdata[0].value;
				account.account_number = jdata[1].value;
				account.branch = jdata[5].value;
				account.account_holder = jdata[4].value;
			}
		});
		
		var display_account_type = mymodel.account_type;
		if (display_account_type=="operating") {
			display_account_type = "cost";
		}
		$(this.el).html(this.template({accounts: accounts, account_type: mymodel.account_type, display_account_type: display_account_type, page_title: mymodel.page_title}));
		
		tableSortIt("account_listing");
		
		if (typeof this.model.get("blnShowRegister")=="undefined") {
			this.model.set("blnShowRegister", false);
		}
		
		if (this.model.get("blnShowRegister")) {
			setTimeout(function() {
				$(".review_transactions").trigger("click");
			}, 1111);
		}
		if (mymodel.account_type=="operating") {
			setTimeout(function() {
				self.getOperatingBalance();
			}, 1500);
		}
		
		return this;
    },
	getOperatingBalance: function() {
		var url = "api/account/balanceall/" + $("#operating_account_id").val();
		var account_name = "Cost Trust";
		
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				if (typeof data.deposits != "undefined") {
					$("#deposits_cell").html("$" + formatDollar(data.deposits));
					$("#withdrawals_cell").html("$" + formatDollar(data.withdrawals));
					$("#pendings_cell").html("$" + formatDollar(data.pendings));
					$("#balance_cell").html("$" + formatDollar(data.balance));
					var available = Number(data.balance) - Number(data.pendings);
					if (available > 0) {
						$("#available_cell").html("$" + formatDollar(available));
						$("#available_cell").css("background", "#1453b3;");
					} else {
						$("#available_cell").html("($" + formatDollar(available) + ")");
						$("#available_cell").css("background", "orange");
					}
				}
			}
		});
	},
	listAccountPendingRequests: function(event) {
		event.preventDefault();
		
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		
		var account_type = arrID[0];
		var account_id = arrID[arrID.length - 1];
		
		var row = "account_balance_row_" + account_id;
		var holder = "account_balance_" + account_id;
		
		$('#' + holder).html(loading_image);
		var checkrequests = new CheckRequestsCollection({approved: "P", account: account_type});
		
		checkrequests.fetch({
			success: function(data) {
				var reqkase = new Backbone.Model();
				reqkase.set("holder", "#" + holder);
				reqkase.set("account_type", account_type);
				if (account_type=="operating") {
					account_type = "cost trust";
				}
				reqkase.set("page_title", "Pending Check Requests - " + account_type.capitalizeWords());
				reqkase.set("embedded", false);
				$('#' + holder).html(new checkrequest_listing_view({collection: data, model: reqkase}).render().el);
				$('.' + row).fadeIn();
			}
		});
	},
	displayAccountBalance: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		
		var account_type = arrID[0];
		var account_id = arrID[arrID.length - 1];
		
		var row = "account_balance_row_" + account_id;
		var holder = "account_balance_" + account_id;
		
		var url = "api/account/displaybalance/" + account_id;
		$(".account_summary_row").fadeOut();
		
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				
				var mymodel = new Backbone.Model();
				mymodel.set(data);
				mymodel.set("holder", holder);
				$('#' + holder).html(new trust_display_view({model: mymodel}).render().el);
				$('.' + row).fadeIn();
			}
		});
	},
	newPayment: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		
		var account_type = arrID[1];
		var account_id = arrID[arrID.length - 1];
		
		this.newCheck("IN", account_id, account_type);
	},
	newCheck:function (ledger, account_id, account_type) {
		if (blnNewCheck) {
			return;
		}
		blnNewCheck = true;
		
		composeCheck("new_check_-1", ledger, "", {}, account_id, account_type, -2);
		
		var self = this;
		setTimeout(function() {
			blnNewCheck = false;
		}, 1200);
    },
	newAdjustment: function(event) {
		if (blnNewAdjustment) {
			return;
		}
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		
		var account_id = arrID[arrID.length - 1];
		
		blnNewAdjustment = true;
		var element_id = "new_adjustment_-1";

		composeAdjustment(element_id, account_id);
		
		var self = this;
		setTimeout(function() {
			blnNewAdjustment = false;
		}, 1200);
	},
	reviewAdjustments: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var account_id = element.id.split("_")[1];
		var account_type = this.model.get("account_type");
		
		//hide any adjustments lookup
		$(".account_adjustments_row_" + account_id).fadeOut();
		$(".account_adjustments_" + account_id).html("");
		
		var url = "api/account/adjustments/" + account_id;
		
		var row = "account_kases_row_" + account_id;
		var holder = "account_kases_" + account_id;
		
		var blnBalance = ($("#account_balance_" + account_id).html()!="");
		$(".account_summary_row").hide();
		if (blnBalance) {
			$(".account_balance_row_" + account_id).show();
		}
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				var mymodel = new Backbone.Model({
					"holder": holder, 
					"account_id": account_id,
					"account_type": account_type,
					"embedded": true,
					"page_title": "Adjustments"
				});
	
				$('#' + holder).html(new adjustment_listing_view({collection: data, model: mymodel}).render().el);
				$('.' + row).fadeIn();
			}
		});
	},
	reviewKases: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var account_id = element.id.split("_")[1];
		var account_type = this.model.get("account_type");
		
		var row = "account_kases_row_" + account_id;
		var holder = "account_kases_" + account_id;
				
		var url = "api/account/cases/" + account_id;
		
		var blnBalance = ($("#account_balance_" + account_id).html()!="");
		$(".account_summary_row").hide();
		if (blnBalance) {
			$(".account_balance_row_" + account_id).show();
		}
		
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				var mymodel = new Backbone.Model({
					"holder": holder, 
					"account_id": account_id,
					"account_type": account_type,
					"embedded": true,
					"page_title": "Kases"
				});
	
				$('#' + holder).html(new account_kases_listing_view({collection: data, model: mymodel}).render().el);
				$('.' + row).fadeIn();
			}
		});
	},
	reviewTransactions: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var account_id = element.id.split("_")[1];
		var account_type = this.model.get("account_type");
		
		var row = "account_checks_row_" + account_id;
		var holder = "account_checks_" + account_id;
		
		var blnBalance = ($("#account_balance_" + account_id).html()!="");
		$(".account_summary_row").hide();
		if (blnBalance) {
			$(".account_balance_row_" + account_id).show();
		}
		
		var account_checks = new ChecksCollection([], { case_id: "", ledger: "", account_id: account_id });
		account_checks.fetch({
			success: function(data) {
				var mymodel = new Backbone.Model({
					"holder": holder, 
					"account_type": account_type,
					"embedded": true,
					"page_title": account_type + " Transactions"
				});
	
				$('#' + holder).html(new check_listing_view({collection: data, model: mymodel}).render().el);
				$('.' + row).fadeIn();
			}
		});	
	},
	newAccount: function(event) {
		event.preventDefault();
		var account_type = this.model.get("account_type");
		window.Router.prototype.newBankAccount(account_type);
		window.history.replaceState(null, null, "#bankaccount/new/" + account_type);
		app.navigate("bankaccount/new/" + account_type, {trigger: false});
	}
});
window.account_selection_view = Backbone.View.extend({
	initialize:function () {
		
    },
	events:{
		"change .account_select":							"showSaveSaveLink",
		"click .account_selection .save_field":				"saveAccountField",
		"click .new_trust_account":							"newTrustAccount",
		"click .new_operating_account":						"newOperatingAccount",
		"click .dont_use_account":							"dontUseAccount",
		"click .add_check":									"newPayment",
		"click .review_transactions":						"reviewTransactions",
        "click #account_selection_done":					"doTimeouts"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "account_selection_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		
		var mymodel = this.model.toJSON();
		
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		return this;
	},
	doTimeouts: function() {
		var self = this;
		var kase_type = this.model.get("case_type");
		
		//gridsterById("gridster_account_selection");
		var blnWCAB = isWCAB(kase_type);
		var blnSS = (kase_type.indexOf("social_security") == 0 || kase_type=="SS");
		var blnImm = (kase_type == "immigration");
		
		if (!blnWCAB && !blnSS && !blnImm) {
			//personal injury only
			this.lookupCaseAccount("trust");
		} else {
			$("#trust_accountGrid").hide();
			$("#new_trust_account").hide();
		}
		this.lookupCaseAccount("operating");
	},
	reviewTransactions: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var account_type = arrID[0];
		
		var case_id = current_case_id;
		var account_id = $("#" + account_type + "_accountInput").val();
		
		if (account_id=="") {
			return;
		}
		var div = account_type + "_account_checks_div";
		var holder = account_type + "_account_checks";
		
		if ($("#" + div).css("display")=="none") {
			var account_checks = new ChecksCollection([], { case_id: case_id, ledger: "", account_id: account_id });
			account_checks.fetch({
				success: function(data) {
					var mymodel = new Backbone.Model({
						"holder": holder, 
						"account_type": account_type,
						"embedded": true,
						"blnShowMemo": false,
						"page_title": account_type + " Transactions"
					});
		
					$('#' + holder).html(new check_listing_view({collection: data, model: mymodel}).render().el);
					$('#' + div).fadeIn();
				}
			});
		} else {
			$('#' + holder).html("");
			$('#' + div).fadeOut();
		}
	},
	showSaveSaveLink: function(event) {
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var account = arrID[0];
		
		$("#new_" + account + "_account").hide();
		$("#operating_accountDoNotUse_holder").hide();
		$("#" + account + "_accountSaveLink").show();
		
	},
	newTrustAccount: function(event) {
		event.preventDefault();
		document.location.href = "#bankaccount/new/trust";
	},
	newOperatingAccount: function(event) {
		event.preventDefault();
		document.location.href = "#bankaccount/new/operating";
	},
	dontUseAccount: function(event) {
		var self = this;
		
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var account_type = arrID[0];
		
		if (element.checked) {
			var url = "api/account/detach";
			var formValues = "case_id=" + this.model.get("case_id");
			formValues += "&account_type=" + account_type;
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						$("#add_" + account_type + "_check").hide();
						$("#" + account_type + "_accountInput").html("<option value='' selected>No " + account_type.capitalize() + " Account</option>");
						$("#new_" + account_type + "_holder").html("<span style='background:lime; color:black; padding:2px'>Saved&nbsp;&#10003;</span>");
						setTimeout(function() {
							//$("#new_" + account_type + "_holder").html("");
							self.render();
						}, 2500);
					}
				}
			});
		} else {
			//clear out the no_
			var url = "api/account/clear";
			var formValues = "case_id=" + this.model.get("case_id");
			formValues += "&account_type=" + account_type;
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						$("#" + account_type + "_accountInput").html("");
						//fresh lookup
						self.lookupCaseAccount(account_type);
					}
				}
			});
		}
	},
	newPayment: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		
		var account_type = arrID[1];
		var account_id = $("#" + account_type + "_accountInput").val();
		
		this.newCheck("IN", account_id, account_type);
	},
	newCheck:function (ledger, account_id, account_type) {
		if (blnNewCheck) {
			return;
		}
		blnNewCheck = true;
		
		composeCheck("new_check_-1", ledger, "", {}, account_id, account_type);
		
		var self = this;
		setTimeout(function() {
			blnNewCheck = false;
		}, 1200);
    },
	saveAccountField: function(event) {
		var self = this;
		event.preventDefault();
		
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var account = arrID[0];
		
		$("#new_" + account + "_holder").html("Saving ...");
		
		var url = "api/account/attach";
		var formValues = "case_id=" + this.model.get("case_id");
		formValues += "&account_id=" + $("#" + account + "_accountInput").val();
		formValues += "&account_type=" + account;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#new_" + account + "_holder").html("<span style='background:lime; color:black; padding:2px'>Saved&nbsp;&#10003;</span>");
					setTimeout(function() {
						//$("#new_" + account + "_holder").html("");
						//$("#add_" + account + "_check").fadeIn();
						self.render();
					}, 2500);
				}
			}
		});
	},
	lookupCaseAccount: function(account_type) {
		var self = this;
		//lookup if the case has a trust account attached
		var case_id = this.model.get("case_id");
		
		if (case_id=="") {
			return;
		}
		var account =  new Account({"case_id": case_id, "account_type": account_type});
		account.fetch({
			success: function (data) {
				//now show the drop with accounts, selected if already chosen
				self.showBankAccounts(data.toJSON().id, account_type);
			}
		});
	},
	showBankAccounts: function(account_id, account_type) {
		var self = this;
		
		var accounts = new AccountCollection({"account_type": account_type});
		accounts.fetch({
			success: function(data) {
				//create options for dropdown
				var accts = data.toJSON();
				var arrOptions = [];
				
				var option = "<option value=''>Select from " + account_type.capitalizeWords() + " Accounts</option>";
				arrOptions.push(option);
				var accts_number = accts.length;
				var blnSelected = false;
				//var account_balance = "";
				if (accts_number > 0) {
					_.each( accts, function(account) {
						if (account.account_info!="") {
							var jdata = JSON.parse(account.account_info);
							account.bank = jdata[0].value;
							account.account_number = jdata[1].value;
						}
						var selected = "";
						if (account.id==account_id) {
							selected = " selected";
							blnSelected = true;
							//account_balance = account.account_balance;
						}
						var option = "<option value='" + account.id + "'" + selected + ">" + account.bank + " :: " + account.account_number + "</option>";
						arrOptions.push(option);
					});
					
					//show the account selector
					$("#" + account_type + "_accountInput").append(arrOptions.join(""));

					if (blnSelected) {
						$("#add_" + account_type + "_check").show();
						$("#" + account_type + "_transactions_link_holder").show();
					}
					self.model.set("blnDetached", false);
					
					var url = "api/account/balance/" + current_case_id + "/" + account_type;
					$.ajax({
						url:url,
						type:'GET',
						dataType:"json",
						success:function (data) {
							data.available = Number(data.balance) - Number(data.pendings);
							data.available_background = "black";
							if (data.available <= 0) {
								data.available = "$(" + formatDollar(data.available) + ")";
								data.available_background = "orange";
							} else {
								data.available = "$" + formatDollar(data.available);
							}
							setTimeout(function() {
								var ledger = "<table>";
								if ($("#" + account_type + "_accountInput").val()!="no_" + account_type) {
									ledger += "<tr><td><span style='font-weight:bold'>Balance:</span></td><td align='right' nowrap><a id='trust_transactions_" + current_case_id + "_" + account_id + "' class='review_transactions white_text' style='cursor:pointer' title='Click to Review " + account_type.capitalize() + " Account Transactions'>$" + formatDollar(data.balance) + "</a></td></tr>";
									ledger += "<tr><td><span style='font-weight:bold'>Transfers:</span></td><td align='right' nowrap style='background:red'>($" + formatDollar(data.transfers) + ")</td></tr>";
									ledger += "<tr><td><span style='font-weight:bold'>Pending:</span></td><td align='right' nowrap>$" + formatDollar(data.pendings) + "</td></tr>";
									ledger += "<tr><td><span style='font-weight:bold; background:" + data.available_background + "'>Available:</span></td><td align='right' nowrap><span style='font-weight:bold; background:" + data.available_background + "'>" + data.available + "</span></td></tr>";
								}
								ledger += "<tr><td><span style='font-weight:bold'>Pre-Bill:</span></td><td align='right' nowrap>$" + formatDollar(data.transfers) + "</td></tr>";
								ledger += "</table>";
								
								//ledger += "<tr><td><span style='font-weight:bold'>Billable:</span></td><td align='right' nowrap>$" + formatDollar(data.billable) + "</td></tr>";
								
								$("#kase_invoiced_holder").html("|&nbsp;<span style='font-weight:bold; color:white'>Invoiced:</span> $" + formatDollar(data.invoiced));
								$("#kase_invoiced_holder").fadeIn();
								$("#kase_billables_amount").html("$" + formatDollar(data.billable));
								$("#kase_billables_holder").fadeIn();

								
								$("#" + account_type + "_balance_holder").html(ledger);
								if (account_type=="operating") {
									if (!self.model.get("blnDetached")) {
										$("#" + account_type + "_balance_holder").fadeIn();
									}
								} else {
									$("#" + account_type + "_balance_holder").fadeIn();
								}
							}, 555);
						}
					});
				} else {
					$("#" + account_type + "_accountInput").hide();
					var html = "No " + account_type.capitalize() + " Account in iKase";
					html += '&nbsp;&nbsp;<button id="new_' + account_type + '_request" class="new_' + account_type + '_account btn btn-sm btn-primary" title="Click to create a new Trust Account" style="margin-top:-5px">Add ' + account_type.capitalize() + ' Account</button>';
					
					$("#" + account_type + "_accountDoNotUse_holder").html(html);
					$("#new_" + account_type + "_account").hide();
				}
				
				var blnChecked = false;
				if ($("#" + account_type + "_accountDoNotUse").length > 0) {
					blnChecked = document.getElementById(account_type + "_accountDoNotUse").checked;
				}
				
				if (!blnSelected && !blnChecked) {
					if (account_id == -1 && accts_number == 1) {
						//automatic add, unless marked as "no trust"
						var case_id = self.model.get("case_id");
						var url = "api/accountsno/" + case_id + "/" + account_type;
						$.ajax({
							url:url,
							type:'GET',
							dataType:"json",
							success:function (data) {
								if (data.detached) {
									if (account_type=="operating") {
										self.model.set("blnDetached", true);
									}
									if (document.getElementById(account_type + "_accountInput").options.length > 1) {
										document.getElementById(account_type + "_accountInput").options[1].selected = true;
										$("#" + account_type + "_accountSaveLink").show();
										
										//only 1 account per type
										$("#new_" + account_type + "_account").hide();
									} 
									$("#" + account_type + "_not_attached").show();
									if (account_type=="trust") {
										$("#" + account_type + "_accountSaveLink").trigger("click");
									}
								} else {
									document.getElementById(account_type + "_accountDoNotUse").checked = true;
									$("#add_" + account_type + "_check").hide();
									$("#" + account_type + "_accountInput").html("<option value='no_" + account_type + "' selected>No " + account_type.capitalize() + " Account</option>");
								}
							}
						});
					}
				}
				if (blnSelected) {
					$("#" + account_type + "_accountSaveLink").hide();
				}
			}
		});
	}
});
window.billables_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .review_billable":							"reviewBillables",
		"click .review_transactions":						"reviewTransactions",
		"click .review_books":								"reviewBooks",
		"click .add_check":									"newPayment"
	},
    render:function () {		
		if (typeof this.template != "function") {
			var view = "billables_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}
		var self = this;
		
		var billables = this.collection.toJSON();
		var arrUserNickNames = [];
		 _.each( billables, function(billable) {
			/*
			if (billable.case_name == "") {
				billable.case_name = billable.case_number;
			}
			if (billable.case_name == "") {
				billable.case_name = billable.file_number;
			}
			
			if (billable.supervising_attorney_name=="" && isNaN(billable.supervising_attorney)) {
				billable.supervising_attorney_name = billable.supervising_attorney;
			}
			if (billable.attorney_name=="" && isNaN(billable.attorney)) {
				billable.attorney_name = billable.attorney;
			}
			if (billable.worker_name=="" && isNaN(billable.worker)) {
				billable.worker_name = billable.worker;
			}
			
			var arrAssigneds = [];
			if (billable.supervising_attorney_name!="") {
				arrAssigneds.push(billable.supervising_attorney_name);
			}
			if (billable.attorney_name!="") {
				arrAssigneds.push(billable.attorney_name);
			}
			if (billable.worker_name!="") {
				arrAssigneds.push(billable.worker_name);
			}
			*/
			
			var dollar = formatDollar(billable.trust_balance);
			if (Number(billable.trust_balance) > 0) {
				billable.trust_checks = '<a id="trust_transactions_' + billable.case_id + '" class="review_transactions white_text" style="cursor:pointer" title="Click to Review Trust Account Transactions">$' + dollar + '</a>';
			} else {
				billable.trust_checks = '$' + dollar;
				//if not account
				if (billable.trust_account_id=="") {
					billable.trust_checks = '-';
				}
			}
			
			var dollar = formatDollar(billable.operating_balance);
			if (Number(billable.operating_balance) > 0) {
				billable.operating_checks = '<a id="operating_transactions_' + billable.case_id + '" class="review_transactions white_text" style="cursor:pointer" title="Click to Review Trust Account Transactions">$' + dollar + '</a>';
			} else {
				billable.operating_checks = '$' + dollar;
				//if not account
				if (billable.operating_account_id=="") {
					billable.operating_checks = '-';
				}
			}
			
			if (!isNaN(billable.supervising_attorney)) {
				if (typeof arrUserNickNames[billable.supervising_attorney] == "undefined") {
					var theworker = worker_searches.findWhere({"user_id": billable.supervising_attorney});
					if (typeof theworker != "undefined") { 
						var the_nickname = theworker.get("nickname").toUpperCase();
						var the_username = theworker.get("user_name").toUpperCase();
						arrUserNickNames[billable.supervising_attorney] = the_nickname;
						billable.supervising_attorney = the_nickname;
					}
				} else {
					billable.supervising_attorney = arrUserNickNames[billable.supervising_attorney];
				}
			}
			if (!isNaN(billable.attorney)) {
				if (typeof arrUserNickNames[billable.attorney] == "undefined") {
					var theworker = worker_searches.findWhere({"user_id": billable.attorney});
					if (typeof theworker != "undefined") { 
						var the_nickname = theworker.get("nickname").toUpperCase();
						var the_username = theworker.get("user_name").toUpperCase();
						arrUserNickNames[billable.attorney] = the_nickname;
						billable.attorney = the_nickname;
					}
				} else {
					billable.attorney = arrUserNickNames[billable.attorney];
				}
			}
			
			if (!isNaN(billable.worker)) {
				if (typeof arrUserNickNames[billable.worker] == "undefined") {
					var theworker = worker_searches.findWhere({"user_id": billable.worker});
					if (typeof theworker != "undefined") { 
						var the_nickname = theworker.get("nickname").toUpperCase();
						var the_username = theworker.get("user_name").toUpperCase();
						arrUserNickNames[billable.worker] = the_nickname;
						billable.worker = the_nickname;
					}
				} else {
					billable.worker = arrUserNickNames[billable.worker];
				}
			}
			 
			var arrAssigneds = [];
			if (billable.supervising_attorney!="") {
				arrAssigneds.push(billable.supervising_attorney);
			}
			if (billable.attorney!="") {
				arrAssigneds.push(billable.attorney);
			}
			if (billable.worker!="") {
				arrAssigneds.push(billable.worker);
			}
			billable.assigneds = arrAssigneds.join(",&nbsp;");
		});
		 
		var mymodel = this.model.toJSON();
		
		$(this.el).html(this.template({billables: billables, page_title: mymodel.page_title}));
		
		tableSortIt("billable_listing");
		
		return this;
    },
	reviewBillables: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		
		//alert(element.id);
		var case_id = element.id.split("_")[1];
		
		window.Router.prototype.kaseBillables(case_id);
		window.history.replaceState(null, null, "#kasebillables/" + case_id);
		app.navigate("kasebillables/" + case_id, {trigger: false});
		
	},
	reviewBooks: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		
		//alert(element.id);
		var case_id = element.id.split("_")[1];
		
		window.Router.prototype.kaseChecks(case_id);
		window.history.replaceState(null, null, "#payments/" + case_id);
		app.navigate("payments/" + case_id, {trigger: false});
	},
	reviewTransactions: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var account_type = arrID[0];
		var case_id = arrID[arrID.length - 1];
		var account_id = $("#" + account_type + "_id_" + case_id).val();
		
		var row = "billable_checks_row_" + case_id;
		var holder = "billable_checks_" + case_id;
		
		//hide any and all
		$(".billable_checks_row").fadeOut();
		if ($("." + row).css("display")=="none") {		
			var account_checks = new ChecksCollection([], { case_id: case_id, ledger: "", account_id: account_id });
			account_checks.fetch({
				success: function(data) {
					var mymodel = new Backbone.Model({
						"holder": holder, 
						"account_type": account_type,
						"embedded": true,
						"page_title": account_type + " Transactions"
					});
		
					$('#' + holder).html(new check_listing_view({collection: data, model: mymodel}).render().el);
					$('.' + row).fadeIn();
				}
			});
		} else {
			$('#' + holder).html("");
			$('.' + row).fadeOut();
		}
	},
	newPayment: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		
		var account_type = arrID[1];
		var case_id = 	arrID[arrID.length - 1];
		var account_id = $("#" + account_type + "_id_" + case_id).val();
		
		this.newCheck("IN", case_id, account_id, account_type);
	},
	newCheck:function (ledger, case_id, account_id, account_type) {
		if (blnNewCheck) {
			return;
		}
		blnNewCheck = true;
		//element_id, ledger, context, jsonInvoice, account_id, account_type, case_id
		composeCheck("new_check_-1", ledger, "", {}, account_id, account_type, case_id);
		
		var self = this;
		setTimeout(function() {
			blnNewCheck = false;
		}, 1200);
    }
});
window.account_kases_listing_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	events:{
		"click .review_transactions":								"reviewKaseTransactions",
		"click .review_books":										"reviewBooks",
		"click .review_tasks":										"reviewTasks",
		"click .review_notes":										"reviewNotes",
        "click #account_kases_listing_all_done":					"doTimeouts"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "account_kases_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		
		var account_kases = this.collection;
		var mymodel = this.model.toJSON();
		
		try {
			$(this.el).html(this.template({account_kases: account_kases, page_title: mymodel.page_title}));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		return this;
    },
	doTimeouts: function() {
	},
	reviewKaseTransactions: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var account_id = this.model.get("account_id");
		var case_id = element.id.split("_")[1];
		var account_type = this.model.get("account_type");
		
		var row = "account_checks_row_" + account_id;
		var holder = "account_checks_" + account_id;
		
		var blnBalance = ($("#account_balance_" + account_id).html()!="");
		$(".account_summary_row").hide();
		if (blnBalance) {
			$(".account_balance_row_" + account_id).show();
		}
				
		var account_checks = new ChecksCollection([], { case_id: case_id, ledger: "", account_id: account_id });
		account_checks.fetch({
			success: function(data) {
				var mymodel = new Backbone.Model({
					"case_id":case_id,
					"holder": holder, 
					"account_type": account_type,
					"embedded": true,
					"page_title": account_type + " Transactions"
				});
	
				$('#' + holder).html(new check_listing_view({collection: data, model: mymodel}).render().el);
				$('.' + row).fadeIn();
			}
		});
	},
	reviewBooks: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var case_id = element.id.split("_")[1];
		
		document.location.href = "#payments/" + case_id;
	},
	reviewTasks: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var case_id = element.id.split("_")[1];
		
		document.location.href = "#tasks/" + case_id;
	},
	reviewNotes: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var case_id = element.id.split("_")[1];
		
		document.location.href = "#notes/" + case_id;
	}
});
window.trust_display_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	events:{
		"change #starting_amount":								"showSaveStarting",
		"change #statement_date":								"showSaveStarting",
		"keyup #starting_amount":								"showSaveStarting",
		"click #save_starting_amount":							"saveStartingAmount",
		"click #close_display":									"closeDisplay",
		"click .list_cleared":									"listClearedByLedger",
		"click .list_uncleared":								"listUnclearedByLedger",
		"click .list_checks":									"listChecks",
        "click #trust_display_view_all_done":					"doTimeouts"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "trust_display_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		
		var mymodel = this.model.toJSON();
		
		if (mymodel.starting_statement_date=="0000-00-00") {
			mymodel.starting_statement_date = "";
		}
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		return this;
    },
	recalculateBalance: function() {
		var starting_amount = $("#starting_amount").val();
		var mymodel = this.model.toJSON();
		var balance = formatDollar(Number(starting_amount) + (Number(mymodel.total_cleared_receipts) + Number(mymodel.total_uncleared_receipts)) - (Number(mymodel.total_cleared_disburs) + Number(mymodel.total_uncleared_disburs)) - Number(mymodel.total_adjusted) + Number(mymodel.total_interest));
		
		$("#balance_cell").html("$" + balance);
	},
	showSaveStarting: function() {
		if ($("#starting_amount").val()=="" || $("#statement_date").val()=="") {
			$("#save_starting_holder").hide();
			return;
		}
		if ($("#save_starting_holder").css("display")=="none") {
			$("#save_starting_holder").fadeIn();
		}
		this.recalculateBalance();
	},
	saveStartingAmount: function() {
		$("#save_starting_amount").hide();
		$("#starting_amount_feedback").show();
		$("#starting_amount_feedback").html("Saving...");
		var id = $("#balance_account_id").val();
		var starting_amount = $("#starting_amount").val();
		var statement_date = $("#statement_date").val();
		
		var url = "api/account/starting";
		///account/starting
		var formValues = "id=" + id;
		formValues += "&starting_amount=" + starting_amount;
		formValues += "&statement_date=" + statement_date;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#starting_amount_feedback").html("<span style='background:lime; color: black; padding: 2px'>Saved&nbsp;&#10003;</span>");
					
					setTimeout(function() {
						$("#save_starting_holder").fadeOut(function() {
							$("#save_starting_amount").show();
							$("#starting_amount_feedback").html("");
						});
					}, 2500);
				}
			}
		});
	},
	closeDisplay: function() {
		var account_id = $("#balance_account_id").val();
		$(".account_balance_row_" + account_id).fadeOut(function() {
			$("#account_balance_" + account_id).html("");
		});
	},
	listChecks: function(event) {
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var ledger = "OUT";
		var source = arrID[arrID.length - 1].replace("disburs", "Disbursments");;
		if (source=="receipts") {
			ledger = "IN";
		}
		var account_id = $("#balance_account_id").val();
		
		var row = "account_checks_row_" + account_id;
		var holder = "account_checks_" + account_id;
		
		var blnBalance = ($("#account_balance_" + account_id).html()!="");
		$(".account_summary_row").hide();
		if (blnBalance) {
			$(".account_balance_row_" + account_id).show();
		}
		
		var all_checks = new ChecksCollection([], { account_id: account_id, ledger: ledger });
		all_checks.fetch({
			success: function(data) {
				var kase = new Backbone.Model();
				kase.set("holder", holder);
				kase.set("page_title", source.capitalize() + " :: All Check");
				$('#' + holder).html(new check_listing_view({collection: data, model: kase}).render().el);
				$('.' + row).fadeIn();
			}
		});
	},
	listClearedByLedger: function(event) {
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var ledger = "OUT";
		var source = arrID[arrID.length - 1].replace("disburs", "Disbursments");
		if (source=="receipts") {
			ledger = "IN";
		}
		var account_id = $("#balance_account_id").val();
		
		var row = "account_checks_row_" + account_id;
		var holder = "account_checks_" + account_id;
		
		var blnBalance = ($("#account_balance_" + account_id).html()!="");
		$(".account_summary_row").hide();
		if (blnBalance) {
			$(".account_balance_row_" + account_id).show();
		}
		
		var cleared_checks = new ChecksCollection([], { account_id: account_id, ledger: ledger, check_status: "C" });
		cleared_checks.fetch({
			success: function(data) {
				if (data.length > 0) {
					var kase = new Backbone.Model();
					kase.set("holder", holder);
					kase.set("page_title", source.capitalize() + " :: Cleared Check");
					$('#' + holder).html(new check_listing_view({collection: data, model: kase}).render().el);
					$('.' + row).fadeIn();
				} else {
					$('#' + holder).html("No " + source.capitalize() + " :: Cleared Checks Found");
					$('.' + row).fadeIn();
				}
			}
		});
	},
	listUnclearedByLedger: function(event) {
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var ledger = "OUT";
		var source = arrID[arrID.length - 1].replace("disburs", "Disbursments");
		if (source=="receipts") {
			ledger = "IN";
		}
		var account_id = $("#balance_account_id").val();
		
		var row = "account_checks_row_" + account_id;
		var holder = "account_checks_" + account_id;
		
		var blnBalance = ($("#account_balance_" + account_id).html()!="");
		$(".account_summary_row").hide();
		if (blnBalance) {
			$(".account_balance_row_" + account_id).show();
		}
		
		var uncleared_checks = new ChecksCollection([], { account_id: account_id, ledger: ledger, check_status: "U" });
		uncleared_checks.fetch({
			success: function(data) {
				if (data.length > 0) {				
					var kase = new Backbone.Model();
					kase.set("holder", holder);
					kase.set("page_title", source.capitalize() + " :: Uncleared Check");
					$('#' + holder).html(new check_listing_view({collection: data, model: kase}).render().el);
					$('.' + row).fadeIn();
				} else {
					$('#' + holder).html("No " + source.capitalize() + " :: Cleared Checks Found");
					$('.' + row).fadeIn();
				}
			}
		});
	},
	doTimeouts: function() {
		$("#trust_display_table tr").css("background", "black");
		$("#trust_display_table").css("background", "black");
		$("#trust_display_table td").css("padding", "10px");
		$("#trust_display_table th").css("padding", "10px");
	}
});