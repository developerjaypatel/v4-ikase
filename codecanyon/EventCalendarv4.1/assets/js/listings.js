/* 
===============================================================

	Event Calendar By EZCode
	-------
	For more details --> http://themeforest.net/user/ezcode
	
	Version: 4.1
	
===============================================================
 */
 
"use strict";
function EliminaEvento(id){

	swal({   title: "Are you sure?",   text: "You will not be able to recover this information!",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Yes, delete it!",   cancelButtonText: "No, cancel please!",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   if (isConfirm) {    location.href='events_del.php?id='+id;   } else {     swal("Cancelled", "Your information is safe :)", "error");   } });
	
}

function EliminaTipo(id){

	swal({   title: "Are you sure?",   text: "You will not be able to recover this information!",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Yes, delete it!",   cancelButtonText: "No, cancel please!",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   if (isConfirm) {    location.href='types_del.php?id='+id;   } else {     swal("Cancelled", "Your information is safe :)", "error");   } });
	
}

function scrollNav() {
  $('.nav a').click(function(){  
    //Toggle Class
    $(".active").removeClass("active");      
    $(this).closest('li').addClass("active");
    var theClass = $(this).attr("class");
    $('.'+theClass).parent('li').addClass('active');
    //Animate
    $('html, body').stop().animate({
        scrollTop: $( $(this).attr('href') ).offset().top - 0
    }, 800);
    return false;
  });
  $('.scrollTop a').scrollTop();
}
scrollNav();


$(document).ready(function () { 
  $(document).click(function () {
     // if($(".navbar-collapse").hasClass("in")){
       $('.navbar-collapse').collapse('hide');
     // }
  });
});



