<?php
if($_SERVER['SERVER_NAME']=="v2.starlinkcms.com")
{
  $application = "StarLinkCMS";
  $application_url = "https://v2.starlinkcms.com/";
  $application_fevicon = "logo-starlinkcms.png";
}
else
{
  $application = "iKase";
  $application_url = "https://v4.ikase.org/";
  $application_fevicon = "favicon.png";
}

include("browser_detect.php");

if($blnMobile) {
	header("location:" . $application_url . "index_mobile.php");
}

if($_SERVER["HTTPS"]=="off") {
	header("location:$application_url");
}
//include ("text_editor/ed/datacon.php");
//include("api/connection.php");
$version_number = 8;
$page_title = "Features $application";
?>
<?php include("site_nav.php"); ?>

    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron" style="border-bottom: 1px solid black;">
      <div class="container">
        <h1><?=$application; ?> Features</h1>
        <p><?=$application; ?> functionality has been crafted in conjunction with large and small legal firms, to best address the needs of the legal worker community. Moreover, these features can customized just for your firm, because the modular architecture allows us to build the software to match your exact needs.</p>
      </div>
    </div>

    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div class="col-md-4">
          <h2>Cases          </h2>
          <ul>
              <li>Various Listing Modes</li>
              <li>Advance Case Search</li>
              <li>Activity and Inactivity Reports</li>
              <li>Year/Month Summaries</li>
              <li>Import Case from EAMS</li>
              <li>Integrated EAMS Search </li>
            </ul>
          </p>
        </div>
        <div class="col-md-4">
          <h2>Search</h2>
          <ul>
            <li>Fast Algorithm</li>
            <li>Rolodex Functionality</li>
            <li>Integrated EAMS Companies</li>
            <li>Search by Case Type</li>
          </ul>
          <p>&nbsp;</p>
</div>
        <div class="col-md-4">
          <h2>Documents</h2>
          <ul>
            <li>Categorize Uploads</li>
            <li>Upload as Batch Scan</li>
            <li>Letter Templates</li>
            <li>PDF Form Templates</li>
            <li>Demographic Document</li>
            <li>Integrated EAMS Submissions</li>
          </ul>
          <p>&nbsp;</p>
</div>
      </div>
<div class="row">
    <div class="col-md-4">
      <h2>Calendar          </h2>
      <ul>
          <li>Firm Listing</li>
          <li>Case Listing</li>
          <li>EAMS Court Calendar Integration</li>
          <li>Sync with Outlook, Google Calendar</li>
        </ul>
      </p>
      </div>
      
    <div class="col-md-4">
        <h2>Communication</h2>
        <ul>
        <li>Interoffice</li>
        <li>Email Integration</li>
        <li>Phone Messages</li>
        <li>Chat Functionality</li>
        </ul>
        <p>&nbsp;</p>
    </div>
    
    <div class="col-md-4">
        <h2>Jetfile</h2>
        <ul>
            <li>ADJ Filling No Additional Typing (ADJ # In 2 Hours)</li>
            <li>DOR</li>
            <li>DOR (E)</li>
            <li>Lien</li>
            <li>All EAMS Legal Forms Can Be Filed Through <?=$application; ?></li>
            </li>
        </ul>
        <p>&nbsp;</p>
    </div>
</div>
<div class="row">
	<div class="col-md-4">
      <h2>Tasks</h2>
      <ul>
        <li>Notifications</li>
        <li>Overdue Indicator</li>
        <li>Firm and Case Level Tasks</li>
      </ul>
      <p>&nbsp;</p>
    </div>
    <div class="col-md-4">
      <h2>Employees          </h2>
      <ul>
          <li>Email Integration</li>
          <li>Activity Monitor</li>
          <li>Job Assignments</li>
        </ul>
      </p>
      </div>
        <div class="col-md-4">
          <h2>Marketing</h2>
          <ul>
            <li>Email all Clients</li>
            <li>Generate Envelopes for All Clients</li>
            <li>Track Client Activity</li>
          </ul>
          <p>&nbsp;</p>
		</div>
        
    <div class="col-md-4">
        <h2>Save Money</h2>
        <ul>
        <li>No Server Needed</li>
        <li>No Programs To Install</li>
        <li>All Updates Are Automatic</li>
        <li>No Back Ups To Worry About</li>
        <li>Best Customer Service</li>
        <li>On 24/7 Via- Pc /Tablet / Phone</li>
        </ul>
        <p>&nbsp;</p>
    </div>
    
        <div class="col-md-4">
          <h2>&nbsp;</h2>
          <p>&nbsp;</p>
</div>
      </div>      
      <hr>
    </div> <!-- /container -->
	<?php include("site_footer.php"); ?>
  </body>
</html>
