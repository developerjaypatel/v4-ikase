window.UserSetting = Backbone.Model.extend({
	urlRoot:"api/setting/user",
	initialize: function(options) {
		if (typeof options != "undefined") {
			this.id = options.setting_id;
		}
	  },
	defaults : {
		"user_setting_id" : "",
		"category" : "",
		"setting": "",
		"setting_value": "",
		"default_value": ""
	}
});
window.UserSettingCollection = Backbone.Collection.extend({
    initialize: function(options) {
		//this.case_id = options.case_id;
	 },
	model: UserSetting,
    url:"api/setting/user"
});