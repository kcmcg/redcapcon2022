<?php
/** @var $module \REDCapCon\REDCapConTestModule\REDCapConTestModule */
include_once(\ExternalModules\ExternalModules::getProjectHeaderPath());

$recordData = \REDCap::getData([
	"return_format" => "json",
	"project_id" => $project_id
]);
$recordData = json_decode($recordData,true);

$launchesPerYear = [];

foreach($recordData as $thisRecord) {
	$launchYear = $thisRecord["launch_date"] ?: "";
	$launchYear = (int)substr($launchYear,0,4);
	
	if($launchYear != 0) {
		if(!array_key_exists($launchYear,$launchesPerYear)) {
			$launchesPerYear[$launchYear] = 0;
		}
		
		$launchesPerYear[$launchYear]++;
	}
}

echo "<br /><pre>";
var_dump($launchesPerYear);
echo "</pre><br />";