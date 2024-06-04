<html>
<head>

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"> 
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

  <!-- Include Bootstrap for styling -->
  <!-- <link rel="stylesheet"   href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">  -->
  <!-- Include the Bootstrap Table CSS  for the table -->
  <link rel="stylesheet"  href="https://unpkg.com/bootstrap-table@1.16.0/dist/bootstrap-table.min.css">

  <!-- Include jQuery and other required files for Bootstrap -->
  <script src="https://code.jquery.com/jquery-3.5.1.js">  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js">  </script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js">  </script>  
  <!-- Include the JavaScript file  for Bootstrap table -->
  <script src="https://unpkg.com/bootstrap-table@1.16.0/dist/bootstrap-table.min.js">  </script>

  <!-- jQuery UI library -->
  <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>


  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />  
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  


  
  <!-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> -->
  <!-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/select/1.6.2/js/dataTables.select.min.js"></script>
  <script src="https://cdn.datatables.net/datetime/1.4.1/js/dataTables.dateTime.min.js"></script>
  <script src="https://editor.datatables.net/extensions/Editor/js/dataTables.editor.min.js"></script>

  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/select/1.6.2/css/select.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.4.1/css/dataTables.dateTime.min.csss">
  <link rel="stylesheet" href="https://editor.datatables.net/extensions/Editor/css/editor.dataTables.min.css"> -->

  
  



  <script type="text/javascript" src="ajaxScript.js"></script>

  
  <style>
       .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            /* prevent horizontal scrollbar */
            overflow-x: hidden;
            /* add padding to account for vertical scrollbar */
            padding-right: 20px;
        } 
        /* .dataTables_filter {
display: none;
} */

.dataTables_filter input {
    width: 350px;
}

#example2_filter input {
   width: 270px;
}

#example2 {
    /* width: 300px; */
}

fieldset.scheduler-border {
    border: 1px groove #ddd !important;
    padding: 0 1.4em 1.4em 1.4em !important;
    margin: 0 0 1.5em 0 !important;
    -webkit-box-shadow:  0px 0px 0px 0px #000;
            box-shadow:  0px 0px 0px 0px #000;
            border-radius: 5px;
}

legend {
    display: block;
    width: 25%;
    padding: 0;
    margin-bottom: 20px;
    font-size: 21px;
    line-height: inherit;
    color: #333;
    border: 0;
    /* border-bottom: 1px solid #e5e5e5; */
}


.messageBox {
  /* border: 2px solid #dedede;
  background-color: #f1f1f1;
  border-radius: 5px;
  padding: 10px;
  margin: 10px 0; */
  width: 400px;
  font-size: small;
}



.threadMessageBox {
  /* border: 2px solid #dedede;
  background-color: #f1f1f1;
  border-radius: 5px;
  padding: 10px;
  margin: 10px 0; */
 
  font-size: small;
}

.time-right {
  float: right;
  color: #aaa;
  margin-bottom: 10px;
}
table.dataTable tbody td {
    word-break: break-word;
    vertical-align: top;
}
input[type="search"]::-webkit-search-cancel-button {
    -webkit-appearance: searchfield-cancel-button;
}

  </style>



</head>

 <body>
 <div class="container-fluid">
   
 <div class="row"> 
 <div class="col-sm-1"></div>
 <div class="col-sm-4"></div>
 <div class="col-sm-3"></div>
 <div class="col-sm-3" style="margin-top: 20px;"></div>
 </div>
 

 <div class="row"> 
 <div class="col-sm-1"></div>
 <div class="col-sm-10"  style="background-repeat: no-repeat;
        background-size: 100%;  background-image: url('https://ikase.org/img/glass_edit_header_new.png');">

 <fieldset class="scheduler-border">
    <legend >Client SMS Subscription Form</legend> 
    <div class="row"> 
    
    <div class="col-sm-10">

<form id="fupForm" enctype="multipart/form-data" method="POST">    
    
    <div class="row" style="padding-bottom:5px;">
    <!-- <div class="col-sm-1"></div> -->
    <div class="col-sm-2"> <label for="recipient">Phone Number:</label></div>
    <div class="col-sm-5"><input type="text" class="form-control" name='phone' id='phone' placeholder="Enter Phone Number | Format : +10000000000" /></div>
    <div class="col-sm-4">  Format : +10000000000 </div>
    </div>

    <div class="row" style="padding-bottom:5px;">
    <!-- <div class="col-sm-1"></div> -->
    <div class="col-sm-2"> <label for="language">Opt-In language:</label></div>
    <div class="col-sm-2">
        <select class="form-control form-select form-select-sm mt-3" name="language" id="language">
  <option value="English">English</option>
  <option value="Latin">Latin</option>
  <option value="Spanish">Spanish</option>
  <option value="French">French</option>
        </select></div>   
    </div>

    <div class="row" style="padding-bottom:5px;">
    <!-- <div class="col-sm-1"></div> -->
    <div class="col-sm-2"><label for="file"></label></div>
    <div class="col-sm-5"><span> 
        IKase Case Management Software complies with HIPAA and wants to exchange text messages with you.</br> 
        Text messaging may not be entirely secure. </br>  To Consent, Please tick the Check Box.
        <input type="checkbox" id="consent" name="consent" value="Yes"  class="form-check-input">
        <label for="consent"> Yes, I Agree</label><br> 
    </span> </div>  
         
    </div>

    <div class="row" style="padding-bottom:5px;">
    <!-- <div class="col-sm-1"></div> -->
    <div class="col-sm-2"></div>
    <div class="col-sm-3">
      <input type="submit" name="submit" class="btn btn-primary" value="Submit"/>
      <input type="reset" name="cancel" class="btn btn-secondary" value="Cancel"/>
  </div>    
    </div>
                  
    </form> 
    </div>
    
    </fieldset>



</div>
<div class="col-sm-1"></div>
 </div>




 <div class="row"> 
 <div class="col-sm-1"></div>
 <div class="col-sm-4">
</div>
<div class="col-sm-6">


</div>
<div class="col-sm-1"></div>
</div>





<!-- <div class="row"> 
 <div class="col-sm-1"></div>
 <div class="col-sm-10">

 <fieldset class="scheduler-border">
    <legend >List All Messages</legend> 
    <div class="row"> 
    
    <div class="col-sm-12">
    <table id="example"  class="display" style="width:100%">
        <thead>
            <tr>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Client Name</th>
                <th>Date/Time</th>
                <th>Message</th>                
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Client Name</th>
                <th>Date/Time</th>
                <th>Message</th>                
            </tr>
        </tfoot>
      </table>

    </div>
    </div>
</fieldset>
</div>
</div> -->






<div class="row"> 
 <div class="col-sm-1"></div>
 <div class="col-sm-4"></div>
 <div class="col-sm-3"></div>
 <div class="col-sm-3" style="margin-top: 20px; margin-bottom: 40px;"></div>
 </div>

<!-- Status message -->
<div class="statusMsg"></div>



 <hr>

 <div class="row"> 
 <div class="col-sm-1"></div>
 <div class="col-sm-10"><p></p>    
 </div>
 </div>


 </div>
      
  </body>
</html>
