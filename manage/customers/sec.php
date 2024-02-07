<?php
if (!isset($_SESSION["user_plain_id"])) {
	header("location:https://www.ikase.org/index.php?noid");
}
if (!isset($_SESSION["user_role"])) {
	header("location:https://www.ikase.org/index.php?norol");
}
if ($_SESSION["user_role"] != "owner" && $_SESSION["user_role"] != "masteradmin") {
	header("location:https://www.ikase.org/index.php?noperm");
}
?>