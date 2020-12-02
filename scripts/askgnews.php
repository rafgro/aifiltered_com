<?php

# Includes
include_once('./class_item.php');
include_once('./class_category.php');
include_once('./translate_timestamp.php');
include_once('./util_network.php');
include_once('./util_images.php');

# Domains allowed (as a dictionary), with corresponding base scores and names
$whitelistGN = ['zdnet.com'=>[0.1,'ZDNet'], 'technologyreview.com'=>[0.8,'MIT Tech Review'], 'techcrunch.com'=>[0.1,'Tech Crunch'], 'theverge.com'=>[0.3,'The Verge'], 'wired.com'=>[0.6,'Wired'], 'inverse.com'=>[0.5,'Inverse'], 'geekwire.com'=>[0.1,'Geekwire'], 'vox.com'=>[0.7,'Vox'], 'cnn.com'=>[0.2,'CNN'], 'economist.com'=>[0.8,'The Economist'], 'fastcompany.com'=>[0.5,'Fast Company'], 'techxplore.com'=>[0.3,'Tech Xplore'], 'therobotreport.com'=>[0.4,'Robot Report'], 'voicebot.ai'=>[0.2,'Voicebot'], 'futurism.com'=>[0.1,'Futurism'], 'spectrum.ieee.org'=>[0.5,'IEEE Spectrum'], 'bbc.com'=>[0.5,'BBC'], 'infoq.com'=>[0.2,'InfoQ']];

# Keywords that suggest general news slipping in
$graylistGN = ['trump', 'politic', 'lgbt', 'blm', 'china'];

# Keyword that are individually blocked, for control of implicit ads etc
$blacklistGN = ['disrupt', 'overcrowding', 'salesforce', 'sales', 'deals', 'java', ' adds ', 'sonos', 'willo', 'alexa', 'audioburst', 'curio'];
# reasons: ' adds ' for uninteresting stories from voicebot
#          alexa is usually very vaguely connected to ai (mostly not at all)

# A subreddit analysis
function process_gnews($term, $howold, $howmany, $supressscore) {
	global $whitelistGN;
	global $graylistGN;
	global $blacklistGN;

	# download data
	$obj = simple_json_dl('https://news.google.com/rss/search?q='.$term.'&hl=en-US&gl=US&ceid=US:en', true);

	# collect posts to an array (it will be sorted then)
	#var_dump($obj->{'channel'}->{'item'}[0]);
	$arrofposts = [];
	$i = -1;
	while ( $i < 99 ) { # there are 100 items in the feed
		$i += 1;

		# check for existence
		if ( !isset($obj->{'channel'}->{'item'}[$i]) ) {
			break;
		}

		# first take link and check if it's in the whitelist
		$link = (string) $obj->{'channel'}->{'item'}[$i]->{'link'};
		$domain = str_replace('www.', '', parse_url($link, PHP_URL_HOST));
		if ( !isset($whitelistGN[$domain][0]) ) { continue; }

		# then take date and check if it's inside the timeline
		$timestamp = strtotime($obj->{'channel'}->{'item'}[$i]->{'pubDate'});
		if ( (time() - $timestamp) > $howold ) { continue; }

		# basic data
		$title = (string) $obj->{'channel'}->{'item'}[$i]->{'title'};
		$category = classify($title);
		$highlighttext = ''; # placeholder

		# consulting blacklist
		$backoff = false;
		foreach( $blacklistGN as $b ) { if ( stripos($title,$b) !== false ) { $backoff = true; break; } }
		if ( $backoff == true ) { continue; }
		
		# other stats
		$rssheight = 1.2 * ((101-min($i,51))/101); # higher = more, lower = less, following google's relevancy scoring

		# internal stats
		# explanation: base score + bonus for classifiable category + 0.3 founding correction, times time score supression (lower weight of static items) and times height in rss
		$internalscore = ($whitelistGN[$domain][0] + ((strlen($category)>1) ? 0.5 : 0) + 0.3) * $supressscore * $rssheight;

		# thumbnail dealings
		$thumbnail = get_thumbnail($title, $link);

		# description stuff
		$description = '<i class="far fa-newspaper"></i>&nbsp;&nbsp;' . $whitelistGN[$domain][1] . ' &nbsp;';

		# the end
		$arrofposts[] = new Item(array( 'internalscore'=>$internalscore, 'timestamp'=>$timestamp, 'title'=>$title, 'link'=>$link, 'thumbnail'=>str_replace('../','',$thumbnail), 'source'=>'google news', 'category'=>$category, 'highlighttext'=>$highlighttext, 'description'=>$description ));

		if ( sizeof($arrofposts) >= $howmany ) { break; } # reached intended number of news
	}

	# consulting graylist
	$suspicious = false;
	foreach ( $arrofposts as $singlepost ) {
		foreach( $graylistGN as $b ) {
			if ( stripos($singlepost->title,$b) !== false ) {
				$suspicious = true;
				echo("Suspicion of general news: " . $singlepost->title . "<br/>");
			}
		}
	}

	if ( $suspicious == true ) { return []; }
	return $arrofposts;
}
