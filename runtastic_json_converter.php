<?php

ini_set('memory_limit','-1');

/* When true, any existing export file will be over-written */
$allow_clobber = false;

/* Debug PHP Warnings */

$debug_php_warnings = false;

if(!$debug_php_warnings){
	error_reporting(E_ERROR | E_PARSE);
}

$input_folder  = @$argv[1];
$output_folder = @$argv[2];

if(empty($input_folder)){
	echo "Invalid Input Folder";
	exit;
}

if(empty($output_folder)){
	echo "Invalid Output Folder";
	exit;
}

$input_folder = realpath($input_folder);
$output_folder = realpath($output_folder);
$gps_data_folder = '';

if(!file_exists($input_folder)){
	echo "Invalid Input Folder. Doesn't Exist?";
	exit;
}

if(!file_exists($output_folder)){
	echo "Invalid Output Folder. Doesn't Exist?";
	exit;
}


if(file_exists($input_folder.'/GPS-data/')){
	$gps_data_folder = realpath($input_folder.'/GPS-data/');	
}

if(empty($gps_data_folder)){
	echo "Unable to find GPS-data folder. Doesn't Exist?";
	exit;
}

$json_files = scandir($input_folder);

if($json_files == false){
	echo "Unable to find files in input folder";
	exit;
}

$converted_files = array();

$saved_files = array();

foreach($json_files as $json_file){
	
	try{
		
		if($json_file == '.' || $json_file == '..'){
			continue;
		}
		
		$file_length = strlen($json_file);
		$file_path = realpath("{$input_folder}/{$json_file}");

		if(is_dir($file_path)){
			continue;
		}
	
		if($file_length < 5 || substr($json_file, $file_length - 5) != '.json'){
			echo "File: {$file_path}  does not appear to be a Json File\n";
			continue;
		}
	
		$temp_json_data = file_get_contents($file_path);
		
		if(empty($temp_json_data) || !is_json_string($temp_json_data)){
			echo "File: {$file_path}  is not a valid json data file\n";
			continue;
		}
		
		$temp_json_data = json_decode($temp_json_data, true);
		
		if(!is_array($temp_json_data) || count($temp_json_data) <= 0){
			echo "Data: {$file_path} json data invalid\n";
			continue;
		}
		
		if(!isset($temp_json_data['start_time']) || empty($temp_json_data['start_time'])){
			echo "Data: {$file_path} timestamp appears invalid {$temp_json_data['start_time']}\n";
			continue;			
		}

		if(!isset($temp_json_data['id']) || empty($temp_json_data['id'])){
			echo "Data: {$file_path}, unable to find GPX ID: {$temp_json_data['id']}\n";
			continue;			
		}

		$gpx_file_name = "{$gps_data_folder}/{$temp_json_data['id']}.gpx";

		if(!file_exists($gpx_file_name)){
			echo "Data: {$file_path}, unable to find GPX File: {$gpx_file_name}\n";
			continue;	
		}

		$timestamp = $temp_json_data['start_time'] / 1000;
		$offset = 0;

		if(isset($temp_json_data['start_time_timezone_offset'])){
			$offset = $temp_json_data['start_time_timezone_offset'];
		}
		
		$file_timestamp = date('Y-m-d_H-i-s', ($timestamp + $offset));
		$export_file_name = "runtastic_export_{$file_timestamp}_{$temp_json_data['id']}.gpx";
		$output_file = "{$output_folder}/{$export_file_name}";
		
	
		if(file_exists($output_file) && !$allow_clobber){
			echo "Export File: {$output_file} exists for {$file_path}. To allow clobbering, change variable at top\n";
			exit;
		}
		
		$export_result = copy($gpx_file_name, $output_file);		
		
		if(!$export_result){
			echo "Export File: Unable to write export file to {$output_file} \n";
			break;
		}
		
		echo "Exported Activity {$temp_json_data['id']} to {$output_file}\n";

		unset($temp_json_data, $export_file_name, $gpx_file_name, $output_file);

		
	} catch(Exception $e){
		echo 'Exception: '.$e->getMessage()."\n";
		
	}
		
}

function is_json_string($string){
	
 try{
	
	if(empty($string) || !is_string($string) || is_numeric($string)){
		return false;
	}
	
	@json_decode($string, true);
	
	return (json_last_error() == JSON_ERROR_NONE);
	
} catch(Exception $e){
	return false;	
}

}

