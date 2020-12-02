<?php

# Includes
include_once('./class_item.php');
include_once('./class_category.php');
include_once('./translate_timestamp.php');
include_once('./util_images.php');

# Papers with code download
function process_deepai($howold, $howmany) {
    global $arrofdefs;

	# download data
	$ch = curl_init('https://deepai.org/research');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if(!$text = curl_exec($ch)) {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);
    
    # 10 items, each beginning with <li id="x" objectclass="publication"
    preg_match_all('/(objectclass="pub)/', $text, $matches, PREG_OFFSET_CAPTURE);

	# collect posts to an array (it will be sorted then)
    $arrofposts = [];
    # $matches[0][x][1] has positions of all item starts
    for ( $i=0; $i < 10; $i++ ) {
        # date: <span isodate="
        # title and link: <h3 class="title">
        # thumbnail: <div class="thumbnail" style="background-image: url(' 
        # twitter favs: <span class="heart-count">
        $start = $matches[0][$i][1];
        if ( $i < 9 ) { $end = $matches[0][$i+1][1]; }
        else { $end = strlen($text); }
        $cutout = substr($text, $start, $end-$start);

        # date first, if not present then skip
        $wheredate = stripos($cutout, '<span isodate="');
        $lastsign = '" c';
        if ( $wheredate !== false ) { $wheredate += 15; }
        if ( $wheredate === false ) { continue; }
        $date = substr($cutout, $wheredate, stripos($cutout,$lastsign,$wheredate)-$wheredate);
        if ( strlen($date) < 15 || strlen($date) > 35 ) { continue; }

		# then take date and check if it's inside the timeline
		$timestamp = strtotime($date);
		if ( (time() - $timestamp) > $howold ) { continue; }

        # title and link next
        $whereh1 = stripos($cutout, '<h3 class="title">');
        if ( $whereh1 === false ) { continue; }
        $h1 = substr($cutout, $whereh1+18, stripos($cutout,'</h3>',$whereh1)-($whereh1+18));
        $title = trim(strip_tags($h1));
        $wherelink = stripos($h1, 'href="');
        $prefix = '';
        if ( stripos($h1,'f="/') !== false ) { $prefix = 'https://deepai.org'; }
        $link = $prefix . substr($h1, $wherelink+6, stripos($h1,'"',$wherelink+10)-($wherelink+6));
        
        # cat
        $category = classify($title);

        # thumbnail
        $wherethumb = stripos($cutout, "background-image: url(' ");
        try {
            if ( $wherethumb !== false ) {
                $thumbnail = './../thumbs/' . hash('md5', $title) . '.jpg';
                if ( !file_exists($thumbnail) ) {
                    # get link
                    $thumblink = substr($cutout, $wherethumb+24, stripos($cutout," '",$wherethumb+30)-($wherethumb+24));
                    if ( stripos($thumblink,'.jpg') === false ) { throw new Exception("no jpg thumbnail"); }
                    # download thumb
                    $ch2 = curl_init($thumblink);
                    $fp = fopen($thumbnail, "w");
                    curl_setopt($ch2, CURLOPT_FILE, $fp);
                    curl_setopt($ch2, CURLOPT_HEADER, 0);
                    curl_exec($ch2);
                    curl_close($ch2);
                    fclose($fp);
                    # usually are ~300x420/250x370, we cut out 300x230/250x230
                    cut_image_h($thumbnail, 230); # 370 px of height, width preserved
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

        # favs
        $favs = 0;
        $wherefavs = stripos($cutout, '<span class="heart-count">');
        if ( $wherefavs !== false ) {
            $favs = (int) (substr($cutout, $wherefavs+26, stripos($cutout,'</span>',$wherefavs+27)-($wherefavs+26)));
        }
        if ( $favs < 20 ) { continue; } # minimal no of favs
        
        # internal descriptions
		$highlighttext = '';
		if ( $favs > 200 ) { $highlighttext = 'top of DeepAI\'s trending'; $highlight = true; }
		$internalscore = (min($favs,80*3)/80) + 0.1;
        $description = '<i class="far fa-heart"></i>&nbsp;&nbsp;' . $favs . ' favorites at Twitter&nbsp; <span>deepai.org&nbsp;</span>';
        
		# the end
		$arrofposts[] = new Item(array( 'internalscore'=>$internalscore, 'timestamp'=>$timestamp, 'title'=>$title, 'link'=>$link, 'thumbnail'=>str_replace('../','',$thumbnail), 'source'=>'arxiv', 'category'=>$category, 'highlighttext'=>$highlighttext, 'description'=>$description ));

		if ( sizeof($arrofposts) >= $howmany ) { break; } # reached intended number of news
    }

	return $arrofposts;
}
