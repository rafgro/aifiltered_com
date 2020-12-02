<?php

# Proportional resizing of an image to specific height
function resize_image_h($file, $newfile, $h) {
    try { 
        list($width, $height) = getimagesize($file);
        if ( $width == 0 || $height == 0 ) { return false; }
        $r = $width / $height;
        $newwidth = $h*$r;
        $newheight = $h;
        if (filesize($file) > 2000000) { return false; } # allow images smaller than 2mb only
        if (stripos($file, '.gif') > 1) { $src = imagecreatefromgif($file); }
        else if (stripos($file, '.jpg') > 1 || stripos($file, '.jpeg') > 1) { $src = imagecreatefromjpeg($file); }
        else if (stripos($file, '.png') > 1) { $src = imagecreatefrompng($file); }
        else { return false; }
        $dst = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        imagejpeg($dst, $newfile);
        imagedestroy($src);
        imagedestroy($dst);
        return true;
    } catch( Exception $e ) {
        return false;
    }
}

# Cutting out specific width of an image
function cut_image_w($file, $w) {
    list($width, $height) = getimagesize($file);
    if (stripos($file, '.gif') > 1) { $src = imagecreatefromjpeg($gif); }
    else if (stripos($file, '.jpg') > 1 || stripos($file, '.jpeg') > 1) { $src = imagecreatefromjpeg($file); }
    else if (stripos($file, '.png') > 1) { $src = imagecreatefrompng($file); }
    $dst = imagecrop($src, ['x' => 0, 'y' => 0, 'width' => $w, 'height' => $height]);
    if ($dst !== false) {
        if (stripos($file, '.jpg') > 1) { imagejpeg($dst, $file); }
        else if (stripos($file, '.png') > 1) { imagepng($dst, $file); }
    }
    imagedestroy($src);
    imagedestroy($dst);
}

# Cutting out specific height of an image
function cut_image_h($file, $h) {
    list($width, $height) = getimagesize($file);
    if (stripos($file, '.gif') > 1) { $src = imagecreatefromjpeg($gif); }
    else if (stripos($file, '.jpg') > 1 || stripos($file, '.jpeg') > 1) { $src = imagecreatefromjpeg($file); }
    else if (stripos($file, '.png') > 1) { $src = imagecreatefrompng($file); }
    $dst = imagecrop($src, ['x' => 0, 'y' => 0, 'width' => $width, 'height' => $h]);
    if ($dst !== false) {
        if (stripos($file, '.jpg') > 1) { imagejpeg($dst, $file); }
        else if (stripos($file, '.png') > 1) { imagepng($dst, $file); }
    }
    imagedestroy($src);
    imagedestroy($dst);
}

# An array for default image numbering
$arrofdefs = [
    'q'=>1, 'w'=>1, 'e'=>1, 'r'=>1, 't'=>2, 'y'=>2, 'u'=>2, 'i'=>2, 'o'=>2, 'p'=>2, 'a'=>2, 's'=>3, 'd'=>3, 'f'=>3, 'g'=>3, 'h'=>3, 'j'=>3, 'k'=>3, 'l'=>3, 'z'=>4, 'x'=>4, 'c'=>4, 'v'=>4, 'b'=>4, 'n'=>4, 'm'=>4, ' '=>4
];

