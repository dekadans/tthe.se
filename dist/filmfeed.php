<?php
$url = 'https://letterboxd.com/dekadans/rss/';
$rss = file_get_contents($url);

header('Content-type: application/xml');
echo $rss;
