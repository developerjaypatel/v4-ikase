<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name=viewport content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?php echo $page_title; ?></title>
<meta http-equiv=Content-Type content="text/html; charset=windows-1252">
<script src="../js/jquery.js"></script>
</head>
<body>
<table>
    <tr>
        <td>
            <table>
                <tr>
                    <td>
                        Enter a Cellphone Number
                    </td>
                    <td>
                        <input type="text" id="cellphone" name="cellphone" />
                    </td>
                    <td>
                        <input type="button" id="submit_cellphone" name="sumbit_cellphone" value="Submit" />
                    </td>
                </tr>
                <tr>
                    <td>
                        Enter an Email Address
                    </td>
                    <td>
                        <input type="text" id="email" name="name" />
                    </td>
                    <td>
                        <input type="button" id="submit_email" name="sumbit_email" value="Submit" />
                    </td>
                </tr>
             <!--   <tr>
                    <td>
                        Click to to run nexmo
                    </td>
                    <td>
                        <input type="button" id="nexmo" name="nexmo" value="Run Nexmo" />
                    </td>
                    <td>
                        <span id="response"></span>
                    </td>
                </tr> -->
            </table>
        </td>
    </tr>
</table>
<script>
$(document).ready(function(){
    // $("#nexmo").on("click", function(event){
    //     event.preventDefault();
    //     var cellphone = $("#cellphone").val();
    //     var url = "temp.php?cellphone=" + cellphone;

    //     $.ajax({
    //         url: url,
    //         type: "GET",
    //         dataType: "json",
    //         success:function(data){
    //             console.log(data);
    //         }
    //     });
    // });

    $("#submit_email").on("click", function(event){
        event.preventDefault();
        var email = $("#email").val();
        var url = "temp.php?email=" + email;

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success:function(data){
                console.log(data);
            }
        });
    });

    $("#submit_cellphone").on("click", function(event){
        var cellphone = $("#cellphone").val();
        var url = "https://rest.nexmo.com/sms/json?api_key=1623e20b&api_secret=4aad68ee8c2ca1d4&from=12046743938&to=" + cellphone +"&text=Welcome+to+Nexmo";

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success:function(data){
                console.log(data);
            }
        });
    });
});
</script>
</body>
</html>