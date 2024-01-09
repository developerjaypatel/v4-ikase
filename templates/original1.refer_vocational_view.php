<div class="refer_vocational">
	<form id="refer_vocational_form">
    <input type="hidden" id="case_id" name="case_id" value="<%=id %>" enctype="multipart/form-data" />
	<table width="100%" cellpadding="2" cellspacing="0">
    	<tr>
        	<td align="left" valign="top" style="border-bottom:1px solid white; padding-bottom:10px">
            	<div style="background:orange; color:black; font-weight:bold; padding: 5px">
                	Maximum Medical Improvement Date is not set. Setting the MMI Date will help you notify your client of free, state-funded, career counseling and training benefits.
                </div>
            </td>
        </tr>
    	<tr>
    	  <td align="left" valign="top" style="border-bottom:1px solid white; padding-bottom:10px">
              Max Med Improvement  Date:
              <br />
              <input type="date" name="max_med_date" id="max_med_date" class="form_input" />          
          </td>
  	  	</tr>
   		<tr>
    	  <td align="left" valign="top" style="border-bottom:1px solid white; padding-bottom:10px">
              <input type="radio" name="voucher" id="voucher_sjdb" value="SJDB" class="form_input" />&nbsp;SJDB Voucher<br />
              Supplemental Job Displacement Benefits                             
          </td>
  	  	</tr>
        <tr>
    	  <td align="left" valign="top" style="border-bottom:1px solid white; padding-bottom:10px">          		
                <input type="radio" name="voucher" id="voucher_rtwsp" value="RTWSP" class="form_input" />&nbsp;RTWSP Voucher<br />
              Return To Work Supplemental Program (Only valid for injuries that occurred on or after January 1, 2013)
            
          </td>
  	  	</tr>
    	<tr>
    	  <td align="left" valign="top" style="border-bottom:1px solid white; padding-bottom:10px">
   	      You may include the Physician's Return to Work &amp; Voucher Report (DWC AD 10133.36 or similar), along with any notices you have sent to the Applicant regarding Occupational Counseling if you wish. (Optional)
          </td>
  	  	</tr>
    	<tr>
    	  <td align="left" valign="top">
              <input id="FileUpload1" name="FileUpload1" type="file" multiple="false" class="form_input">
          </td>
  	  </tr>
    	<tr>
    	  <td align="left" valign="top" style="padding-top:20px">
          		<button class="btn btn-primary" id="submit_refer" disabled="disabled">Submit Referral</button>
          </td>
  	  </tr>
    	<tr style="display:none">
    	  <td align="left" valign="top"><p>You will send to these emails after document upload and they  hit send<br>
    	    <a href="mailto:cavoucher@gmail.com">cavoucher@gmail.com</a><br>
    	    <a href="mailto:voucherQRR@gmail.com">voucherQRR@gmail.com</a><br>
    	    <a href="mailto:stvpineda@gmail.com">stvpineda@gmail.com</a></p>
            <p>You will send the Document they uploaded with the Summary  Demographic document</p>
          <p>PRS Vocational Services - Name of the Company its going to</p>     
          </td>     
  	  </tr>
    </table>
    </form>
    <iframe width="1px" height="1px" src="reports/demographics_sheet.php?case_id=<%=case_id %>" style="display:none"></iframe>
</div>