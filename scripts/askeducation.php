<?php

# Includes
include_once('./class_item.php');
include_once('./class_category.php');
include_once('./translate_timestamp.php');
include_once('./util_network.php');
include_once('./util_images.php');

# Domains allowed (as a dictionary), with corresponding base scores and names
$whitelistED = ['kdnuggets.com'=>['KDnuggets'], 'machinelearningmastery.com'=>['ML Mastery'], 'jalammar.github.io'=>['Jay Alammar'], 'thegradient.pub'=>['The Gradient'], 'distill.pub'=>['Distill']];

# Kdnuggets filtering (case insesitive keywords - if detected, the article is let through)
$kdnuggetsfilter = ['TensorFlow', 'PyTorch', ' get', 'build', 'dive', 'introduction', 'how ', 'ranking', 'machine learning'];
$kdnuggetsreject = ['data scien', 'top stories'];

# A subreddit analysis
function process_education($howold, $howmany) {
	global $whitelistED;
	global $kdnuggetsfilter;
	global $kdnuggetsreject;

	# download data
	$obj = simple_json_dl('http://www.rssmix.com/u/12089288/rss.xml', true);

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
		if ( !isset($whitelistED[$domain][0]) ) { continue; }

		# then take date and check if it's inside the timeline
		$timestamp = strtotime($obj->{'channel'}->{'item'}[$i]->{'pubDate'});
        if ( (time() - $timestamp) > $howold ) { continue; }
        
        # then for kdnuggets check if have certain keywords
		$title = (string) $obj->{'channel'}->{'item'}[$i]->{'title'};
		if ( $domain == 'kdnuggets.com' ) {
			$letthrough = false;
			foreach ( $kdnuggetsfilter as $aword ) {
				if ( stripos($title, $aword) !== false ) { $letthrough = true; break; }
			}
			if ( $letthrough == false ) {
				continue;
			}
			foreach ( $kdnuggetsreject as $aword ) {
				if ( stripos($title, $aword) !== false ) { $letthrough = false; break; }
			}
			if ( $letthrough == false ) {
				continue;
			}
		}

		# basic data
		$category = classify($title);
		$highlighttext = ''; # placeholder
		$internalscore = 0; # placeholder

		# thumbnail dealings
		$thumbnail = get_thumbnail($title, $link);

		# description stuff
		$description = '<i class="fab fa-leanpub"></i>&nbsp;&nbsp;' . $whitelistED[$domain][0] . ' &nbsp;';

		# the end
		$arrofposts[] = new Item(array( 'internalscore'=>$internalscore, 'timestamp'=>$timestamp, 'title'=>$title, 'link'=>$link, 'thumbnail'=>str_replace('../','',$thumbnail), 'source'=>'education picks', 'category'=>$category, 'highlighttext'=>$highlighttext, 'description'=>$description ));

		if ( sizeof($arrofposts) >= $howmany ) { break; } # reached intended number of news
	}

	return $arrofposts;
}
