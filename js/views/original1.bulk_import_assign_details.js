window.bulk_import_assign_view = Backbone.View.extend({
	events:{
		"click .event .save": 								"addEvent",
		"click .invoice_items":								"newInvoice",
		"click #bulk_import_assign_all_done":				"doTimeouts"
    },
	render: function () {
		var self = this;
		if (typeof this.template != "function") {
			this.model.set("holder", "myModalBody");
			var view = "bulk_import_assign_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}
		if (typeof this.model.get("invoice") == "undefined") {
			this.model.set("invoice", false);
		}
		try {
			$(self.el).html(self.template(self.model.toJSON()));
		} 
		catch(err) {
			alert(err);
			
			return "";
		}
		setTimeout(function() {
			var theme = "event";
			if (self.model.get("invoice")) {
				theme = {
					theme: "event", 
					tokenLimit:1,
					onAdd: function(item) {
						$("#template_invoices_holder").show();
						
						self.lookupCaseAccount();
					}
				}
			}
			$("#case_idInput").tokenInput("api/kases/tokeninput", theme);
			
			if (typeof self.model.get("case_id")!="undefined") {
				var kase = kases.findWhere({"case_id": self.model.get("case_id")});
				if (typeof kase !="undefined") {
					$("#case_idInput").tokenInput("add", {
						id: kase.get("case_id"), 
						name: kase.get("case_name"),
						tokenLimit:1
					});
				}
			}
		}, 500);
		return this;
	},
	triggerEdit:function() {
		$(".event .edit" ).trigger( "click" );
		
	},
	lookupCaseAccount: function() {
		var self = this;
		//lookup if the case has a trust account attached
		var case_id = $("#case_idInput").val();
		
		if (case_id=="") {
			return;
		}
		var account =  new Account({case_id: case_id, "account_type" : "trust"});
		account.fetch({
			success: function (data) {
				//now show the drop with accounts, selected if already chosen
				self.showBankAccounts(data.toJSON().id);
			}
		});
	},
	showBankAccounts: function(account_id) {
		var self = this;
		var case_id = $("#case_idInput").val();
		if (case_id == "") {
			return;
		}
		if (account_id > -1) {
			var account =  new Account({"id": account_id}); //"case_id": case_id, "account_type": "trust"
			account.fetch({
				success: function (data) {
					//console.log(data);
					var bank = JSON.parse(data.toJSON().account_info)[0].value;
					$(".account_input").hide();
					$("#account_idSpan").html(bank);
					
					var option = "<option value='" + account_id + "' selected></option>";
					$("#account_idInput").append(option);
					$("#account_idInput").hide();
					$("#account_id_row").show();
					
					$("#template_invoices_holder").show();
				}
			});
		} else {
			var accounts = new AccountCollection({"account_type": "trust"});
			accounts.fetch({
				success: function(data) {
					//create options for dropdown
					var accts = data.toJSON();
					var arrOptions = [];
					
					var option = "<option value=''>Select from Trust Accounts</option>";
					arrOptions.push(option);
					var accts_number = accts.length;
					
					if (accts_number > 1) {
						_.each( accts, function(account) {
							var jdata = JSON.parse(account.account_info);
							account.bank = jdata[0].value;
							account.account_number = jdata[1].value;
							var selected = "";
							if (account.id==account_id || accts_number==1) {
								selected = " selected";
							}
							var option = "<option value='" + account.id + "'" + selected + ">" + account.bank + " :: " + account.account_number + "</option>";
							arrOptions.push(option);
						});
						
						//show the account selector
						$("#account_idInput").append(arrOptions.join(""));
					}
					if (accts_number == 1) {
						$("#account_idInput").hide();
						//$("#new_account_holder").show();
						var account = accts[0];
						
						var jdata = JSON.parse(account.account_info);
						bank = jdata[0].value;
						account_number = jdata[1].value;
						
						$(".account_input").hide();
						$("#account_idSpan").html(bank + " :: " + account_number);
						
						var option = "<option value='" + account.id + "' selected>" + bank + " :: " + account_number + "</option>";
						$("#account_idInput").append(option);
						$("#account_idInput").hide();
						$("#account_id_row").show();
					}
					if (accts_number == 0) {
						$("#account_idInput").hide();
						$("#new_account_holder").show();
					}
					$("#account_id_row").fadeIn();
				}
			});
		}
	},
	newInvoice: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var element_id = element.id;
		var arrID = element_id.split("_");;
		var document_id = arrID[arrID.length - 2];
		var kinvoice_id = arrID[arrID.length - 1];
		var case_id = $("#case_idInput").val();
		
		var kinvoice = new KInvoice({id: kinvoice_id});
		kinvoice.fetch({
			success: function (data) {
				var jdata = data.toJSON();
				if (jdata.template_name=="Billing Template") {
					$("#myModal4").modal("toggle");
					document.location.href = "#activity/" + case_id;
					setTimeout(function() {
						$("#invoice_activities").trigger("click");
					}, 1000);
				} else {
					var account_id = $("#account_idInput").val();
					//then compose letter, with kinvoice id in there somewhere
					var element_id = "edit_letter_" + case_id + "_" + jdata.parent_id + "_" + kinvoice_id;
					if (account_id!="") {
						element_id += "_" + account_id;
					}
					$("#myModal4").modal("toggle");
					setTimeout(function() {
						composeLetter(element_id, jdata);
					}, 1000);
				}
			}
		});
	},
	doTimeouts: function(event) {
		var self = this;
		if (self.model.get("invoice")) {
			$("#modal_save_holder").hide();
			//get all the invoices templates
			var kinvoices = new TemplateKInvoices({});
			kinvoices.fetch({
				success: function(data) {
					if (data.length > 0) {
						var jdata = data.toJSON();
						var arrButtons = [];
						_.each( jdata, function(datum) {
							//show button
							var button = '<div style="margin-bottom:5px"><input id="kinvoice_id_' + datum.kinvoice_id + '" name="kinvoice_id_' + datum.kinvoice_id + '" type="hidden" class="kinvoice_id_input document_input" value="' + datum.kinvoice_id + '" />';
							button += '<button class="invoice_items btn btn-primary" id="invoice_items_' + datum.document_id + '_' + datum.kinvoice_id + '" style="width:150px">' + datum.template_name + '</button></div>';
							arrButtons.push(button);
						});
						$("#template_invoices_holder").hide();
						var invoice_title = '<div style="font-size: 1.2em; margin-top: 5px; margin-bottom: 10px;">Select an Invoice Template below</div>';
						$("#template_invoices_holder").html(invoice_title + arrButtons.join(""));
					}
				}
			});
		}
	},
	assignKase:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "bulk_import_assign", "bulk_import_assign");
		return;
    }
});