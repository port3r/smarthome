<?php
	$localUrl = 'http://192.168.0.200';

	if (!empty($_GET['redirect']))
	{
		$content = implode('', file($localUrl .'/'. $_GET['redirect']));
		echo $content;
	}

	else
	{
		$content = implode('', file($localUrl .'/getdata.js'));
		echo $content;
	}		