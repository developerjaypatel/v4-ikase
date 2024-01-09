<nav class="navbar navbar-inverse navbar-fixed-top">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="index.php" style="color:white"><img src="img/favicon.png" width="18" height="18" alt="iKase">&nbsp;iKase</a>
    </div>
    <div id="navbar" class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
            <li <?php if ($_SERVER["URL"]=="/index.php") { echo 'class="active"'; } ?>><a href="index.php">Home</a></li>
            <li <?php if ($_SERVER["URL"]=="/about.php") { echo 'class="active"'; } ?>><a href="about.php">About</a></li>
            <li><a href="privacy.html" target="_blank">Privacy Policy</a></li>
            <li <?php if ($_SERVER["URL"]=="/features.php") { echo 'class="active"'; } ?>><a href="features.php">Features</a></li>
            <li <?php if ($_SERVER["URL"]=="/contact.php") { echo 'class="active"'; } ?>><a href="contact.php">Contact</a></li>
            <li><a href="https://www.avast.com/en-us/download-thank-you.php?product=ASB&locale=en-us" target="_blank">Download Avast</a></li>
        </ul>
      <div class="navbar-form navbar-right" style="display:<?php 
	  if ($_SERVER['REMOTE_ADDR']!='47.153.51.181') { 
	  	//echo "none"; 
	} ?>">
        <div class="form-group">
          <input type="text" placeholder="Username" class="form-control" id="inputEmail" name="inputEmail" onKeyPress="enterLogin(event)" required autocomplete="off" tabindex="1" >
        </div>
        <div class="form-group" id="password_holder">
          <input type="password" placeholder="Password" class="form-control" id="inputPassword" name="inputPassword" onKeyPress="enterLogin(event)" tabindex="2" >
        </div>
        <button class="btn btn-primary" onClick="userLogin(event)" id="signin_button">Sign in&nbsp;&nbsp;<img src="img/secure_login.png" width="16" height="15" alt="Secure Login"></button>
      </div>
    </div><!--/.navbar-collapse -->
  </div>
</nav>
<?php include("nav_functions.php"); ?>