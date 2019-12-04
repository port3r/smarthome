<?php
  //header('Content-Type: image/jpeg');
  //readfile('http://192.168.0.73/cgi/jpg/image.cgi');
  
  header('content-type: multipart/x-mixed-replace; boundary=--myboundary');
  
  while (@ob_end_clean()); 
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'http://192.168.0.73/cgi/video/video.cgi'); 
  curl_setopt($ch, CURLOPT_HEADER, 0);
  $im = curl_exec($ch);

  echo $im;
  curl_close($ch);
