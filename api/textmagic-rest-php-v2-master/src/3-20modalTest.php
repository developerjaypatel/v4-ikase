<html lang="en">
<head>
  <title>SMS Clients Module</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">






  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet"  href="https://unpkg.com/bootstrap-table@1.16.0/dist/bootstrap-table.min.css">
  <script src="https://code.jquery.com/jquery-3.5.1.js">  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js">  </script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js">  </script>  
  <script src="https://unpkg.com/bootstrap-table@1.16.0/dist/bootstrap-table.min.js">  </script>
  <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />  
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> 
  


  <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>         -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tokenfield/0.12.0/css/bootstrap-tokenfield.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tokenfield/0.12.0/bootstrap-tokenfield.js"></script>


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
   width: 230px;
}


fieldset.scheduler-border {
    border: 1px groove #ddd !important;
    padding: 1em 1.4em 0.6em 1.4em !important;
    margin: 0 0 1.5em 0 !important;
    -webkit-box-shadow:  0px 0px 0px 0px #000;
            box-shadow:  0px 0px 0px 0px #000;
            border-radius: 5px;
}

legend {
    display: block;
    width: 14%;
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
  color: rgb(255, 255, 255);
  overflow-wrap: break-word;
  width: 360px;
  font-size: small;
}



.threadMessageBox {
  /* border: 2px solid #dedede;
  background-color: #f1f1f1;
  border-radius: 5px;
  padding: 10px;
  margin: 10px 0; */
  color: rgb(255, 255, 255);
  width: 800px;
  font-size: small;
}

.time-right {
  float: right;
  color: #aaa;
  margin-bottom: 10px;
}
table.dataTable tbody {
vertical-align: left;
}
table.dataTable tbody td {
    /* word-break: break-word; */
    vertical-align: top;
}
input[type="search"]::-webkit-search-cancel-button {
    -webkit-appearance: searchfield-cancel-button;
}



.vertical-alignment-helper {
    display:table;
    height: 100%;
    width: 100%;
    pointer-events:none;
    border:none;
}
.vertical-align-center {
    /* To center vertically */
    display: table-cell;
    vertical-align: middle;
    pointer-events:none;
    width: 90%;
    border:none;
}
.modal-content {
    /* Bootstrap sets the size of the modal in the modal-dialog class, we need to inherit it */
    width:inherit;
 max-width:inherit; /* For Bootstrap 4 - to avoid the modal window stretching full width */
    height:inherit;
    /* To center horizontally */
    margin: 0 auto;
    pointer-events:all;
    border:none;
}



.ui-front {
    z-index: 9999;
}
.ui-autocomplete {
  z-index: 215000000 !important;
}

  </style>
</head>
<body>

<!-- Button trigger modal -->
<br>
<div class="row"> 
   <div class="col-sm-1"></div>
   <div class="col-sm-4">
   <button id= "btnOpenModal" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal">SMS Clients</button>
   </div>
   <div class="col-sm-3"></div>
   <div class="col-sm-3"></div>

   
   </div>

<!-- Modal -->
<div class="modal fade" style="padding-top: 30px; padding-bottom: 30px;" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center" style="">
            <div class="modal-content" style="
        background-size: 100%; background-image: url('https://ikase.org/img/glass_edit_header_new.png');">
                <div class="modal-header" style="background-repeat: no-repeat;
        background-size: 100%;  background-image: url('https://ikase.org/img/glass_edit_header_new.png');">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span>

                    </button>
                     <h4 class="modal-title" id="myModalLabel">SMS to Clients</h4>

                </div>
                <div class="modal-body" style="
        background-size: 100%;  color: rgb(255, 255, 255); background-image: url('https://ikase.org/img/glass_edit_header_new.png'); overflow-x: hidden;">
        
        <!-- -------------------- -->
    

