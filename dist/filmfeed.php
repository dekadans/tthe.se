<?php
$url = 'https://letterboxd.com/dekadans/rss/';
$rss = file_get_contents($url);

header('Cache-control: max-age=10000');
header('Content-type: application/xml');
echo $rss;
