<?php
namespace REDCapCon\REDCapConTestModule;

class REDCapConTestModule extends \ExternalModules\AbstractExternalModule {
	public function __construct() {
		parent::__construct();
		// Other code to run when object is instantiated
	}
	
	public function redcap_add_edit_records_page( $project_id, $instrument, $event_id ) {
	
	}

	
	public function redcap_control_center(  ) {
	
	}

	
	public function redcap_custom_verify_username( $username ) {
	
	}

	
	public function redcap_data_entry_form( $project_id, $record, $instrument, $event_id, $group_id, $repeat_instance ) {
		$data = \REDCap::getData([
			"project_id" => $project_id,
			"records" => $record,
			"fields" => "launch_date",
			"return_format" => "json"
		]);
		
        $time = time();
		$data = json_decode($data,true);
		
		$upcomingLaunch = false;
		foreach($data as $thisRow) {
			$launch = $thisRow["launch_date"] ? strtotime($thisRow["launch_date"]) : 0;
			if($launch > $time && $launch < ($time + 60*60*24*90)) {
				$upcomingLaunch = true;
				break;
			}
		}
		
		if($upcomingLaunch) {
			?>
            <script type="text/javascript">
                $(document).ready(function() {
                    $("#dataEntryTopOptions").after("<div style='background-color:red;color:white;font-weight:bold;max-width:300px;text-align:center;padding:10px'>Upcoming!</div>");
                });
            </script>
			<?php
		}
	}

	
	public function redcap_data_entry_form_top( $project_id, $record, $instrument, $event_id, $group_id, $repeat_instance ) {
 
	}

	
	public function redcap_email( $to, $from, $subject, $message, $cc, $bcc, $fromName, $attachments ) {
	
	}

	
	public function redcap_every_page_before_render( $project_id ) {
	
	}

	
	public function redcap_every_page_top( $project_id ) {
	
	}

	
	public function redcap_pdf( $project_id, $metadata, $data, $instrument, $record, $event_id, $instance ) {
	
	}

	
	public function redcap_project_home_page( $project_id ) {
	
	}

	
	public function redcap_save_record( $project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance ) {
		$data = \REDCap::getData([
			"project_id" => $project_id,
			"records" => $record,
			"fields" => "launch_date",
			"return_format" => "json"
		]);
		$recordData = json_decode($data,true);
        
        $launchDate = reset($recordData)["launch_date"];
        
        $q = $this->queryLogs("SELECT launch_date WHERE record = ? AND project_id = ?", [$record, $project_id]);
        $latestLaunch = false;
        
        while($row = db_fetch_assoc($q)) {
	        $latestLaunch = $row["launch_date"];
		}
        
        if($launchDate != $latestLaunch) {
            if($launchDate > $latestLaunch && $latestLaunch !== false) {
    	        $this->log("Launch Delayed",["record" => $record, "project_id" => $project_id, "launch_date" => $launchDate]);
                \REDCap::logEvent("Launch Delayed","","",$record,null,$project_id);
			}
            else if($latestLaunch === false) {
    			$this->log("New Launch",["record" => $record, "project_id" => $project_id, "launch_date" => $launchDate]);
			}
            else {
				$this->log("Launch Pulled Ahead",["record" => $record, "project_id" => $project_id, "launch_date" => $launchDate]);
			}
		}
	}

	
	public function redcap_survey_acknowledgement_page( $project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance ) {
	
	}

	
	public function redcap_survey_complete( $project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance ) {
	
	}

	
	public function redcap_survey_page( $project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance ) {
	
	}

	
	public function redcap_survey_page_top( $project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance ) {
	
	}

	
	public function redcap_user_rights(  $project_id ) {
		
	}

	
	public function redcap_module_system_enable( $version ) {
		
	}

	
	public function redcap_module_system_disable( $version ) {
		
	}

	
	public function redcap_module_system_change_version( $version, $old_version ) {
		
	}

	
	public function redcap_module_project_enable( $version, $project_id ) {
		
	}

	
	public function redcap_module_project_disable( $version, $project_id ) {
		
	}

	
	public function redcap_module_link_check_display( $project_id, $link ) {
		## TODO SUPERUSER only
		return $link;
	}

	
	public function redcap_module_save_configuration( $project_id ) {
		
	}

	

	public function run_cron( $cronParameters ) {
		$launchData = $this->connectToSpaceXAPI("launches");
		
		$projects = $this->framework->getProjectsWithModuleEnabled();
		
		foreach($projects as $projectId) {
			$this->saveLaunchDataToProject($projectId, $launchData);
		}
	}
	
	public function saveLaunchDataToProject( $project_id, $launchData) {
		$recordData = \REDCap::getData([
			"return_format" => "json",
			"project_id" => $project_id
		]);
		$recordData = json_decode($recordData,true);
		
		$recordIdField = $this->getRecordIdField($project_id);
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
		
	}
	
	public function connectToSpaceXAPI( $dataType ) {
		$ch = curl_init("https://api.spacexdata.com/v4/".$dataType);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$results = curl_exec($ch);
		curl_close($ch);
		
		$data = json_decode($results,true);
		
		return $data;
	}
}