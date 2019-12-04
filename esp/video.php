<?php
  //header('Content-Type: image/jpeg');
  //readfile('http://192.168.0.73/cgi/jpg/image.cgi');

# To stop apache from killing the script
set_time_limit(0);

# Sending the correct header
# The boundary=ipcamera is important.
# You will have to change "ipcamera" to whatever your camera uses to seperate the 
# images. Mine uses "--ipcamera" as seperator. You can omit the leading --, but have
# to use the rest.
header('Content-Type: multipart/x-mixed-replace;boundary=ipcamera');

# Sending the images
# You probably have to adapt the url to the video screen of your cam.
readfile('http://192.168.0.73/cgi/video/video.cgi');
