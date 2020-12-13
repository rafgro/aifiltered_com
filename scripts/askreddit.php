<?php

# Includes
include_once('./class_item.php');
include_once('./class_category.php');
include_once('./translate_timestamp.php');
include_once('./util_network.php');
include_once('./util_images.php');

# Arrays of data for analyses of specific subreddits
$subreddits = [
	'ml'=>['url'=>'https://www.reddit.com/r/MachineLearning/top/.json?limit=20',
    'record'=>6422, 'top10'=>1760, 'top50'=>912, 'top100'=>658, 'top250'=>450, 'top500'=>320, 'top1000'=>216,
    'manycomments'=>76, 'avgupvote'=>425, 'commbareer'=>33, 'source'=>'r/machinelearning', 'shortsource'=>'r/ML',
	'minimalupvote'=>10, 'fastrisingperhour'=>10, 'hugediscussion'=>200],
	'futurology'=>['url'=>'https://www.reddit.com/r/Futurology/search.json?q=ai&restrict_sr=1&sort=top&limit=10',
    'record'=>147000, 'top10'=>81200, 'top50'=>59900, 'top100'=>49000, 'top250'=>36500, 'top500'=>27300, 'top1000'=>19000,
    'manycomments'=>968, 'avgupvote'=>3000, 'commbareer'=>416, 'source'=>'r/futurology', 'shortsource'=>'r/Futurology',
	'minimalupvote'=>200, 'fastrisingperhour'=>50, 'hugediscussion'=>3000]
];

