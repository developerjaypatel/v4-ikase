<?php
require_once '../spamfilter.php';
$text = "Do you want to purchase some [url=shadywebsite.ca]Canadian Cialis[/url] from me?";

// Search in a specific blacklist (absolute paths can be used instead)
$filter = new SpamFilter();
$result = $filter->check_text($text);
if ($result)
{
    echo "You like talking about economics and trading, right? Go away!";
}


// Search in all available blacklists
$filter = new SpamFilter();

$result = $filter->check_text($text);
if ($result)
{
    // Result contains the matched word (not the matched regular expression)
    // In our example, $result will contain the value "viagra".
    echo "There is a special place in hell reserved for people who talk about '$result' on my blog!";
}
else
{
	echo "Your comment is clean from all known spam!";
}
?>