<?php

$username = 'dekadans';
$url = 'https://letterboxd.com/'. $username .'/rss/';

$rss = file_get_contents($url);

header('Content-type: application/xml');
echo $rss;
