(function($){
    window.GlobalFunctions = {};
    //var GlobalFunctions = GlobalFunctions || {};
    GlobalFunctions.paginationFunc = 0;
    GlobalFunctions.currentDate = 0;
    $(document).ready(function() {
        var criteria = [],
            headerParam = '',
            searchCount = 0,
            initCaseCount = 0,
            feedPageHolder = '',
            initSearch = 0,
            caseOptionButton = 0,
            attorneys_print = {},
            attorneys_print_locations = {};

        // call page setup scripts
        homePage();
        caseBriefsPage();
        seminarPage();
        //bulletinPage();
        accordionPage();
        editAttorney();
        archivePage();
        searchPage();
        feedPages();
        aboutPage();
        referralPage();
        calculatorPage();
        modalSubmitConfirm();
        mull_people_search_page();


       		 //Navigation Menu Slider
            $('#main-menu li:first-child > a').attr({
                'id'    : 'nav-close'
            });
            $('#main-menu').addClass('text-left');

            $('#nav-expander').on('click',function(e){
                e.preventDefault();
                $('body').toggleClass('nav-expanded');
			    $('.menu_overlay').toggleClass('show_overlay');
				$('.container').toggleClass('blur');
            });

            $('#nav-close').on('click',function(e){
                e.preventDefault();
                $('body').removeClass('nav-expanded');
				$('.menu_overlay').toggleClass('show_overlay');
				$('.container').toggleClass('blur');
            });

			$('.menu_overlay').on('click',function(e){
                e.preventDefault();
                $('body').removeClass('nav-expanded');
				$('.menu_overlay').removeClass('show_overlay');
				$('.container').removeClass('blur');
            });


            // Initialize navgoco with default options
            $(".main-menu").navgoco({
                caret: '<span class="caret"></span>',
                accordion: false,
                openClass: 'open',
                save: true,
                cookie: {
                    name: 'navgoco',
                    expires: false,
                    path: '/'
                },
                slide: {
                    duration: 300,
                    easing: 'swing'
                }
            });

			// Search Button Expansion on Mobile

			$('.searchBox').focus(function(){
			if ($(window).width() <= 800){	
				$('.header_logo img').addClass('hide_logo');
			}
			});
			$('.searchBox').focusout(function(){
				$('.header_logo img').removeClass('hide_logo');
			});

            $('#btnPrinter').on('click', function(){
                var $this = $(this),
                    $area = $this.closest('body > .container').find('.printArea');

                console.log('clicked');
                window.print();
                //printPageArea();
                //console.log($area);
                //window.print()

            });
        $('#btnPrintDir').on('click', function () {
            var win = window.open(),
            // var
            //is_chrome = Boolean(win.chrome);
                i = 0, l, defaultImgClass,
                html = '', year = new Date().getFullYear(),
                years_as_a_specialist,
                year_hired,
                location_id,
                locations = [],
                attorneys = attorneys_print,
                attorneyHTML = '',
                adminAttorneys = getAdminPartners(attorneys),
                attorneys_locations = attorneys_print_locations,
                office_locations = [],
                len = globalLocations.length,
                currentDate = currentDate2();
            //mull_get_all_from_table("office").then(function (res) {
            /*len = globalLocations.length;
             for (i = 0; i < len; ++i) {
             locations.push(globalLocations[i]);
             }*/
            console.log(attorneys);
            console.log(typeof globalLocations);
            console.log(attorneys_locations);
            console.log(globalLocations);
            attorneyHTML = officeLocations(attorneys, globalLocations);
            // console.log(attorneyHTML);
            // return false;
            var browser=navigator.userAgent.toLowerCase();
            //console.log(browser.indexOf('firefox'));
            if(browser.indexOf('firefox') == -5) {

                html += '<style>' +
                    '*{' +
                  //  'border: 1px solid #0F0;' +
                    '}' +
                    'html, body{' +
                    'padding: 0;' +
                    'margin: 0;' +
                    //'font-family: "Source Sans Pro", sans-serif !important; ' +
                    '}'+
                    'body{' +
                    //         'border: 1px solid #F00;' +
                    '}'+
                    '.txtCenter{' +
                    'text-align: center' +
                    '}' +
                    '.floatRight{' +
                    'float: right !important' +
                    '}' +
                    '.directoryPage{' +
                    'height: auto;' +
                    'position:relative;' +
                    '}'  +
                    '.directoryPage .directory_mainBody{' +
                    'width: 95%' +
                    'margin-left: 10px;' +
                    'border-left: 1px solid transparent;' +
                    'display: inline-block !important;' +
                    // 'border: 1px solid #000;' +
                    // 'border: 1px solid #000;' +
                    // 'border: 1px solid #000;' +
                    '}'  +
                    '.directoryPage .directory_mainBody .directory_mainWrapper{' +
                  //  'position: relative;' +
                   'border-top: 1px solid #000;' +
                    'margin-top: 2px;' +
                    'padding-bottom: 10px;' +
                    '}' +
                    '.directoryPage .directory_sidebar{' +
                   // 'border-right: 1px solid #000;' +
                   'border-bottom: 1px solid transparent;' +
                    'height: 100% !important;' +
                    '}' +
                    '.directoryPage .addressWrapper{' +
                    'margin-top: 5px;' +
                    '}' +
                    '.directoryPage .printHeaderWrapper{' +
                    'text-align: center' +
                    '}' +
                    '.officeLocationTitle{' +
                    'font-size: 12px !important;' +
                    'font-weight: 700 !important;' +
                    '}' +
                    '.directoryPage .printHeaderWrapper{' +
                    'text-align: center !important;' +
                    'margin-bottom: 5px}' +
                    '.directoryPage .printSubHeaderWrapper{' +
                    // 'border: 1px solid #00F;' +
                    'text-align: center}' +
                    '.col-xs-2{' +
                    'width: 16.6%;}' +
                    '.col-xs-3{' +
                    'width: 22%;' +
                    'float: left}' +
                    '.col-xs-9{' +
                    'width: 75%;' +
                    'float: left}' +
                    '.col-xs-10{' +
                    'width: 83.3%;}' +
                    '.col-xs-12{' +
                    'width: 100%;' +
                    '}' +
                    '.printCols{' +
                    'display: block;' +
                    'margin: 0 !important;' +
                    'padding-left: 10px;' +
                    'height: 100% !important;' +
                    // 'border: 1px inset #CCC !important;' +
                    'width: 46%;' +
                    'float: left;' +
                    '}'+
                    '.printColLeft{' +
                    'border-left: 1px solid transparent !important;' +
                    // 'padding: 0;' +

                    '}'+
                    '.timeFooter{' +
                    'width: 100%;'+
                    'display: inline-block;'+
                    //'clear: both;' +
                    'margin-top: 5px;' +
                    '}'+
                    '.directory_mainBody{' +
                    // 'border: 1px solid #00F !important;' +
                    'width: 400px !important;' +
                    // 'padding: 0;' +
                    '}'+
                    '.timeFooterInnerWrapper{' +
                    'display: inline-block;' +
                    'text-align: center;' +
                    'float: right;' +
                    '}'+
                    'h3{' +
                    'margin: 10px 0;' +
                    // 'padding: 0;' +
                    '}'+
                    '.officeSection{' +
                    'display: inline-block;' +
                    'margin: 0 !important;' +
                    //'border: 1px solid #BDB !important;' +
                    'width: 100%;' +
                    'float: left;' +
                    '}' +
                    '.officeSection h3{' +
                    'margin: 5px 0 -1px 0 !important;' +
                    // 'padding: 0;' +
                    '}'+
                    'label{' +
                    'font-size: 9px' +
                    '}'+
                    'label.headerAtt{' +
                    'margin-left: 25px' +
                    '}' +
                    'label.mainAtts{' +
                    'margin-left: 25px' +
                    '}' +
                    'label.mainAtts:before{' +
                    'color: #000 !important;' +
                    'margin-top: 2px' +
                    // 'content: "u+2022"' +
                    'content: "* ' +
                    '}'+
                    'img.img-resp{' +
                    // 'margin: 10px' +
                    '}'+
                    '</style>';

           // }else{

                html += '<style>' +
                    '*{' +
                     //'border: 1px solid #0F0;' +
                    '}' +
                    'html, body{' +
                    'padding: 0;' +
                    'margin: 0;' +
                    //'font-family: "Source Sans Pro", sans-serif !important; ' +
                    '}'+
                    'body{' +
                    //         'border: 1px solid #F00;' +
                    '}'+
                    '.txtCenter{' +
                    'text-align: center' +
                    '}' +
                    '.floatRight{' +
                   // 'float: right !important' +
                    '}' +
                    '.directoryPage{' +
                   // 'height: auto;' +
                    'position:relative;' +
                    '}'  +
                    '.directoryPage .directory_mainBody{' +
                    'width: 95%' +
                    'margin-left: 10px;' +
                    'border-left: 1px solid transparent;' +
                   // 'display: inline-block !important;' +
                    // 'border: 1px solid #000;' +
                    // 'border: 1px solid #000;' +
                    // 'border: 1px solid #000;' +
                    '}'  +
                    '.directoryPage .directory_mainBody .directory_mainWrapper{' +
                    'display: inline-block;' +
                    'position: relative;' +
                    'border-top: 1px solid #000;' +
                    'margin-top: 2px;' +
                    //'padding-bottom: 10px;' +
                    '}' +
                    '.directoryPage .directory_sidebar{' +
                    // 'border-right: 1px solid #000;' +
                    'border-bottom: 1px solid transparent;' +
                   // 'height: 100% !important;' +
                    '}' +
                    '.directoryPage .addressWrapper{' +
                    'margin-top: 5px;' +
                    '}' +
                    '.directoryPage .printHeaderWrapper{' +
                    'text-align: center' +
                    '}' +
                    '.logoWrapperDiv{' +
                    //'font-size: 12px !important;' +
                    //'font-weight: 700 !important;' +
                    '}' +'.officeLocationTitle{' +
                    'font-size: 12px !important;' +
                    'font-weight: 700 !important;' +
                    '}' +
                    '.directoryPage .printHeaderWrapper{' +
                    'text-align: center !important;' +
                    'margin-bottom: 5px}' +
                    '.directoryPage .printSubHeaderWrapper{' +
                    // 'border: 1px solid #00F;' +
                    'text-align: center}' +
                    '.col-xs-2{' +
                    'width: 16.6%;}' +
                    '.col-xs-3{' +
                    'width: 22%;' +
                    'float: left}' +
                    '.col-xs-9{' +
                    'width: 75%;' +
                    'float: left}' +
                    '.col-xs-10{' +
                    'width: 83.3%;}' +
                    '.col-xs-12{' +
                    'width: 100%;' +
                    '}' +
                    '.printCols{' +
                    'display: inline-block;' +
                    'margin: 0 !important;' +
                    'padding-left: 10px;' +
                    //'height: 100% !important;' +
                    // 'border: 1px inset #CCC !important;' +
                    'width: 46%;' +
                    'float: left;' +
                    '}'+
                    '.printColLeft{' +
                    'border-left: 1px solid transparent !important;' +
                    // 'padding: 0;' +
                    '}'+'.timeFooter{' +
                    'width: 100%;'+
                    'display: inline-block;'+
                    //'clear: both;' +
                    'margin-top: 5px;' +
                    '}'+
                    '.directory_mainBody{' +
                    // 'border: 1px solid #00F !important;' +
                    'width: 400px !important;' +
                    // 'padding: 0;' +
                    '}'+
                    '.timeFooterInnerWrapper{' +
                    'display: inline-block;' +
                    'text-align: center;' +
                    'float: right;' +
                    '}'+
                    'h3{' +
                    'margin: 10px 0;' +
                    // 'padding: 0;' +
                    '}'+
                    '.officeSection{' +
                    'display: inline-block;' +
                    'margin: 0 !important;' +
                    //'border: 1px solid #BDB !important;' +
                    'width: 100%;' +
                    'float: left;' +
                    '}' +
                    '.officeSection h3{' +
                    'margin: 5px 0 -1px 0 !important;' +
                    // 'padding: 0;' +
                    '}'+
                    'label{' +
                    'font-size: 9px' +
                    '}'+
                    'label.headerAtt{' +
                    'margin-left: 25px' +
                    '}' +
                    'label.mainAtts{' +
                    'margin-left: 25px' +
                    '}' +
                    'label.mainAtts:before{' +
                    'color: #000 !important;' +
                    'margin-top: 2px' +
                    // 'content: "u+2022"' +
                    'content: "* ' +
                    '}'+
                    'img.img-resp{' +
                        'display: inline-block !important;' +
                        'width: 50% !important;' +
                        'margin-top: 40px !important;' +
                        'height: auto !important;' +
                        'border: 1px solid #00F !important;' +
                    '}'+
                    '</style>';
            }
            if(browser.indexOf('firefox') > -1) {

            }else{
                html += '<div class="pathWrapper"><label>'+pagePermalink+'</label></div>';
            }

            html += '<main id="directoryPage" class="directoryPage"><section class="col-xs-3 directory_sidebar" ' +
                'id="directory_sidebar"><div class="col-xs-12 logoWrapperDiv"><img src="'+site_url+'/files/mull_logo-1.svg" class="img-resp"></div>';
            //console.log(attorneys_locations);
            //console.log(site_url+'/files/mull_logo-1.svg');

            for (; i < len; ++i) {
                console.log(attorneys_locations);
                console.log(attorneys_locations[globalLocations[i]]);
                office_locations = attorneys_locations[globalLocations[i]];
                html += '<section class="col-xs-12 addressWrapper"><label class="officeLocationTitle">' + globalLocations[i] + '</label><address>' +
                    '<label>' + office_locations.addy1 + ' ' + office_locations.suite + '</label><br/>' +
                    '<label>' + office_locations.addy2 + '</label><br/>' +
                    '<label>' + office_locations.phone + ' ' + office_locations.fax + '</label><br/>' +
                    '</address></section>';
            }
            html += '</section><section class="col-xs-9 directory_mainBody"><section class="col-xs-12 printHeaderWrapper"><h3>ATTORNEY DIRECTORY</h3></section><section class="col-xs-12 printSubHeaderWrapper"><div class="titleWrapper"><label>' +
                'Firmwide Administrative Partners: </label>';

            for(i=0, len = adminAttorneys.length; i < len; ++i){
                if(i === 0){
                    html += '<label class="headerAtt">'+adminAttorneys[i]+'</label>';
                }else{
                    html += '<label class="headerAtt mainAtts">'+adminAttorneys[i]+'</label>';
                }


            }
            html += '</div></section><section class="col-xs-12 directoryRightBody floatRight">'+attorneyHTML+'</section></section>';
            html += '<div class="col-xs-12 timeFooter"><div class="timeFooterInnerWrapper"><label>Print Date:</label><br/><label>'+currentDate+'</label></div></div>';

            html += '</main>';

            self.focus();
            win.document.open();
            if(browser.indexOf('firefox') > -1) {
                win.document.write('<html><link rel="stylesheet" type="text/css" href="/build/print_ff.css"><body>');
            }else{
                win.document.write('<html><link rel="stylesheet" type="text/css" href="/build/print.css"><body>');
            }
            
            win.document.write(html);
            win.document.write('</body></html>');
            win.document.close(); // necessary for IE >= 10
            win.focus(); // necessary for IE >= 10
            setTimeout(function() { // wait until all resources loaded
                // continue to print
                win.print();
                win.close();
            }, 250);
        });
        $('#btnPrintDir2').on('click', function () {
            var data, id, value;
            console.log('click');
            /*if(!$('body').hasClass('page-id-39')){
                return false;

            }else{
                data = {
                    'action': 'm_getAllAttorneysJSON'
                };
                data = {
                 'action': 'mull_getAllAttorneys'
                 };
            }*/
            data = {
                'action': 'generateAttorneyPDF'
            };

            //console.log(data);
            $.ajax({
                type: "GET",
                url: wp_variables.ajax_url,
                data: data,
                success: function (res) {
                    console.log(res);
                    return false;
                    /*attorneys_print = res['atts'];
                    attorneys_print_locations = res['locations'];
                    console.log(res['atts']);*/
                },
                error: function (e, l) {
                    console.log(e);
                }
            });
        });
        /**************
         * MAIN FUNCTIONS*
         *************/
        function mull_people_search_page() {
            var data = '', id, value;

            if(!$('body').hasClass('page-id-39')){
                return false;

            }else{
                data = {
                    'action': 'm_getAllAttorneysJSON'
                };
                /*data = {
                    'action': 'mull_getAllAttorneys'
                };*/
            }

           // console.log(data);
            $.ajax({
                type: "GET",
                url: wp_variables.ajax_url,
                data: data,
                success: function (res) {
                    var loc;
                    console.log(res);
                    attorneys_print = res['atts'];
                    attorneys_print_locations = res['locations'];
                    console.log(res['atts']);

                    loc = getUrlParameter('loc');

                    if(typeof loc != 'undefined'){
                        loc = loc.split(' Office');
                        loc.pop();
                        loc = loc.join(' ');

                        $('#sortLocation li button:contains('+loc+')').trigger('click');
                    }
                },
                error: function (e, l) {
                    console.log(e);
                }
            });
        }

        function getAdminPartners(obj) {
            var arr = [];
            for(x in obj){
                if(obj[x].secondary_title === 'Administrative Senior Partner'){
                    if(obj[x].middle_initial === ''){
                        arr.push(obj[x].first_name +' ' + obj[x].last_name);
                    }else{
                        arr.push(obj[x].first_name +' '+ obj[x].middle_initial+' ' + obj[x].last_name);
                    }
                }
            }

            return arr;
        }

        function officeLocations(atts, locs) {
            var result = [],
                tempArr = [],
                tempObj = {},
                html = '',
                htmlLeft = '<div class="printCols printColLeft">',
                htmlRight = '<div class="printCols printColRight">',
                i = 0,
                j = 0,
                len = locs.length,
                len2 = 0,
                locsLeft = Math.ceil(len / 2);
            name = '';

            for(; i< len; ++i){
                result[locs[i]] = [];
            }

            //for(i = 0, len = atts.length; i< len; ++i){
            for(i in atts){

                if (atts[i]['middle_initial'] == null) {
                    atts[i]['middle_initial'] = '';

                    name = atts[i]['first_name'] + " " + atts[i]['last_name'];
                }else{
                    name = atts[i]['first_name'] + " " + atts[i]['middle_initial'] + " " + atts[i]['last_name'];
                }

                tempObj = {
                    'name'   :   name,
                    'managing'  :  atts[i]['managing_attorney']
                };
                if((atts[i]['office_location'] + ' Office') in result){
                    result[atts[i]['office_location'] + ' Office'].push(tempObj);
                }
            }

            for(i = 0, len = locs.length; i < len; ++i){
                html = '<figure class="officeSection"><h3>'+locs[i]+'</h3>';
                console.log(result[locs[i]].length);
                (function(counter){
                    var html1 = '',
                        html2 = '';
                    for(var j = 0, len2 = result[locs[counter]].length; j < len2; ++j){
                        if (result[locs[counter]][j].managing > 0) {
                            result[locs[counter]][j].name += ' - Managing Attorney';
                            html1 += '<label>' + result[locs[i]][j].name + '</label><br/>';
                        }else{
                            html2 += '<label>' + result[locs[i]][j].name + '</label><br/>';
                        }
                        // }
                    }
                    html += html1 + html2;
                })(i);
                html += '</figure>';
                if(i < locsLeft){
                    htmlLeft += html;
                }else{
                    htmlRight += html;
                }
                html = '';
            }
            htmlLeft += '</div>';
            htmlRight += '</div>';
            // console.log(htmlLeft);
            // console.log(htmlRight);
            html += '<div class="directory_mainWrapper">';
            html += htmlLeft + htmlRight + '</div>';
            // console.log(html);
            return html;
        }
        function mull_parse_attorneys_data(attorneys) {
            var i = 0, html;
            console.log(wp_variables.upload_dir);
            for (; i < attorneys.length; ++i) {
                if (attorneys[i]['middle_initial'] == null) {
                    attorneys[i]['middle_initial'] = '';
                }

                if (attorneys[i]['profile_image'] == '') {
                    attorneys[i]['profile_image'] = 'profile_img.jpg';
                }
                // console.log(attorneys[i]['pending_draft']);
                html = "<div class='col-sm-6 col-md-4'><div class='mull_attorney'><a href='" + wp_variables.site_url + "/wp-admin/admin.php?page=wcc-add-new&id=" + attorneys[i]['user_id'] +
                    "'><img src='" + wp_variables.upload_dir + "/../../../uploads/headshots/" +
                    attorneys[i]['profile_image'] + "'/>" + "<figcaption>" +
                    attorneys[i]['first_name'] + " " + attorneys[i]['middle_initial'] + " " + attorneys[i]['last_name'] +
                    "<br>" + attorneys[i]['secondary_title'] +
                    "<br>" + attorneys[i]['email'] +
                    "</figcaption></a>";
                if (attorneys[i]['pending_draft'] === '1') {
                    html += '<section class="pendingDefault">Pending Changes</section>';
                }

                html += "</div></div>";
                $('.mull_attorneys').append(html);
                // console.log(attorneys[i]);
            }
        }
        function currentDate2() {
            var today = new Date();
            return ("0" + (today.getMonth() + 1)).slice(-2) + '-' + ("0" + (today.getDate())).slice(-2) + '-' + today.getFullYear();
            // time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
            //return date +' '+time;
        }
			//People Search Mobile Menu
			
			$('.sort_button_container button').on('click', function(){
				$('.peopleSidebar').toggleClass('peopleSearchMobile');
				$('.menu_overlay').toggleClass('show_overlay');
			});

			$('.menu_overlay').on('click', function(){
				$('.menu_overlay').removeClass('show_overlay');
				$('.peopleSidebar').toggleClass('peopleSearchMobile');
			});

			$('.hide_sort_container button').on('click', function(){
				$('.menu_overlay').removeClass('show_overlay');
				$('.peopleSidebar').toggleClass('peopleSearchMobile');
			});

        // Check if value exists in 2 arrays (needle, haystack)
        function compareArrays(arr1, arr2){
            var len = arr1.length,
                i = 0;

            for(;i < len; ++i){
                if(arr2.indexOf(arr1[i]) > -1) return true;
            }
        }

        function constructSearch (res, key) {
            var i,
                searchItem,
                len,
                catNameHolder;

            search_data = JSON.parse(res);
            len = search_data.length;
            // console.log(search_data);

            if(key === 'Case Brief'){
                headerParam = '?single_header=briefs&get_cat=case-brief';
            }else{
                headerParam = '';
            }
            //console.log(headerParam);
            //console.log(search_data[0]);


            // console.log(typeof search_data);

            for (i = 0; i < len; ++i) {
                //console.log(search_data[i].catsArr);
                // console.log(search_data[i].title);
                // console.log(search_data[i].custom_url);
                // console.log(search_data[i].permalink);
                if (search_data[i].catsArr.indexOf(key) > -1) {
                    search_data[i].catName = search_data[i].catsArr.splice(search_data[i].catsArr.indexOf(key), 1);
                } else if (search_data[i].catsArr.length > 0) {
                    search_data[i].catName = search_data[i].catsArr;
                // Added by Dewayne November 10, 2016
                } else if (typeof search_data[i].custom_type !== 'undefined') {
                    search_data[i].catName = search_data[i].custom_type;
                    search_data[i].catsArr = [search_data[i].custom_type];
                } else {
                    search_data[i].catName = 'Page'
                }
                // console.log(search_data[i].excerpt);
                // Added by Dewayne November 10, 2016
                if (typeof search_data[i].custom_url !== 'undefined') {
                    search_data[i].permalink = search_data[i].custom_url;
                }
                searchItem = document.createElement('figure');
                // console.log(search_data[i].excerpt);
                // console.log(search_data[i].permalink);
                $excerptHolder = $(search_data[i].excerpt);

                if(search_data[i].catName === 'Attorney'){
                    $excerptHolder.append('<br/><a href="/attorney/?attorney_id=' + search_data[i].att_id +
                        '" class="readMore">read more</a>');
                }else{
                    $excerptHolder.append('<br/><a href="' + search_data[i].permalink + headerParam +
                        '" class="readMore">read more</a>');
                }

                // console.log($excerptHolder);

                //build html element from post information
                if(key === 'Case Brief') {
                    searchItem.innerHTML = ' <label>' +
                        search_data[i].catsArr[0] +
                        '</label><br/><label>'+search_data[i].date+'</label><br/><a href="' + search_data[i].permalink + headerParam + '">' +
                        search_data[i].title + '</a>' + $excerptHolder[0].outerHTML;
                }else if(search_data[i].catName === 'Attorney'){
                        searchItem.innerHTML = ' <label>' +
                        search_data[i].catName +
                        '</label><br/><a href="/attorney/?attorney_id=' + search_data[i].att_id + '">' +
                        search_data[i].title + '</a>' + $excerptHolder[0].outerHTML;
                }else{
                    catNameHolder = (search_data[i].catName === null ) ? 'Page' : search_data[i].catName;
                    searchItem.innerHTML = ' <label>' +
                        catNameHolder +
                        '</label><br/><a href="' + search_data[i].permlink + headerParam + '">' +
                        search_data[i].title + '</a>' + $excerptHolder[0].outerHTML;
                }
                // add html to object
                search_data[i].htmlItem = searchItem;
            }
            searchRender([]);
        }
        // Render Search
        function searchRender(sort){
            var len = search_data.length,
                results = [],
                i,
                resultsHolder = document.createElement('div'),
                $resultsArea = $('#resultsArea');

            // console.log(search_data);
            searchCount = 0;
            /*console.log(search_data[0]);
            console.log(sort);
            console.log(sort.length);*/
            if(sort.length < 1){
                for(i = 0; i < len; ++i) {
                    resultsHolder.appendChild(search_data[i].htmlItem);
                    ++initCaseCount;
                }
            }else{
                for(i = 0; i < len; ++i) {
                    if(search_data[i].hasOwnProperty('catName')){
                        if($('#caseBriefsPage').length || $('#searchPage').length){
                            if(search_data[i].hasOwnProperty('catsArr')){
                                if (compareArrays(search_data[i].catsArr, sort)) {
                                    resultsHolder.appendChild(search_data[i].htmlItem);
                                    ++searchCount;
                                }
                            }
                        }else{
                            if (sort.indexOf(search_data[i].catName[0]) > -1) {
                                resultsHolder.appendChild(search_data[i].htmlItem);
                                ++searchCount;
                            }
                        }

                    }
                    // console.log('searchCount');
                    // console.log(searchCount);
                }
                initCaseCount = 0;
            }

            $resultsArea.html(resultsHolder.innerHTML);

            /*if($('#searchPage').length) {
                searchCount = +initCaseCount;
                GlobalFunctions.paginationFunc();
            }*/
        }
        function homePage() {
            if ($('#homePage').length) {
                $('#homepage-slider').cycle({
                    fx:      'scrollLeft',
                    slides: '> a ',
                    speed:    800,
                    //pause: 1,
                    timeout:  5000
                });
            }
        }
        function editAttorney() {
            var $editBtn, $draftInputs;
            if ($('body').hasClass('page-id-548')) {
                $('header > a').addClass('disableAnchor').removeAttr('href');

                /*$editBtn = $('#edit_attorney');
                $draftInputs = $('.draft-input');
                
                $editBtn.on('click', function(){
                    var $this = $(this),
                        $degrees = $('.degrees input');
                    if($this.hasClass('engage')){
                        // $degrees.attr('disabled', '');
                        $degrees.prop("disabled", true);
                        $this.removeClass('engage');
                        $draftInputs.addClass('txtHide');
                    }else{
                        $degrees.prop("disabled", false);
                        $this.addClass('engage');
                        $draftInputs.removeClass('txtHide');
                    }
                });
*/
                /*$("#submit_attorney").on("click", function (e) {
                    e.preventDefault();
                    // $("form:first").remove();
                    // $("#mull_edit_attorney").submit();
                    $(this).parent('form').submit();
                });*/

                // mull_validate();

                // validation
                if(1 == 1){
                    
                } else if ($("input[name='id']").val() != '') {
                    $("form").validate({

                        // Specify validation rules
                        rules: {
                            first_name: "required",
                            last_name: "required",
                            password1: {
                                //required: true,
                                equalTo: "#password"
                            },
                            email: {
                                required: true,
                                email: true
                            }
                            /*nickname: "required",
                             title: "required",
                             day_hired: "required",
                             years_as_a_certified_specialist: "required",
                             number_of_years_in_worker_compensation: "required",
                             bar_number: "required",
                             year_of_juris_degree: {
                             required: true,
                             minlength: 4,
                             maxlength: 4,
                             digits: true
                             },
                             year_admitted_to_state_bar: {
                             required: true,
                             minlength: 4,
                             maxlength: 4,
                             digits: true
                             },
                             'bachelors_degree[]': "required",
                             'bachelors_degree_location[]': "required",
                             'bachelors_degree_year_of_graduation[]': {
                             required: true,
                             minlength: 4,
                             maxlength: 4,
                             digits: true
                             }*/
                        },
                        // Specify validation error messages
                        messages: {
                            first_name: "Please enter your first name",
                            last_name: "Please enter your last name",
                            email: "Please enter a valid email address",
                            password1: {
                                required: "Please enter password again",
                                equalTo: "Password must match"
                            }

                            /*nickname: "Please enter your nickname",
                             title: "Please enter your title",
                             day_hired: "Please enter your hired day ",
                             years_as_a_certified_specialist: "Please enter your years as a certified specialist",
                             number_of_years_in_worker_compensation: "Please enter your number of years in worker compensation",
                             bar_number: "Please enter your bar number",
                             year_of_juris_degree: {
                             required: "Please enter Year of Juris Degree",
                             minlength: "Please enter a valid Year of Juris Degree",
                             maxlength: "Please enter a valid Year of Juris Degree",
                             digits: "Please enter a valid Year of Juris Degree"
                             },
                             year_admitted_to_state_bar: {
                             required: "Please enter Year admitted to state bar",
                             minlength: "Please enter a valid Year admitted to state bar",
                             maxlength: "Please enter a valid Year admitted to state bar",
                             digits: "Please enter a valid Year admitted to state bar"
                             },
                             'bachelors_degree[]': "Please enter your bachelors degree",
                             'bachelors_degree_location[]': "Please enter a place where you received your bachelors degree",
                             'bachelors_degree_year_of_graduation[]': {
                             required: "Please enter your Year of graduation",
                             minlength: "Please enter a valid Year of graduation",
                             maxlength: "Please enter a valid Year of graduation",
                             digits: "Please enter a valid Year of graduation"
                             }
                        },
                        errorElement: "div",
                        errorPlacement: function (error, element) {
                            $("#errors_here").append(error);
                        },
                        // Make sure the form is submitted to the destination defined
                        // in the "action" attribute of the form when valid
                        submitHandler: function (form) {
                            form.submit();
                        }

                    });
                } else {
                    $("form").validate({

                        // Specify validation rules
                        rules: {
                            first_name: "required",
                            password: {
                                required: true,
                                minlength: 6
                            },
                            password1: {
                                required: true,
                                equalTo: "#password"
                            },
                            last_name: "required",
                            email: {
                                required: true,
                                email: true
                            }
                            /*nickname: "required",
                             title: "required",
                             day_hired: "required",
                             years_as_a_certified_specialist: "required",
                             number_of_years_in_worker_compensation: "required",
                             bar_number: "required",
                             year_of_juris_degree: {
                             required: true,
                             minlength: 4,
                             maxlength: 4,
                             digits: true
                             },
                             year_admitted_to_state_bar: {
                             required: true,
                             minlength: 4,
                             maxlength: 4,
                             digits: true
                             },
                             'bachelors_degree[]': "required",
                             'bachelors_degree_location[]': "required",
                             'bachelors_degree_year_of_graduation[]': {
                             required: true,
                             minlength: 4,
                             maxlength: 4,
                             digits: true
                             }*/
                        },
                        // Specify validation error messages
                        messages: {
                            first_name: "Please enter your first name",
                            last_name: "Please enter your last name",
                            email: "Please enter a valid email address",
                            password: {
                                required: "Please enter a password",
                                minlength: "Password must consist of at least 6 characters"
                            },
                            password1: {
                                required: "Please enter password again",
                                equalTo: "Password must match"
                            }
                            /*nickname: "Please enter your nickname",
                             title: "Please enter your title",
                             day_hired: "Please enter your hired day ",
                             years_as_a_certified_specialist: "Please enter your years as a certified specialist",
                             number_of_years_in_worker_compensation: "Please enter your number of years in worker compensation",
                             bar_number: "Please enter your bar number",
                             year_of_juris_degree: {
                             required: "Please enter Year of Juris Degree",
                             minlength: "Please enter a valid Year of Juris Degree",
                             maxlength: "Please enter a valid Year of Juris Degree",
                             digits: "Please enter a valid Year of Juris Degree"
                             },
                             year_admitted_to_state_bar: {
                             required: "Please enter Year admitted to state bar",
                             minlength: "Please enter a valid Year admitted to state bar",

                             maxlength: "Please enter a valid Year admitted to state bar",
                             digits: "Please enter a valid Year admitted to state bar"
                             },
                             'bachelors_degree[]': "Please enter your bachelors degree",
                             'bachelors_degree_location[]': "Please enter a place where you received your bachelors degree",
                             'bachelors_degree_year_of_graduation[]': {
                             required: "Please enter your Year of graduation",
                             minlength: "Please enter a valid Year of graduation",
                             maxlength: "Please enter a valid Year of graduation",
                             digits: "Please enter a valid Year of graduation"
                             }*/
                        },
                        errorElement: "div",
                        errorPlacement: function (error, element) {
                            $("#errors_here").append(error);
                        },
                        // Make sure the form is submitted to the destination defined
                        // in the "action" attribute of the form when valid
                        submitHandler: function (form) {
                            form.submit();
                        }

                    });
                }
            }
        }
        function referralPage() {
            if ($('#referralPage').length) {
                var $officeSelectBox = $('.column-half > .ChooseOffice > select[name="ChooseOffice"]'),
                    $attorneySelectBox = $('#chooseAttorney'),
                    $checkBoxSection = $('#checkBoxSection'),
                    attorneysObj;

                loadLocations();
                function loadLocations() {
                    var data,
                        arr = [],
                        i = 0,
                        res,
                        len,
                        opts = '',
                        divHolder = document.createElement('div'),
                        optHolder,
                        $div = $('#chooseOffice');

                    res = globalLocations;
                    data = res;
                    len = data.length;

                    for(; i < len ;++i){
                        //console.log(data[i]);
                        optHolder = document.createElement('option');
                        optHolder.innerHTML = data[i];
                        optHolder.value = data[i].split(' Office')[0];
                        // optHolder.value = data[i].office_uno;

                        divHolder.appendChild(optHolder);
                    }

                    $div.append(divHolder.innerHTML);
                    $('#chooseOffice').trigger('change');
                }

                $officeSelectBox.on('change', function(){
                    var $this = $(this),
                        $formData = $this.find(':selected'),
                        $email = $('#emailHolder'),
                        $field = $('#fieldVal'),
                        value = $formData.val();

                    // console.log(checksLength);
                    // console.log(value);
                    // console.log($formData.val());
                    $field.html($this.find('option:selected').html());
                    $field.attr('value', $this.find('option:selected').html());

                    // console.log($field);
                    // $field.html(value);

                    (value !== 'Choose Office') ? $('input[type="submit"]').removeClass('txtDisableButton') :
                        $('input[type="submit"]').addClass('txtDisableButton');
                    // console.log(value === 'Choose Office');
                    // console.log(value);

                    $.ajax({
                        url: site_url + '/wp-admin/admin-ajax.php',
                        type: 'POST',
                        data: {
                            'action'    : 'getAttorneysUpdate',
                            'submit'    : 'getAttorneysUpdate',
                            'formData'  : $formData.val()
                        },
                        success: function (res) {
                            console.log(res);
                            var data,
                                arr = [],
                                i = 0,
                                len,
                                fullName,
                                opts = '',
                                divHolder = document.createElement('div'),
                                optHolder,
                                $div = $('#chooseAttorney');

                            data = JSON.parse(res);
                            len = data.length;

                            //set default
                            optHolder = document.createElement('option');
                            optHolder.innerHTML = 'Choose Attorney';
                            // console.log(value);
                            // console.log(value.replace(' ', '')+'Referral@mulfil.com');
                           optHolder.setAttribute('data-email', value.replace(' ', '')+'Referral@mulfil.com');
                           // optHolder.setAttribute('data-email', 'marketing@mulfil.com');
                            divHolder.appendChild(optHolder);
                            //console.log(data);
                            // return false;
                            for(; i < len ;++i){
                                if(data[i].middle_initial){
                                    fullName = data[i].first_name + ' ' + data[i].middle_initial + ' ' + data[i].last_name;
                                }else{
                                    fullName = data[i].first_name + ' ' + data[i].last_name;
                                }
                                    // console.log(data[i].middle_initial);
                                optHolder = document.createElement('option');
                                optHolder.innerHTML = fullName;
                                //optHolder.setAttribute('data-email', data[i].email);
                                optHolder.setAttribute('data-email', value.replace(' ', '')+'Referral@mulfil.com');
                                divHolder.appendChild(optHolder);
                            }

                            $div.html(divHolder.innerHTML);
                            //console.log($('#chooseAttorney option:first-of-type').attr('data-email'));
                            $email.html($('#chooseAttorney option:first-of-type').attr('data-email'));
                            $email.attr('value', $('#chooseAttorney option:first-of-type').attr('data-email'));

                        },
                        error: function (errorThrown) {
                            console.log("This has thrown an error:" + errorThrown);
                        }
                    });

                });
                $('.submit_button').on('mouseenter', function(){
                   var $this = $(this),
                       title = '';

                    title = ($this.find('input[type="submit"]').hasClass('txtDisableButton')) ? 'You must choose an office to submit this form' : '';
                    $this.attr('title', title);
                });
                $attorneySelectBox.on('change', function() {
                    var $this = $(this),
                        $formData = $this.find(':selected'),
                        $email = $('#emailHolder');

                    $email.html($this.find('option:selected').attr('data-email'));
                    $email.attr('value', $this.find('option:selected').attr('data-email'));
                });
                $checkBoxSection.on('change', function () {
                    var officeVal = $('.column-half > .ChooseOffice > select[name="ChooseOffice"]:selected').val();

                    (officeVal !== 'Choose Office') ? $('input[type="submit"]').removeClass('txtDisableButton') :
                        $('input[type="submit"]').addClass('txtDisableButton');
                })
            }
        }
        function caseBriefsPage() {
            if ($('#caseBriefsPage').length) {
                var key = 'Case Brief';

                $.ajax({
                    url: site_url + '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        'action': 'submitSearch',
                        'submit': 'getCaseBriefs'
                    },
                    success: function (res) {
                        //console.log(res);
                        constructSearch(res, key);

                        // console.log(GlobalFunctions);
                        // console.log(GlobalFunctions.paginationFunc());
                        feedPages('#resultsArea figure');
                        /*console.log(searchCount);
                        console.log(GlobalFunctions['paginationFunc'].call());*/
                        //console.log(GlobalFunctions['paginationFunc']);
                        GlobalFunctions['paginationFunc']();
                    },
                    error: function (errorThrown) {
                        console.log("This has thrown an error:" + errorThrown);
                    }
                });

                $('#caseOptions').on('click', 'button', function(e){
                    var $this = $(this),
                        btnState = false;
                    if($this.data('sort') === 'reset'){
                        criteria = [];
                        $this.parent().siblings().children('button').removeClass('selectedSort');
                        initSearch = 0;

                    }else{
                        criteria = [];
                        $this.parent().siblings().children('button').removeClass('selectedSort');
                        //$('#btnReset').trigger('click');

                        btnState = updateSearch($this.data('sort'));
                        $('#btnReset').removeClass('selectedSort');
                    }

                    if(btnState){
                        $this.removeClass('selectedSort');
                    }else{
                        $this.addClass('selectedSort');
                    }

                    //console.log('criteria : ', criteria);
                    searchRender(criteria);
                    GlobalFunctions.paginationFunc();
                });
            }
        }
        function feedPages(divArea){
            if ($('#seminarPage').length || $('#bulletinPage').length || $('#searchPage').length ||
                $('#caseBriefsPage').length) {
                var $resultsArea = "",
                    $pgArea = $('#paginationArea'),
                    $numArea = $('#pgNums'),
                    itemsPerPage = 10,
                    currentPage = 1,
                    endPage = 0,
                    slice,
                    len = 0,
                    i = 0,
                    j = 1;
                console.log('Feed Page');
                if(typeof div != 'undefined'){
                    $resultsArea = $(divArea);
                }else if($('#resultsArea').length){
                    $resultsArea = $('#resultsArea figure');
                }else{
                    $resultsArea = $('.feedArea figure');
                }
                // Define the Feed Area for returned post
                feedPageHolder = $resultsArea.parent().attr('id');

                // pagination
                GlobalFunctions.paginationFunc = function setPagination() {
                    /*if(typeof div != 'undefined'){
                        $resultsArea = $(divArea);
                    }else if($('#resultsArea').length){
                        $resultsArea = $('#resultsArea figure');
                    }else{
                        $resultsArea = $('.feedArea figure');
                    }*/
                    var $resultsArea = $('#'+ feedPageHolder + ' figure'),
                        slice = [0, itemsPerPage],
                        $pgArea = $('#paginationArea'),
                        $numArea = $('#pgNums'),
                        i = 0,
                        j = 1;

                    // console.log(slice);
                    // console.log(feedPageHolder);
                    // console.log($resultsArea);
                    // console.log($resultsArea.length);
                    // console.log(searchCount);
                    // console.log(itemsPerPage);
                    // console.log('init-search');
                    // console.log(initSearch);
                    // console.log((searchCount > itemsPerPage));

                    if ((searchCount > itemsPerPage) || initCaseCount > 0|| initSearch < 1) {
                        //console.log('---hits here----');
                        if(initSearch === 0){
                            len = $resultsArea.length;
                        }else{
                            len = searchCount;
                        }
                        $numArea.html('');
                        // console.log($resultsArea.length);
                        // console.log(len);
                        if(searchCount > itemsPerPage){
                            $('.paginationArea').removeClass('txtHide');
                        }else{
                            $('.paginationArea').addClass('txtHide');
                        }

                        // console.log($resultsArea.length);
                        $resultsArea.slice(0, itemsPerPage).css({display: 'block'});
                        $resultsArea.slice(itemsPerPage, $resultsArea.length).css({display: 'none'});
                        // console.log('init-search');
                        // console.log(initSearch);
                        // console.log($numArea.html(''));

                        //init button area
                        $numArea.html('');
                        //console.log($numArea.html(''));
                        // loop over each page to create a numbered button
                        if((searchCount > itemsPerPage) || initSearch === 0){
                            //console.log('search counter is higher');
                            for (; i < len;) {
                                $numArea.append('<li><button type="button" class="btnPg" data-paginate="' + j + '">' +
                                j + '</button></li>');
                                ++j; // button counter
                                i += itemsPerPage; // loop counter
                                /*console.log('counter - j ');
                                console.log(j);*/
                            }
                            endPage = j - 1;
                            $numArea.find('button[data-paginate="1"]').addClass('selectedSort');

                            if($('#searchPage').length){
                                if(initCaseCount > itemsPerPage){
                                    $('.paginationArea').removeClass('txtHide');
                                }else{
                                    $('.paginationArea').addClass('txtHide');
                                }
                            }else{
                                $pgArea.removeClass('txtHide');
                            }


                        }else{
                            $pgArea.addClass('txtHide');
                        }

                        initSearch = 1;
                    }else{
                        $pgArea.addClass('txtHide');
                    }
                    $('.next').click(function () {
                        if (slice[1] < len) {
                            slice = slice.map(addSlice);
                        }
                        showSlice(slice);
                    });

                    $('.prev').click(function () {
                        if (slice[0] > 0) {
                            slice = slice.map(subtractSlice);
                        }
                        showSlice(slice);
                    });

                    $('.btnPg').click(function () {
                        var $this = $(this),
                            pg = +$this.attr('data-paginate'),
                            begin = ((pg - 1) * itemsPerPage) + 1,
                            end = ((pg - 1) * itemsPerPage) + 10,
                            slice = [begin, end];

                        showSlice(slice);
                    });
                };

                //GlobalFunctions['paginationFunc'].call();
                function addSlice(num){
                    return num + itemsPerPage;
                }

                function subtractSlice(num){
                    return num - itemsPerPage;
                }
                function showSlice(slice){
                    var $btnPage = '',
                        $resultsArea = $('#'+ feedPageHolder + ' figure');
                    // get current pagination page number
                    currentPage = Math.floor((slice[1] / itemsPerPage));

                   // hide previous button if on first page
                   if(currentPage === 1){
                       $('.prev').parent().addClass('txtHide');
                   }else{
                       $('.prev').parent().removeClass('txtHide');
                   }
                    // hide next button if on last page
                    if(currentPage === endPage){
                        $('.next').parent().addClass('txtHide');
                    }else{
                        $('.next').parent().removeClass('txtHide');
                    }
                    //console.log($numArea);
                    // highlight button for current page
                    $btnPage = $numArea.find('button[data-paginate="'+currentPage+'"]').addClass('selectedSort');
                    $btnPage.addClass('selectedSort');
                    $btnPage.parent().siblings().find('button').removeClass('selectedSort');

                    // hide all results
                    $resultsArea.css('display', 'none');

                    //show correct segment of results
                    $resultsArea.slice(slice[0], slice[1]).css('display','block');
                }
                // console.log('---feed--area--done');
            }
        }
        function seminarPage() {
            if ($('#seminarPage').length) {
                var key = 'Seminar';

                $.ajax({
                    url: site_url + '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        'action': 'submitSearch',
                        'submit': 'getSeminars'
                    },
                    success: function (res) {
                        var $resultsArea,
                            $stickyPosts,
                            $postHolder,
                            divHolder = document.createElement('div');

                        constructSearch(res, key);
                        $resultsArea = $('#resultsAreaSeminar');
                        $stickyPosts = $resultsArea.find('.stickyPost');

                        // handles sticky posts for seminars
                        $stickyPosts.each(function(){
                            var $this = $(this);
                            $postHolder = $this.detach();
                            divHolder.innerHTML += $postHolder[0].outerHTML;
                        });
                        $resultsArea.prepend(divHolder.innerHTML);
                    },
                    error: function (errorThrown) {
                        console.log("This has thrown an error:" + errorThrown);
                    }
                });

                $('#caseOptions').on('click', 'button', function(e){
                    var $this = $(this),
                        btnState = false,
                        criteria = [];
                    if($this.data('sort') === 'reset'){

                        $this.parent().siblings().children('button').removeClass('selectedSort');
                    }else{
                        btnState = updateSearch($this.data('sort'));
                        $('#btnReset').removeClass('selectedSort');
                    }

                    if(btnState){
                        $this.removeClass('selectedSort');
                    }else{
                        $this.addClass('selectedSort');
                    }

                    console.log('criteria : ', criteria);
                    searchRender(criteria);
                    GlobalFunctions.paginationFunc();
                });
            }
        }
        function searchPage() {
            if ($('#searchPage').length) {
                var key = search_query;

                console.log(typeof key);
                $.ajax({
                    url: site_url + '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        'action': 'submitSearch',
                        'key'   : key,
                        'submit': 'getSearch'
                    },
                    success: function (res) {
                        // console.log(JSON.parse(res));
                        constructSearch(res, key);

                        feedPages('#resultsArea figure');
                        //console.log(searchCount);

                        GlobalFunctions['paginationFunc']();
                    },
                    error: function (errorThrown) {
                        console.log("This has thrown an error:" + errorThrown);
                    }
                });

                $('#searchOptions').on('click', 'button', function(e){
                    var $this = $(this),
                        btnState = false;
                    if($this.data('sort') === 'reset'){
                        criteria = [];
                        $this.parent().siblings().children('button').removeClass('selectedSort');
                    }else{
                        criteria = [];
                        $this.parent().siblings().children('button').removeClass('selectedSort');
                        btnState = updateSearch($this.data('sort'));
                        $('#btnReset').removeClass('selectedSort');
                    }

                    //$('#btnReset').trigger('click');

                    if(btnState){
                        $this.removeClass('selectedSort');
                    }else{
                        $this.addClass('selectedSort');
                    }

                    console.log('criteria : ', criteria);
                    searchRender(criteria);
                    GlobalFunctions.paginationFunc();
                });
            }
        }
        function archivePage() {
            if ($('#archives').length) {
                var $archive = $('#archives'),
                    $bannerArea = $('.headerBanner1');

                $archive.find('ul li a').each(function(e){
                    var $this = $(this),
                        html =  $this.html();
                    if(!$this.parent().hasClass('allButtonLink')){
                        html = html.split(' ');
                        $this.html(html[0]);
                    }
                });

                if(archivePageActive){
                    $bannerArea.find('div > h1').append('<h2 class="archiveHeader">Archive</h2>');
                }
            }
        }

        // only for Search Page
        function searchPage2() {
            if ($('#searchPage2').length) {
                //console.log(search_data);
                var len = search_data.length,
                    results = [],
                    i,
                    searchItem;


                    for(i = 0; i < len; ++i){
                        console.log(search_data[i].catsArr);
                        if(search_data[i].catsArr.indexOf('Case Brief') > -1){
                            search_data[i].catName = search_data[i].catsArr.splice(search_data[i].catsArr.indexOf('Case Brief'), 1);
                        }else if(search_data[i].catsArr.length > 0){
                            search_data[i].catName = search_data[i].catsArr;
                        }else{
                            search_data[i].catName = ''
                        }

                        searchItem = document.createElement('figure');
                        $excerptHolder = $(search_data[i].excerpt);
                        $excerptHolder.append('..<a href="' + search_data[i].permalink +
                            '" class="readMore">read more</a>');
                        console.log($excerptHolder);
                        //build html element from post information
                        searchItem.innerHTML = ' <label>' +
                            search_data[i].catName +
                            '</label><br/><a href="' + search_data[i].permalink + '">' +
                            search_data[i].title + '</a>' + $excerptHolder.html();

                        // add html to object
                        search_data[i].htmlItem = searchItem;
                    }
                searchRender([]);

                // Sorting

                $('#categories').on('click', 'button', function(e){
                    var $this = $(this),
                        btnState = false;
                    if($this.data('sort') === 'reset'){
                        criteria = [];
                        $this.parent().siblings().children('button').removeClass('selectedSort');
                    }else{
                        btnState = updateSearch($this.data('sort'));
                        $('#btnReset').removeClass('selectedSort');
                    }

                    if(btnState){
                        $this.removeClass('selectedSort');
                    }else{
                        $this.addClass('selectedSort');
                    }
                    console.log('criteria : ', criteria);
                    searchRender(criteria);
                });

            }
        }
        function updateSearch(item){
            var index;
            if(criteria.indexOf(item) < 0){
                criteria.push(item);
                return false;
            }else{
                index = $.inArray(item, criteria);
                criteria.splice(index, 1);
                return true;
            }
        }
        function aboutPage() {
            if ($('#aboutPage').length) {
                var hash = window.location.hash;
                console.log('hello');
                if (hash) {
                    $('html, body').animate({
                        scrollTop: $(hash).offset().top
                    }, 1000);
                }
            }
        }

        function calculatorPage(){
            if($('#calcWrapper').length){
                var $calcArea = $('#btnCalcArea > li > button'),
                    $frame = $('#calcFrame'),
                    $resultsFrame = $('#formResultsArea'),
                    $formWeekly = $frame.find('#formWeekly'),
                    $formTemp = $frame.find('#formTemp'),
                    $formWage = $frame.find('#formWage'),
                    $formPermanent = $frame.find('#formPermanent'),
                    $formPension = $frame.find('#formPension'),
                    $formDates = $frame.find('#formDates'),
                    $calcOne= $frame.find('.calc1'),
                    $calcTwo= $frame.find('.calc2'),
                    propsOff = {
                        'readonly'  : true,
                        'value'     : ''
                    },
                    propsOn = {
                        'readonly'  : false,
                        'value'     : '',
                        'disable'   : 'none'
                    },
                    currentDate = getCurrentDate(),
                    $dis1 = $('#disclaimer1'),
                    $dis2 = $('#disclaimer2'),
                    $dis3 = $('#disclaimer3'),
                    $dis4 = $('#disclaimer4');

                GlobalFunctions.currentDate = getCurrentDate();
                // console.log(new Date('1/08/2007') <= new Date('12/31/2012'));
                // console.log(comparePdDate('1/08/2007'));
                // console.log(typeof currentDate);
                // console.log(currentDate);
                // console.log($calcArea);
                // console.log($calcArea.length);
                //console.log(betweenDate('01/01/2003'));
                // defaults
                $('[data-sync="9"]').each(function () {
                    var $this = $(this);
                    $this.val(prepDateHyphen(currentDate));
                });
                //$formPension.find('.field7')[0].checked = true;
                $frame.on('click', '.btnStartOver', function(){
                    var $this = $(this),
                        $mainArea = $('#formResultsArea');

                    $this.parent().parent().parent().find('input:not([type=radio])').each(function(){
                        var $this = $(this);
                        console.log($this);
                        $this.prop(propsOn);
                        $this.removeClass('txtDisableInput');
                    });
                    $('.dateField[data-sync="9"]').each(function(){
                        $this = $(this);
                        $this.val(GlobalFunctions.currentDate);
                        console.log($this);
                    });
                    /*$mainArea.find('h2.calcTitle').siblings().addClass('txtHide');
                    $mainArea.find('h2.calcTitle').removeClass('txtHide');*/
                    $mainArea.find('h2.calcTitle').siblings().addClass('txtHide');
                });
                // Calculator Header Functionality
                $calcArea.on('click', function(){
                    var $this = $(this),
                        formTitle = '#' + $this.val(),
                        $form = $frame.find(formTitle);

                        $this.parent().siblings().find('button').removeClass('btnSelected');
                        $this.addClass('btnSelected');

                            /*console.log(formTitle);
                            console.log($this.val() === "0");
                            console.log($this.val());*/
                        if($this.val() == "0"){

                            $frame.find('.formWrapper').addClass('txtHide');
                        }else{
                            console.log($form);
                            $form.removeClass('txtHide').siblings().addClass('txtHide');
                        }

                    $frame.find('#formStandard').removeClass('txtHide').siblings().addClass('txtHide');
                });

                $formWeekly.find('input').on('change', function(){
                    //Disables other fields so only one type of calculation can be entered at a time
                    if(~~$formWeekly.find('.field1').val() && !$formWeekly.find('.field1').hasClass('txtDisableInput')) {
                        // using calc type 4 for this form
                        $formWeekly.find('.field1').prop('readonly', false).removeClass('txtDisableInput');
                        $formWeekly.find('.field2, .field3, .field4, .field5').prop(propsOff).addClass('txtDisableInput');
                    }else if(~~$formWeekly.find('.field2').val() > '' || ~~$formWeekly.find('.field3').val() > ''){
                        // using calc type 1 for this form
                        $formWeekly.find('.field2, .field3').prop('readonly', false).removeClass('txtDisableInput');
                        $formWeekly.find('.field1, .field4, .field5').prop(propsOff).addClass('txtDisableInput');
                    }else if(~~$formWeekly.find('.field4').val() > ''){
                        // using calc type 2 for this form
                        $formWeekly.find('.field4').prop('readonly', false).removeClass('txtDisableInput');
                        $formWeekly.find('.field1, .field2, .field3, .field5').prop(propsOff).addClass('txtDisableInput');
                    }else if(~~$formWeekly.find('.field5').val() > ''){
                        // using calc type 3 for this form
                        $formWeekly.find('.field5').prop('readonly', false).removeClass('txtDisableInput');
                        $formWeekly.find('.field1, .field2, .field3, .field4').prop(propsOff).addClass('txtDisableInput');
                    }else{
                        $formWeekly.find('.field1, .field2, .field3, .field4, .field5').prop('readonly', false).removeClass('txtDisableInput').prop('readonly', false);
                    }

                //    add confirm check to clear fields if they click on one that is readonly

                });

                $formWeekly.on('click', '.btnCalc', function(){
                    var calcType = 0,
                        formInfo = [];
                    // checks if any of the form calc types are valid
                    if((    !~~$formWeekly.find('.field1').val() > '') &&
                        (   !~~$formWeekly.find('.field2').val() > '' || !~~$formWeekly.find('.field3').val() > '') &&
                            (!~~$formWeekly.find('.field4').val() > '') &&
                            (!~~$formWeekly.find('.field5').val() > '')
                    ){
                        alertBoxComplete();
                        return false;
                    }
                    console.log($formWeekly.find('.field1').hasClass('txtDisableInput'));
                    console.log(!$formWeekly.find('.field1').hasClass('txtDisableInput'));
                    if($formWeekly.find('.field1').val() > '' && !$formWeekly.find('.field1').hasClass('txtDisableInput')){
                        calcType = 4;
                        formInfo.push($formWeekly.find('.field1').val());
                    }else if($formWeekly.find('.field2').val() > '' && $formWeekly.find('.field3').val() > ''){
                        calcType = 1;
                        formInfo.push($formWeekly.find('.field2').val());
                        formInfo.push($formWeekly.find('.field3').val());
                    }else if($formWeekly.find('.field4').val() > ''){
                        calcType = 2;
                        formInfo.push($formWeekly.find('.field4').val());
                    }else{
                        calcType = 3;
                        formInfo.push($formWeekly.find('.field5').val());
                    }
                    console.log(formInfo);
                    $.ajax({
                        url: site_root + '/ajax/mull.php',
                        type: 'POST',
                        data: {
                            'action'    : 'calc',
                            'submit'    : 'formWeekly',
                            'calcType'  : ~~calcType,
                            'info'      : formInfo
                        },
                        success:function(res) {
                            var $form = $('#formWeeklyResults'),
                                $thisInput = $form.find('.field1');

                            console.log(res);
                            $thisInput.html('$ '+res);
                            $('input[data-sync="5"]').each(function () {
                                var $this = $(this);
                                $this.val(convertNum(res));
                            });
                            $form.removeClass('txtHide').siblings().not('h2').addClass('txtHide');
                        },
                        error: function(errorThrown){
                            console.log("This has thrown an error:" + errorThrown);
                        }
                    });
                });
                $formTemp.on('click', '.btnStartOver', function(){
                    $formTemp.find('[class*="field"]').removeClass('txtDisableInput').val('');
                });
                $formTemp.on('click', '.btnCalc', function(){
                    var $form = $('#formTempResults'),
                        getAww = +$formTemp.find('.field1').val(),
                        getDate = $formTemp.find('.field2').val(),
                        formInfo = [];

                    console.log(typeof getAww);
                    console.log(getAww);
                    // checks if any of the form calc types are valid
                    if((!~~getAww > '') || (getDate === '')){
                        alertBoxComplete();
                        return false;
                    }
                    formInfo.push(getAww);
                    //formInfo.push(getDate);

                    console.log(getDate);
                    // console.log(prepDateHyphen(getDate));
                    // console.log(mdyToYmd(prepDateHyphen(getDate)));
                    formInfo.push(mdyToYmd(prepDateHyphen(getDate)));
                    //(checkType('date')) ? formInfo.push(getDate) : formInfo.push(mdyToYmd(prepDateHyphen(getDate)));
                    /*if(!checkType('date')){
                        // set datepicker for browsers that don't support input of date type

                    }*/
                    console.log(formInfo);
                    $.ajax({
                        url: site_root + '/ajax/mull.php',
                        type: 'POST',
                        data: {
                            'action'    : 'calc',
                            'submit'    : 'formTemp',
                            'info'      : formInfo
                        },
                        success:function(res) {
                            var data = '',
                                setAww;
                            console.log(res);
                            if(res === '0'){
                                alertBoxNoResult();
                            }else {
                                data = JSON.parse(res);
                                setAww = parseFloat(data[0].split(',').join(''));
                                //(checkType('date')) ?  $form.find('.field1').html(dmyToMdy(reverseDateOrder(getDate))); : formInfo.push(mdyToYmd(prepDateHyphen(getDate)));
                                $form.find('.field1').html(getDate);
                                $form.find('.field2').html('$ ' + data[0]);
                                $form.find('.field3').html('$ ' + data[1]);

                                $('input[data-sync="5"]').each(function () {
                                    var $this = $(this);
                                    $this.val(convertNum(setAww));
                                });

                                //doi
                                $('input[data-sync="6"]').each(function () {
                                    var $this = $(this);
                                    $this.val(getDate);
                                });

                                // tdr
                                $('input[data-sync="12"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+data[1]);
                                });

                                $form.siblings().not('h2').addClass('txtHide');
                                $form.removeClass('txtHide');

                                // disclaimers
                                $dis1.removeClass('txtHide');
                                $dis2.addClass('txtHide');
                                $dis3.addClass('txtHide');
                                $dis4.addClass('txtHide');
                            }
                        },
                        error: function(errorThrown){
                            console.log("This has thrown an error:" + errorThrown);
                        }
                    });
                });
                $formWage.on('click', '.btnStartOver', function(){
                    $formWage.find('[class*="field"]').removeClass('txtDisableInput').val('');
                });
                $formWage.on('click', '.btnCalc', function(){
                    var getDate= $formWage.find('.field1').val(),
                        getAww  = +$formWage.find('.field2').val(),
                        getPtd  = +$formWage.find('.field3').val(),
                        formInfo = [];

                    // checks if any of the form calc types are valid
                    if((!~~getAww > '') || (!~~getPtd > '') ||(getDate === '')){
                        alertBoxComplete();
                        return false;
                    }
                    formInfo.push(mdyToYmd(prepDateHyphen(getDate)));
                    formInfo.push(getAww);
                    formInfo.push(getPtd);


                    $.ajax({
                        url: site_root + '/ajax/mull.php',
                        type: 'POST',
                        data: {
                            'action'    : 'calc',
                            'submit'    : 'formWage',
                            'info'      : formInfo
                        },
                        success:function(res) {
                            // aww | td | wl
                            var data = "",
                                $form = "",
                                setAww = "",
                                date = getDate.split('-');
                            if(res === '0'){
                                alertBoxNoResult();
                            }else{
                                data = JSON.parse(res);
                                $form = $('#formWageResults');
                                setAww = parseFloat(data[0].split(',').join(''));
                                $form.find('.field1').html(getDate); // DOI
                                $form.find('.field2').html('$ ' +data[0]); // AWW
                                $form.find('.field3').html('$ ' +data[1]); // TDR
                                $form.find('.field4').html('$ ' +data[2]); // WLTD

                                // aww
                                $('input[data-sync="5"]').each(function () {
                                    var $this = $(this);
                                    $this.val(convertNum(setAww));
                                });

                                //doi
                                $('input[data-sync="6"]').each(function () {
                                    var $this = $(this);
                                    $this.val(getDate);
                                });

                                // tdr
                                $('input[data-sync="12"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+data[1]);
                                });

                                // wltd
                                $('input[data-sync="22"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+data[2]);
                                });

                                $form.siblings().not('h2').addClass('txtHide');
                                $form.removeClass('txtHide');

                                // disclaimers
                                $dis1.removeClass('txtHide');
                                $dis2.addClass('txtHide');
                                $dis3.addClass('txtHide');
                                $dis4.addClass('txtHide');
                            }
                        },
                        error: function(errorThrown){
                            console.log("This has thrown an error:" + errorThrown);
                        }
                    });
                });
                $formPermanent.on('click', '.btnStartOver', function(){
                    $formPermanent.find('[class*="field"]').removeClass('txtDisableInput').val('');
                });
                $formPermanent.on('click', '.btnCalc', function(){
                    var getDate= $formPermanent.find('.field3').val(),
                        getAww  = +$formPermanent.find('.field1').val(),
                        getPd  = +$formPermanent.find('.field2').val(),
                        formInfo = [];

                    // checks if any of the form calc types are valid
                    if((!~~getAww > '') || (!~~getPd > '') ||(getDate === '')){
                        alertBoxComplete();
                        return false;
                    }
                    formInfo.push(mdyToYmd(prepDateHyphen(getDate)));
                    formInfo.push(getAww);
                    formInfo.push(getPd);


                    //console.log(getDate);
                    $.ajax({
                        url: site_root + '/ajax/mull.php',
                        type: 'POST',
                        data: {
                            'action'    : 'calc',
                            'submit'    : 'formPermanent',
                            'info'      : formInfo
                        },
                        success:function(res) {
                            //console.log(res);
                            var data = '',
                                $form = $('#formPermanentResults');
                            if(res === '0'){
                                alertBoxNoResult();
                            }else {
                                data = JSON.parse(res);

                                $form.find('.field1').html(getDate); // DOI
                                $form.find('.field2').html('$ ' + data[0]); // AWW
                                $form.find('.field3').html('$ ' + data[1]); // TDR

                                $form.find('.field5').html('$ ' + data[3]); // PD Rate
                                $form.find('.field6').html(getPd + ' %'); // PD Level
                                $form.find('.field7').html(data[4]);
                                $form.find('.field8').html('$ ' + data[5]); // PD Value

                                if(beforeDate(getDate)){
                                    $form.find('.field4').html('N/A'); // VR
                                }else{
                                    $form.find('.field4').html('$ ' + data[2]); // VR
                                }
                                // aww
                                $('input[data-sync="5"]').each(function () {
                                    var $this = $(this);
                                    $this.val(getAww);
                                });

                                //doi
                                $('input[data-sync="6"]').each(function () {
                                    var $this = $(this);
                                    $this.val(getDate);
                                });
                                //pd
                                $('input[data-sync="8"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+getPd);
                                });
                                // tdr
                                $('input[data-sync="12"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+data[1]);
                                });

                                // vr
                                $('input[data-sync="16"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+data[2]);
                                });

                                // pdr
                                $('input[data-sync="17"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+data[3]);
                                });

                                //pdl
                                $('input[data-sync="18"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+getPd);
                                });

                                // pdw
                                $('input[data-sync="19"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+data[4]);
                                });

                                // pdv
                                $('input[data-sync="20"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+data[5]);
                                });

                                $form.siblings().not('h2').addClass('txtHide');
                                $form.removeClass('txtHide');

                                // disclaimers
                                $dis1.removeClass('txtHide');
                                $dis3.addClass('txtHide');
                                $dis4.addClass('txtHide');

                                if(comparePdDate(getDate) && getPd < 100){
                                    $dis2.removeClass('txtHide');
                                }else{
                                    $dis2.addClass('txtHide');
                                }
                            }
                        },
                        error: function(errorThrown){
                            console.log("This has thrown an error:" + errorThrown);
                        }
                    });
                });
                $formPermanent.find('.field2').on('change', function(){
                    var $this = $(this),
                        val1 = 0, val2 = 0;

                    if($this.val() >= 70){
                        val1 = ($('#formPermanent .field1').val() > 0) ? $('#formPermanent .field1').val() : 0;
                        val2 = $this.val();

                        $('#formPension .field1').val(val1);
                        $('#formPension .field2').val(val2);
                        $('#btnFormPension').trigger('click');
                    }
                });

                // Form Pension
                $formPension.find('.field2').on('change', function(){
                    var $this = $(this),
                        val1 = 0, val2 = 0;

                    console.log($this);
                    if($this.val() < 70){
                        val1 = ($('#formPension .field1').val() > 0) ? $('#formPension .field1').val() : 0;
                        val2 = $this.val();

                        $('#formPermanent .field1').val(val1);
                        $('#formPermanent .field2').val(val2);
                        $('#btnFormPermanent').trigger('click');
                    }
                });
                $formPension.on('click', '.btnStartOver', function(){
                    $formPension.find('[class*="field"]:not([class*="field7"])').removeClass('txtDisableInput').val('');
                    $formPension.find('input[type="radio"]').prop("checked", false);
                    $('.dateField[data-sync="9"]').each(function(){
                        $this = $(this);
                        $this.val(GlobalFunctions.currentDate);
                        console.log($this);
                    });
                });
                $formPension.on('click', '.btnCalc', function(){
                    var getAww = $formPension.find('.field1').val(),
                        getPd  = $formPension.find('.field2').val(),
                        getDate  = $formPension.find('.field3').val(),
                        getDOI  = $formPension.find('.field4').val(),
                        getDOB  = $formPension.find('.field5').val(),
                        getPs  = $formPension.find('.field6').val(),
                        getGender = $formPension.find('.field7:checked').val(),
                        formInfo = [];

                    // checks if any of the form calc types are valid
                    if((!~~getAww > '') || (!~~getPd > '') ||(getDate === '') ||(getDOI === '')
                        ||(getDOB === '')  ||(getPs === '') || typeof getGender === 'undefined'){
                        alertBoxComplete();
                        return false;
                    }
                    formInfo.push(mdyToYmd(prepDateHyphen(getDate))); // calculation date
                    formInfo.push(getAww); // aww
                    formInfo.push(getPd); // percent disability
                    formInfo.push(mdyToYmd(prepDateHyphen(getDOI))); // date of injury
                    formInfo.push(mdyToYmd(prepDateHyphen(getDOB)));  // DOB
                    formInfo.push(mdyToYmd(prepDateHyphen(getPs))); // PS
                    formInfo.push(getGender); // gender



                    if(getPd < 50 ){
                        return false;
                    }
                    console.log(formInfo);
                    $.ajax({
                        url: site_root + '/ajax/mull.php',
                        type: 'POST',
                        data: {
                            'action'    : 'calc',
                            'submit'    : 'formPension',
                            'info'      : formInfo
                        },
                        success:function(res) {

                            var data ='',
                                $form = $('#formPensionResults');

                           // console.log(res);
                            if(res === '0'){
                                alertBoxNoResult();
                            }else {
                                data = JSON.parse(res);
                                console.log(data);
                                console.log(getAww);
                                console.log(numberFormat(getAww));
                                $form.find('.field1').html(getDOI);
                                $form.find('.field2').html(getDOB);
                                $form.find('.field3').html(getPs);
                                $form.find('.field4').html(prepDateSlashes(dmyToMdy(reverseDateOrder(data[0])))); // PD End
                                $form.find('.field5').html(data[1] + ' yrs');   // Life Expectancy
                                $form.find('.field6').html(data[2] + ' yrs');   // age
                                $form.find('.field7').html('$ ' + numberFormat(getAww));      // AWW
                                $form.find('.field8').html('$ ' + data[3]);     // TD rate
                               // $form.find('.field9').html('$ ' + data[4]);     // voucher
                                $form.find('.field12').html(getPd + ' %');      // PDL
                                if(getPd >= 100) {
                                    $form.find('.field9').html('N/A');              // voucher
                                    $form.find('.field10').html('N/A');             // LPR
                                 //   $form.find('.field11').html('N/A');           // PD rate
                                    //$form.find('.field11').html('N/A');    // PD rate
                                    $form.find('.field11').html('$ ' + data[5]);    // PD rate
                                    // $form.find('.field12').html('N/A');             // PDL
                                    $form.find('.field13').html('N/A');             // PDW
                                    $form.find('.field14').html('N/A');             // PDV
                                    $form.find('.field15').html('N/A');             // LPV
                                    $form.find('.field16').html('N/A');             // PD/LPV

                                    $('.labelSwap label:first-of-type').addClass('txtHide');
                                    $('.labelSwap label:last-of-type').removeClass('txtHide');
                                }else{
                                    if(data[4] == 'N/A'){
                                        $form.find('.field9').html(data[4]);     // voucher
                                    }else{
                                        $form.find('.field9').html('$ ' + data[4]);     // voucher
                                    }



                                    $form.find('.field10').html('$ ' + data[8]);    // LPR
                                    $form.find('.field11').html('$ ' + data[5]);    // PD rate

                                    $form.find('.field13').html(data[6]);           // PDW
                                    $form.find('.field14').html('$ ' + data[7]);    // PDV
                                    $form.find('.field15').html('$ ' + data[9]);    // LPV
                                    $form.find('.field16').html('$ ' + data[10]);   // PD/LPV

                                    $('.labelSwap label:first-of-type').removeClass('txtHide');
                                    $('.labelSwap label:last-of-type').addClass('txtHide');
                                }
                                // aww
                                $('input[data-sync="5"]').each(function () {
                                    var $this = $(this);
                                    $this.val(convertNum(+getAww));
                                });

                                //doi
                                $('input[data-sync="6"]').each(function () {
                                    var $this = $(this);
                                    $this.val(getDOI);
                                });
//pd
                                $('input[data-sync="8"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+getPd);
                                });
                                // tdr
                                $('input[data-sync="12"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+data[3]);
                                });

                                // wltd
                                $('input[data-sync="22"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+data[2]);
                                });

                                // dob
                                $('input[data-sync="10"]').each(function () {
                                    var $this = $(this);
                                    $this.val(getDOB);
                                });

                                //ps
                                $('input[data-sync="11"]').each(function () {
                                    var $this = $(this);
                                    $this.val(getPs);
                                });

                                // pde
                                $('input[data-sync="13"]').each(function () {
                                    var $this = $(this);
                                    $this.val(data[0]);
                                });

                                // le
                                $('input[data-sync="14"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+data[1]);
                                });

                                // pdr
                                $('input[data-sync="17"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+data[5]);
                                });

                                //pdl
                                $('input[data-sync="18"]').each(function () {
                                    var $this = $(this);
                                    $this.val(getPd);
                                });

                                // pdw
                                $('input[data-sync="19"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+data[6]);
                                });

                                // pdv
                                $('input[data-sync="20"]').each(function () {
                                    var $this = $(this);
                                    $this.val(+data[7]);
                                });

                                //disclaimers

                                $form.siblings().not('h2').addClass('txtHide');
                                $form.removeClass('txtHide');

                                // disclaimers
                                $dis1.removeClass('txtHide');
                                $dis4.addClass('txtHide');

                                if(comparePdDate(getDate)){
                                    $dis2.removeClass('txtHide');
                                }else{
                                    $dis2.addClass('txtHide');
                                }

                                if(getPd >= 70){
                                    $dis3.removeClass('txtHide');
                                }else{
                                    $dis3.addClass('txtHide');
                                }

                                if(getPd == 100){
                                    $dis4.removeClass('txtHide');
                                }else{
                                    $dis4.addClass('txtHide');
                                }
                            }
                        },
                        error: function(errorThrown){
                            console.log("This has thrown an error:" + errorThrown);
                        }
                    });
                });
                // Find Date
                $formDates.on('click', '.btnStartOver.clearFormDate', function(){
                    $formDates.find('[class*="field"]:not([type="radio"])').removeClass('txtDisableInput').val('');
                });
                $formDates.on('click', '.btnCalc.calcFormDate', function(){
                    var knownDate = $calcOne.find('.field1').val(),
                        calcSign  = $calcOne.find('.field2:checked').val(),
                        nDays  = $calcOne.find('.field3').val(),
                        nWeeks = $calcOne.find('.field4').val(),
                        sign = '+',
                        signClass = 'txtGreen',
                        formInfo = [];

                    // checks if any of the form calc types are valid
                    if((!(nDays > 0) && !(nWeeks > 0)) ||(knownDate === '')){
                        alertBoxComplete();
                        return false;
                    }

                    if(calcSign == 2){
                        sign = '-';
                        signClass = 'txtRed';
                    }

                    console.log(knownDate);
                    console.log(mdyToYmd(prepDateHyphen(knownDate)));

                    formInfo.push(mdyToYmd(prepDateHyphen(knownDate)));
                    formInfo.push(calcSign);
                    formInfo.push(nDays);
                    formInfo.push(nWeeks);
                    console.log(formInfo);
                    $.ajax({
                        url: site_root + '/ajax/mull.php',
                        type: 'POST',
                        data: {
                            'action'    : 'calc',
                            'submit'    : 'formDates',
                            'info'      : formInfo
                        },
                        success:function(res) {
                            var $form = $('#formDatesResults'),
                                weeks = '',
                                data;
                            console.log(res);
                            data = JSON.parse(res);
                            console.log(data);
                            $form.find('.calcResult1 .field1').html('<span class="txtFont1 '+signClass+'">'+sign+'</span> '+ymdToMdy(data[0]));

                            weeks = data[2] + ' wks & ' + data[3] + ' days';
                            $form.find('.calcResult1 .field4').html('<span class="txtFont1 '+signClass+'">'+sign+'</span> '+data[1]);
                            $form.find('.calcResult1 .field3').html('<span class="txtFont1 '+signClass+'">'+sign+'</span> '+weeks);
                            $form.find('.calcResult1 .field5').html(knownDate);
                            $form.find('.calcResult1 .field6').html(ymdToMdy(data[0]));

                            //$form.siblings().not('h2').addClass('txtHide');

                            $('.calcFindResults').removeClass('txtHide');
                            /*$('.calcDiffResults, #formWeeklyCalcResults, #formTempResults, ' +
                                '#formWageResults, #formPermResults, #formPensionResults').addClass('txtHide');*/

                            $form.removeClass('txtHide').siblings().not('h2').addClass('txtHide');
                            $form.removeClass('txtHide');
                        },
                        error: function(errorThrown){
                            console.log("This has thrown an error:" + errorThrown);
                        }
                    });
                });

                // Find Difference
                $formDates.on('click', '.btnStartOver.clearFormDifference', function(){
                    $formDates.find('[class*="field"]').removeClass('txtDisableInput').val('');
                });
                $formDates.on('click', '.btnCalc.calcFormDifference', function(){
                    var startDate = $calcTwo.find('.field1').val(),
                        endDate  = $calcTwo.find('.field2').val(),
                        formInfo = [];

                    // checks if any of the form calc types are valid
                    if((startDate === '')||(endDate === '')){
                        alertBoxComplete();
                        return false;
                    }

                    formInfo.push(mdyToYmd(prepDateHyphen(startDate)));
                    formInfo.push(mdyToYmd(prepDateHyphen(endDate)));

                    $.ajax({
                        url: site_root + '/ajax/mull.php',
                        type: 'POST',
                        data: {
                            'action'    : 'calc',
                            'submit'    : 'formDifference',
                            'info'      : formInfo
                        },
                        success:function(res) {
                            var $form = $('#formDatesResults'),
                                weeks = '',
                                data = JSON.parse(res);
                            console.log(data);
                            weeks = data[1] + ' wks & ' + data[2] + ' days';
                            //$form.find('.calcResult2 .field1').html(data[0]);
                            $form.find('.calcResult1 .field4').html(data[0]);
                            $form.find('.calcResult1 .field3').html(weeks);
                            $form.find('.calcResult1 .field5').html(startDate);
                            $form.find('.calcResult1 .field6').html(endDate);
                            //$form.siblings().not('h2').addClass('txtHide');


                            $('.calcFindResults').addClass('txtHide');
                            $('.calcDiffResults').removeClass('txtHide');

                            $form.removeClass('txtHide');


                            // disclaimers
                            $dis1.addClass('txtHide');
                            $dis2.addClass('txtHide');
                            $dis3.addClass('txtHide');
                            $dis4.addClass('txtHide');
                        },
                        error: function(errorThrown){
                            console.log("This has thrown an error:" + errorThrown);
                        }
                    });
                });
                $('.btnCalc').on('click', function () {
                    $('.calcTitle').removeClass('txtHide');
                    $('.calcNotes').removeClass('txtHide');

                });
                $('.btnStartOver').on('click', function () {
                    $('.calcTitle').addClass('txtHide');
                    $('.calcNotes').addClass('txtHide');
                });

                $('.btnStartOver').on('click', function(){
                    // disclaimers
                    $dis1.addClass('txtHide');
                    $dis2.addClass('txtHide');
                    $dis3.addClass('txtHide');
                    $dis4.addClass('txtHide');
                });
            }
        }
        function modalSubmitConfirm(){
            if($('.wpcf7-response-output.wpcf7-mail-sent-ok').length){
                var msg = document.createElement('p');

                msg.innerHTML = "Referral Confirmation <br/><br/> Thank you for submitting your information. " +
                    "We will be contacting you within 24 hours (or the next work day) to obtain the additional information necessary to perform a conflict check and determine " +
                    "if it is appropriate to represent your interests.";

                genericModal(msg);
           }
        }

        function genericModal(msg){
            var $modal = $('#genericModal');
            $modal.find('.modal-body').html(msg);
            $modal.modal('show');
        }
        function alertBoxComplete(){
            alert('Please complete form');
        }
        function alertBoxNoResult(){
            alert('No data returned from this entry');
        }
        function printPageArea(){
            /*var printContent = document.getElementsByClassName('printArea'),
                WinPrint = window.open('', '', 'width=900,height=650');
            WinPrint.write('<html><head><title>Print it!</title><link rel="stylesheet" type="text/css" href="'+site_url+'/build/css/styles.css"></head><body>');
            WinPrint.document.write(printContent);
            WinPrint.write('</body></html>');

            WinPrint.document.close();
            WinPrint.focus();*/
            /*var WinPrint = document.getElementsByClassName('printArea');
            WinPrint.print();*/
            //WinPrint.close();
        }


		disabledSort = false;

		function disabledSorting(sortDisabled){
			disabledSort = sortDisabled;
			
			if(sortDisabled == false){
				$('#filterBy').removeClass('disabled');
				$('#sortLocation').removeClass('disabled');
			}
			
			if(sortDisabled == 'filterBy'){
				//$('#filterBy').addClass('disabled');
			}

			if(sortDisabled == 'sortLocation'){
				//$('#sortLocation').addClass('disabled');
			}

		}

		//var $lazy_reset = $('#person-search-div-wrapper').clone(true, true);
		var $lazy_reset = $('#person-search-div-wrapper').html();

		$('#peopleSidebar button:not(.btnPrintDir)').on('click', function(){

			var checkFilter = $(this).parent().parent().attr('id'),
                filterSelected = $('#filterBy button.selected');

			if (checkFilter == 'filterBy'){ // if the button clicked is disabled
				//return;

			}
            //console.log($('#filterBy button'));
            $('#filterBy button').removeClass('selected');
            //console.log($('#filterBy button'));
            //filterSelected.addClass('selected');
            //console.log(filterSelected);
            //console.log('clicked');
            // console.log(this);
            // console.log(checkFilter);
            // console.log(filterSelected);
            // console.log(filterSelected.attr('id'));

			if ($(this).attr('id') != 'btnNone' && !$(this).hasClass('btnPrintDir')){
                //onsole.log($(this).attr('id'));
                //console.log($(this).data('sort'));
				//$(this).addClass('selected');

                $('#'+ $(this).attr('id')).addClass('selected');
                /*if (checkFilter == 'filterBy'){
                    //$('#btnAll').trigger('click');
                }*/
			}

			switch($(this).data('sort')){
				case 'alphabet':
					sortByAlpha('all');
					break;
				case 'title':
					$('#office-location-wrapper > div').addClass('hidden');
					sortByTitle('all', 0);
					break;
				case 'ManagingAttorneys':
					//disabledSorting('sortLocation');
					sortByTitle('ManagingAttorneys', 0);
					break;
				case 'SeniorPartners':
					//disabledSorting('sortLocation');
					sortByTitle('SeniorPartner', 1);
					break;
				case 'location':
					//disabledSorting('filterBy');
                   // $('#btnAll').trigger('click');
					sortByLocation($(this).text(), filterSelected.attr('id'));
					break;
				case 'clearFilters':
					sortByLocation('All', null);

					break;
				default:
					console.log('didnt do anything');
			}
            // if the button clicked is not clear and a filter has been selected
            if (checkFilter && $(this).attr('id')  !== 'btnNone'){
                //filterSelected.trigger('click');
            }
		});

		//initialize location if in URL params
		var getUrlParameter = function getUrlParameter(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

			for (i = 0; i < sURLVariables.length; i++) {
					sParameterName = sURLVariables[i].split('=');

					if (sParameterName[0] === sParam) {
							return sParameterName[1] === undefined ? true : sParameterName[1];
					}
			}
		};

		function sortByLocation(theLocation, filter) {
		// console.log(theLocation);
		// console.log(filter);
			//$('#person-search-div-wrapper').html($lazy_reset);
            var txtLocation = theLocation,
                newurl;
			theLocation = theLocation.replace(/ /g, '');
            //console.log(theLocation);
            $('#sortLocation button').removeClass('selected');
			if(theLocation != 'All'){

                sortByTitle('Senior Partner ', 1);

				$('.peopleSearchLocation').addClass('hidden');
				$('#'+ theLocation +'Office.peopleSearchLocation').removeClass('hidden');
				$('#person-search-div-wrapper div.person-search-div').each(function(){
                    //console.log(($(this).data('sort-location').replace(/ /g, '') == theLocation));
					if($(this).data('sort-location').replace(/ /g, '') == theLocation){
						$(this).attr('data-sort-active', 'active');
					}else{
						$(this).attr('data-sort-active', 'inactive');
					}
				});
                // console.log(theLocation);
                // console.log(txtLocation);
                $('#sortLocation button:contains('+txtLocation+')').addClass('selected');

                if (history.pushState) {
                    newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?loc=' + encodeURI(txtLocation);
                    window.history.replaceState({path:newurl},'',newurl);
                }
			}else{
				
				sortByTitle('all', 0);
				disabledSorting(false);

				$('.peopleSearchLocation').addClass('hidden');
				$('#person-search-div-wrapper div.person-search-div').attr('data-sort-active', 'active');
				$('#btnAll').addClass('selected');
			}
			
			//if filtering by title, hide "empty" sections
					
            $('#person-search-div-wrapper .titleContainer').each(function(){

                if($(this).find('.person-search-div[data-sort-active="active"]').length > 0){
                    $(this).css('display', 'block');
                }else{
                    $(this).css('display', 'none');
                }

            });

		}

		function sortByAlpha(theAlpha) {

			//ungroup by title
			if($('.titleContainer').length > 0){
				$('.titleContainer .titleText').unwrap();	
				$('.titleText').remove();	
			}

			var $alphaOrder = $('#peopleSearch div[data-sort-active="active"]').sort(function (a, b) {
                                    if ($(a).data('sort-lastname').toLowerCase() < $(b).data('sort-lastname').toLowerCase()) return -1;
                                    if ($(a).data('sort-lastname').toLowerCase() > $(b).data('sort-lastname').toLowerCase()) return 1;
                                            return 0;
                                });

			$('#person-search-div-wrapper').html($alphaOrder);
		}


		function sortByTitle(theTitle, locationFilter) {
            // console.log(theTitle);
            // console.log(locationFilter);
			$('#person-search-div-wrapper').html($lazy_reset);
			att_to_search = $('.peopleResultsContainer .person-search-div');
				
			//sortByLocation('All');
			//sortByAlpha(' lazy ;) '); //lazy reset
            //console.log(theTitle);
			if(theTitle=='ManagingAttorneys'){

				var sortedByTitle = {'Managing Attorneys' : []},
                    sortedByTitle1 = ["Managing Attorney ",  "Managing Senior Counsel ", "Managing Associate Partner ",  "Managing Senior Partner "];

				$('#person-search-div-wrapper .person-search-div').each(function(){	
					var sortTitle = $(this).data('sort-subtitle'),
                        manage = ($(this).data('management-committee') > 0) ? "1" : "0";
                        /*manage = (  $(this).data('management-committee') > 0 ||
                                    $(this).data('managing-attorney') > 0   ||
                                    $(this).data('office-manager') > 0) ? "1" : "0";*/
                    
                    /*if(sortTitle.match(/Managing/g)){
                        //console.log($(this));
                        sortedByTitle['Managing Attorneys'].push($(this));
                    }*/
                    if(manage === "1" || sortedByTitle1.indexOf(sortTitle) > -1){
                        sortedByTitle['Managing Attorneys'].push($(this));
                    }
				});

			}else{

				var sortedByTitle = {"Management Committee ": [], "Senior Partner " : [], "Associate Partner " : [], "Senior Counsel " : [], "Associate Attorney " : []},
                    sortedByTitle1 = ["Senior Partner ", "Administrative Senior Partner ", "Managing Senior Partner "];
                    sortedByTitle2 = ["Associate Partner ", "Managing Associate Partner "];
                    sortedByTitle3 = ["Associate Attorney ", "Managing Attorney "];
					sortedByTitle4 = ["Senior Counsel ", "Managing Senior Counsel "];
					

				$('#person-search-div-wrapper .person-search-div').each(function(){	
				//$('#person-search-div-wrapper div[data-sort-active="active"]').each(function(){	

					var sortTitle = $(this).data('sort-title'),
                        manage = (  $(this).data('management-committee') > 0) ? 1 : 0,
                       // manage = $(this).data('management-committee'),
                        //manage = (  $(this).data('management-committee') > 0 ||
                        //$(this).data('managing-attorney') > 0   ||
                        //$(this).data('office-manager') > 0) ? "1" : "0";
                        manage2 = (  $(this).data('management-committee') > 0 ) ? "1" : "0";
					if(sortTitle == ''){
						sortTitle = 'Employee';
					}

                    if(locationFilter === 1 && sortedByTitle1.indexOf(sortTitle) > -1)
					{
                        sortedByTitle['Senior Partner '].push($(this));
                    }
					else if(manage)
					{
                        sortedByTitle['Management Committee '].push($(this));
                    } 
					else if(sortTitle in sortedByTitle) 
					{
						sortedByTitle[sortTitle].push($(this));
					}
					else if(sortedByTitle1.indexOf(sortTitle) > -1)
					{
                        sortedByTitle['Senior Partner '].push($(this));
                    }
					else if(sortedByTitle2.indexOf(sortTitle) > -1)
					{
                        sortedByTitle['Associate Partner '].push($(this));
					}
					else if(sortedByTitle4.indexOf(sortTitle) > -1)
					{
                        sortedByTitle['Senior Counsel '].push($(this));						
                    }
					else if(sortedByTitle3.indexOf(sortTitle) > -1)
					{
                        sortedByTitle['Associate Attorney '].push($(this));
                    }
					else
					{
						sortedByTitle[sortTitle] = [];
						sortedByTitle[sortTitle].push($(this));
					}

                    // console.log($(this));
                    // console.log(sortTitle);
                    // console.log(sortedByTitle1.indexOf(sortTitle) > -1);
                    // console.log(sortedByTitle1.indexOf(sortTitle));
                    // console.log(sortedByTitle2.indexOf(sortTitle) > -1);
                    // console.log(sortedByTitle2.indexOf(sortTitle));
                    // console.log(sortedByTitle3.indexOf(sortTitle) > -1);
                    // console.log(sortedByTitle3.indexOf(sortTitle));
				});
                // console.log(sortedByTitle);
			}
            // console.log(locationFilter);
			var html = '',
                titleClass;

			$.each(sortedByTitle, function(val){
				//TODO: clean up with .wrap()
                titleClass = val.split(' ');
                titleClass = (titleClass[0]+'_'+titleClass[1]).toLowerCase();

				html += '<div id="'+ val.replace(/ /g, '') +'" class="titleContainer '+titleClass+'">' +
				'<h2 class="titleText">' + val + '</h2>';
				//console.log(this.length);
				for($i = 0, $max = this.length; $i < $max; $i++){
					html += this[$i][0].outerHTML;
				}
				html += '</div>';
			});

			$('#person-search-div-wrapper').html(html);
			
			if(theTitle != 'all'){
				$('#person-search-div-wrapper .titleContainer').css('display', 'none');
				$('#person-search-div-wrapper #'+ theTitle +'.titleContainer').css('display', 'block');
			}
			
		}

		sortByTitle('all', 0);

		var loc = getUrlParameter('loc');

		if(typeof loc != 'undefined'){
            loc = loc.split(' ');
            loc.pop();
            loc = loc.join(' ');

            //console.log(loc);
            $('#sortLocation li button').each(function(){
                if($(this).text() == loc){
                    //$(this).trigger('click');
                }
            });
		}


		var att_to_search = $('.peopleResultsContainer .person-search-div');

		function sortByKeyword(keyword){	
		console.log(att_to_search);	
			att_to_search.each(function(){
				var keyword_regex = new RegExp('^'+keyword, 'i');
				var firstName = $(this).find('.att_firstName').text();
				var lastName = $(this).find('.att_lastName').text();

				if(firstName.match(keyword_regex) || lastName.match(keyword_regex)) {
					$(this).attr('data-sort-active', 'active');
				}else{
					$(this).attr('data-sort-active', 'inactive');
				}

			});
		}
		
		$('#peopleSearchBox').on('keyup', function() {
			sortByKeyword($(this).val())
		});
		
		$('#peopleSearchBox').submit(function(){
			return false;
		});

        // Polyfills

        // date field polyfill

        $('input[type="date"]').addClass('dateField');

        /*$('input[type="date"]').attr({
            dateFormat: 'mm/dd/yy',
            altFormat: 'yyyy-mm-dd'
         });*/
        $('.dateField').attr({
            type: 'text'
        });
        $('.dateField').datepicker({
            dateFormat: 'mm/dd/yy'
        });
        /*$('.dateField').datepicker({
            dateFormat: "mm/dd/yy", // format shown to the user
            //altField: "#" + $(this).attr('id'),
            altFormat: "yy-mm-dd" // format for database processing
        });*/
        $('.dateField').attr({
            placeholder: 'MM/DD/YYYY'
        });
        // reset all date types except date of calculation
        $('input.dateField').each(function(){
            $this = $(this);
            if($this.data('sync') != '9'){
                $this.val('');
            }
        });

        $('.dateField[data-sync="9"]').each(function(){
            $this = $(this);
            $this.val(GlobalFunctions.currentDate);
            //console.log($this);
        });
        //console.log(GlobalFunctions.currentDate);



        /*console.log('----first-----');
        console.log(!window.addEventListener );
        console.log(typeof(UserAgentInfo) != 'undefined' && !window.addEventListener);
        console.log(checkType('date'));
        console.log('check------------------');*/
        //if(!checkType('date')){
            // set datepicker for browsers that don't support input of date type
            /*$('input[type="date"]').datepicker({
                dateFormat: 'mm/dd/yy'
            });

            // reset all date types except date of calculation
            $('input[type="date"]:not([data-sync="9"])').each(function(){
                $this = $(this);
                $this.val('');
                console.log($this);
            });*/
     //   }

        // number field polyfill

            $("input[type=number]").on("keydown", function(evt) {
                var $this = $(this),
                    stringsearch = ".",
                    val = $this.val(),
                    len = val.length,
                    keyCode = evt.which,
                    i = 0,
                    count = 0,
                    validKeyCodes = [
                        8,      // backspace
                        9,      // tab
                        37,     // left arrow
                        39,     // right arrow
                        46,     // delete
                        110,    // period
                        190,    // period
                        96,     // zero
                        97,     // one
                        98,     // two
                        99,     // three
                        100,    // four
                        101,    // five
                        102,    // six
                        103,    // seven
                        104,    // eight
                        105     // nine
                    ],
                    periodCodes = [
                        110,
                        190
                    ];

                // check how many decimals exists in val
                for(; i<len; ++i){
                    if(stringsearch == val[i]){
                        ++count;
                    }
                }

                if ((((evt.which < 48 || evt.which > 57) && (validKeyCodes.indexOf(keyCode) < 0) ) ||
                    ((periodCodes.indexOf(keyCode) > -1) && count > 0))){
                    evt.preventDefault();
                }
            });

        function checkType(testType){
            try {
                var input = document.createElement("input");

                input.type = testType;

                if (input.type === testType) {
                    console.log("supported");
                    return true;
                } else {
                    console.log("not supported");
                    return false;
                }
            } catch(e) {
                console.log("not supported:2");
                return false;
            }
        }

       
        
    }); // ready? second time?!

    // get YMD
    function getCurrentDate(){
        var d = new Date(),
            curr_date = d.getDate(),
            curr_month = d.getMonth() + 1,
            curr_year = d.getFullYear();

        if(curr_month < 10)
            curr_month = '0' + curr_month;

        if(curr_date < 10)
            curr_date = '0' + curr_date;

        return (curr_month + "/" + (curr_date) + "/" + curr_year);
        //  return (curr_month + "-" + (curr_date) + "-" + curr_year);
        //  return (curr_year + "-" + (curr_month) + "-" + curr_date);
        // return (curr_date + "-" + (curr_month) + "-" + curr_year);
    }
    function reverseDateOrder(date){
        var d = date.split("-");
        return (d[2] + "/" + d[1] + "/" + d[0]);
    }
    function prepDateSlashes(date){
        var d = date.split("-");
        return (d[0] + "/" + d[1] + "/" + d[2]);
    }
    function prepDateHyphen(date){
        var d = date.split("/");
        return (d[0] + "-" + d[1] + "-" + d[2]);
    }
    function dmyToMdy(date){
        var d = date.split("/");
        return (d[1] + "-" + d[0] + "-" + d[2]);
    }
    function dmyToMdy2(date){
        var d = date.split("-");
        return (d[1] + "/" + d[0] + "/" + d[2]);
    }
    function mdyToYmd(date){
        var d = date.split("-");
        return (d[2] + "-" + d[0] + "-" + d[1]);
    }
    function ymdToMdy(date){
        var d = date.split("-");
        return (d[1] + "/" + d[2] + "/" + d[0]);
    }
    function numberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    function numberFormat(num) {
        var num1;

        if(typeof num === 'number'){
            // number
            num1 = parseFloat(num);
        }else{
            //string
            num1 = parseFloat(num.replace(',', ''));
        }
        return num1.formatMoney(2);
    }
    function betweenDate(date){
        var dateFrom = "01/01/2005",
            dateTo = "12/31/2012",
            d1 = dateFrom.split("/"),
            d2 = dateTo.split("/"),
            c = date.split("/"),
            from = new Date(d1[2], parseInt(d1[1])-1, d1[0]),  // -1 because months are from 0 to 11
            to   = new Date(d2[2], parseInt(d2[1])-1, d2[0]),
            check = new Date(c[2], parseInt(c[1])-1, c[0]);

        return !!(check > from && check < to);

    }
    function beforeDate(date){
        var dateMin = "01/01/2004",
            d1 = dateMin.split("/"),
            d2 = date.split("/"),
            minDate = new Date(d1[2], parseInt(d1[1])-1, d1[0]),
            current = new Date(d2[2], parseInt(d2[1])-1, d2[0]);

        return !!(current < minDate);
    }
    function comparePdDate(time) {
        var minTime = '1/1/2005';
        var maxTime = '12/31/2012';
        return ((new Date(minTime) <= new Date(time)) && (new Date(maxTime) >= new Date(time))) ? true : false;
        //return new Date(time1) > new Date(time2); // true if time1 is later
    }
    function convertNum(num) {
        var num1;
        if (typeof num === 'string') {
            // num1 = num.replace(/ ,/g, '');
            num1 = num.replace(',', '');
            num1 = parseFloat(num1);
        }else{
            num1 = parseFloat(num, 2);
        }
        return num1.toFixed(2);
    }

    function accordionPage() {
        if($('.accordion').length){
            var acc = document.getElementsByClassName("accordion");
            var i;
            console.log(acc);
            for (i = 0; i < acc.length; i++) {
                acc[i].onclick = function() {
                    this.classList.toggle("active");
                    var panel = this.nextElementSibling;
                    console.log(panel);
                    if (panel.style.maxHeight){
                        panel.style.maxHeight = null;
                    } else {
                        panel.style.maxHeight = panel.scrollHeight + 'px';
                    }
                }
            }
        }
    }


    // add to prototype
    Date.prototype.toDateInputValue = (function() {
        var local = new Date(this);
        local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
        return local.toJSON().slice(0,10);
    });
    Number.prototype.formatMoney = function(c, d, t){
        var n = this,
            c = isNaN(c = Math.abs(c)) ? 2 : c,
            d = d == undefined ? "." : d,
            t = t == undefined ? "," : t,
            s = n < 0 ? "-" : "",
            i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
            j = (j = i.length) > 3 ? j % 3 : 0;
        return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
    };


})(jQuery);// ready? first time?!