# Thumbnail checking and extraction
function get_thumbnail($title, $link) {
    global $arrofdefs;
    try {
        # hash title and check if we're fine
        $thumbnail = './../thumbs/' . hash('md5', $title) . '.jpg';
        if ( file_exists($thumbnail) ) {
            return $thumbnail;
        }
    
        # if got past this, then we have to download it

        # download original website
        $ch = curl_init($link);
        $headers = array();
        if (stripos($link,'techcrunch') || stripos($link,'techxplore')) {
            $headers[] = "Pragma: no-cache";
            $headers[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:81.0) Gecko/20100101 Firefox/81.0";
            $headers[] = "Accept: */*";
            $headers[] = "Cookie: BX=cln7ij5fj30vu&b=3&s=p7; GUC=AQABAgFfSzRgK0IgqwSb; A3=d=AQABBJeFMV8CEEzPsLmeylX3qm0arHVOcs0FEgABAgE0S18rYOIjb2UBACAAAAcI_oMxX5ny3Mo&S=AQAAAqpPepWqP7O_q7AdQ1YYzPw; A1=d=AQABBJeFMV8CEEzPsLmeylX3qm0arHVOcs0FEgABAgE0S18rYOIjb2UBACAAAAcI_oMxX5ny3Mo&S=AQAAAqpPepWqP7O_q7AdQ1YYzPw; cmp=v=14&t=1599299686&j=1; rxx=1ne5w2123hq.20wi8jp3&v=1; __tbc=%7Bjzx%7DjGAToaZMxJYLoS7N4KRjDaN3At_VsGrR0T67HFx4CT5gFgvZhow-FOkaZ-T0mMKIq9qafK7iiWw7wTenc-ZBbDovYfGJYrH4xX-T3WS5CNCx_sHH_kkTe0-FyGBfEc_oOzaeIILFhlSTw5lulLLvTw; __pat=-25200000; xbc=%7Bjzx%7DD_xjgXy8Amh8yx-JHbzyyjCj4z_8ccF3GccszsxSQ00wYTD-Khdrd0hnfF4Zh63vCZSEwHzqciFyHZt3oTQtBzTSyPqBE5Wsxa6EMBKwn5yCFZjZErzgCh-yH3tBU7d8CYEogm-Svtbhv68HBOszpB71mtf4DDOry22k8FwGFBzFt2q3WUQWs8RKN8tKwYWjinZ3-4nQuoFvwjJXKbrL_8eLzt_9JNfJsPTIwXa9u_USFj5uAATjJ3T0BmMCd13tvxMCV0p7Zhes-6OEBaZDGWEx1iFY5f0PzXFA5llb8w6z6q7_78h7_1FZzdYPv4ZXrAGj3wpq2WGfDlb-awjxwCqfpEvFctAzcSNoLZU-Z2CtKXaHHGsDcLeFCnL4lBBy-zPntBZH1MuoIErLdNy8CNL1FlXcgSblG3wvz2w6bdYX8X3eyX-9LRL0dtYqWqVz1KMf-tJMIjb1Hs3VOsyVdPO2Rf3RMEFTouFpgJzBpdZjZZusp7q6MburUOi6oFBbVtm9wzVXzIRI8EgKYv6RksyZpppJmjsb828fqDv-7kVuiXnPJ8julPnvoP0rKeUEHRZ9rb3RMOdI8wTcKIh8bIYQ4uHhpZNHPTvwyxpjbMG7QkG9DHMxtdfnzf5dc--8q_HPc4XoTqkPwLPwSYxUsKFq8A3nUGN7-NU8UZV2LB7_vNkk1hM4QKEQy-VcBU5LIZ0j-sqC-BItsCosiRhhQ9RRbgEB8KV1b4xFlhip5oTXu0F2vFXEbez7oXSHLX0CLIn7eCXZSiKZTGlfbAin8q2-D3UDSfTlBrMzjWaabXA; A1S=d=AQABBJeFMV8CEEzPsLmeylX3qm0arHVOcs0FEgABAgE0S18rYOIjb2UBACAAAAcI_oMxX5ny3Mo&S=AQAAAqpPepWqP7O_q7AdQ1YYzPw; EuConsent=CO37zfmO37zfmAOACBENA0CoAP_AAH_AACiQGCNX_TxfLWvj82R5t7tkaYwf8Zynp-wyhgYIM6gBSYIHpBgGu2MQvBXoBiACGBgkMjDBAQFlHCgYCQgAgIhBiTJMYk2MCzNCJJAAilsac0JYGCVsmkHTGYAQxE4EEQAAgABAwQgkwUL4CRISggNJsUohTABCiIApBwCUEIAAGEAISAAlIEARygAAAIBAAAAAAAEBEIIIAAAAAEAAAEAEBAIACIBAACAAaAgAAAAAAsABEgCAAQAkAACIAAAAADAwCAAEAAIAAA; __pvi=%7B%22id%22%3A%22v-2020-09-05-10-54-46-235-PXL9UozIfhdkqqMk-a739729eaffa9f00f4e380cd1d4a806a%22%2C%22domain%22%3A%22.techcrunch.com%22%2C%22time%22%3A1599301552579%7D; spotim_visitId={%22visitId%22:%2218e19a8a-fc08-44e4-aeb3-40acfa598293%22%2C%22creationDate%22:%222020-09-05T09:54:47.198Z%22%2C%22duration%22:1867}";
            $headers[] = "Connection: keep-alive";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        #curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if(!$result = curl_exec($ch)) {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);

        # look for twitter or facebook thumbnails
        $imgpos = stripos($result, ':image" content="');
        $adder = 17;
        if ( $imgpos < 10 ) { $imgpos = stripos($result, 'og:type"><meta content="'); $adder=24; } # cnn-specific
        if ( $imgpos < 10 ) { throw new Exception('no thumbnail defined'); }

        # download thumbnail with resizing if needed
        $original_thumbnail = substr($result, $imgpos+$adder, stripos($result, '"', $imgpos+$adder+5)-($imgpos+$adder));
        if ( stripos($original_thumbnail, 'http') === false ) { $original_thumbnail = 'http:' . $original_thumbnail; }
        if ( stripos($original_thumbnail, '?') > 1 ) { # parameters in url
            $original_thumbnail = substr($original_thumbnail, 0, stripos($original_thumbnail, '?'));
        }
        $beginningoffilename = strrpos($original_thumbnail, '/');
        $extension = substr($original_thumbnail, strrpos($original_thumbnail, '.'));
        if ( strlen($extension) > 6 ) { throw new Exception("wrong extension"); }
        $downloadedthumb = './../thumbs/' . hash('md5', substr($original_thumbnail, $beginningoffilename+1)) . $extension;
        
        # download original one
        $ch2 = curl_init($original_thumbnail);
        $fp = fopen($downloadedthumb, "w");
        curl_setopt($ch2, CURLOPT_FILE, $fp);
        curl_setopt($ch2, CURLOPT_HEADER, 0);
        curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch2);
        curl_close($ch2);
        fclose($fp);

        # resize by default, they usually are normal feature images
        $ifnoerr = resize_image_h($downloadedthumb, $thumbnail, 300); # 300 px of height, we don't need more
        if ( $ifnoerr == false ) { throw new Exception("image processing error"); }

        # clean after operations
        unlink($downloadedthumb);

        return $thumbnail;

    } catch (Exception $e) {
        # no provided thumbnail
        # simple mechanism of consistent choice from 4 thumbnails
        $aletter = strtolower(substr($title,6,1)); # seventh letter
        $def = 1;
        if ( isset($arrofdefs[$aletter]) ) { $def = $arrofdefs[$aletter]; }
        $thumbnail = './../thumbs/default'.$def.'.jpg';
        return $thumbnail;
    }
}
