window.Kompany = Backbone.Model.extend({
	urlRoot:"api/kompanys",
	defaults: {
		"id":1,
		"name":"kustomweb",
		"last_name":"giszpenc",
		"preferred_name":"stuff",
		"title":"kustomweb",
		"company_name":"giszpenc",
		"office":"hzgkdxjh",
		"home":"kustomweb",
		"skype":"giszpenc",
		"office_phone":"35465834",
		"office_ext":"kustomweb",
		"mobile":"giszpenc",
		"fax":"4654644456",
		"homep":"kustomweb",
		"address":"giszpenc",
		"address2":"khjdgkhjdgj",
		"city":"kustomweb",
		"state":"giszpenc",
		"zip":"5465"
	},
	initialize:function () {
	}
});

window.KompanyCollection = Backbone.Collection.extend({
    model: Kompany,
    url:"api/kompanys"
});