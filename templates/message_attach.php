<div style="position:relative" id="message_attach_holder">
    
    <div>
    <form class="message_attach_form" style="height:30px;">
        <div style="border:0px solid red">
            <div style="display:inline-block; border:0px solid yellow">
                <input id="file_upload" name="file_upload" type="file" multiple="true">
                &nbsp;<div id="manage_documents_link_holder" style="display:none">
                	<button id="manage_documents_button" class="btn btn-xs btn-primary">Manage Documents</button>
                </div>
            </div>
            <div style="display:none; border:0px solid green; vertical-align:top; margin-top:0px; margin-right:-50px; float:right">
            <a id="upload_it_five" style="position: relative; top: 8px;color:white; cursor:pointer">
                <i class="glyphicon glyphicon-open" style="color:#FFFFFF; font-size:20px">&nbsp;</i>
            </a>
            </div>
        </div>
        <div id="queue"></div>
    </form>
    </div>
    <div id="send_queue"></div>
    <div style="position:absolute; top:60px; left:-20px;">
    <button id="select_case_documents" style="cursor:pointer;margin-left: 84px !important; display:none" class="btn btn-xs">Case Documents</button>
    </div>
    <div id="batch_indicator"></div>
    <div id="message_attach_all_done"></div>
</div>
<script language="javascript">
$( "#message_attach_all_done" ).trigger( "click" );
</script>