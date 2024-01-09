<?
# Connect to the database
# Collect the results
$arrResult = [
    ["id" => 1, "name" => 'nick'],
    ["id" => 2, "name" => 'angel']
];

# JSON-encode the response
$json_response = json_encode($arrResult);

# Optionally: Wrap the response in a callback function for JSONP cross-domain support
if ($_GET["callback"]) {
    $json_response = $_GET["callback"]."($json_response)";
}

echo $json_response;
die();
