<?php
require_once('../shared/legacy_session.php');
session_write_close();

//die(print_r($_SESSION));
if (!isset($_SESSION['user_id'])) {
	die();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>K-Arc</title>
<script language="javascript" src="../lib/jquery.min.1.10.2.js"></script>
<style type="text/css">

</style>
</head>

<body onload="init()">
<div style="border:0px solid blue">
    <div style="border:0px solid blue; float:left;" id="left_closed">
        <div style="font-size:2em; font-weight:bolder; color:blue; cursor:pointer" onclick="javascript:showLeft()"> > </div>
    </div>
    
    <div style="border:1px solid blue; float:left; width:11.3%; background:#EDEDED;" id="left_open">
    <div style="font-size:2em; font-weight:bolder; color:blue; float:left; cursor:pointer; background:#EDEDED; padding:5px" onclick="javascript:hideLeft()"> < </div>
        <div style="font-size:2em; font-weight:bolder; color:blue; float:left; width:80%;">
        	<div style="width:100%; margin-right:auto; margin-left:auto; border:#CCCCCC; padding:5px; font-size:.8em">
            	<div style="width:100%; margin-left:25px; color:black">History</div><br/>
                <div style="width:100%; margin-left:25px; color:black">Settings</div><br/>
                <div style="width:100%; margin-left:25px; color:black">Donate</div>
            </div>
        </div>
    </div>
    
    <div style="border-left:1px solid blue; float:right; width:98.3%" id="right_content">
        <br/>
        <div style="padding:5px;">
        	<select id="method" style="font-size:1.3em">
                <option value="GET">GET</option>
                <option value="POST">POST</option>
            </select>
            <select id="url_type" style="font-size:1.3em">
                <option value="http://">http://</option>
                <option value="https://" selected="selected">https://</option>
            </select>
            <input type="text" id="action" class="hover" value="www.ikase.org/api" style="width:81.5%; border-bottom:1px solid blue; border-left:0px; border-right:0px; border-top:0px; outline: none;; font-size:1.3em" />
            
        </div>
        
        <br/>
        <br/>
        
        <div>
            <div style="float:left; padding:5px">
                <button onclick="javascript:showForm()" id="show_form" style="font-size:1.3em">Form View</button>
            </div>
            <div style="float:right; margin-right:9.3%">
            	<button id="submit_it" style="font-size:1.3em">Submit</button>
            </div>
        </div>
        
        <br/>
        <br/>
        
        <div style="padding:5px">
        	<br/><br/>
            <textarea id="query" style="width:90.6%" rows="4" placeholder="values"></textarea>
        </div>
        
        <br/>
        
        <div style="padding:5px">
        	<textarea name="feedback_text" id="feedback_text" rows="5" style="width:90.6%" placeholder="Feedback Text.."></textarea>
        </div>
        
        <div id="feedback"></div>
    </div>
</div>
<script language="javascript">
var init = function() {
	$("#submit_it").on("click", function() {
		submitIt();
	});
	$("#left_open").hide();
}
var showForm = function() {
	var query = $("#query").val();
	var arrQuery = query.split("&");
	$("#form_view").html(arrQuery.join("<BR>"));
}
var submitIt = function() {
	//get current id
	$("#feedback").html("fetching");
	$("#feedback_text").val("");
		
	var data = $("#query").val();
	var url_type = $("#url_type").val();
	var action = $("#action").val();
	var method = $("#method").val();
	$.ajax({
	  type: method,
	  url: url_type + action,
	  data: data
	})
	  .done(function( msg ) {
		$("#feedback").html(msg);
		$("#feedback_text").val(msg);
	});
}
var showLeft = function() {
	$("#left_closed").hide( function() {
		//change widths
		$("#right_content").css("width", "88.3%");
		$("#action").css("width", "80%");
		$("#left_open").show();
	});
}
var hideLeft = function() {
	$("#left_open").hide( function() {
		//change widths
		$("#right_content").css("width", "98.3%");
		$("#action").css("width", "81.5%");
		$("#left_closed").show();
	});
}
</script>

</body>
</html>
