<?php
	$url = 'http://api.wunderground.com/api/f95a6e63f61d64c3/conditions/q/SK/Nitra.json';

	$json = file_get_contents($url);
	$root = json_decode($json);
	$data = $root->current_observation;

	$content = 
		'var dataWU = {'.
			'"date":"'. date('j.n G:i', $data->observation_epoch) .'",'.
			'"location":"'. $data->display_location->full .'",'.
			'"temperature":"'. $data->temp_c .'",'.
			'"feelslike":"'. $data->feelslike_c .'",'.
			'"humidity":"'. $data->relative_humidity .'",'.
			'"dewpoint":"'. $data->dewpoint_c .'",'.
			'"icon":"'. (!empty($data->icon)? $data->icon_url:"") .'",'.
			'"pressure":"'. $data->pressure_mb .'"'.
		'};';

	/*
	echo '<pre>';
	print_r($root->current_observation);
	echo '</pre>';
	*/

	echo $content;
