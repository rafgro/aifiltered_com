<?php

# Given URL string, downloads its content, decodes using JSON, and returns the object
function simple_json_dl($url, $ifrss=false) {
	echo($url.'<br/>');
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	if(!$json = curl_exec($ch)) {
	    trigger_error(curl_error($ch));
	}
	curl_close($ch);
	if ( $ifrss == false ) {
		return json_decode($json);
	} else {
		$result = simplexml_load_string($json, "SimpleXMLElement", LIBXML_NOCDATA);
	    #return json_decode(json_encode($simpleXml));
	    return $result;
	}
}

# Util sorting functions
function cmppost($a, $b) { return ($a['internalscore'] <= $b['internalscore']) ? 1 : -1; }
function cmpcomm($a, $b) { return ($a['score'] <= $b['score']) ? 1 : -1; }
function cmpstamp($a, $b) { return ($a['timestamp'] <= $b['timestamp']) ? 1 : -1; }
# Util string functions
function sanitizeforecho($t) { return str_replace('"', '\\"', $t); }
function shortennumber($n) {
	if ( $n < 1000 ) { return $n; }
	else if ( $n < 10000 ) { return round($n/1000,1) . 'k'; }
	else { return round($n/1000) . 'k'; }
}
