function writeCookie(cookieName, cookieValue, duration) {
	var expiredate = new Date();
	expiredate.setMinutes(expiredate.getMinutes()+duration);
	document.cookie = cookieName + "=" + cookieValue + ";expires=" + expiredate.toGMTString();
}
var sess_id;
var current_url;
function readCookie(search_cookie) {
	if (typeof search_cookie == "undefined") {
		search_cookie = "";
	}
	var result = "";
	
	if (document.cookie != "") {
		var thisCookie = document.cookie.split("; ");
		var intCounter;
		var cookieName = new String();
		var myvalue = new String();
		var sess_id = new String();
		blnFoundSession = false;
		for (intCounter=0;intCounter<thisCookie.length;intCounter++) {
			cookieName = thisCookie[intCounter].split("=")[0];
			if (search_cookie=="") {
				if (cookieName=='sess_id') {
					sess_id = thisCookie[intCounter].split("sess_id=")[1];
					if (sess_id!="") {
						blnFoundSession = true;
						writeCookie('sess_id', sess_id, 60);
						loginNav();
						break;
					}
				}
			} else {
				if (cookieName==search_cookie) {
					result = thisCookie[intCounter].split(search_cookie + "=")[1];
					break;
				}
			}
			/*
			if (cookieName=='logged_in_as') {
				if (typeof $(".logged-in-as") != "undefined") {
					if ($(".logged-in-as").html()=="Welcome ") {
						var logged_in_as = thisCookie[intCounter].split("logged_in_as=")[1];
						$(".logged-in-as").append(logged_in_as);
						$(".logged-in-as").prop("title", customer_id + " - " + customer_name);
					}
				}
			} else {
				if ($(".logged-in-as").html()=="Welcome ") {
					if (login_username!="") {
						$(".logged-in-as").append(login_username);
						//$("#logged-in-customer_name").html(customer_name);
						$(".logged-in-as").prop("title", customer_id + " - " + customer_name);
						//$(".logged-in-as").prepend(customer_name + "<br>");
					}
				}
			}
			*/
		}
		if (search_cookie!="") {
			return result;
		}

		if (!blnFoundSession) {
			current_url = window.location.hash;
			document.location.href = "#logout";
		}
	}
}
function originCookie() {
	if (document.cookie != "") {
		var thisCookie = document.cookie.split("; ");
		var intCounter;
		var cookieName = new String();
		var myvalue = new String();
		var sess_id = new String();
		blnFoundSession = false;
		for (intCounter=0;intCounter<thisCookie.length;intCounter++) {
			cookieName = thisCookie[intCounter].split("=")[0];
			if (cookieName=='origin') {
				var origin = thisCookie[intCounter].split("origin=")[1];
				if (origin!="") {
					return origin;
				}
			}
		}
	}
}
function lookupCookie() {
	if (document.cookie != "") {
		var thisCookie = document.cookie.split("; ");
		var intCounter;
		var cookieName = new String();
		var myvalue = new String();
		var sess_id = new String();
		blnFoundSession = false;
		for (intCounter=0;intCounter<thisCookie.length;intCounter++) {
			cookieName = thisCookie[intCounter].split("=")[0];
			if (cookieName=='lookup') {
				var lookup = thisCookie[intCounter].split("lookup=")[1];
				if (lookup!="") {
					return lookup;
				}
			}
		}
	}
}
function customerCookie() {
	if (document.cookie != "") {
		var thisCookie = document.cookie.split("; ");
		var intCounter;
		var cookieName = new String();
		var myvalue = new String();
		var sess_id = new String();
		blnFoundSession = false;
		for (intCounter=0;intCounter<thisCookie.length;intCounter++) {
			cookieName = thisCookie[intCounter].split("=")[0];
			if (cookieName=='current_customer_id') {
				var cookie_customer_id = thisCookie[intCounter].split("current_customer_id=")[1];
				if (cookie_customer_id!="") {
					return cookie_customer_id;
				}
			}
		}
	}
}
function getPanelWidthCookie() {
	if (document.cookie != "") {
		var thisCookie = document.cookie.split("; ");
		var intCounter;
		var cookieName = new String();
		var myvalue = new String();
		var sess_id = new String();
		blnFoundSession = false;
		var cookie_left_width = 0;
		var cookie_right_width = 0;
		for (intCounter=0;intCounter<thisCookie.length;intCounter++) {
			cookieName = thisCookie[intCounter].split("=")[0];
			if (cookieName=='left_width') {
				cookie_left_width = thisCookie[intCounter].split("left_width=")[1];
				continue;
			}
			if (cookieName=='right_width') {
				cookie_right_width = thisCookie[intCounter].split("right_width=")[1];
				continue;
			}
		}
		if (cookie_left_width > 0) {
			return [cookie_left_width, cookie_right_width];
		} else {
			return [];
		}
	}
}
function maxTrackCookie(table_name) {
	var the_cookie_name = 'current_max_' + table_name + '_track_id';
	if (document.cookie != "") {
		var thisCookie = document.cookie.split("; ");
		var intCounter;
		var cookieName = new String();
		var myvalue = new String();
		blnFoundSession = false;
		for (intCounter=0;intCounter<thisCookie.length;intCounter++) {
			cookieName = thisCookie[intCounter].split("=")[0];
			if (cookieName==the_cookie_name) {
				var max_track = thisCookie[intCounter].split("=")[1];
				if (max_track!="") {
					return Number(max_track);
				}
			}
		}
		//in case nothing there yet
		return 0;
	}
	//no cookies yet
	return 0;
}
function rememberCookie() {
	if (document.cookie != "") {
		var thisCookie = document.cookie.split("; ");
		var intCounter;
		var cookieName = new String();
		var myvalue = new String();
		var user_name = new String();
		
		for (intCounter=0;intCounter<thisCookie.length;intCounter++) {
			cookieName = thisCookie[intCounter].split("=")[0];
			if (cookieName=='user_name') {
				if (typeof $("#inputEmail") != "undefined") {
					user_name = thisCookie[intCounter].split("user_name=")[1];
					$("#inputEmail").val(user_name);
					$("#remember_me").attr("checked",true);
					//$("#inputPassword").focus();
					break;
				}
			}
		}
	}
}
function loginNav() {
	$('.navbar-header').show();
	$('#logoutLink').show();
	$('#loginLink').hide();
	$('#logged_in').val(sess_id);
}