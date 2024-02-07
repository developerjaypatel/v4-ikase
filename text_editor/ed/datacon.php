<?php
//TODO: check where else this file is used - many places, but $link is probably only used in check_zip; it could receive some extra upgrade so we can get rid of $link and include settings directly?
include('settings.php');
$link = DB::conn();
