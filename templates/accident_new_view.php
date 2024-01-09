<div class="gridster accident_new" id="gridster_accident_new" style="display:none; text-align:left; margin-top:13px; border:0px solid red; width:900px">
	<span class="form_title">Accident Type</span>
	<br/>
    <ul>
        <li id="car_accidentGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="accident gridster_border gridster_holder" style="background:url(img/glass_card_fade_1.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-top:0px; font-size:1em;" onclick="document.location.href='#accident/<%= case_id %>/caraccident'">
        
        <a style="color:white; text-decoration:none;" href="#accident/<%= case_id %>/caraccident" title="Click to edit ">Car Accident</a>
        </li>
		<li id="slip_and_fallGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="accident gridster_border gridster_holder" style="background:url(img/glass_card_dark_long_2.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-top:0px; font-size:1em;" onclick="document.location.href='#accident/<%= case_id %>/slipandfall'">
        
        <a style="color:white; text-decoration:none;" href="#accident/<%= case_id %>/slipandfall" title="Click to edit ">Slip & Fall</a>
        </li>
		<li id="motorcycleGrid" data-row="1" data-col="3" data-sizex="1" data-sizey="1" class="accident gridster_border gridster_holder" style="background:url(img/glass_card_fade_5.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-top:0px; font-size:1em;" onclick="document.location.href='#accident/<%= case_id %>/motorcycle'">
       
        <a style="color:white; text-decoration:none;" href="#accident/<%= case_id %>/motorcycle" title="Click to edit ">Motorcycle</a>
        </li>
		<li id="natural_causeGrid" data-row="1" data-col="4" data-sizex="1" data-sizey="1" class="accident gridster_border gridster_holder" style="background:url(img/glass_card_fade.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-top:0px; font-size:1em;" onclick="document.location.href='#accident/<%= case_id %>/naturalcause'">
        
        <a style="color:white; text-decoration:none;" href="#accident/<%= case_id %>/naturalcause" title="Click to edit ">Natural Cause</a>
        </li>
    </ul>
</div>