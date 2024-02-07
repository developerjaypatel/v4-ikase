<?php 
require_once('../../shared/legacy_session.php');
session_write_close(); //FIXME: WHY GOD, WHY
?>
<div style="width:100%">
	<div style="float:left; text-align:center; font-size:2em; color:white; margin-top:5px ">
        <!--
        <img src="img/iklock_logo.png" width="181" height="53" alt="Logo" />
        -->
        <i class="glyphicon glyphicon-hourglass"></i>&nbsp;Payroll&nbsp;Master
    </div>
    <div style="float:right; color:white; text-align:left; margin-top:5px">
	    <?php echo $_SESSION['user_customer_name'] . "<br />Welcome " . $_SESSION['user_name']; ?>
        &nbsp;
        <a href='javascript:location.reload(true);' title="Click to reload iKase and empty browser cache">reload</a>
    </div>
    <div class="collapse navbar-collapse" style="border:0px solid red; width:1020px; margin-left:auto; margin-right:auto; text-align:center">
    	<div style="float:right; margin-top:13px">
        	<div style="display:inline-block">
            	<div id="clockbox" style="text-align:left; font-size:1em; color:white; margin-right:5px"></div>
            </div>
            <div style="display:inline-block">
        		<input type="text" id="search_employee" placeholder="Search by Employees by Name" style="width:210px" />
            </div>
        </div>
        <ul class="nav navbar-nav">
            <li class="tabs home-menu active" id="home_tab">
            	<a href="#">Home</a>
            </li>
            <li class="tabs" id="company_tab">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Company<span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu" style="width:140px">
                 	<li role="presentation"><a href="#company/setup" role="menuitem">Setup</a></li>
                </ul>
            </li>
            <li class="tabs" id="employee_tab">
            	<a href="#" class="dropdown-toggle" data-toggle="dropdown">Employees<span class="caret"></span></a>
                 <ul class="dropdown-menu" role="menu" style="width:140px">
                 	<!--<li role="presentation"><a href="#customers" role="menuitem">List</a></li>-->
                	<li role="presentation"><a role="menuitem" id="navigation_list_employees">List</a></li>
                    <li role="presentation"><a role="menuitem" id="navigation_new_employee">New</a></li>
                 </ul>
            </li>
            <li class="tabs" id="payroll_tab">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Payroll<span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu" style="width:140px">
                 	<li role="presentation"><a href="#paychecks/list" role="menuitem">List Paychecks</a></li>
                    <li role="presentation"><a href="#paychecks/contractors/create" role="menuitem">Contractors Checks</a></li>
                </ul>
            </li>
            <li class="tabs" id="timeoff_tab">
            	<a href="#">Time Off</a> 
            </li>
            
            <li class="tabs logout-menu" id="logout_tab"><a id="logout_link">Logout</a></li>
        </ul>
    </div>
</div>
