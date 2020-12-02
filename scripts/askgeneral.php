<?php

# Includes
include_once('./class_item.php');
include_once('./class_category.php');
include_once('./askreddit.php');
include_once('./askgnews.php');
include_once('./askblogs.php');
include_once('./askkarpathy.php');
include_once('./askpwc.php');
include_once('./askdeepai.php');
include_once('./askpodcasts.php');
include_once('./askeducation.php');
include_once('./translate_timestamp.php');
include_once('./util_log.php');

$modes = [
	'24h'=>['subtparameter'=>'&t=day', 'mlnumber'=>15, 'mlnumberlast'=>30, 'funumber'=>10, 'funumberlast'=>3, 'timelimit'=>60*60*24, 'gnewsnumber'=>10, 'blogsnumber'=>10, 'karpathyparameter'=>'day', 'karpathynumber'=>5, 'pwcparameter'=>'latest', 'pwcnumber'=>5, 'filename'=>'stream24h.php', 'cutoff'=>8, 'supressscore'=>0.5, 'name'=>'24h'],
	'7d'=>['subtparameter'=>'&t=week', 'mlnumber'=>50, 'mlnumberlast'=>5, 'funumber'=>10, 'funumberlast'=>3, 'timelimit'=>60*60*24*7, 'gnewsnumber'=>30, 'blogsnumber'=>30, 'karpathyparameter'=>'week', 'karpathynumber'=>30, 'pwcparameter'=>'', 'pwcnumber'=>10, 'filename'=>'stream7d.php', 'cutoff'=>32, 'supressscore'=>1, 'name'=>'7d'],
	'1m'=>['subtparameter'=>'&t=month', 'mlnumber'=>100, 'mlnumberlast'=>5, 'funumber'=>20, 'funumberlast'=>3, 'timelimit'=>60*60*24*30.4, 'gnewsnumber'=>40, 'blogsnumber'=>40, 'karpathyparameter'=>'month', 'karpathynumber'=>50, 'pwcparameter'=>'', 'pwcnumber'=>20, 'filename'=>'stream1m.php', 'cutoff'=>64, 'supressscore'=>1.3, 'name'=>'1m']
];

# Util func for showing titles
function tailortitle($title) {
	# max length: 66 characters
	if ( strlen($title) <= 66+3 ) { return $title; } # nothing to cut
	$lastword = strrpos(substr($title, 0, 66+1), ' '); # reverse pos
	if ( $lastword < 5 ) { return $title; } # rare case of no spaces
	return substr($title, 0, $lastword) . '...';
}

# Util for primitive sanitization
function cutquotes($text) {
	return str_replace('<','&lt;', str_replace('"', '', $text));
}

# Util for partial unique filtering: titles similar, checked in faster manner than leventhin
function partial_unique($arr) {
	$newarr = [];
	foreach ( $arr as $element ) {
		# too short for comparison
		$thelen = strlen($element->title);
		if ( $thelen < 40 ) { $newarr[] = $element; continue; }
		# taking a part for comparison from the middle
		$start = (int)(($thelen - ($thelen/2)) / 2); # len:60 -> (60-(60/2))/2=15
		$thepart = substr($element->title, $start, (int)($thelen/2));
		# comparing to each title
		$aunique = true;
		foreach ( $newarr as $tocompare ) {
			if ( stripos($tocompare->title, $thepart) !== false ) {
				# found but it is expected for one time in every arr
				if ( $tocompare->title != $element->title ) {
					$aunique = false; #duplicated part, item won't be added to arr
				}
			}
		}
		if ( $aunique ) { $newarr[] = $element; }
	}
	return $newarr;
}

