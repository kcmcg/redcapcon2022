<?php
/** @var $module \REDCapCon\REDCapConTestModule\REDCapConTestModule */
$recordData = \REDCap::getData([
	"return_format" => "json",
	"project_id" => $project_id
]);
$recordData = json_decode($recordData,true);

$launchData = $module->connectToSpaceXAPI("launches");

$recordIdField = $module->getRecordIdField($project_id);
$launchField = "flight_number";
$launchDateField = "launch_date";
$nameField = "name";
$staticFireField = "static_fire";
$videoField = "video";
$detailsField = "details";

foreach($launchData as $thisLaunch) {
	$launchId = $thisLaunch["flight_number"];
	$launchDate = date("Y-m-d",$thisLaunch["date_unix"]);
	$staticFireDate = date("Y-m-d",$thisLaunch["static_fire_date_unix"]);
	$details = $thisLaunch["details"];
	$name = $thisLaunch["name"];
	$video = "";
	if(array_key_exists("links",$thisLaunch)) {
		$video = $thisLaunch["links"]["webcast"];
	}
	
	$existingRecord = false;
	foreach($recordData as $thisRecord) {
		if($thisRecord[$launchField] == $launchId) {
			$existingRecord = $thisRecord[$recordIdField];
		}
	}
	
	$dataRow = [
		$launchField => $launchId,
		$launchDateField => $launchDate,
		$nameField => $name,
		$staticFireField => $staticFireDate,
		$videoField => $video,
		$detailsField => $details
	];
	
	if($existingRecord !== false) {
		$dataRow[$recordIdField] = $existingRecord;
	}
	else {
		$dataRow[$recordIdField] = \REDCap::reserveNewRecordId($project_id);
	}
	
	$saveData = json_encode([$dataRow]);
	
	$results = \REDCap::saveData([
		"project_id" => $project_id,
		"data" => $saveData,
		"dataFormat" => "json"
	]);
	
	if((is_array($results["errors"]) && count($results["errors"]) > 0) || (is_string($results["errors"]) && $results["errors"] != "")) {
		echo "<br /><pre>";
		var_dump($results);
		echo "</pre><br />";
	}
}

