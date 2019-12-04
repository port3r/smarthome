<?php
  //header('Content-Type: image/jpeg');
  //readfile('http://192.168.0.73/cgi/jpg/image.cgi');

header('Content-type: multipart/x-mixed-replace; boundary=focus73');
$useragent="Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_0 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Version/4.0 Mobile/7A341 Safari/528.16";

while (@ob_end_clean());
//	header('Content-type: image/jpeg');
// create curl resource
$ch = curl_init();
curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
curl_setopt($ch, CURLOPT_URL, 'http://192.168.0.73/cgi/video/video.cgi');
curl_setopt($ch, CURLOPT_HEADER, 0);

// $output contains the output string
$output = curl_exec($ch);
echo $output;

// close curl resource to free up system resources
curl_close($ch);
