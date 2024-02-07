<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script language="javascript" src="../../lib/jquery.min.1.10.2.js"></script>
<script language="javascript" src="../../lib/underscore-min.js"></script>
<script language="javascript" src="../../lib/backbone.js"></script>
<script language="javascript" src="../../js/models/eventmodel.js"></script>
</head>
<body>
<script language="javascript">
var stored_customer_events = new OccurenceStoredCustomerCollection();
stored_customer_events.fetch({
	success: function(stored_customer_events) {
		_.chain(stored_customer_events.models).clone().each(function(model){
			model.destroy();
		});
	}
});
</script>
</body>
</html>