<div class="container-fluid">
   
   <div class="row"> 
   <div class="col-sm-1"></div>
   <div class="col-sm-4"></div>
   <div class="col-sm-3"></div>
   <div class="col-sm-3"></div>
   </div>
   
  
   <div class="row"> 
  
  
      <fieldset class="scheduler-border">
          <!-- <legend >Send SMS Form</legend>  -->
      
          <div class="row"> 
   <div class="col-sm-1"></div>
   <div class="col-sm-4"></div>
   <div class="col-sm-3"></div>
   <div class="col-sm-3" style="margin-top: 10px;"></div>
   </div>
  
              <form id="fupForm" enctype="multipart/form-data" method="POST">    
                  

                   
              <!-- <div class="form-group">
                        <label>Enter Customer Name/Phone</label>
                        <div class="input-group">
                            <input type="text" id="search_data" placeholder="" autocomplete="off" class="form-control input-lg" />
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-primary btn-lg" id="search">Get Value</button>
                            </div>
                        </div>
                        <br />
                        <span id="country_name"></span>
                    </div> -->


                  <div class="row" style="padding-bottom:5px;">
                  <!-- <div class="col-sm-1"></div> -->
                  <div class="col-sm-1"> <label for="recipient">Recipient:</label></div>
                  <div class="col-sm-6"><input type="text" class="form-control" name='phone' id='phone'  autocomplete="off"  placeholder="" /></div>
                 
                  <div class="col-sm-5">  Format : +10000000000,+10000000000 </div>
                  
                  <div class="input-group-btn">
                                <!-- <button type="button" class="btn btn-primary btn-lg" id="search">Get Value</button> -->
                            </div>
                  
                  <input type="hidden" class="form-control" name='formatted_phone_numbers' id='formatted_phone_numbers'  />
                 
                  </div>
  
                  <div class="row" style="padding-bottom:5px;">
                  <!-- <div class="col-sm-1"></div> -->
                  <div class="col-sm-1"> <label for="message">Message:</label></div>
                  <div class="col-sm-6"><textarea class="form-control"  rows=4 cols=40 name='message' id='message' placeholder="Enter message" ></textarea></div>   
                  </div>
  
                  <div class="row" style="padding-bottom:5px;">
                  <!-- <div class="col-sm-1"></div> -->
                  <div class="col-sm-1"><label for="file">Attachment:</label></div>
                  <div class="col-sm-3"> <input type="file" class="form-control" name="fileToUpload[]" id="fileToUpload" multiple="multiple"/></div>    
                  </div>
  
                  <div class="row" style="padding-bottom:5px;">
                  <!-- <div class="col-sm-1"></div> -->
                  <div class="col-sm-1"></div>
                  <div class="col-sm-3">
                  <input type="submit" name="submit" id="submit" class="btn btn-primary" value="Send SMS"/>
                  <input type="reset" name="cancel" class="btn btn-secondary" value="Cancel"/>
              </div>    
                  </div>
                              
                  </form> 
    
      

                  



      </fieldset>
  
     
  
  </div>
  
  
  
   <div class="row "> 
   
      <div class="col-sm-4" style=" padding-left: 0px; padding-right: 0px;">
  
          <fieldset class="scheduler-border">
              <!-- <legend >Messages</legend>  -->
              
              
          
              <table id="example2"  class="display" style="width:100%" >
                  <thead>
                      <tr>
                          <th >Number</th>
                          <!-- <th>Receiver</th>
                          <th>Client Name</th>
                          <th>Date/Time</th>
                          <th>Message</th>                 -->
                      </tr>
                  </thead>
                  <tfoot>
                      <tr>
                          <th>Number</th>
                          <!-- <th>Receiver</th>
                          <th>Client Name</th>
                          <th>Date/Time</th>
                          <th>Message</th>                 -->
                      </tr>
                  </tfoot>
              </table>
  
          
          </fieldset>
      </div>
  
      <div class="col-sm-8" style="padding-right: 0px;">
          <fieldset class="scheduler-border">
              <!-- <legend >Thread</legend>  -->
              <div class="row"> 
              
              
              <table id="example3"  class="display" style="width:100%">
                  <thead>
                      <tr>
                          
                          <th>Message</th>                
                      </tr>
                  </thead>
                  <tfoot>
                      <tr>
                          <th>Message</th>                
                      </tr>
                  </tfoot>
              </table>
  
          
              </div>
          </fieldset>
  
      </div>

      <script>
  $(document).ready(function(){      
    $('#phone').tokenfield({
       // alert ("here");
        autocomplete :{
            source: function(request, response)
            {
                jQuery.get('searchCustomer.php', {
                    query : request.term
                }, function(data){
                    data = JSON.parse(data);
                    response(data);
                });
            },
            delay: 100
        }
    });
    $('#submit').click(function(){
     
        $('#formatted_phone_numbers').val($('#phone').val().replace(/\(|\)|\-|\s/g, ""));
    });
    $('#phone').on('tokenfield:createtoken', function (event) {
        var tokens = $(this).tokenfield('getTokens');
        $.each(tokens, function(index, token) {
            token.value.replace(/\(|\)|\-|\s/g, "");            
            //alert (token.value);
            // if (token.value === event.attrs.value)
            //     event.preventDefault();
        });
    });
  });
</script>
  
  </div>
  
  
  
  
  
  

    <!-- ------------------------------ -->
    </div>
                               
                
                <!-- <div class="modal-footer"> -->
                    <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button> -->
                <!-- </div> -->
            </div>
        </div>
    </div>
</div>




</body>


