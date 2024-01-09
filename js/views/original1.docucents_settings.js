window.dccsetting_view = Backbone.View.extend({
	events: {
		"click .save_dccapi_key": "saveDCCAPIkey"
	},
	render: function () {
		var self = this;
		if (typeof this.template != "function") {
			var view = "dccsetting_view";
			var extension = "php";

			loadTemplate(view, extension, this);
			return "";
		}

		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch (err) {
			alert(err);

			return "";
		}

		//$(this.el).html(this.template(this.model.toJSON()));

		return this;
	},
	doTimeouts: function () {
	},
	saveDCCAPIkey: function (event) {
		var apikey = $('.docucents_apikey').val();
		$.ajax({
			type: 'POST',
			url: 'api/docucents/addAPIKey',
			data: { apikey: apikey },
			dataType: 'json',
			success: function (data) {
				if (data == 1) {
					alert('Docucents APi Key Added Successfully!!');
					$('.close').trigger('click');
					var newFragment = Backbone.history.getFragment($(this).attr('href'));
					if (Backbone.history.fragment == newFragment) {
						// need to null out Backbone.history.fragement because 
						// navigate method will ignore when it is the same as newFragment
						Backbone.history.fragment = null;
						Backbone.history.navigate(newFragment, true);
					}
				}else{
					alert('Something went wrong please try again later.')
				}
			}
		});
	}
});