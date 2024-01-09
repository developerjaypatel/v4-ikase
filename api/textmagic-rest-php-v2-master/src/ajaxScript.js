




$(document).ready(function(e){


    $("#btnOpenModal").click(function(){
       

        if ( ! $.fn.DataTable.isDataTable( '#example2' ) ) {
            var table2 = $('#example2').DataTable( {
        
                "processing": true,
                "serverSide": true,
                // "order": [[3, 'desc']],
                "ajax": "listMessagesContacts.php",   
                "bInfo" : false,
                //"searchPlaceholder": "SEARCH MEMBER",   
                  columns: [            
                    { "data": null,
                    "width" : '250px',
                     render: function(data, type, row, meta) {
                       return '<div class="messageBox" > <p> ' + data[0] +'</p> <div class="">'  + data[4] + '</div><span class="time-right">' + data[3] + '</span></div>'
                     }},
                  ] ,
                  "drawCallback": function( settings ) {
                    $("#example2 thead").remove();
                    $("#example2 tfoot").remove();
                }   ,
                language: { search: "",searchPlaceholder: "Search Message or Client" },       
                  
            } );
        
          }

        
        
        var data = "";
        $('#example2 tbody').on('click', 'tr', function () {
            var plainArray = table2.row(this).data();
            //alert (plainArray[0]);
            table3.ajax.url('listMessageThread.php?phone=' + plainArray[0]).load();
        });
    
        
        if ( ! $.fn.DataTable.isDataTable( '#example3' ) ) {
            var table3 = $('#example3').DataTable( {
            
                "processing": true,
                "serverSide": true,
                //"order": [[3, 'desc']],
                "ajax": "listMessages.php",
                
                "bInfo" : false,
                //"searchPlaceholder": "SEARCH MEMBER",
                columns: [            
                    { "data": null,
                     render: function(data, type, row, meta) {
                       return '<div class="threadMessageBox"> <p>'  + data[4] + '</p> <span class="time-right">' + data[3] + '</span></div>'
                     }},
                  ] ,
                  "drawCallback": function( settings ) {
                    $("#example3 thead").remove();
                    $("#example3 tfoot").remove();
                }   ,
                language: { search: "",searchPlaceholder: "Search Messages" }, 
               
                
            } );
        }

    
       
        //table3.search(data[0]).draw();
        // table
        // .order( [ 3, 'desc' ])
        // .draw();
        //"order": [[3, 'desc']],
        
        //$('.dataTables_filter input').attr("placeholder", "Search for users");
    
    
    $.ajax({
    
            url : 'incoming-sms-callback.php',
            type : 'GET',
            data : {
               //term :  nameOrPhone
            },
            dataType:'json',
            success : function(data) {              
               // $('#example').draw();
                
            },
            error : function(request,error)
            {
               // alert("Request: "+JSON.stringify(request));
            }
        });
    

// Submit form data via Ajax
$("#fupForm").on('submit', function(e){
        
    e.preventDefault();
    $.ajax({
        type: 'POST',
        url: 'sendsms.php',
        data: new FormData(this),
        dataType: 'json',
        contentType: false,
        cache: false,
        processData:false,
        // beforeSend: function(){
        //     $('.submitBtn').attr("disabled","disabled");
        //     $('#fupForm').css("opacity",".5");
        // },
        //success: function(response){
        //     $('.statusMsg').html('');
        //     if(response.status == 1){
        //         $('#fupForm')[0].reset();
        //         alert(response.message);
        //         $('.statusMsg').html('<p class="alert alert-success">'+response.message+'</p>');
        //     }else{
        //         $('.statusMsg').html('<p class="alert alert-danger">'+response.message+'</p>');
        //     }
        //     $('#fupForm').css("opacity","");
        //     $(".submitBtn").removeAttr("disabled");
        // },
        complete: function(jqXHR) {
            if(jqXHR.readyState === 4) {
            //    alert(jqXHR.message);
            table2.ajax.reload( null, false );
               
            }}
    });
});






      });


    // var table = $('#example').DataTable( {
        
    //     "processing": true,
    //     "serverSide": true,
    //     "order": [[3, 'desc']],
    //     "ajax": "listMessages.php",
       
    //     //"searchPlaceholder": "SEARCH MEMBER",
    //     "columnDefs": [
    //         { "width": "10%", "targets": 0 },
    //         { "width": "10%", "targets": 1 },
    //         { "width": "20%", "targets": 2 },
    //         { "width": "15%", "targets": 3 },
    //         { "width": "50%", "targets": 4 }
    //       ]
        
    // } );



//-------------------------------------------------








// siteTable.on( 'select', function () {
//     usersTable.ajax.reload();
 
//     usersEditor
//         .field( 'users.site' )
//         .def( siteTable.row( { selected: true } ).data().id );
// } );
 
// siteTable.on( 'deselect', function () {
//     usersTable.ajax.reload();
// } );

//-----------------------------------------



    // $(function() {
    //     $("#example2 .dataTables_filter input").autocomplete({
    //         source: "searchClientsByNamePhone.php",
    //         select: function( event, ui ) {
    //             event.preventDefault();
    //             $("#example2 .dataTables_filter input").val(ui.item.value);
    //            // $(".dataTables_filter input").placeHolder("hsbsd");
               
    //            // getClientMessages(this.value);
    //            table
    //            .search(this.value)
    //            .draw();
    //         } 
    //     });
    // });

         

    
});


function getClientMessages (nameOrPhone){
   
    // $.ajax({

    //     url : 'listMessagesByNamePhone.php',
    //     type : 'GET',
    //     data : {
    //        term :  nameOrPhone
    //     },
    //     dataType:'json',
    //     success : function(data) {              
    //        // $('#example').draw();
            
    //     },
    //     error : function(request,error)
    //     {
    //         alert("Request: "+JSON.stringify(request));
    //     }
    // });
}
