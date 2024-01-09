<?php
include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");

$blnIPad = isPad();

?>
<div class="active fade in glass_header_no_padding" style="border:0px solid blue; padding:0px">
    <div style="border:0px solid purple; padding:0px">
        <div class="container" style="width:100%; border:0px solid green; padding:0px; color:#FFF; font-size:1.8em; padding:0px">
            <div style="width:100%; margin-left:auto; margin-right:auto; text-align:center; margin-top:-10px">
                <label for="srch-term" id="label_search" style="font-size:1.3em; cursor:text; position:relative; top:60px; left:0px; color:#CCC; margin-left:-35%; margin-top:-28px; -moz-user-select: none; -webkit-user-select: none; -ms-user-select:none; user-select:none;-o-user-select:none; font-weight:300" unselectable="on" onselectstart="return false;">Search for Kases</label>
            
              <input type="text" class="form-control" placeholder="" name="srch-term" id="srch-term" autocomplete="off" style="margin-top:0px; height:65px; line-height:55px; font-size:1.5em; width:98%; margin-left:5px">
            </div>
        </div>
    </div>
    <div>&nbsp;</div>
</div>
<div>&nbsp;</div>
<div id="home_view_all_done_mobile"></div>
<script language="javascript">
$( "#home_view_all_done_mobile" ).trigger( "click" );
</script>