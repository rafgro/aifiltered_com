<?php

# Includes
include_once('./class_item.php');
include_once('./class_category.php');
include_once('./translate_timestamp.php');
include_once('./util_network.php');
include_once('./util_images.php');

# A subreddit analysis
function process_podcasts($howold, $howmany) {
	# download data
	$obj = simple_json_dl('http://www.rssmix.com/u/12088075/rss.xml', true);

	# collect posts to an array (it will be sorted then)
	$arrofposts = [];
	$i = -1;
	while ( $i < 99 ) { # there are 100 items in the feed
		$i += 1;

		# check for existence
		if ( !isset($obj->{'channel'}->{'item'}[$i]) ) {
			break;
		}

		# first take date and check if it's inside the timeline
		$timestamp = strtotime($obj->{'channel'}->{'item'}[$i]->{'pubDate'});
		if ( (time() - $timestamp) > $howold ) { continue; }

		# basic data
        $link = (string) $obj->{'channel'}->{'item'}[$i]->{'link'};
        $dc = $obj->{'channel'}->{'item'}[$i]->children('http://purl.org/dc/elements/1.1/');
        if ( isset($dc->creator) ) {
            $thename = $dc->creator;
        } else {
            if ( stripos($link,'aitalk.podbean.com') !== false ) { $thename = "Let's Talk AI"; }
            else if ( stripos($link,'twimlai.com') !== false ) { $thename = "TWIML"; }
        }
		$title = (string) $obj->{'channel'}->{'item'}[$i]->{'title'};
		$category = classify($title);
		$highlighttext = ''; # placeholder
        $internalscore = 0.1; # placeholder
        
        # filtering fridman
        if ( stripos($thename,'Fridman') !== false ) {
            if ( stripos($title,'podcast') === false ) {
                continue;
            }
        }

		# thumbnail dealings
		$thumbnail = get_thumbnail($title, $link);

		# description stuff
		$description = '<i class="fas fa-microphone-alt"></i>&nbsp;&nbsp;' . $thename . ' &nbsp;';

		# the end
		$arrofposts[] = new Item(array( 'internalscore'=>$internalscore, 'timestamp'=>$timestamp, 'title'=>$title, 'link'=>$link, 'thumbnail'=>str_replace('../','',$thumbnail), 'source'=>'podcasts', 'category'=>$category, 'highlighttext'=>$highlighttext, 'description'=>$description ));

		if ( sizeof($arrofposts) >= $howmany ) { break; } # reached intended number of news
	}

	return $arrofposts;
}
