<?php
include("browser_detect.php");

if($blnMobile) {
	header("location:https://v2.ikase.org/index_mobile.php");
}

if($_SERVER["HTTPS"]=="off") {
	header("location:https://v2.ikase.org");
}
//include ("text_editor/ed/datacon.php");
//include("api/connection.php");
$version_number = 8;

$page_title = "About iKase";
?>
<!-- Bootstrap core JavaScript
================================================== -->
<?php include("site_nav.php"); ?>
<style>
#form_holder div {
	margin-bottom:10px;
}
</style>
<!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron" style="border-bottom: 1px solid black;">
      <div class="container">
        <h1>About iKase Legal Management Software</h1>
        <p>iKase is a state-of-the-art legal management system, combining rock-solid data management with modern integration tools. The screen interfaces are minimal, allowing you to follow your workflow while reviewing and updating cases.</p>
      </div>
    </div>

    <div class="container">      
        <div>
       	  <p>iKase is a California based software development effort, a partnership of KustomWeb.com and Matrix Documents Imaging, Inc.  Based on years of expertise dealing with complex legal cases, multiple records requirements, and the integration of forms and letters, a development team was assembled to provide legal professionals with a modern tool that could be infinitely customized to the customer's exact specifications.</p>
        	<p>With our legal partners, we have created a Cloud-based solution, accessible from desktop to the phone. While the core of iKase was written to cater to Worker's Comp law, the system is constantly evolving as we incorporate different types of law, such as Personal Injury, Immigration, and Social Security. The modular nature of the design allows us to incorporate new and third-party features as we expand the reach of iKase. Currently, iKase allows you to search and import from EAMS, QME, and the Court Calendar. Additionally, the system exports to JetFile.        </p>
        <p>The iKase system tracks everything, so that no data can be lost and you can undo any changes. To minimize data entry itself, EAMS and PQME are incorporated into iKase. We also make our very powerful Batchscan technology available to all our customers, facilitating the incorporation of real-world mail into the system.</p>
        <p>Extensive notes, tasks, documents, and events can be associated with each case. The system reminds you when tasks are overdue, when cases are overlooked, and when court events are upcoming. </p>
        <p>We customize the system for each customer, so that the system matches your exact needs. Your data resides in its own database. We specialize in importing legacy databases into iKase, including A1, Tritek, and Abacus. Everything is imported, including archived documents.        </p>
        </div>
      <hr>
    </div> <!-- /container -->
    <?php include("site_footer.php"); ?>
    
  </body>
</html>
