<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body unselectable="on">
    <div class="window">
        <div class="headerPanelName ui-corner-top">
            <img src="/images/calendarIcon.jpg" alt="">
            <span class="headerName">Date Calculator</span><div class="aa-header-elpsFade" style="display:none"></div>
        </div>
        <div class="middle">
            <div class="middle_area">
                <div id="up_holder" style="border-radius: 5px; border: 1px solid rgb(204, 204, 204); border-image: none; height: 24px; margin-right: 4px; float: left; box-shadow: inset 0px 1px 1px rgba(0,0,0,0.075); background-color: rgb(255, 255, 255);">
                    <div style="margin-right: 2px; float: left;">
                        <input class="amicus-textfield control-updown-input" vtype="numeric" style="border: currentColor; border-image: none; width: 90px; height: 18px; text-align: right; box-shadow: none;" type="text" id="updwn" maxvalue="32600">
                    </div>
                    <div class="updown" style="margin-right: 3px;">
                        <div class="upbtn">
                        </div>
                        <div class="dwnbtn">
                        </div>
                    </div>
                </div>
                <div id="hid" style="float: left; width: 125px; margin-top: 5px; display: none;">
                    Count the Number of</div>
                <select name="" id="Days" class="drop-down">
                    <option value="0">business days</option>
                    <option value="1">calendar days</option>
                    <option value="2">weeks</option>
                    <option value="3">months</option>
                    <option value="4">years</option>
                </select>
                <select name="" class="drop-down" id="after-before-between">
                    <option value="0">after</option>
                    <option value="1">before</option>
                    <option value="2">between</option>
                </select>
            </div>
            <div class="middle_area">
                <div class="middle_area_in" style="margin-top:0px;">
                    <div class="first-date-time">
                        
                        <div style="margin-left:10px;">
                            <span class="k-widget k-datepicker k-header" style="width: 120px;"><span class="k-picker-wrap k-state-default"><input id="date_picker_start" style="width: 100%;" data-role="datepicker" type="text" class="k-input" role="textbox" aria-haspopup="true" aria-expanded="false" aria-owns="date_picker_start_dateview" aria-disabled="false" aria-readonly="false" aria-label="Current focused date is Tuesday, February 21, 2017"><span unselectable="on" class="k-select" role="button" aria-controls="date_picker_start_dateview"><span unselectable="on" class="k-icon k-i-calendar">select</span></span></span></span>
                        </div>
                    </div>
                    <div style="float: left; margin-top: 6px; width: 50px; text-align: center; display: none;" id="and">
                        and</div>
                    <div class="second-date-time">
                        
                        <div id="end_date_div" style="margin-left: 10px; display: none;">
                            <span class="k-widget k-datepicker k-header" style="width: 120px;"><span class="k-picker-wrap k-state-default"><input id="date_picker_end" style="width: 100%;" data-role="datepicker" type="text" class="k-input" role="textbox" aria-haspopup="true" aria-expanded="false" aria-owns="date_picker_end_dateview" aria-disabled="false" aria-readonly="false" aria-label="Current focused date is Tuesday, February 21, 2017"><span unselectable="on" class="k-select" role="button" aria-controls="date_picker_end_dateview"><span unselectable="on" class="k-icon k-i-calendar">select</span></span></span></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="middle_area" style="height: 50px">
                <div class="middle_area_in" style="width: 236px">
                    <span id="ifdayfallsspan">If day falls on a weekend or holiday</span></div>
                <div class="middle_area_in" style="width: 114px">
                    National Holidays</div>
                <div class="middle_area_in" style="width: 244px; margin-top: 0px; margin-left: 0px;">
                    <select id="ifdaysfallselect" name="ifdaysfall" disabled="disabled" style="width: 235px" class="drop-down">
                        <option value="0">Show it anyway</option>
                        <option value="1">Show the previous business day</option>
                        <option value="2" selected="selected">Show the next business day</option>
                    </select>
                    &nbsp;
                </div>
                <div class="middle_area_in" style="width: 138px; margin-top: 0px; margin-left: 0px;">
                    <select name="ifdaysfall0" id="holiday" class="drop-down">
                        <option value="0">Canada</option>
                        <option value="1">UK</option>
                        <option value="2" selected="selected">USA</option>
                    </select>
                </div>
            </div>
            <div class="middle_area" style="margin-top:15px;">
                <button type="button" id="Calc" class="button-generic-rounded">Calculate</button>
                <input type="text" id="result" readonly="readonly" class="amicus-textfield" style="width: 135px;margin-left:5px;padding-left:5px;color:black;height:18px;" name="">
                <button type="button" id="showday" class="button-generic-rounded" style="margin-left:8px;" disabled="disabled">
                <img src="/images/showday.png" class="fleft" style="margin: 0px 4px 0px 0px;">Show me the day</button>
            </div>
        </div>
        <div class="ui-corner-bottom panelFooter">
            <div class="footerRightButtons">
                <button type="button" class="button-generic-rounded" id="btnClose">
                    Close</button>
            </div>
        </div>
    </div>


