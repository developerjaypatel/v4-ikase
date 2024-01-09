<?php
include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");

$blnIPad = isPad();

?>
<div id="kase_abstract_holder" style="margin-bottom:10px; display:none"></div>
<div class="active fade in glass_header_no_padding">
	<div style="text-align:left; margin-top:13px;">
         <div style="z-index:2356">
	        <span class="alert alert-success" style="display:none; float:right; height:35px; width:300px;font-size:14px; z-index:3356; margin-top:-45px;">Saved</span>
        </div>
	</div>
    <div style="">
        <div class="container" style="border:0px solid red; margin:0px; padding:0px; width:100%">
            <?php if ($blnIPad) { ?>
            <div class="dashboard_person col-md-10" id="person_holder" style="margin-top:10px; border:0px solid white">
            </div>
           	<?php } ?>
            <table width="100%">
            	<tr>
                    <td width="33%" valign="top">
                        <div id="person_holder" style="margin-top:10px; border:0px solid red; padding:2px; display:inline-block">
                </div>
                    </td>
                    <td width="33%" valign="top">
                        <div id="kai_holder" style="margin-top:10px; border:0px solid yellow; padding:2px; display:inline-block">
                </div>
                    </td>
                    <td width="33%" valign="top">
                        <div id="work_holder" style="margin-top:10px; border:0px solid yellow; padding:2px"></div>
                        <div style="height:10px; width:100%">&nbsp;</div>
                        <div id="disability_holder" style="margin-top:10px;"></div>
                        <div style="height:10px; width:100%">&nbsp;</div>
                        <div id="compensation_holder" style="margin-top:10px;"></div>
                    </td>
               	</tr>
            </table>
        </div>
        <div class="container" style="border:0px solid red; margin:0px; padding:0px; width:100%">
        	<table width="100%">
            	<tr>
                    <td width="33%" valign="top">
            			<div id="image_holder" style="margin-top:10px; border:0px solid yellow"></div>
                    </td>
                    <td width="33%" valign="top">&nbsp;
		            	
                    </td>
                    <td width="33%" valign="top">&nbsp;
		                
                    </td>
               </tr>
            </table>
            </div>
        </div>
        <div class="container" style="border:0px solid red; margin:0px; padding:0px">
            <div class="dashboard_person col-md-5" id="picture_holder" title="Click to expand image" style="margin-top:10px; border:0px solid yellow; cursor:pointer"></div>
        </div>
    </div>
    <a name="priors" id="priors"></a>
   	<div id="applicant_prior_treatment" style="margin-top:20px; width:100%; border:0px solid white"></div>    
    <div id="applicant_notes" style="margin-top:20px; width:100%; border:0px solid white"></div>
    <a name="rxs" id="rxs"></a>
    <div id="applicant_rx" style="margin-top:20px; width:100%; border:0px solid white"></div>    
</div>
<div>&nbsp;</div>
<div id="dashboard_person_all_done"></div>
<script language="javascript">
$( "#dashboard_person_all_done" ).trigger( "click" );
</script>