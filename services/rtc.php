<?php
	$content = 
		'var dataRTC = {'.
			'"now":"'. date('d.m.Y H:i') .'",'.
			'"day":"'. date('d') .'",'.
			'"month":"'. date('m') .'",'.
			'"year":"'. date('y') .'",'.
			'"hours":"'. date('H') .'",'.
			'"minutes":"'. date('i') .'",'.
			'"timestamp":"'. time() .'"'.
		'};';

	echo $content;	