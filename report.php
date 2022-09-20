<?php
namespace REDCapCon\REDCapConTestModule;

/** @var $module REDCapConTestModule */
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

#### Output
include_once(\ExternalModules\ExternalModules::getProjectHeaderPath());
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<div id="chartDiv" style="max-height:400px;max-width:800px;">
	<canvas id="launchesChart"></canvas>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		let ctx = document.getElementById("launchesChart").getContext("2d");
		
        let myChart = new Chart(ctx, {
			type:"bar",
			data: {
				datasets:[{
                    label: "Launches",
					data:<?=json_encode($launchesPerYear)?>
                }]
            }
        });
    });
</script>