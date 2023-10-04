<?php
/** @var $module \REDCapCon\REDCapConTestModule\REDCapConTestModule */
if(!$module->getUser()->hasDesignRights()) {
	die();
}

$recordCount = (int)$_POST['newRecordCount'];
$instanceCount = (int)$_POST['repeatInstanceCount'];
$formToInstanceMap = [];

if($recordCount) {
	$repeatingForms = $module->getRepeatingForms();
    $recordIdField = $module->getRecordIdField();
    $metadata = $module->getMetadata($project_id);
    $sampleRecord = [[]];
    foreach($metadata as $field => $fieldDetails) {
		## Skip record ID field for sample record
		if($field == $recordIdField ||
                in_array($fieldDetails["field_type"], ["calc","descriptive"]) ||
                strpos($fieldDetails["field_annotation"],"@CALCTEXT") !== false ||
			    strpos($fieldDetails["field_annotation"],"@CALCDATE") !== false) {
			continue;
		}
		$formName = $module->getFormForField($field);

        if(in_array($formName,$repeatingForms)) {
            for($i = 1; $i <= $instanceCount;$i++) {
                if(!array_key_exists($formName,$formToInstanceMap)) {
					$currentRepeatForm = count($formToInstanceMap);
                    for($newInstance = 1; $newInstance <= $instanceCount; $newInstance++) {
                        $instance = $newInstance + $currentRepeatForm * $instanceCount;
                        $sampleRecord[$instance]["redcap_repeat_instance"] = $newInstance;
                        $sampleRecord[$instance]["redcap_repeat_instrument"] = $formName;
                        $formToInstanceMap[$formName][$newInstance] = $instance;
					}
				}
                
                $instance = $formToInstanceMap[$formName][$i];
                
                addItemToSampleData($sampleRecord,$field,$instance,$fieldDetails);
            }
        }
		else {
			addItemToSampleData($sampleRecord,$field,0,$fieldDetails);
		}
    }
    $time1 = 0.0;
    $time2 = 0.0;
	echo "<br /><pre>";
	var_dump($sampleRecord);
	echo "</pre><br />";
	
    ## Generate new record ID for first record
	$newRecord = \REDCap::reserveNewRecordId($project_id);
	$saveData = [];
	for($i = 0; $i < $recordCount; $i++) {
		$startTime = microtime(true);
		$endTime = microtime(true);
        $time1 += $endTime - $startTime;
        $startTime = $endTime;
		foreach($sampleRecord as $instance => $instanceDetails) {
			$saveData[] = $instanceDetails;
			$saveData[count($saveData) - 1][$recordIdField] = $newRecord;
		}
        
        ## If continuing to generate new records, generate next
        ## Doing this here allows us to start with suspected next record ID,
        ## which speeds up reserveNewRecordId greatly
        if($i < ($recordCount - 1)) {
			$newRecord = \REDCap::reserveNewRecordId($project_id, $newRecord + 1);
        }
		$endTime = microtime(true);
		$time2 += $endTime - $startTime;
		$startTime = $endTime;
	}
    echo "Time: $time1 ~ $time2 <Br />";
    
//	echo "<br /><pre>";
//	var_dump($metadata);
//	echo "</pre><br />";
//	echo "<br /><pre>";
//	var_dump($saveData);
//	echo "</pre><br />";
	$results = \REDCap::saveData([
		"project_id" => $project_id,
		"data" => json_encode($saveData),
		"dataFormat" => "json"
	]);
	echo "<br /><pre>";
	var_dump($results);
	echo "</pre><br />";
}

function addItemToSampleData(&$sampleData, $field, $instance, $fieldDetails) {
    global $module, $project_id;
    
	## TODO Need to check field type and validation type
	if($fieldDetails["field_type"] == "text") {
		if(substr($fieldDetails["text_validation_type_or_show_slider_number"],0,5) == "date_") {
			$sampleData[$instance][$field] = "2022-01-01";
		}
		else if(substr($fieldDetails["text_validation_type_or_show_slider_number"], 0,9) == "datetime_") {
			$sampleData[$instance][$field] = "2022-01-01 01:01:01";
		}
        else if($fieldDetails["text_validation_type_or_show_slider_number"] == "time") {
			$sampleData[$instance][$field] = "01:01";
		}
		else if($fieldDetails["text_validation_type_or_show_slider_number"] == "number" || $fieldDetails["text_validation_type_or_show_slider_number"] == "integer") {
			$sampleData[$instance][$field] = floor(rand(1,20));
		}
		else {
			$sampleData[$instance][$field] = "rock";
		}
	}
	else if($fieldDetails["select_choices_or_calculations"] != "") {
		$types = $module->getChoiceLabels($field,$project_id);
		$rand = floor(rand(0,count($types)));
		foreach($types as $rawValue => $label) {
			if($rand == 0) {
				if($fieldDetails["field_type"] == "checkbox") {
					$sampleData[$instance][$field."___".$rawValue] = 1;
				}
				else {
					$sampleData[$instance][$field] = $rawValue;
				}
				break;
			}
			$rand--;
		}
	}
	
}

include_once(\ExternalModules\ExternalModules::getProjectHeaderPath());
?>

<form method="post">
    <div>Number of Records to Generate</div>
	<input type="text" name="newRecordCount" value="<?=$recordCount?>" /><br /><br />

    <div>Instances to Generate for each Record</div>
	<input type="text" name="repeatInstanceCount" value="<?=$instanceCount?>" /><br /><br />
    
    <input type="submit" value="Generate Records" />
</form>
