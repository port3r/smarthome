<?php
	$localUrl = 'http://192.168.0.73';
	if (!empty($_GET['redirect']))
	{
		// -- PING
		if ($_GET['redirect'] == 'index')
		{
			$_GET['redirect'] = '';
		}
		
		if ($_GET['redirect'] == 'fb_stop')
		{
			file($localUrl .'/?action=command&command=lr_stop');
			delay(2000);
		}
		
		$content = implode('', file($localUrl .'/?action=command&command='. $_GET['redirect']));
		echo $content;
	}
	else
	{
		$content = implode('', file($localUrl .'/getdata.js'));
		echo $content;
	}	
