<?php

# Includes
include_once('./class_item.php');
include_once('./class_category.php');
include_once('./translate_timestamp.php');
include_once('./util_images.php');

# Papers with code download
function process_pwc($howold, $parameter, $howmany) {
    global $arrofdefs;

	# download data
	$ch = curl_init('https://paperswithcode.com/' . $parameter);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if(!$text = curl_exec($ch)) {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);
    
    # 10 items, each beginning with <div class="col-lg-3 item-image-col">
    preg_match_all('/(col-lg-3 item-image-col)/', $text, $matches, PREG_OFFSET_CAPTURE);

	# collect posts to an array (it will be sorted then)
    $arrofposts = [];
    # $matches[0][x][1] has positions of all item starts
    for ( $i=0; $i < 10; $i++ ) {
        # date: author-name-text"> or ars-accumulated text-center">
        # title and link: <h1>
        # thumbnail: background-image: url('
        # github stars: star"></ion-icon> 
        $start = $matches[0][$i][1];
        if ( $i < 9 ) { $end = $matches[0][$i+1][1]; }
        else { $end = strlen($text); }
        $cutout = substr($text, $start, $end-$start);

        # date first, if not present then skip
        $wheredate = stripos($cutout, 'author-name-text">');
        $lastsign = '</span>';
        if ( $wheredate !== false ) { $wheredate += 18; }
        if ( $wheredate === false ) { # second try
            $wheredate = stripos($cutout, 'ars-accumulated text-center">');
            if ( $wheredate !== false ) { $wheredate += 50; $lastsign = "\n"; }
        }
        if ( $wheredate === false ) { continue; }
        $date = substr($cutout, $wheredate, stripos($cutout,$lastsign,$wheredate)-$wheredate);
        if ( strlen($date) < 8 || strlen($date) > 15 ) { continue; }

		# then take date and check if it's inside the timeline
		$timestamp = strtotime($date) + 60*60*16;
		if ( (time() - $timestamp) > $howold ) { continue; }
		if ( (time() - $timestamp) < (-60*60*12) ) { continue; } # date before today

        # title and link next
        $whereh1 = stripos($cutout, '<h1>');
        if ( $whereh1 === false ) { continue; }
        $h1 = substr($cutout, $whereh1+4, stripos($cutout,'</h1>',$whereh1)-($whereh1+4));
        $title = strip_tags($h1);
        $title = str_replace('[email&#160;protected]','',$title);
        $wherelink = stripos($h1, 'href="');
        $prefix = '';
        if ( stripos($h1,'f="/') !== false ) { $prefix = 'https://paperswithcode.com'; }
        $link = $prefix . substr($h1, $wherelink+6, stripos($h1,'"',$wherelink+10)-($wherelink+6));
        
        # cat
        $category = classify($title);

        # thumbnail
        $wherethumb = stripos($cutout, "background-image: url('");
        try {
            if ( $wherethumb !== false ) {
                $thumbnail = './../thumbs/' . hash('md5', $title) . '.jpg';
                if ( !file_exists($thumbnail) ) {
                    # get link
                    $thumblink = 'https://paperswithcode.com' . 
                        substr($cutout, $wherethumb+23, stripos($cutout,"'",$wherethumb+30)-($wherethumb+23));
                    # download thumb
                    $downloadedthumb = './../thumbs/' . substr($thumblink, strrpos($thumblink,'/')+1);
                    $ch2 = curl_init($thumblink);
                    $fp = fopen($downloadedthumb, "w");
                    curl_setopt($ch2, CURLOPT_FILE, $fp);
                    curl_setopt($ch2, CURLOPT_HEADER, 0);
                    curl_exec($ch2);
                    curl_close($ch2);
                    fclose($fp);
                    # resize
                    $ifnoerr = resize_image_h($downloadedthumb, $thumbnail, 300); # 300 px of height, we don't need more
                    if ( $ifnoerr == false ) { throw new Exception("image processing error"); }
                    # clean
                    unlink($downloadedthumb);
                }
            } else { throw new Exception("no thumbnail provided"); }

        } catch (Exception $e) {
              # no provided thumbnail
              # simple mechanism of consistent choice from 4 thumbnails
              $aletter = strtolower(substr($title,5,1)); # sixth letter
              $def = 1;
              if ( isset($arrofdefs[$aletter]) ) { $def = $arrofdefs[$aletter]; }
              $thumbnail = './../thumbs/default'.$def.'.jpg';
        }

        # stars
        $stars = 0;
        $wherestars = stripos($cutout, 'star"></ion-icon> ');
        if ( $wherestars !== false ) {
            $stars = (int) (substr($cutout, $wherestars+18, stripos($cutout,'</span>',$wherestars+17)-($wherestars+18)));
        }
        if ( $stars < 2 ) { continue; } # minimal no of stars
        
        # internal descriptions
		$highlighttext = '';
		if ( $stars > 148000 ) { $highlighttext = 'record of github'; $highlight = true; }
		else if ( $stars > 31000 ) { $highlighttext = 'top10 of github'; $highlight = true; }
		else if ( $stars > 12000 ) { $highlighttext = 'top50 of github'; $highlight = true; }
		else if ( $stars > 2000 ) { $highlighttext = 'top of PWC\'s trending'; $highlight = true; }
		$internalscore = (min($stars,200*3)/200) + 0.1;
        $description = '<i class="fas fa-star"></i>&nbsp;&nbsp;' . $stars . ' stars on github&nbsp; <span>papers with code&nbsp;</span>';
        
		# the end
		$arrofposts[] = new Item(array( 'internalscore'=>$internalscore, 'timestamp'=>$timestamp, 'title'=>$title, 'link'=>$link, 'thumbnail'=>str_replace('../','',$thumbnail), 'source'=>'arxiv', 'category'=>$category, 'highlighttext'=>$highlighttext, 'description'=>$description ));

		if ( sizeof($arrofposts) >= $howmany ) { break; } # reached intended number of news
    }

	return $arrofposts;
}
