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

$page_title = "Home";
?>
<?php include("site_nav.php"); ?>

    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron" style="border-bottom: 1px solid black;">
      <div class="container">
        <h1>Welcome to iKase!</h1>
        <p>iKase is a Cloud-based Legal Case Management System, dedicated to supporting the legal case worker. Our highly customizable software maximizes your productivity by facilitating workflows and providing targeted reports.</p>
        <p>The iKase system tracks everything, so that no data can be lost and you can undo any changes. To minimize data entry itself, EAMS and PQME are incorporated into iKase. We also make our very powerful Batchscan technology available to all our customers, facilitating the incorporation of real-world mail into the system.</p>
        <p>Extensive notes, tasks, documents, and events can be associated with each case. The system reminds you when tasks are overdue, when cases are overlooked, and when court events are upcoming. </p>
        <p>We customize the system for each customer, so that the system matches your exact needs. Your data resides in its own database. We specialize in importing legacy databases into iKase, including A1, Tritek, and Abacus. Everything is imported, including archived documents.        </p>
        <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more &raquo;</a></p>
      </div>
    </div>
	<div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div class="col-md-4">
          <h2>Features          </h2>
          <ul>
              <li>Segregated Cloud-based Database</li>
              <li>Full Data Tracking</li>
              <li>Extensive Case Management Tools</li>
              <li>Full Communication Functionality</li>
              <li>Firm and Case Level Tasks</li>
              <li>Employee Productivity Tools</li>
              <li>Marketing Interface</li>
            </ul>
          </p>
          <p><a class="btn btn-default" href="features.php" role="button">View details &raquo;</a></p>
        </div>
        <div class="col-md-4">
          <h2>Data Migration</h2>
          <p>We understand that you may have a large legacy dataset upon which your firm depends. We have developed migration paths for the most popular Case Management Systems and have successfully moved millions of records for our customers.</p>
          <p><a class="btn btn-default" href="#" role="button">View details &raquo;</a></p>
       </div>
        <div class="col-md-4">
          <h2>Productivity</h2>
          <p>It's one thing to provide your employees with great tools.  It's another to know and trust that these tools are being used.  iKase gives you detailed reports on firm and employee productivity.</p>
          <p><a class="btn btn-default" href="#" role="button">View details &raquo;</a></p>
        </div>
      </div>

      <hr>
      <?php include("site_footer.php"); ?>
    </body>
</html>
