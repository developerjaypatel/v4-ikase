<div class="document_search" style="width:300px">
	<form id="document_search_form">
	<div>
        <span style="width:220px; font-size:1.1em;"><b>Search: </b>
        <br />
        <input type="text" name="document_name" id="document_name" value="" style="width:220px" />
        </span>
    </div>
	<div>
        <span style="width:220px; font-size:1.1em;"><b>Type: </b>
        <br />
        <select name="document_type" id="document_type" style="width:220px">
                  <option value="">Filter By Type</option>
                  <option value="client">Client</option>
                  <option value="carrier">Carrier</option>
                  <option value="correspondence">Correspondence</option>
                  <option value="letters">Letters</option>
                  <option value="defense_attorney">Defense Attorney</option>
                  <option value="document">Document</option>
                  <option value="employment">Employement</option>
                  <option value="notes">Notes</option>
                  <option value="medical">Medical</option>
              </select>
        </span>
        <!--
        &nbsp;&nbsp;
        <span style="width:220px; font-size:1.1em;"><b>Start: </b>
        <input type="text" name="document_start_date" id="document_start_date" value="" style="width:14%" /></span>
        &nbsp;&nbsp;
        <span style="width:220px; font-size:1.1em;"><b>End: </b>
        <input type="text" name="document_end_date" id="document_end_date" value="" style="width:14%" /></span>
        -->
	</div>
    <div style="padding-top:20px">
        <button id="document_search_button" class="btn btn-primary">Search</button>
	</div>
    </form>
</div>
<div id="document_search_done"></div>
<script language="javascript">
$( "#document_search_done" ).trigger( "click" );
</script>
