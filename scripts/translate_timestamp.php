<?php

# Take unix timestamp, compare with current time, return in words 'something ago'
function translate_timestamp($timestamp, $highlight) {
	$difference = time() - $timestamp; # correction of reddit jump
	# highlighting
	$h1 = ''; $h2 = '';
	if ( $highlight == true ) { $h1 = '<strong>'; $h2 = '</strong>'; }
	# 7 days; 24 hours; 60 mins; 60 secs
	if ( $difference < 60 ) { return "{$h1}just now{$h2}"; }
	else if ( $difference < 60*60 ) { $d = round($difference / 60); return "{$h1}{$d}min ago{$h2}"; }
	else if ( $difference < 60*60*23 ) { $d = round($difference / (60*60)); return "{$d}h ago"; } # 23 to avoid 24=yesterday
	else if ( $difference < 60*60*24*7 ) {
		$d = round($difference / (60*60*24));
		if ( $d == 1 ) { return "yesterday"; }
		else if ( $d == 7 ) { return "1 week ago"; }
		else { return "{$d} days ago"; }
	}
	else if ( $difference < 60*60*24*7*30.4 ) { # 365/12=30.4
		$d = round($difference / (60*60*24*7));
		if ( $d == 1 ) { return "1 week ago"; }
		else { return "{$d} weeks ago"; }
	}
	else {
		$d = round($difference / (60*60*24*7*30.4));
		if ( $d == 1 ) { return "1 month ago"; }
		else { return "{$d} months ago"; }
	}
	return ' ';
}
