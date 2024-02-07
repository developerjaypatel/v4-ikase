function sendKase(event) {
	var element = event.target;
	//console.log(element.id);
	var id = element.id.split("_")[2];
	var note_holder = document.getElementById("text_" + id);
	var thenote = note_holder.value;
	var the_client = document.getElementById("table_month_year").innerHTML;
	var case_number = document.getElementById("case_number_" + id).innerHTML;
	
	var message = theclient + "  has sent feedback re:Case #<a href='?n=#kases/" + id + "'>" + case_number + "</a>";
	message += "\r\n";
	message += thenote;
	

	var url = "api/feedback/add";
	
	alert("not quite ready");
	return;
	var data = new FormData(form);
	data.append("customer_id", customer_id);
	data.append("table_name", "message");
	data.append("message_to", "NG");
	data.append("messageInput", message);
	data.append("case_file", id);
	data.append("send_document_id", "");
	data.append("apply_notes", "Y");
	data.append("subject", "Client Summary Feedback - " + theclient);
	data.append("from", "MA");
	data.append("notification", "Y");
	 
	var xhr = new XMLHttpRequest();
	xhr.open('POST', url);
	xhr.send(data);
	
	xhr.onreadystatechange = function () {
		var DONE = 4; // readyState 4 means the request is done.
		var OK = 200; // status 200 is a successful return.
		if (xhr.readyState === DONE) {
			if (xhr.status === OK) {
				//console.log(xhr.responseText); // 'This is the returned text.'
				document.getElementById(".kase_data_row_" + id).style.background = "green";
				setTimeout(function() {
					document.getElementById(".kase_data_row_" + id).style.display = "none";
				}, 1500);
			}
		}
	}
	
}
var note_holders = document.getElementsByClassName("note_holder");
var arrLength = note_holders.length;
for(var i = 0; i < arrLength; i++) {
	var note_holder = note_holders[i];
	var note_holder_id = note_holder.id;
	note_holder.innerHTML = '<textarea id="' + note_holder_id.replace("note_holder_", "text_") + '" style="width:100%" rows="3"></textarea>';
}