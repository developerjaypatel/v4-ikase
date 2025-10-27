<?php 

include ("../api/connection.php");

$case_id = passed_var("case_id", "get");
$attribute = "personal_injury_picture"; //passed_var("attribute");
$cus_id = passed_var("cus_id", "get");

// Jay added 2/7/2023 start
// $customer_id = passed_var("customer_id", "get");
// $attribute_got = passed_var("attribute", "get");
// if($cus_id==""){
//   $cus_id = $customer_id;
// }
// if($attribute!=$attribute_got){
//   echo $attribute_got;
//   $attribute = $attribute_got;
// }
// Jay added 2/7/2023 end


$query = "SELECT doc.`document_id` , doc.`document_uuid` , doc.`parent_document_uuid` , 
	REPLACE(  `document_name` ,  '/home/cstmwb/public_html/autho/web/fileupload/server/php/file_container/" . $case_id . "/',  '' ) `document_name` ,  
	IF (DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p'))`document_date` , 
	IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p'))`received_date` , `doc`.`source`, 	
	`document_filename` ,  `document_extension`, `thumbnail_folder` ,  `description` ,  IF (`description_html` IS NULL, '', `description_html`) `description_html` ,  `type` ,  `verified` , doc.`deleted` , doc.`document_id`  `id` , doc.`document_uuid`  `uuid`, doc.customer_id, cu.user_name,  `cse_case`.`case_uuid`, `cse_case`.`case_id`
	FROM  `cse_document` doc
	INNER JOIN  `cse_pi_document` ON  (`doc`.`document_uuid` =  `cse_pi_document`.`document_uuid` AND `cse_pi_document`.`attribute_1` = '" . $attribute . "')
	INNER JOIN  ikase.`cse_user` cu ON cse_pi_document.last_update_user = cu.user_uuid 
	INNER JOIN  `cse_case` ON (  `cse_pi_document`.`case_uuid` =  `cse_case`.`case_uuid` 
	AND  `cse_case`.`case_id` = " . $case_id . " ) 
	WHERE doc.customer_id = '" . $cus_id . "'
	AND doc.deleted =  'N'
	AND `cse_pi_document`.deleted =  'N'
	ORDER BY doc.document_date DESC, doc.document_id DESC";

$docs = DB::select($query);

foreach($docs as $doc) {
	$document_id = $doc->document_id;
	$document_name = $doc->document_name;
	$document_date = $doc->document_date;
	$document_filename = $doc->document_filename;
	$description = $doc->description;
	$doc_type = $doc->type;
	
	$document_type_norm = ucfirst(str_replace("_", " ", $doc_type));
	
	$the_image = "<div class='element-item " . $doc_type . " ' data-category='" . $doc_type . "'><img src='D:/uploads/" . $cus_id . "/" . $case_id . "/" . $document_filename . "' class='personal_injury_img_" . $int . " " . $doc_type . "' id='personal_injury_img_" . $int . "' height='128' width='128' /><br><span style='font-size:0.8em; color:white' class='" . $doc_type . "'>" . $document_type_norm . "&nbsp;<a id='deleteimage_" . $document_id . "' class='delete_image' style='cursor:pointer'><i class='glyphicon glyphicon-trash' style='color:#FA1616;'></i></span></a></div>";

	$arrImages[] = $the_image;
}
//die(print_r($arrImages));
?>
<!doctype html>
<html class="export">
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width">
  <meta name="description" content="filter &amp; sort magical layouts">
    <!-- Isotope does not require any CSS files -->
    <link rel="stylesheet" href="css/isotope-docs.css" media="screen">

</head>
<body class="page--index" data-page="index" topmargin="0" leftmargin="0">

  

<div class="hero" style="margin-top:0px; margin-left:0px">
<div class="big-demo" data-js-module="hero-demo" style="margin-top:0px; margin-left:0px">
  <div class="ui-group" style="margin-top:5px; margin-left:5px">
    <h3 class="ui-group__title">Filter</h3>
    <div class="filters button-group js-radio-button-group">
      <button class="button is-checked" data-filter="*">show all</button>
      <!--<button class="button" data-filter=".metal">metal</button>
      <button class="button" data-filter=".transition">transition</button>
      <button class="button" data-filter="ium">&#x2013;ium</button>-->
      <button class="button" data-filter=".scene_photo">Scene</button>
      <button class="button" data-filter=".injury_photo">Injury</button>
      <button disabled class="button" data-filter=".prop_damage_photo">Damage</button>
      <button disabled class="button" data-filter=".scene_diagram">Diagram</button>
    </div>
  </div>
  <!--<div class="ui-group" style="margin-top:5px; margin-left:5px">
    <h3 class="ui-group__title">Sort</h3>
    <div class="sort-by button-group js-radio-button-group">
      <button class="button is-checked" data-sort-by="original-order">original order</button>
      <button class="button" data-sort-by="name">Name</button>
      <button class="button" data-sort-by="symbol">Type</button>
    </div>
  </div>-->
  
  <div class="grid" style="margin-top:5px; margin-left:5px; padding:15px; margin-right:5px">
  	  <?php if(is_array($arrImages)) { 
        echo implode($arrImages);
       }
      ?>
      <!--	
      <div class="element-item metalloid transition " data-category="transition">
        <h5 class="name">Mercury</h5>
        <p class="symbol">Hg</p>
        <p class="number">80</p>
        <p class="weight">200.59</p>
      </div>
      <div class="element-item metalloid " data-category="metalloid">
        <h5 class="name">Tellurium</h5>
        <p class="symbol">Te</p>
        <p class="number">52</p>
        <p class="weight">127.6</p>
      </div>
      <div class="element-item post-transition metal " data-category="post-transition">
        <h5 class="name">Bismuth</h5>
        <p class="symbol">Bi</p>
        <p class="number">83</p>
        <p class="weight">208.980</p>
      </div>
      <div class="element-item post-transition metal " data-category="post-transition">
        <h5 class="name">Lead</h5>
        <p class="symbol">Pb</p>
        <p class="number">82</p>
        <p class="weight">207.2</p>
      </div>
      <div class="element-item transition metal " data-category="transition">
        <h5 class="name">Gold</h5>
        <p class="symbol">Au</p>
        <p class="number">79</p>
        <p class="weight">196.967</p>
      </div>
      <div class="element-item alkali metal " data-category="alkali">
        <h5 class="name">Potassium</h5>
        <p class="symbol">K</p>
        <p class="number">19</p>
        <p class="weight">39.0983</p>
      </div>
      <div class="element-item alkali metal " data-category="alkali">
        <h5 class="name">Sodium</h5>
        <p class="symbol">Na</p>
        <p class="number">11</p>
        <p class="weight">22.99</p>
      </div>
      <div class="element-item transition metal " data-category="transition">
        <h5 class="name">Cadmium</h5>
        <p class="symbol">Cd</p>
        <p class="number">48</p>
        <p class="weight">112.411</p>
      </div>
      <div class="element-item alkaline-earth metal " data-category="alkaline-earth">
        <h5 class="name">Calcium</h5>
        <p class="symbol">Ca</p>
        <p class="number">20</p>
        <p class="weight">40.078</p>
      </div>
      <div class="element-item transition metal " data-category="transition">
        <h5 class="name">Rhenium</h5>
        <p class="symbol">Re</p>
        <p class="number">75</p>
        <p class="weight">186.207</p>
      </div>
      <div class="element-item post-transition metal " data-category="post-transition">
        <h5 class="name">Thallium</h5>
        <p class="symbol">Tl</p>
        <p class="number">81</p>
        <p class="weight">204.383</p>
      </div>
      <div class="element-item metalloid " data-category="metalloid">
        <h5 class="name">Antimony</h5>
        <p class="symbol">Sb</p>
        <p class="number">51</p>
        <p class="weight">121.76</p>
      </div>
      <div class="element-item transition metal " data-category="transition">
        <h5 class="name">Cobalt</h5>
        <p class="symbol">Co</p>
        <p class="number">27</p>
        <p class="weight">58.933</p>
      </div>
      <div class="element-item lanthanoid metal inner-transition " data-category="lanthanoid">
        <h5 class="name">Ytterbium</h5>
        <p class="symbol">Yb</p>
        <p class="number">70</p>
        <p class="weight">173.054</p>
      </div>
      <div class="element-item noble-gas nonmetal " data-category="noble-gas">
        <h5 class="name">Argon</h5>
        <p class="symbol">Ar</p>
        <p class="number">18</p>
        <p class="weight">39.948</p>
      </div>
      <div class="element-item diatomic nonmetal " data-category="diatomic">
        <h5 class="name">Nitrogen</h5>
        <p class="symbol">N</p>
        <p class="number">7</p>
        <p class="weight">14.007</p>
      </div>
      <div class="element-item actinoid metal inner-transition " data-category="actinoid">
        <h5 class="name">Uranium</h5>
        <p class="symbol">U</p>
        <p class="number">92</p>
        <p class="weight">238.029</p>
      </div>
      <div class="element-item actinoid metal inner-transition " data-category="actinoid">
        <h5 class="name">Plutonium</h5>
        <p class="symbol">Pu</p>
        <p class="number">94</p>
        <p class="weight">(244)</p>
      </div>-->
  </div>


</div>

  </div> 

</div> 

<!-- Looking for isotope.js? Use isotope.pkgd.min.js -->
<!-- Isotope does NOT require jQuery. But it does make things easier -->
<script src="https://code.jquery.com/jquery-2.2.3.min.js"></script>
<script>window.jQuery || document.write('<script src="js/jquery.min.js"><\/script>')</script>

  <script src="js/isotope-docs.min.js"></script>


</body>
</html>
