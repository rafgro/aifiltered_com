<?php

# Includes
include_once('./class_item.php');
include_once('./class_category.php');
include_once('./translate_timestamp.php');
include_once('./util_network.php');
include_once('./util_images.php');

# Getting data on twitter/arxiv indirectly from Karpathy's sanity
function process_karpathy($howold, $thetime, $howmany) {
	# download data
	$ch = curl_init('http://www.arxiv-sanity.com/toptwtr?timefilter=' . $thetime);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	if(!$json = curl_exec($ch)) {
	    trigger_error(curl_error($ch));
	    return [];
	}
	curl_close($ch);

	# two jsons, first stats on tweets, second data on pubs, assumed same order (!)
	$jsonstart = stripos($json, 'var tweets = ')+13;
	$jsonend = stripos($json, '}];', $jsonstart+20)+2;
	$obj = json_decode(substr($json, $jsonstart, $jsonend-$jsonstart));
	$jsonstart2 = stripos($json, 'var papers = ')+13;
	$jsonend2 = stripos($json, '}];', $jsonstart2+20)+2;
	$obj2 = json_decode(substr($json, $jsonstart2, $jsonend2-$jsonstart2));

	# collect posts to an array (it will be sorted then)
	#var_dump($obj[0]->{'pid'});
	#var_dump($obj[0]->{'num_tweets'});
	#var_dump($obj2[0]->{'pid'});
	#var_dump($obj2[0]->{'title'});
	$arrofposts = [];
	$i = -1;
	while ( $i < 99 ) { # there are 100 items in the feed
		$i += 1;

		# check for existence
		if ( !isset($obj[$i]) && !isset($obj2[$i]) ) {
			break;
		}

		# first take date and check if it's inside the timeline
		$timestamp = strtotime($obj2[$i]->{'originally_published_time'}) + 60*60*16; # utc/nonutc correction +16h
		if ( (time() - $timestamp) > $howold ) { continue; }

		# then check minimal req for number of tweets
		$tweets = $obj[$i]->{'num_tweets'};
		if ( $tweets < 5 ) { continue; }
		$highlighttext = '';
		if ( $tweets > 515 ) { $highlighttext = 'top of twitter'; }
		else if ( $tweets > 68 ) { $highlighttext = 'popular on twitter'; }

		# basic data
		$title = (string) $obj2[$i]->{'title'};
		$link = (string) $obj2[$i]->{'link'};
		$category = classify($title);
		# additional classification
		if ( $obj2[$i]->{'category'} == 'cs.CV' ) { $category = 'CV'; }
		else if ( $obj2[$i]->{'category'} == 'cs.CL' ) { $category = 'NLP'; }
		else if ( $obj2[$i]->{'category'} == 'cs.SD' ) { $category = 'Audio'; }

		# internal stats
		# explanation: tweets calculated as upvotes from reddit
		$internalscore = min($tweets,68*2) / 68;

		# thumbnail dealings
		try {
			$original_thumbnail = 'http://www.arxiv-sanity.com' . $obj2[$i]->{'img'};
			# downloading if not already downloaded
			$endofdomain = strrpos($original_thumbnail, '/')+1;
			$thumbnail = './../thumbs/' . substr($original_thumbnail, $endofdomain);
			if ( !file_exists($thumbnail) ) {
				$ch2 = curl_init($original_thumbnail);
				$fp = fopen($thumbnail, "w");
				curl_setopt($ch2, CURLOPT_FILE, $fp);
				curl_setopt($ch2, CURLOPT_HEADER, 0);
				curl_exec($ch2);
				curl_close($ch2);
				fclose($fp);
				# usually are ~1000x200, we cut out 370x200
				cut_image_w($thumbnail, 370); # 370 px of width, height preserved
			}
		} catch (Exception $e) {
			# no provided thumbnail
			$thumbnail = './../thumbs/default.jpg';
		}

		# description stuff
		$description = '<i class="fab fa-twitter"></i>&nbsp;&nbsp;' . $tweets . ' tweets &nbsp; <span>arxiv sanity&nbsp;</span>';

		# the end
		$arrofposts[] = new Item(array( 'internalscore'=>$internalscore, 'timestamp'=>$timestamp, 'title'=>$title, 'link'=>$link, 'thumbnail'=>str_replace('../','',$thumbnail), 'source'=>'arxiv', 'category'=>$category, 'highlighttext'=>$highlighttext, 'description'=>$description ));

		if ( sizeof($arrofposts) >= $howmany ) { break; } # reached intended number of news
	}

	return $arrofposts;
}

#var_dump(process_karpathy(60*60*24*2, 'day', 5));
