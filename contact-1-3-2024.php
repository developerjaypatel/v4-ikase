<?php
include("browser_detect.php");

// code added for anti CSRF require for CWE-332 - CASA for Gmail app verification and apprval : 12-7-2023
$token = array("","312#3","6$213","23!45","43%23","3*233","8@!54","1921&","87%4","977@3","65@15");
$csrf_token = $token[rand(1,10)];

if($blnMobile) {
	header("location:https://v2.ikase.org/index_mobile.php");
}

if($_SERVER["HTTPS"]=="off") {
	header("location:https://v2.ikase.org");
}
//include ("text_editor/ed/datacon.php");
//include("api/connection.php");
$version_number = 8;

$page_title = "Contact Us";
?>
<?php include("site_nav.php"); ?>
<style>
#form_holder div {
	margin-bottom:10px;
}
</style>
<!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron" style="border-bottom: 1px solid black;">
      <div class="container">
        <h1>Contact iKase Support</h1>
        <p>iKase development relies on the constant and pointed feedback of our customers.  Bugs are fixed immediately upon receipt, and custom requests are processed as soon as possible.  We appreciate your feedback and guidance as we build the best system for legal professionals.</p>
      </div>
    </div>

    <div class="container"> 
    	<?php if ($captcha=="y") { ?>
		<div id="captcha_res" style="font-size:1.6em; color:maroon">
        	Please verify that you are human :)
        </div>
		<?php } ?> 
    	<?php if (isset($_GET["thanks"])) { ?>
        <div id="form_holder" style="font-size:1.6em">
        Thank you very much for your submission.  We will be in touch as soon as possible.
        </div>
        <?php } else { ?>    
        <div id="form_holder">
			<form action="postcontact.php" method="post" enctype="multipart/form-data" id="main_form">
            	<div>
                	<label style="width:100px; display:inline-block">Name&nbsp;*</label>
                    <input type="text" name="name" id="name" style="width:250px">
                </div>
                <div>
                	<label style="width:100px; display:inline-block">Email&nbsp;*</label>
                    <input type="text" name="email" id="email" style="width:250px">
                </div>
                <div>
                	<label style="width:100px; display:inline-block">Phone</label>
                    <input type="text" name="phone" id="phone" style="width:250px">
                </div>
                <div>
                	<label style="width:100px; display:inline-block">Message</label>
                    <textarea name="message" id="message" rows="4" style="width:250px"></textarea>
                </div>
                <div>
                	<input type="checkbox" name="existing" value="Y"> I am an iKase user
                </div>
                <div>
                	<label style="width:100px; display:inline-block">Firm</label>
                    <input type="text" name="firm" id="firm" style="width:250px">
                </div>
                <div>
                	<label style="width:100px; display:inline-block">Username</label>
                    <input type="text" name="username" id="username" style="width:250px"> <input type="hidden" name="inputToken" id="inputToken" value="<?php echo $csrf_token; ?>" >
                </div>
                <div onBlur="releaseSave()">
                	<div class="g-recaptcha" data-sitekey="6Ld5xncUAAAAACCJYw9MOXP8UlEUyeo-xXxq_Hvc" data-callback="enableBtn"></div>
                </div>
                <div>
                	<button id="submit_button" class="btn" disabled onClick="submitForm()">Submit</button>
                </div>
                <div>
                	* required
                </div>
            </form>        
        </div>
        <?php } ?>    
      <hr>
    </div> <!-- /container -->
    <?php include("site_footer.php"); ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script type="text/javascript" src="lib/jquery-1.7.2.min.js"></script>
    
    <script src="lib/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="lib/ie10-viewport-bug-workaround.js"></script>
    
    <script src="js/cookies.js"></script>
    <script src="js/utilities.js"></script>
    <script type="text/javascript">
	var captcha = "";
	document.getElementById("submit_button").disabled = true;
	function enableBtn(){
		var name = document.getElementById("name").value;
		var email = document.getElementById("email").value;
		document.getElementById("submit_button").disabled = !(name!="" && email!="");
    }
	function submitForm() {
		document.getElementById("main_form").submit();
	}
	/*
	$(".recaptcha-checkbox-checkmark").on( "click", function() {
	  releaseSave();
	});
	function releaseSave() {
		var name = document.getElementById("name").value;
		var email = document.getElementById("email").value;
		alert(captcha);
		document.getElementById("submit_button").disabled = !(name!="" && email!="" && captcha!="");
	}*/
	</script>
  </body>
</html>
