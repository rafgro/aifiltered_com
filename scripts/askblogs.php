<?php

# Includes
include_once('./class_item.php');
include_once('./class_category.php');
include_once('./translate_timestamp.php');
include_once('./util_network.php');
include_once('./util_images.php');

# Domains allowed (as a dictionary), with corresponding base scores and names
$whitelistBL = ['syncedreview.com'=>[0.2,'Synced Review'], 'deepmind.com'=>[2.1,'DeepMind'], 'blogs.microsoft.com'=>[0.3,'Microsoft'], 'news.mit.edu'=>[0.5,'MIT'], 'blog.google'=>[0.6,'Google'], 'theconversation.com'=>[0.4,'The Conversation'], 'openai.com'=>[2.8,'OpenAI'], 'aiweirdness.com'=>[0.3,'AI Weirdness']];

# A subreddit analysis
function process_blogs($howold, $howmany, $supressscore) {
	global $whitelistBL;

	# download data
	$obj = simple_json_dl('http://www.rssmix.com/u/12037289/rss.xml', true);

	# collect posts to an array (it will be sorted then)
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
		if ( !isset($whitelistBL[$domain][0]) ) { continue; }

		# then take date and check if it's inside the timeline
		$timestamp = strtotime($obj->{'channel'}->{'item'}[$i]->{'pubDate'});
		if ( (time() - $timestamp) > $howold ) { continue; }

		# then check against whitelist for mit
		$title = (string) $obj->{'channel'}->{'item'}[$i]->{'title'};
		if ( $domain == 'news.mit.edu' ) {
			if ( stripos($title,' AI ') === false && stripos($title,'machine') === false ) {
				continue;
			}
		}

		# basic data
		$category = classify($title);
		$highlighttext = ''; # placeholder

		# internal stats
		# explanation: base score + bonus for classifiable category, times time score supression (lower weight of static items in the beginning)
		$internalscore = ($whitelistBL[$domain][0] + ((strlen($category)>1) ? 0.2 : 0)) * $supressscore;

		# thumbnail dealings
		$thumbnail = get_thumbnail($title, $link);

		# description stuff
		$description = '<i class="fas fa-desktop"></i>&nbsp;&nbsp;' . $whitelistBL[$domain][1] . ' &nbsp;';

		# the end
		$arrofposts[] = new Item(array( 'internalscore'=>$internalscore, 'timestamp'=>$timestamp, 'title'=>$title, 'link'=>$link, 'thumbnail'=>str_replace('../','',$thumbnail), 'source'=>'blog picks', 'category'=>$category, 'highlighttext'=>$highlighttext, 'description'=>$description ));

		if ( sizeof($arrofposts) >= $howmany ) { break; } # reached intended number of news
	}

	return $arrofposts;
}
