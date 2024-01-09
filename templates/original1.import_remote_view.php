<?php
include("../api/manage_session.php");
session_write_close();
?>
<div class="import" style="padding-left:20px; ">
    <div style="position:absolute; z-index:9999; left:750px; top:5px; background:#CCC; padding:5px">
        <a href="https://www.ikase.website/uploads/batchscan_barcode_plain.pdf" target="_blank" title="Click here to print the New Separator Sheet" class="black_text" style="text-decoration:underline">Click here to print the New Separator Sheet</a>
        <br />
        <span style="font-style:italic; color:black; font-size:0.8em">
        1) Print the separator sheet<br />
        2) Make copies<br />
        3) Insert a separator in between each communication.</span>
        <div id="batchscan_review" style="display:; width:100%">
            <!--
            <iframe src="https://www.ikase.org/uploads/batchscan_barcode_plain.pdf" width="100%" height="500px"></iframe>
            -->
            <a href="https://www.ikase.website/uploads/batchscan_barcode_plain.pdf" target="_blank" title="Click here to print the New Separator Sheet" class="black_text" style="text-decoration:underline"><img src="images/page.png" width="350px" height="auto" style="border:1px solid black"></a>
        </div>
    </div>
    <!--
    <div style="background:red; color:white; font-size:1.6; padding:2px">Under Repairs - 3/23/17 - Please do not use until this announcement is removed. Thank you.</div>
    -->
    <div style="width:300px">
        <div style="float:right; padding-top:2px">
            <a href="Batch Scan Instructions.pdf" target="_blank" class="white_text" title="Click here for Batchscan Instructions"><i class="glyphicon glyphicon-question-sign" style="color:aqua"></i></a>
        </div>
        <span style="color:#FFFFFF; font-size:1.25em; font-weight:lighter; margin-left:3px;" id="import_title"><%= import_type.capitalizeWords() %> Upload</span>
    </div>
    
    <iframe src="https://www.ikase.website/barcode/ikase_form.php" height="600" width="100%" allowtransparency="1" frameborder="0" scrolling="no">
    </iframe>
    <!--
    <iframe src="https://www.ikase.xyz/ikase/limapi/ikase_form.php?customer_id=<%=customer_id %>&user_name=<%=login_username %>&user_id=<?php echo $_SESSION['user_plain_id']; ?>" height="600" width="100%" allowtransparency="1" frameborder="0" scrolling="no">
    </iframe>
    -->
    <div id="batch_indicator" style="margin-top:10px; display:none">&nbsp;</div>
</div>