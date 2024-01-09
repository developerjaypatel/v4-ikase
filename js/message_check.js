if (!!window.EventSource) {
  var source = new EventSource('../api/inboxcheck');
  source.onmessage = function (event) {
	  if(event.data > 0) {
		  setInboxIndicator(event.data);
	  }
	};
}