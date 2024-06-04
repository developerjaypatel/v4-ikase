<?php
if($_SERVER['SERVER_NAME']=="v2.starlinkcms.com")
{
  $application_url = "StarLinkCMS.com";
}
else
{
  $application_url = "iKase.org";
}
?>
<div id="navbar-collapse-1" class="navbar-collapse collapse navbar-fixed-bottom" style="background:#000137; color:#000">
    <footer class="footer">
        <p style="text-align:center">&copy; <?php echo date("Y"); ?> <?=$application_url;?></p>
    </footer>
</div>