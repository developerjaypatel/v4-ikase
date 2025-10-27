window.reset_password_view = Backbone.View.extend({
    initialize:function () {
        console.log('Initializing Home View');
    },

    events:{
       /*"click #reset_password":			"resetPassword",
	   "keyup .password_field":			"checkPasswords"
	   */
	   "keyup #new_password":				"rankPassword",
	   "keyup #new_password2":				"comparePasswords",
	   "click #ok_password":				"savePassword"
    },

    render:function () {
        $(this.el).html(this.template(this.model.toJSON()));
		
		setTimeout(function() {
			//$("#inputPassword").focus();
			$("#new_password").focus();
		}, 300);
		return this;
    },
	savePassword:function () {
		var user_id = this.model.get("user_id");
		if (document.getElementById("new_password").value == document.getElementById("new_password2").value) {
			var formValues = "table_name=user&table_id=" + user_id + "&password=" + document.getElementById("new_password").value;
			var url = 'api/user/update';
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						
					}
					
					if (data.success==user_id) {
						var href = "index.php";
						document.location.href = href;
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) { 
					 
				} 
			});
		}
	},
	comparePasswords: function () {
		document.getElementById("ok_password").disabled = true;
		var res = "<span style='background:red;color:white;padding:2px'>&times;</span>";
		if (document.getElementById("new_password").value == document.getElementById("new_password2").value) {
			res = "<span style='background:green;color:white;padding:2px'>&#10003;</span>";
			document.getElementById("ok_password").disabled = false;		
		}
		document.getElementById("confirm_password_status").innerHTML = res;
		
	},
	rankPassword: function () {
		document.getElementById("ok_password").disabled = true;
		document.getElementById("confirm_holder").style.visibility = "hidden";
		var user_id = this.model.get("user_id");
		
		var formValues = "user_id=" + user_id + "&password=" + document.getElementById("new_password").value;
		var url = 'api/rankpassword';
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					
				}
				
				if (!data.success) {
					var res = "<span style='background:red;color:white;padding:2px'>&times;</span>";
				} else {
					var res = "<span style='background:green;color:white;padding:2px'>&#10003;</span>";
					document.getElementById("confirm_holder").style.visibility = "visible";
				}
				document.getElementById("new_password_status").innerHTML = res;
				
				var rank = data.rank;
				if (rank.length > 5) {
					document.getElementById("min_length").style.background = "green";
					document.getElementById("min_length").style.padding = "2px";
				} else {
					document.getElementById("min_length").style.background = "none";
				}
				if (rank.lowercase > 0) {
					document.getElementById("min_lowercase").style.background = "green";
					document.getElementById("min_lowercase").style.padding = "2px";
				} else {
					document.getElementById("min_lowercase").style.background = "none";
				}
				if (rank.uppercase > 0) {
					document.getElementById("min_uppercase").style.background = "green";
					document.getElementById("min_uppercase").style.padding = "2px";
				} else {
					document.getElementById("min_uppercase").style.background = "none";
				}
				if (rank.numbers > 0) {
					document.getElementById("min_number").style.background = "green";
					document.getElementById("min_number").style.padding = "2px";
				} else {
					document.getElementById("min_number").style.background = "none";
				}
				if (rank.symbols > 0) {
					document.getElementById("min_symbol").style.background = "green";
					document.getElementById("min_symbol").style.padding = "2px";
				} else {
					document.getElementById("min_symbol").style.background = "none";
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				 
			} 
		});
	},
	resetPassword:function(event) {
		
		event.preventDefault();
		
		var url = "api/request/act";
		var formValues = "password=" + $("#inputPassword").val();
		formValues += "&id=" + $("#table_id").val();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					alert("Key Error");
					return;
				} else {
					$("#password_panel").html("<div style='color:white; font-size:1.2em; text-align:center'>Your password has been reset</div><div style='padding-top:5px; text-align:center'><a href='https://v4.ikase.org/' style='color:white'>Login</a></div>");
				}
			}
		});
	},
	checkPasswords: function(event) {
		if ($("#inputPasswordTwice").val()!="") {
			if ($("#inputPassword").val() != $("#inputPasswordTwice").val()) {
				$("#password_match").html("<span style='color:red'>x</span>");
				$(".btn-primary").prop("disabled", true);
			} else {
				$("#password_match").html("<span style='color:green'>&#10003;</span>");
				$(".btn-primary").prop("disabled", false);
			}
		}
	}
});
