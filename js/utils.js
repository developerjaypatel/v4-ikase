// The Template Loader. Used to asynchronously load templates located in separate .html files
window.templateLoader = {

    load: function(views, callback) {

        var deferreds = [];
		var extension = 'html';
        $.each(views, function(index, view) {
            if (window[view]) {
				if (view=="notes_view") {
					extension = 'php';
				}
                deferreds.push($.get('tpl/' + view + '.' + extension, function(data) {
                    window[view].prototype.template = _.template(data);
                }, extension));
            } else {
                alert(view + " not found");
            }
        });

        $.when.apply(null, deferreds).done(callback);
    }
};

/*
function writeCookie(cookieName, cookieValue) {
	var expiredate = new Date();
	expiredate.setMinutes(expiredate.getMinutes()+15);
	document.cookie = cookieName + "=" + cookieValue + ";expires=" + expiredate.toGMTString();
}
var sess_id;
function readCookie() {
	if (document.cookie != "") {
		var thisCookie = document.cookie.split("; ");
		var intCounter;
		var cookieName = new String();
		var myvalue = new String();
		var pidvalue = new String();
		for (intCounter=0;intCounter<thisCookie.length;intCounter++) {
			cookieName = thisCookie[intCounter].split("=")[0];
			if (cookieName=='sess_id') {
				sess_id = thisCookie[intCounter].split("sess_id=")[1];
			}
		}
	}
}
*/