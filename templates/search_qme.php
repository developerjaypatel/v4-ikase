<div class="qme white_text glass_header_no_padding" style="padding:10px; border:1px solid white">
    <table cellpadding="5" cellspacing="3" style="background:url(img/glass_card_fade_3.png) no-repeat; border:#FFFFFF 1px solid; border-radius: 5px 5px 5px 5px;border-collapse: collapse; border-spacing: 0;" width="385px" border="0">
    	<tr>
        	<td colspan="2"><span id="panel_title" style="font-size:1.5em;">QME Search</span><br /><hr/></td>
        </tr>
        <tr>
            <td colspan="2" align="left" valign="top" style="margin-left:20px">
            Specialty&nbsp;&nbsp;
                <select name="scode" id="scode" style="width:280px">
                    <option value="Select from List" selected></option>        
                    <option value="ACA">Acupuncturist</option>                
                    <option value="MAI">Allergy and Immunology</option>                
                    <option value="DCH">Chiropractic</option>                
                    <option value="DEN">Dentistry</option>                
                    <option value="MDE">Dermatology</option>                
                    <option value="MEM">Emergency Medicine</option>                
                    <option value="MFP">Family Practice</option>                
                    <option value="MPM">General Preventive Medicine</option>                
                    <option value="MHH">Hand</option>                
                    <option value="MMM">Internal Medicine</option>                
                    <option value="MMV">Internal Medicine - Cardiovascular Disease</option>                
                    <option value="MME">Internal Medicine - Endocrinology, Diabetes and Metabolism</option>                
                    <option value="MMG">Internal Medicine - Gastroenterology</option>                
                    <option value="MMH">Internal Medicine - Hematology</option>                
                    <option value="MMI">Internal Medicine - Infectious Disease</option>                
                    <option value="MMN">Internal Medicine - Nephrology</option>                
                    <option value="MMP">Internal Medicine - Pulmonary Disease</option>                
                    <option value="MMR">Internal Medicine - Rheumatology</option>                
                    <option value="MNS">Neurological Surgery</option>                
                    <option value="MPN">Neurology</option>                
                    <option value="MOG">Obstetrics and Gynecology</option>                
                    <option value="MPO">Occupational Medicine</option>                
                    <option value="MMO">Oncology -  Internal Medicine</option>                
                    <option value="MOP">Ophthalmology</option>                
                    <option value="OPT">Optometry</option>                
                    <option value="MOS">Orthopaedic Surgery</option>                
                    <option value="MTO">Otolaryngology</option>                
                    <option value="MPA">Pain Medicine</option>                
                    <option value="MHA">Pathology</option>                
                    <option value="MPR">Physical Medicine &amp; Rehabilitation</option>                
                    <option value="MPS">Plastic Surgery</option>                
                    <option value="POD">Podiatry</option>                
                    <option value="MPD">Psychiatry</option>                
                    <option value="PSY">Psychology</option>                
                    <option value="PSN">Psychology - Clinical Neuropsychology</option>                
                    <option value="MNB">Spine</option>                
                    <option value="MSY">Surgery</option>                
                    <option value="MSG">Surgery - General Vascular</option>                
                    <option value="MTS">Thoracic Surgery</option>                
                    <option value="MTT">Toxicology</option>                
                    <option value="MUU">Urology</option>               
              </select>
            </td>
        </tr>
        <tr>
          <td align="left" valign="top" style="margin-left:20px">
            Distance&nbsp;&nbsp;&nbsp;<input type="text" name="radius" id="radius" maxlength="4" size="4" value="30">&nbsp;Miles
          </td>
          <td align="left" valign="top"><strong>From Zip</strong>&nbsp;&nbsp;&nbsp;<input type="text" maxlength="5" size="5" name="zip" id="zip" value="<%=zip %>"></td>
        </tr>
        <tr>
          <td colspan="2" align="right" valign="top"><input id="qme_button" type="button" value="Search" /></td>
      </tr>
    </table>
</div>
<div id="qme_list"></div>