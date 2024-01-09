
<!DOCTYPE html>
<html>
  <head>
  <title>Retrieving Autocomplete Predictions</title>
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #right-panel {
        font-family: 'Roboto','sans-serif';
        line-height: 30px;
        padding-left: 10px;
      }

      #right-panel select, #right-panel input {
        font-size: 15px;
      }

      #right-panel select {
        width: 100%;
      }

      #right-panel i {
        font-size: 12px;
      }
	  .address_fields {
		  display:none;
	  }
	  .display_address {
		  display:block;
	  }
    </style>
  </head>
  <body onLoad="init()">
    <div id="right-panel" class="personx">
      <input type="text" value="" id="full_addressInput" onKeyUp="searchAddress('personx')" onBlur="hideResults()" style="width:375px"  />
      <div id="map_results_holder" style="background:#FFF; border:1px solid black; padding:2px; display:none; width:375px; position:absolute"></div>
    </div>
    <div style="margin-top:10px; display:none; padding-left: 10px;" id="address_fields_holder">
    	<input type="text" id="street_number_personx" class="address_fields">
        <input type="text" id="street_personx" class="address_fields display_address" placeholder="Street">
        <input type="text" id="route_personx" class="address_fields">
        
        <input type="text" id="locality_personx" class="address_fields">
        <input type="text" id="sublocality_personx" class="address_fields">
        <input type="text" id="sublocality_level_1_personx" class="address_fields">
        <input type="text" id="sublocality_level_2_personx" class="address_fields">
        
        <input type="text" id="neighborhood_personx" class="address_fields">
        
        <input type="text" id="city_personx" class="address_fields display_address" placeholder="City">
        
        <input type="text" id="administrative_area_level_1_personx" class="address_fields display_address" placeholder="State">
        <input type="text" id="administrative_area_level_2_personx" class="address_fields">
        <input type="text" id="postal_code_prefix_personx" class="address_fields">
        <input type="text" id="postal_code_personx" class="address_fields display_address" placeholder="Zip">
        <input type="text" id="postal_code_suffix_personx" class="address_fields">
        <input type="text" id="country_personx" class="address_fields">
    </div>
    <div id="map" style="display:none"></div>
    <script>
		var search_timeoutid = false;
		var init = function() {
		}
		function lightenMe(obj) {
			obj.style.background = "#FFF";
		}
		function darkenMe(obj) {
			obj.style.background = "#EDEDED";
		}
		function addressClick(obj, className) {
			var element = obj;
			var arrElement = element.id.split("_");
			var place_id = arrElement[arrElement.length - 1];
			
			var data = $("#data_" + place_id).val();
			var jdata = JSON.parse(data);
			
			$(".address_fields").val("");
			var sublocality = "";
			var administrative_area_level_1 = "";
			var postal_code = "";
			for (var i = 0; i < jdata.length; i++) {
				var addressType = jdata[i].types[0];
				//might be sublocality_level_1
				if (jdata[i].types.length > 1) {
					if (addressType == "sublocality_level_1" && jdata[i].types[1]=="sublocality") {
						addressType = "sublocality";
					}
				}
				if (typeof jdata[i].long_name == "string") {
				  var val = jdata[i].long_name;
				   if (addressType=="administrative_area_level_1") {
						val = jdata[i].short_name;
						administrative_area_level_1 = val;  
				  }
				  document.getElementById(addressType + "_" + className).value = val;
				  
				  if (addressType=="neighborhood") {
					sublocality = val;  
					document.getElementById("city_" + className).value = sublocality;
				  }	  
				  if (addressType=="sublocality") {
					sublocality = val;  
					document.getElementById("city_" + className).value = sublocality;
				  }
				 
				  if (addressType=="postal_code") {
					postal_code = val;  
				  }
				}
			}
			if (sublocality=="") {
				sublocality = document.getElementById("locality_" + className).value;
			}
			//var text_box = document.querySelectorAll("." + className + " #full_addressInput")[0];
			var text_box = $("." + className + " #full_addressInput");
			var arrAddress = [];
			if (document.getElementById("street_number_" + className).value!="") {
				arrAddress[arrAddress.length] = document.getElementById("street_number_" + className).value + " " + document.getElementById("route_" + className).value;
				if (sublocality!="") {
					arrAddress[arrAddress.length] = sublocality;
				}
				arrAddress[arrAddress.length] = document.getElementById("administrative_area_level_1_" + className).value + " " + document.getElementById("postal_code_" + className).value;
				
			} else {
				sublocality = jdata[0].long_name + ", " + jdata[1].long_name; 
				arrAddress[arrAddress.length] = sublocality;
				arrAddress[arrAddress.length] = administrative_area_level_1;
			}
			
			var full_address = arrAddress.join(", ");
			//text_box.value = full_address;
			text_box.val(full_address);
			
			document.getElementById("city_" + className).value = sublocality;
			document.getElementById("street_" + className).value = document.getElementById("street_number_" + className).value + " " + document.getElementById("route_" + className).value;
			
			document.getElementById('map_results_holder').innerHTML = "";
			document.getElementById('map_results_holder').style.display = "none";
			
			$("#address_fields_holder").fadeIn();
		}
		function hideResults() {
			setTimeout(function() {
				//we need a slight delay to allow for processing of info before hiding
				document.getElementById('map_results_holder').style.display = "none";
			}, 300);
		}
		function searchAddress(className) {
			clearTimeout(search_timeoutid);
			hideResults();
			
			document.getElementById('map_results_holder').innerHTML = "";
			search_timeoutid = setTimeout(function() {
					getGoogleAddresses(className);
				}, 600);
		}
      // This example retrieves autocomplete predictions programmatically from the
      // autocomplete service, and displays them as an HTML list.

      // This example requires the Places library. Include the libraries=places
      // parameter when you first load the API. For example:
      // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">

      function getGoogleAddresses(className) {
        var displaySuggestions = function(predictions, status) {
          if (status != google.maps.places.PlacesServiceStatus.OK) {
            //alert(status);
			document.getElementById('map_results_holder').style.display = "";
			document.getElementById('map_results_holder').innerHTML = "Nothing found";
            return;
          }
			
			var map = new google.maps.Map(document.getElementById('map'));
			service = new google.maps.places.PlacesService(map);
				
          predictions.forEach(function(prediction) {
			  if (typeof prediction.place_id != "undefined") {					  
				  //let's get some details
				  var request = {
					  placeId: prediction.place_id
					};
				
					
					service.getDetails(request, callback);
					
					function callback(place, status) {
					  if (status == google.maps.places.PlacesServiceStatus.OK) {
						  var full_address = $("." + className + " #full_addressInput").val();
						//createMarker(place);
						/*
						var li = document.createElement('li');
						li.appendChild(document.createTextNode(place.formatted_address));
						document.getElementById('map_results_holder').appendChild(li);
						*/
						full_address = full_address.capitalizeWords();
						$('#map_results_holder').append("<div><a id='place_id_" + place.id + "' class='address_place' style='cursor:pointer' onclick='addressClick(this, \"personx\")' onmouseover='darkenMe(this)' onmouseout='lightenMe(this)'>" + place.formatted_address.replace(full_address, "<span style='font-weight:bold'>" + full_address + "</span>") + "</a><textarea id='data_" + place.id + "' style='display:none'>" + JSON.stringify(place.address_components) + "</textarea></div>");
						document.getElementById('map_results_holder').style.display = "";
					  }
					}
				}
			});
        };
		var full_address = $("." + className + " #full_addressInput").val();
		var request = {
			input: full_address,
			componentRestrictions: {country: 'us'},
		};
        var service = new google.maps.places.AutocompleteService();
		
        service.getQueryPredictions(request, displaySuggestions);
      }
	  //https://maps.googleapis.com/maps/api/js?key=AIzaSyDIJ9XX2ZvRKCJcFRrl-lRanEtFUow4piM&libraries=places&callback=getGoogleAddresses
    </script>
    <script type="text/javascript" src="lib/jquery.1.10.2.js"></script>
    <script type="text/javascript" src="js/utilities.js"></script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"
        async defer></script>
  </body>
</html>