<div id="e206ecd3-c9dd-4f17-a4d7-0ab46ff873c5" data-role="calendar" class="k-widget k-calendar" style="display: none;"><div class="k-header"><a href="#" role="button" class="k-link k-nav-prev" aria-disabled="false"><span class="k-icon k-i-arrow-w"></span></a><a href="#" role="button" aria-live="assertive" aria-atomic="true" class="k-link k-nav-fast" aria-disabled="false">February 2017</a><a href="#" role="button" class="k-link k-nav-next" aria-disabled="false"><span class="k-icon k-i-arrow-e"></span></a></div><table tabindex="0" role="grid" class="k-content" cellspacing="0" aria-activedescendant="e206ecd3-c9dd-4f17-a4d7-0ab46ff873c5_cell_selected"><thead><tr role="row"><th scope="col" title="Sunday">Su</th><th scope="col" title="Monday">Mo</th><th scope="col" title="Tuesday">Tu</th><th scope="col" title="Wednesday">We</th><th scope="col" title="Thursday">Th</th><th scope="col" title="Friday">Fr</th><th scope="col" title="Saturday">Sa</th></tr></thead><tbody><tr role="row"><td class="k-other-month k-weekend" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/0/29" title="Sunday, January 29, 2017">29</a></td><td class="k-other-month" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/0/30" title="Monday, January 30, 2017">30</a></td><td class="k-other-month" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/0/31" title="Tuesday, January 31, 2017">31</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/1" title="Wednesday, February 01, 2017">1</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/2" title="Thursday, February 02, 2017">2</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/3" title="Friday, February 03, 2017">3</a></td><td class="k-weekend" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/4" title="Saturday, February 04, 2017">4</a></td></tr><tr role="row"><td class="k-weekend" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/5" title="Sunday, February 05, 2017">5</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/6" title="Monday, February 06, 2017">6</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/7" title="Tuesday, February 07, 2017">7</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/8" title="Wednesday, February 08, 2017">8</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/9" title="Thursday, February 09, 2017">9</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/10" title="Friday, February 10, 2017">10</a></td><td class="k-weekend" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/11" title="Saturday, February 11, 2017">11</a></td></tr><tr role="row"><td class="k-weekend" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/12" title="Sunday, February 12, 2017">12</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/13" title="Monday, February 13, 2017">13</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/14" title="Tuesday, February 14, 2017">14</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/15" title="Wednesday, February 15, 2017">15</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/16" title="Thursday, February 16, 2017">16</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/17" title="Friday, February 17, 2017">17</a></td><td class="k-weekend" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/18" title="Saturday, February 18, 2017">18</a></td></tr><tr role="row"><td class="k-weekend" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/19" title="Sunday, February 19, 2017">19</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/20" title="Monday, February 20, 2017">20</a></td><td class="k-today" role="gridcell" aria-selected="true" id="e206ecd3-c9dd-4f17-a4d7-0ab46ff873c5_cell_selected"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/21" title="Tuesday, February 21, 2017">21</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/22" title="Wednesday, February 22, 2017">22</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/23" title="Thursday, February 23, 2017">23</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/24" title="Friday, February 24, 2017">24</a></td><td class="k-weekend" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/25" title="Saturday, February 25, 2017">25</a></td></tr><tr role="row"><td class="k-weekend" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/26" title="Sunday, February 26, 2017">26</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/27" title="Monday, February 27, 2017">27</a></td><td role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/1/28" title="Tuesday, February 28, 2017">28</a></td><td class="k-other-month" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/2/1" title="Wednesday, March 01, 2017">1</a></td><td class="k-other-month" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/2/2" title="Thursday, March 02, 2017">2</a></td><td class="k-other-month" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/2/3" title="Friday, March 03, 2017">3</a></td><td class="k-other-month k-weekend" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/2/4" title="Saturday, March 04, 2017">4</a></td></tr><tr role="row"><td class="k-other-month k-weekend" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/2/5" title="Sunday, March 05, 2017">5</a></td><td class="k-other-month" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/2/6" title="Monday, March 06, 2017">6</a></td><td class="k-other-month" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/2/7" title="Tuesday, March 07, 2017">7</a></td><td class="k-other-month" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/2/8" title="Wednesday, March 08, 2017">8</a></td><td class="k-other-month" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/2/9" title="Thursday, March 09, 2017">9</a></td><td class="k-other-month" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/2/10" title="Friday, March 10, 2017">10</a></td><td class="k-other-month k-weekend" role="gridcell"><a tabindex="-1" class="k-link" href="#" data-value="2017/2/11" title="Saturday, March 11, 2017">11</a></td></tr></tbody></table><div class="k-footer"><a href="#" class="k-link k-nav-today" title="Tuesday, February 21, 2017">Tuesday, February 21, 2017</a></div></div><div aria-hidden="true" class="k-calendar-container k-popup k-group k-reset" id="date_picker_start_dateview" data-role="popup" style="display: none; position: absolute;"></div><div aria-hidden="true" class="k-calendar-container k-popup k-group k-reset" id="date_picker_end_dateview" data-role="popup" style="display: none; position: absolute;"></div></body>
</html>