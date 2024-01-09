<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>ajax</title>
<script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="http://cdn.intercoolerjs.org/intercooler-1.0.2.min.js"></script>
<script src="lib/jquery.mockjax.js"></script>
</head>

<body>
<div id="contact-div"></div>
<button ic-target="#contact-div" ic-get-from="/api/ajax.php" class="btn btn-default">
  Click To Edit
</button>
<script>
	$.mockjax({
      url: "/api/ajax.php",
      response: function (settings) {
        //$("#feedback").html(settings);
		this.responseText = settings;
      }
    });
</script>
</body>
</html>