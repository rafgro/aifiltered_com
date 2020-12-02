<?php

require_once('./tt/TwitterAPIExchange.php');

$settings = array(
    'oauth_access_token' => "1019644220859088896-I4O2Xr1iMMKDjRWm7OYCNFt8ARLwRl",
    'oauth_access_token_secret' => "YW8e22GqXYWdl20Q4N2YsBHXyBC8cs1axIGOlnYThZuNP",
    'consumer_key' => "PQOwegDtaDPKP6OGVQC2gapJg",
    'consumer_secret' => "8xkNinpQXVeVAoLUjCZrMh5VyMcDbff1vQKl2vx7n9yUSmkY2o"
);

/*$url = "https://api.twitter.com/1.1/lists/statuses.json";
$requestMethod = "GET";
$getfield = '?list_id=1301869700566192129&count=5';*/

$url = "https://api.twitter.com/1.1/search/tweets.json";
$requestMethod = "GET";
$getfield = '?q=https%3A%2F%2Fwww.dailymail.co.uk%2Fhealth%2Farticle-6303379%2FPesticide-free-organic-food-lowers-blood-cancer-risk-86.html&result_type=recent&count=100';

$twitter = new TwitterAPIExchange($settings);
var_dump(json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest()));