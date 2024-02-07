<?php
if (!isset($_SESSION["user_plain_id"])) {
	header("location:https://v2.ikase.org/iklock/index.php?noid");
}
if (!isset($_SESSION["user_role"])) {
	header("location:https://v2.ikase.org/iklock/index.php?norol");
}
if ($_SESSION["user_role"] != "owner" && $_SESSION["user_role"] != "masteradmin") {
	header("location:https://v2.ikase.org/iklock/index.php?noperm");
}
