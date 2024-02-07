<div class="panel-group" id="accordion">
  <div class="panel glass_header_no_padding">
    <div class="panel-heading">
    	<div style="float:right; margin-top:-10px">
        	<a title="Click for New Kase" id="new_kase" style="color:#FFFFFF; text-decoration:none; margin-left:10px">
                <button class="btn btn-transparent" style="color:white; border:0px solid; width:20px">
                    <i class="glyphicon glyphicon-plus-sign" style="color:#00FFFF">&nbsp;</i>
                </button>
            </a>
        </div>
        <div style="float:right; margin-top:-10px">
        	<a title="Click list Kases" id="list_kase" href='#kases' style="color:#FFFFFF; text-decoration:none; margin-left:5px">
                <button class="btn btn-transparent" style="color:white; border:0px solid; width:20px">
                    <i class="glyphicon glyphicon-list" style="color:#FFC">&nbsp;</i>
                </button>
            </a>
        </div>
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
          Recent Kases
        </a>
      </h4>
    </div>
    <div id="collapseOne" class="panel-collapse collapse in">
      <div class="panel-body" id="kases_recent">
         
      </div>
    </div>
  </div>
 <!--
  <div class="panel glass_header_no_padding">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
          Active Kases
        </a>
      </h4>
    </div>
    <div id="collapseTwo" class="panel-collapse collapse">
      <div class="panel-body">
      List most active kases</div>
    </div>
  </div>
  -->
  <div class="panel glass_header_no_padding">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseThreeA">
          Kalendar
        </a>
      </h4>
    </div>
    <!--  id="summaryKalendar" -->
    <div id="collapseThreeA" class="panel-collapse collapse">
      <div class="panel-body" style="margin-left:0px; padding:0px; margin-top:0px">
      	<iframe allowtransparency="no" frameborder="0" scrolling="no" src="basic-views.php" style="margin-left:3px; margin-top:5px; height:285px; width:100%"></iframe>
      </div>
    </div>
  </div>
  <div class="panel glass_header_no_padding">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">
          Recent Tasks
        </a>
      </h4>
    </div>
    <div id="collapseThree" class="panel-collapse collapse">
      <div class="panel-body" id="occurences_recent" style="color:#FFFFFF">
      	List of current active tasks 
      </div>
    </div>
  </div>
  <div class="panel glass_header_no_padding">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour">
          Notifications
        </a>
      </h4>
    </div>
    <div id="collapseFour" class="panel-collapse collapse glass_header_no_padding">
      <div class="panel-body">
      Notifications go here </div>
    </div>
  </div>
</div>