# A subreddit analysis
function process_subreddit($subname, $scale, $howmany, $topswitch=false) {
	global $arrofdefs;
	global $subreddits;
	$sub = $subreddits[$subname];
	# download data
	if ( $topswitch == false ) { $obj = simple_json_dl($sub['url'] . $scale); }
	else { $obj = simple_json_dl(str_replace('top','new',$sub['url']) . $scale); } # workaround for last few hours of posts

	# collect posts to an array (it will be sorted then)
	$arrofposts = [];
	$i = -1;
	while ( $i < $howmany-1 ) {
		$i += 1;

		# check for existence
		if ( !isset($obj->{'data'}->{'children'}[$i]) ) {
			break;
		}

		# analyze basic data
		$highlight = false;
		$highlighttext = '';
		$upvotes = $obj->{'data'}->{'children'}[$i]->{'data'}->{'ups'};
		if ( $upvotes < $sub['minimalupvote'] ) { continue; } # filtering out noise
		$upvoteflair = '';
		if ( $upvotes > $sub['record'] ) { $upvoteflair = 'record'; $highlight = true; }
		else if ( $upvotes > $sub['top10'] ) { $upvoteflair = 'top10'; $highlight = true; }
		else if ( $upvotes > $sub['top50'] ) { $upvoteflair = 'top50'; $highlight = true; }
		else if ( $upvotes > $sub['top100'] ) { $upvoteflair = 'top100'; $highlight = true; }
		else if ( $upvotes > $sub['top250'] ) { $upvoteflair = 'top250'; $highlight = true; }
		else if ( $upvotes > $sub['top500'] ) { $upvoteflair = 'top500'; }
		#else if ( $upvotes > $sub['top1000'] ) { $upvoteflair = 'top1000'; }
		$comments = $obj->{'data'}->{'children'}[$i]->{'data'}->{'num_comments'};
		$commentflair = '';
		if ( $comments > $sub['manycomments'] ) { $commentflair = 'active discussion'; $highlight = true; }
		if ( $comments > $sub['hugediscussion'] ) { $commentflair = 'huge discussion'; $highlight = true; }
		$title = $obj->{'data'}->{'children'}[$i]->{'data'}->{'title'};
		$category = classify($title);
		$link = 'https://www.reddit.com' . $obj->{'data'}->{'children'}[$i]->{'data'}->{'permalink'};
		$timestamp = $obj->{'data'}->{'children'}[$i]->{'data'}->{'created'} - 60*60*7;
		$fastrisingmodifier = 1;

		# highlighting
		$diffinh = (time()-$timestamp)/3600;
		if ( strlen($upvoteflair) > 1 ) { $highlighttext = $upvoteflair . ' of ' . $sub['shortsource']; }
		else if ( strlen($commentflair) > 1 ) { $highlighttext = $commentflair; }
		else if ( $diffinh <= 24 && ($upvotes/$diffinh) >= $sub['fastrisingperhour'] ) { $highlighttext = 'fast rising'; }

		# internal stats
		# explanation: weights, upvotes and comments normalized by top cutoff and avgs of top1000 posts
		$internalscore = 0.5 * (min($upvotes,$sub['avgupvote']*2)/$sub['avgupvote'])
		    + 0.5 * (min($comments,$sub['manycomments']*3)/$sub['manycomments']);

		# thumbnail dealings
		/*if ( $obj->{'data'}->{'children'}[$i]->{'data'}->{'thumbnail'} != 'self' && # no thumbnail = no meaningful preview
		  isset($obj->{'data'}->{'children'}[$i]->{'data'}->{'preview'}) ) { # no image = no preview property*/
		if ( isset($obj->{'data'}->{'children'}[$i]->{'data'}->{'preview'})
		  && isset($obj->{'data'}->{'children'}[$i]->{'data'}->{'preview'}->{'images'}[0]->{'resolutions'}[2]) ) {
			# getting url of 320x180px thumbnail
			$original_thumbnail = str_replace('&amp;', '&', 
				$obj->{'data'}->{'children'}[$i]->{'data'}->{'preview'}->{'images'}[0]->{'resolutions'}[2]->{'url'});
			# downloading if not already downloaded
			$endofdomain = strpos($original_thumbnail, 'dd.it/')+6;
			$endoffilename = strpos($original_thumbnail, '?width=');
			$thumbnail = './../thumbs/' . substr($original_thumbnail, $endofdomain, $endoffilename-$endofdomain);
			if ( !file_exists($thumbnail) ) {
				$ch2 = curl_init($original_thumbnail);
				$fp = fopen($thumbnail, "w");
				curl_setopt($ch2, CURLOPT_FILE, $fp);
				curl_setopt($ch2, CURLOPT_HEADER, 0);
				curl_exec($ch2);
				curl_close($ch2);
				fclose($fp);
			}
		}
		else {
			# no provided thumbnail
			# simple mechanism of consistent choice from 4 thumbnails
			$aletter = strtolower(substr($title,5,1)); # sixth letter
			$def = 1;
			if ( isset($arrofdefs[$aletter]) ) { $def = $arrofdefs[$aletter]; }
			$thumbnail = './../thumbs/default'.$def.'.jpg';
		}

		# check out top comment(s)
		# currently unused to limit reddit queries
		/*$objpost = simple_json_dl($link . '.json');
		$commarr = [];
		$j = -1;
		while( $j < 5 ) {
			$j += 1;
			if ( !isset($objpost[1]->{'data'}->{'children'}[$j]) ) { break; } # in case of no comments
			$commscore = $objpost[1]->{'data'}->{'children'}[$j]->{'data'}->{'score'};
			$commcontent = $objpost[1]->{'data'}->{'children'}[$j]->{'data'}->{'body'};
			# bareer for interesting, highly upvoted comments
			if ( $commscore > $sub['commbareer'] ) {
				$commarr[] = array('score'=>$commscore, 'content'=>$commcontent);
			}
		}
		usort($commarr, "cmpcomm");*/

		# description stuff
		$description = '<i class="fas fa-long-arrow-alt-up"></i>&nbsp;&nbsp;' . shortennumber($upvotes) . ' upvotes&nbsp; <span><i class="fas fa-comment"></i>&nbsp;&nbsp;' . shortennumber($comments) . ' comments&nbsp;</span>';

		# the end
		$arrofposts[] = new Item(array( 'internalscore'=>$internalscore, 'timestamp'=>$timestamp, 'title'=>$title, 'link'=>$link, 'thumbnail'=>str_replace('../','',$thumbnail), 'source'=>$sub['source'], 'category'=>$category, 'highlighttext'=>$highlighttext, 'description'=>$description ));
	}

	return $arrofposts;
}
