<?php
//FIXME: if this is being used for real encryption...................
function encrypt($value, $key = '') {
	return md5(sha1(md5(base64_decode($value.$key)).$key).$value);
}

/**
 * Strips whatever is before $after at $str (if any).
 * @param $str
 * @param $after
 * @return string
 * @todo we should probably use Illuminate\Support instead
 */
function str_after($str, $after) {
    return substr($str, strpos($str, $after) !== false? strlen($after) : 0);
}
