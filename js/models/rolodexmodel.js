window.Contact = Backbone.Model.extend({
	urlRoot:"api/rolodex",
	initialize:function () {
	},
	defaults: {
		"display_name":"",
		"last_name_letter":"",
		"company_name_letter":""
	}
});

window.ContactCollection = Backbone.Collection.extend({
	initialize: function() {
	},
	url: function() {
		var api = 'api/rolodex';
		return api;
	},
	model: Contact,
	searchDB:function(key, blnByLetter) {
		if (typeof blnByLetter == "undefined") {
			blnByLetter = false;
		}
		key = key.trim();
		key = key.replaceAll("/", "-");
		key = key.replaceAll(" ", "_");
		var url = "api/rolodex/search/" + key;
		
		if (!blnByLetter) {
			if (key.length < 3) {
				return;
			}
		}
		var self = this;
		$.ajax({
			url:url,
			dataType:"json",
			success:function (data) {
				self.reset(data);
				key = key.replaceAll("_", " ");
				showRolodexResults(self, key);
			}
		});
	}
});
function showRolodexResults(my_contacts, key) {	
	$('#content').html(new rolodex_listing_view({collection: my_contacts}).render().el);
	//make sure the key is back in
	setTimeout(function(){
		$("#rolodex_searchList").focus();
		$("#rolodex_searchList").val(key);
	}, 600);

}