<?php
require_once('../shared/legacy_session.php');
session_write_close();
//https://www.ikase.xyz/ikase/gmail/examples/index.php?user_id=<?php echo $_SESSION['user_plain_id']; ?>
?>
<div class="twofactor" style="padding-left:20px; ">
	<iframe src="<%= url %>" height="600" width="100%" allowtransparency="1" frameborder="0" scrolling="no">
    </iframe>
</div>
