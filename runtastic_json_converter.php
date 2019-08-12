<?php

error_reporting(E_ERROR | E_PARSE);
ini_set('memory_limit','-1');


/* When true, any existing export file will be over-written */
$allow_clobber = true;

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

if(!file_exists($input_folder)){
	echo "Invalid Input Folder. Doesn't Exist?";
	exit;
}

if(!file_exists($output_folder)){
	echo "Invalid Output Folder. Doesn't Exist?";
	exit;
}


if(file_exists($input_folder.'/GPS-data/')){
	$input_folder = realpath($input_folder.'/GPS-data/');
	
}


$json_files = scandir($input_folder);

if($json_files == false){
	echo "Unable to find files in input folder";
	exit;
}

$converted_files = array();

foreach($json_files as $json_file){
	
	try{
		
		if($json_file == '.' || $json_file == '..'){
			continue;
		}
		
		$file_length = strlen($json_file);
		$file_path = realpath("{$input_folder}/{$json_file}");
	
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
		
		$first_gpx_record = $temp_json_data[0];
		
		if(!isset($first_gpx_record['timestamp']) || empty($first_gpx_record['timestamp']) || strtotime($first_gpx_record['timestamp']) === false){
			echo "Data: {$file_path} timestamp appears invalid {$first_gpx_record['timestamp']}\n";
			continue;			
		}
		
		$file_timestamp = date('Y-m-d-H-i-s', strtotime($first_gpx_record['timestamp']));
		$export_file_name = "runtastic_export_{$file_timestamp}.gpx";
		
		$file_export_contents[] = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
										<gpx version=\"1.1\" 
													creator=\"KCRNC Runstatic PHP Exporter: https:\\\\www.kcrnc\\runtastic\\\" 
													xsi:schemaLocation=\"
													http://www.topografix.com/GPX/1/1
													http://www.topografix.com/GPX/1/1/gpx.xsd
													http://www.garmin.com/xmlschemas/GpxExtensions/v3
													http://www.garmin.com/xmlschemas/GpxExtensionsv3.xsd
													http://www.garmin.com/xmlschemas/TrackPointExtension/v1
													http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd\"
													xmlns=\"http://www.topografix.com/GPX/1/1\"
													xmlns:gpxtpx=\"http://www.garmin.com/xmlschemas/TrackPointExtension/v1\"
													xmlns:gpxx=\"http://www.garmin.com/xmlschemas/GpxExtensions/v3\"
													xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">
										<metadata>
											 <time>{$first_gpx_record['timestamp']}</time>
										</metadata>
										
										<trk>
											<trkseg>\n
												";
		
		
		$has_bad_data = false;
		
		foreach($temp_json_data as $temp_track_data){
			
			$temp_longitude  = @($temp_track_data['longitude']);
			$temp_latitude  = @($temp_track_data['latitude']);
			$temp_timestamp = @($temp_track_data['timestamp']);
			$heart_rate = @($temp_track_data['heart_rate']);
			
			if(empty($temp_latitude) || empty($temp_latitude) || empty($temp_timestamp)){
				$has_bad_data = true;
				echo "Data: {$file_path} json data appears invalid\n";
				break;
			}
			
			$temp_timestamp = date("Y-m-d\TH:i:s.000\Z", strtotime($temp_timestamp));
			
			$file_export_contents[] =  "
										<trkpt lon=\"{$temp_longitude}\" lat=\"{$temp_latitude}\">
											<ele>0</ele>
											<time>{$temp_timestamp}</time>
											".(!empty($heart_rate) ? "
												<extensions><gpxtpx:TrackPointExtension><gpxtpx:hr>{$heart_rate}</gpxtpx:hr></gpxtpx:TrackPointExtension></extensions>" : '')
										."
										</trkpt>\n";
			
			
		}
		
		if($has_bad_data){
			continue;
		}
		
		$file_export_contents[] = "\n</trkseg>\n</trk>\n</gpx>";
		
		if(file_exists("{$output_folder}/{$export_file_name}") && !$allow_clobber){
				echo "Export File: {$output_folder}/{$export_file_name} exists. To allow clobbering, change variable at top\n";
				exit;
		}
		
		$export_result = file_put_contents("{$output_folder}/{$export_file_name}", $file_export_contents );
		
		unset($file_export_contents, $temp_json_data, $temp_longitude, $temp_longitude, $temp_timestamp);
		
		if(!$export_result){
			echo "Export File: Unable to write export file to {$output_folder}/{$export_file_name} \n";
			break;
		}
		
		echo "Exported Activity {$first_gpx_record['timestamp']} to {$output_folder}/{$export_file_name}\n";
		
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
