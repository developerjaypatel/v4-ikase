<!DOCTYPE html>
<html>
    <head>
        <title>SEND SMS - SEARCH CUSTOMER BY NAME/PHONE</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>        
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tokenfield/0.12.0/css/bootstrap-tokenfield.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tokenfield/0.12.0/bootstrap-tokenfield.js"></script>
    </head>
    <body>
        <br />
        <br />
        <div class="container">
            <h1 align="center"></h1>
            <br />
            <br />
            <br />
            <div class="row">
                <div class="col-md-2">

                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Enter Customer Name/Phone</label>
                        <div class="input-group">
                            <input type="text" id="search_data" placeholder="" autocomplete="off" class="form-control input-lg" />
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-primary btn-lg" id="search">Get Value</button>
                            </div>
                        </div>
                        <br />
                        <span id="country_name"></span>
                    </div>
                </div>
                <div class="col-md-2">

                </div>
            </div>
        </div>
    </body>
</html>
<script>
  $(document).ready(function(){
      
    $('#search_data').tokenfield({
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

    $('#search').click(function(){
        $('#country_name').text($('#search_data').val().replace(/\(|\)|\-|\s/g, ""));
    });



    $('#search_data').on('tokenfield:createtoken', function (event) {
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