# Wrapper for the whole module
function ask($mode, $hashes) {
	if ( $mode['name'] == '1m' ) { write_log(''); } # breaking line between month feeds
	write_log('<u>Called ' . $mode['name'] . ' at ' . date('l jS \of F Y h:i:s A') . '</u>');
	global $categories;

	# download and sort posts
	$arrofcombineds = [];
	$ml1 = process_subreddit('ml', $mode['subtparameter'], $mode['mlnumber']); sleep(1);
	$ml2 = process_subreddit('ml', $mode['subtparameter'], $mode['mlnumberlast'], true); # more to survey recent, they are in random order
	sleep(1);
	$fu1 = process_subreddit('futurology', $mode['subtparameter'], $mode['funumber']); sleep(1);
	$fu2 = process_subreddit('futurology', $mode['subtparameter'], $mode['funumberlast'], true); # it's rare to have many recent on low upvotes
	sleep(1);
	$gn1 = process_gnews('ai', $mode['timelimit'], $mode['gnewsnumber'], $mode['supressscore']); sleep(1);
	$gn2 = process_gnews('audio+ai', $mode['timelimit'], $mode['gnewsnumber'], $mode['supressscore']); sleep(1);
	$gn3 = process_gnews('reinforcement+learning', $mode['timelimit'], $mode['gnewsnumber'], $mode['supressscore']); sleep(1);
	$blo = process_blogs($mode['timelimit'], $mode['blogsnumber'], $mode['supressscore']); sleep(1);
	$kar = process_karpathy($mode['timelimit'], $mode['karpathyparameter'], $mode['karpathynumber']); sleep(1);
	$pwc = process_pwc($mode['timelimit'], $mode['pwcparameter'], $mode['pwcnumber']); sleep(1);
	$dai = process_deepai($mode['timelimit'], 10); sleep(1);
	# other content
	$podcasts = process_podcasts(60*60*24*30.4, 32);
	usort($podcasts, "cmpitemstamp");
	$education = process_education(60*60*24*30.4, 32);
	usort($education, "cmpitemstamp");

	# error handling
	$shorttime = $mode['name'];
	if ( sizeof($ml1) < 1 && sizeof($fu1) < 1 ) { echo("Warning: Empty top reddit in {$shorttime}<br/>"); }
	if ( sizeof($ml2) < 1 && sizeof($fu2) < 1 ) { echo("Warning: Empty recent reddit in {$shorttime}<br/>"); }
	if ( sizeof($gn1) < 1 && sizeof($gn2) < 1 && sizeof($gn3) < 1 ) { echo("Warning: Empty google news in {$shorttime}<br/>"); }
	if ( sizeof($blo) < 1 ) { echo("Warning: Empty blog picks in {$shorttime}<br/>"); }
	if ( sizeof($kar) < 1 ) { echo("Warning: Empty karpathy sanity in {$shorttime}<br/>"); }
	if ( sizeof($pwc) < 1 ) { echo("Warning: Empty pwc in {$shorttime}<br/>"); }
	if ( sizeof($dai) < 1 ) { echo("Warning: Empty dai in {$shorttime}<br/>"); }

	# sorting strategy:
	# choose x best by internal score, then sort them by timestamp (or not)
	$combined = array_merge($ml1, $ml2, $fu1, $fu2, $gn1, $gn2, $gn3, $blo, $kar, $pwc, $dai);
	# uniqueness and standard sorting by internal score
	$combined = array_unique($combined);
	$combined = partial_unique($combined);
	if ( $mode['name'] == '1m' ) {
		# combinations for two sorts of the last month
		$unfilteredcombined = partial_unique($combined); # substitute for deepcopy really
		usort($unfilteredcombined, "cmpitem");
		$unfilteredcombined2 = partial_unique($combined); # substitute for deepcopy really
		usort($unfilteredcombined2, "cmpitemstamp");
	}
	# loop for filtering out duplicates between timeframes
	$filteredcombined = [];
	foreach ($combined as $comb) {
		$letthrough = true;
		foreach ($hashes as $onehash) {
			if ($comb->ahash == $onehash) { $letthrough = false; }
		}
		if ( $letthrough == true ) { $filteredcombined[] = $comb; }
	}
	$combined = $filteredcombined;
	usort($combined, "cmpitem");

	# create general stream
	$arrofcombineds[] = array_slice($combined, 0, $mode['cutoff']);
	$maxscore = 0;
	$sumofinternalscores = 0;
	foreach ( $arrofcombineds[0] as $anitem ) {
		$sumofinternalscores += $anitem->internalscore;
		if ( $anitem->internalscore > $maxscore ) { $maxscore = $anitem->internalscore; }
	}
	$averagescore = $sumofinternalscores/sizeof($arrofcombineds[0]);
	if ( $mode['name'] == '24h' ) {
		# dynamic cutoff, based on the sum of scores
		# typically, active on weekends to cut from 8 to 4 recent stories
		if ( $averagescore < 0.2 || sizeof($combined) < 8 ) {
			$arrofcombineds[0] = array_slice($combined, 0, 4);
		}
	}
	#if ( $mode['name'] != '24h' ) { usort($arrofcombineds[0], "cmpitemstamp"); }
	usort($arrofcombineds[0], "cmpitemstamp");

	# monitoring matters
	write_log(sizeof($combined) . ' unique items (' . (sizeof($ml1)+sizeof($ml2)+sizeof($fu1)+sizeof($fu2)) . ' reddit, ' . (sizeof($gn1)+sizeof($gn2)+sizeof($gn3)) . ' gnews, ' . sizeof($blo) . ' blogs, ' . (sizeof($kar)+sizeof($pwc)+sizeof($dai)) . ' papers)');
	if ( $mode['name'] == '1m' ) {
		# all should be present!
		if ( (sizeof($ml1)+sizeof($fu1)+sizeof($ml2)+sizeof($fu2)) < 1 ) { write_log("<b>Warning</b>: reddit fetch was null over month"); }
		if ( (sizeof($gn1)+sizeof($gn2)+sizeof($gn3)) < 1 ) { write_log("<b>Warning</b>: gnews fetch was null over month"); }
		if ( (sizeof($blo)) < 1 ) { write_log("<b>Warning</b>: blog fetch was null over month"); }
		if ( (sizeof($kar)) < 1 ) { write_log("<b>Warning</b>: arxiv-sanity fetch was null over month"); }
		if ( (sizeof($pwc)) < 1 ) { write_log("<b>Warning</b>: paperswithcode fetch was null over month"); }
		if ( (sizeof($dai)) < 1 ) { write_log("<b>Warning</b>: deepai fetch was null over month"); }
		if ( (sizeof($podcasts)) < 1 ) { write_log("<b>Warning</b>: podcast fetch was null over month"); }
		if ( (sizeof($education)) < 1 ) { write_log("<b>Warning</b>: education fetch was null over month"); }
	}
	write_log(round($maxscore,3) . ' top score, ' . round($averagescore,3) . ' average score');

	# create category substreams
	foreach ( $categories as $cat ) {
		# could use filter here but want to preserve flexibility with regards to categories
		$newcombined = [];
		foreach ( $combined as $apost ) {
			if ( $apost->category == $cat->shortname ) { $newcombined[] = $apost; }
		}
		#if ( $mode['name'] != '24h' ) { usort($newcombined, "cmpitemstamp"); }
		usort($newcombined, "cmpitemstamp");
		$arrofcombineds[] = array_slice($newcombined, 0, $mode['cutoff']);
	}

	# save posts to a file
	$iterator = 0;
	foreach ( $arrofcombineds as $acombined ) {
		# recognizing substream categories
		$first = true;
		$prefix = '';
		if ( $iterator > 0 ) { $prefix = $categories[$iterator-1]->shortname; }

		# special case of two sortings for standalone general streams
		$repeats = 1;
		if ( $iterator == 0 && $mode['name'] == '1m' ) {
			$repeats = 2;
			$prefix = 'top';
		}
		
		# formatting
		$i = 0;
		while( $i < $repeats ) {
			$arroftexts = [];
			$thecombined = $acombined;
			$i += 1;
			if ( $prefix == 'top' ) { $thecombined = $unfilteredcombined; }
			if ( $prefix == 'date' ) { $thecombined = $unfilteredcombined2; }
			foreach($thecombined as $apost) {
				$description = $apost->description . ' <span><i class="far fa-clock"></i>&nbsp;&nbsp;' . translate_timestamp($apost->timestamp, true) . '</span>';
				$cat = '';
				if ( strlen($apost->category) > 1 && $iterator == 0 ) { $cat = "<div class=\"img-preview-lefttext\">{$apost->category}</div>"; }
				$high = '';
				if ( strlen($apost->highlighttext) > 1 ) { $high = "<div class=\"img-preview-righttext\">{$apost->highlighttext}</div>"; }
				$t = cutquotes(tailortitle($apost->title));
				$t2 = cutquotes($apost->title);

				if (strlen($t) < 10) { continue; } # safety check

				$arroftexts[] = sanitizeforecho("<div class=\"outer-box-wrapper\"><a href=\"{$apost->link}\" title=\"{$t2}\"><div class=\"item video-box-wrapper\"><div class=\"img-preview\"><img src=\"{$apost->thumbnail}\" loading=\"lazy\" />{$cat}{$high}</div><div class=\"video-description-wrapper\"><p class=\"video-description-header\">{$t}</p><p class=\"video-description-subheader\">{$apost->source}</p><p class=\"video-description-info\">{$description}</p></div></div></a></div>");
			}

			# empty variant
			if (sizeof($arroftexts) == 0) { $arroftexts = ['Empty.']; }

			# save
			file_put_contents('./../' . $prefix . $mode['filename'], '<?php echo("' . implode(' ', $arroftexts) . '");');
			if ( $prefix == 'top' ) { $prefix = 'date'; }
		}
		$iterator += 1;
	}
	
	# other content substreams, violating DRY for now
	$arrofcombineds2 = [$podcasts, $education];
	$iterator = -1;
	foreach ( $arrofcombineds2 as $acombined ) {
		$iterator += 1;
		if ( $iterator == 0 ) { $filename = 'podcastsstream.php'; }
		else if ( $iterator == 1 ) { $filename = 'educationstream.php'; }

		$arroftexts = [];
		foreach($acombined as $apost) {
			$description = $apost->description . ' <span><i class="far fa-clock"></i>&nbsp;&nbsp;' . translate_timestamp($apost->timestamp, true) . '</span>';
			$cat = '';
			if ( strlen($apost->category) > 1 && $iterator == 0 ) { $cat = "<div class=\"img-preview-lefttext\">{$apost->category}</div>"; }
			$high = '';
			if ( strlen($apost->highlighttext) > 1 ) { $high = "<div class=\"img-preview-righttext\">{$apost->highlighttext}</div>"; }
			$t = cutquotes(tailortitle($apost->title));
			$t2 = cutquotes($apost->title);

			if (strlen($t) < 10) { continue; } # safety check

			$arroftexts[] = sanitizeforecho("<div class=\"outer-box-wrapper\"><a href=\"{$apost->link}\" title=\"{$t2}\"><div class=\"item video-box-wrapper\"><div class=\"img-preview\"><img src=\"{$apost->thumbnail}\" loading=\"lazy\" />{$cat}{$high}</div><div class=\"video-description-wrapper\"><p class=\"video-description-header\">{$t}</p><p class=\"video-description-subheader\">{$apost->source}</p><p class=\"video-description-info\">{$description}</p></div></div></a></div>");
		}

		# empty variant
		if (sizeof($arroftexts) == 0) { $arroftexts = ['Empty.']; }

		# save
		file_put_contents('./../' . $filename, '<?php echo("' . implode(' ', $arroftexts) . '");');
	}

	echo('Finished ' . $shorttime . ' at ' . date('l jS \of F Y h:i:s A') . '<br/>');

	# returning hashes for duplication purposes
	$hashestoreturn = [];
	foreach ($arrofcombineds as $singlearr) {
		foreach ($singlearr as $acomb) {
			$hashestoreturn[] = $acomb->ahash;
		}
	}
	return $hashestoreturn;
}

echo('Started at ' . date('l jS \of F Y h:i:s A') . '<br/>');
if ( $_GET['mode'] == 'recent' ) { ask($modes['24h'], []); }
else if ( $_GET['mode'] == 'full' ) {
	$hashes1 = ask($modes['24h'], []); # hashes for avoiding duplications between timeframes
	$hashes2 = ask($modes['7d'], $hashes1);
	ask($modes['1m'], array_merge($hashes1, $hashes2));
}
file_put_contents('./../hour.php', '<?php echo("' . gmdate('H:i') . ' UTC");');